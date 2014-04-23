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

use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Nette\DateTime;
use Nette\Utils\Finder;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LogsPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var string */
	protected $logDir;


	/**
	 * @param $logDir
	 */
	public function __construct($logDir)
	{
		$this->logDir = $logDir;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionShow($name)
	{
		if (!is_string($name)) { // be aware of arrays and other inputs
			throw new BadRequestException;
		}
		if (preg_match("#^exception-([0-9a-zA-Z\-]+)\.html$#D", $name)) {
			$this->sendResponse(new TextResponse(file_get_contents($this->logDir . '/' . $name)));
		} else {
			// prevent directory traversal
			throw new BadRequestException;
		}
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


	/**
	 * @secured(privilege="remove")
	 */
	public function handleDelete()
	{
		unlink($this->logDir . '/' . $this->getParameter('name'));
		$this->flashMessage($this->translator->translate('Log has been removed'), 'success');
		$this->redirect('this');
	}


	/**
	 * @secured(privilege="remove")
	 */
	public function handleDeleteAll()
	{
		foreach ($this->getFiles() as $item) {
			unlink($this->logDir . '/' . $item['link']);
		}

		$this->flashMessage($this->translator->translate('Logs were removed'), 'success');
		$this->redirect('this');
	}


	protected function getFiles()
	{
		$ret = array();

		foreach (Finder::findFiles('exception*')->in($this->logDir) as $file) {
			$data = explode('-', $file->getFileName());

			$date = "{$data[1]}-{$data[2]}-{$data[3]} {$data[4]}:{$data[5]}:{$data[6]}";
			$info = array('date' => DateTime::from($date), 'hash' => $data[7], 'link' => $file->getFileName());

			$ret[$date] = $info;
		}
		ksort($ret);
		return array_reverse($ret);
	}
}
