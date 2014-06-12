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

use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;
use Nette\Diagnostics\Debugger;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;
use Venne\Queue\WorkerManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @Secured
 */
class WorkerPresenter extends Presenter
{

	/** @persistent */
	public $id;

	/** @persistent */
	public $debugMode;

	/** @var WorkerManager */
	private $workerManager;


	/**
	 * @param WorkerManager $workerManager
	 */
	public function __construct(WorkerManager $workerManager)
	{
		$this->workerManager = $workerManager;
	}


	public function renderDefault()
	{
		$worker = $this->getWorker();

		if (!$this->debugMode) {
			ignore_user_abort(TRUE);
			header('Cache-Control: no-cache');
			header('Connection: close');
			header('Content-length: 0');
			flush();

			if (function_exists('fastcgi_finish_request')) {
				fastcgi_finish_request();
			}
		}

		ob_start();
		if (!$worker->run() && !$this->debugMode) {
			sleep($worker->getInterval());
		}
		ob_end_clean();

		if (!$this->debugMode) {
			$this->ping();
		}

		$this->terminate();
	}


	public function handleCreate()
	{
		$this->id = $this->workerManager->createWorker()->getId();
		$this->handleStart();
	}


	public function handleStart()
	{
		$this->workerManager->startWorker($this->getWorker());
		$this->ping();
		$this->terminate();
	}


	public function handleStop()
	{
		$this->workerManager->stopWorker($this->getWorker());
		$this->terminate();
	}


	public function handlePause()
	{
		$this->workerManager->pauseWorker($this->getWorker());
		$this->terminate();
	}


	private function ping()
	{
		$ch = curl_init();
		$timeout = 5;

		curl_setopt($ch, CURLOPT_URL, $this->link('//this', array('id' => $this->id)));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_exec($ch);
		curl_close($ch);
	}


	/**
	 * @return \Venne\Queue\Worker
	 * @throws \Nette\Application\BadRequestException
	 */
	private function getWorker()
	{
		try {
			return $this->workerManager->getWokrer($this->id);
		} catch (InvalidArgumentException $e) {
			throw new BadRequestException;
		}
	}

}
