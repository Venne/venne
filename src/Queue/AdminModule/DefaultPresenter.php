<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue\AdminModule;

use Grido\DataSources\ArraySource;
use Grido\Grid;
use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;
use Venne\System\Components\INavbarControlFactory;
use Venne\Queue\WorkerManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @Secured
 */
class DefaultPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var WorkerManager */
	private $workerManager;

	/** @var INavbarControlFactory */
	private $navbarControlFactory;


	/**
	 * @param WorkerManager $workerManager
	 * @param INavbarControlFactory $navbarControlFactory
	 */
	public function __construct(WorkerManager $workerManager, INavbarControlFactory $navbarControlFactory)
	{
		$this->workerManager = $workerManager;
		$this->navbarControlFactory = $navbarControlFactory;
	}


	protected function createComponentTable()
	{
		$table = new Grid;
		$table->setModel(new ArraySource($this->workerManager->getWorkers()));

		$table->addColumnText('id', 'ID')
			->getCellPrototype()->width = '40%';

		$table->addColumnText('state', 'State')
			->setCustomRender(function($data) {
				$lastCheck = \DateTime::createFromFormat('Y-m-d H:i:s', $data['lastCheck']);
				$lastCheck->modify('+' . ($this->workerManager->getInterval() + 7) . ' second');

				return ($lastCheck < new \DateTime) ? 'break' : $data['state'];
			})
			->getCellPrototype()->width = '20%';

		$table->addColumnText('lastCheck', 'Last check')
			->getCellPrototype()->width = '30%';

		$table->addColumnText('lastJob', 'Last job')
			->getCellPrototype()->width = '30%';

		$table->addActionEvent('debug', 'Debug')
			->onClick[] = $this->tableDebugClick;

		$table->addActionEvent('restart', 'Restart')
			->onClick[] = $this->tableRestartClick;

		$table->addActionEvent('stop', 'Stop')
			->onClick[] = $this->tableStopClick;

		return $table;
	}


	protected function createComponentNavbar()
	{
		$control = $this->navbarControlFactory->create();
		$control->addSection('new', 'Run new Worker')->onClick[] = $this->navbarWorkerClick;
		return $control;
	}


	public function tableDebugClick($id)
	{
		$this->redirect(':Queue:Admin:Worker:', array('id' => $id, 'debugMode' => TRUE));
	}


	public function tableStopClick($id)
	{
		$worker = $this->workerManager->getWokrer($id);
		$this->workerManager->stopWorker($worker);
		$this->redirect('this');
	}


	public function tableRestartClick($id)
	{
		$ch = curl_init();
		$timeout = 5;

		curl_setopt($ch, CURLOPT_URL, $this->link('//:Queue:Admin:Worker:', array('id' => $id)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		curl_close($ch);

		$this->redirect('this');
	}


	public function navbarWorkerClick()
	{
		$ch = curl_init();
		$timeout = 5;

		curl_setopt($ch, CURLOPT_URL, $this->link('//:Queue:Admin:Worker:', array('do' => 'create')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		curl_close($ch);

		$this->redirect('this');
	}

}
