<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Grido\DataSources\ArraySource;
use Grido\Grid;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Utils\DateTime;
use Nette\Utils\Finder;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\Components\INavbarControlFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LogsPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var string */
	private $logDir;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Venne\System\Components\INavbarControlFactory */
	private $navbarControlFactory;

	/**
	 * @param string $logDir
	 * @param \Venne\System\Components\AdminGrid\IAdminGridFactory $adminGridFactory
	 */
	public function __construct($logDir, IAdminGridFactory $adminGridFactory, INavbarControlFactory $navbarControlFactory)
	{
		$this->logDir = $logDir;
		$this->adminGridFactory = $adminGridFactory;
		$this->navbarControlFactory = $navbarControlFactory;
	}

	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->files = $this->getFiles();
	}

	/**
	 * @secured
	 */
	public function actionRemove()
	{
	}

	protected function createComponentNavbar()
	{
		$control = $this->navbarControlFactory->create();
		$control->addSection('delete', 'Delete all', 'remove')->onClick[] = $this->handleDeleteAll;

		return $control;
	}

	/**
	 * @return \Grido\Grid
	 */
	protected function createComponentTable()
	{
		$table = new Grid;
		$table->setModel(new ArraySource($this->getFiles()));

		$table->addColumnText('id', 'Link')
			->setSortable()
			->getCellPrototype()->width = '70%';

		$table->addColumnDate('date', 'Date', 'Y.m.d H:i:s')
			->setSortable()
			->getCellPrototype()->width = '30%';

		$table->addActionEvent('show', 'Show')->onClick[] = $this->handleShow;
		$table->addActionEvent('delete', 'Delete')->onClick[] = $this->handleDelete;

		return $table;
	}

	/**
	 * @secured(privilege="show")
	 */
	public function handleShow($name)
	{
		if (!is_string($name)) { // be aware of arrays and other inputs
			throw new BadRequestException;
		}
		if (preg_match('#^exception-([0-9a-zA-Z\-]+)\.html$#D', $name)) {
			$this->sendResponse(new TextResponse(file_get_contents($this->logDir . '/' . $name)));
		} else {
			// prevent directory traversal
			throw new BadRequestException;
		}
	}

	/**
	 * @secured(privilege="remove")
	 */
	public function handleDelete($id)
	{
		unlink($this->logDir . '/' . $id);
		$this->flashMessage($this->translator->translate('Log has been removed.'), 'success');
		$this->redirect('this');
	}

	/**
	 * @secured(privilege="remove")
	 */
	public function handleDeleteAll()
	{
		foreach ($this->getFiles() as $item) {
			unlink($this->logDir . '/' . $item['id']);
		}

		$this->flashMessage($this->translator->translate('Logs were removed.'), 'success');
		$this->redirect('this');
	}

	/**
	 * @return mixed[]
	 */
	protected function getFiles()
	{
		$ret = array();

		foreach (Finder::findFiles('exception-*')->in($this->logDir) as $file) {
			$data = explode('-', $file->getFileName());

			$date = vsprintf('%s-%s-%s %s:%s:%s', $data);
			$info = array('date' => DateTime::from($date), 'id' => $file->getFileName());

			$ret[$date] = $info;
		}
		ksort($ret);

		return array_reverse($ret);
	}

}
