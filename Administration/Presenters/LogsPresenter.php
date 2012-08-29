<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LogsPresenter extends BasePresenter
{

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
	public function handleDelete()
	{
		unlink($this->logDir . "/" . $this->getParameter("name"));
		$this->flashMessage("Log has been removed", "success");
		$this->redirect("this");
	}


	/**
	 * @secured(privilege="show")
	 */
	public function handleDeleteAll()
	{
		foreach ($this->getFiles() as $item) {
			unlink($this->logDir . "/" . $item["link"]);
		}

		$this->flashMessage("Logs were removed", "success");
		$this->redirect("this");
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionShow()
	{
		$this->sendResponse(new \Nette\Application\Responses\TextResponse(file_get_contents($this->logDir . "/" . $this->getParameter("name"))));
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->files = $this->getFiles();
	}


	protected function getFiles()
	{
		$ret = array();

		foreach (\Nette\Utils\Finder::findFiles("exception*")->in($this->logDir) as $file) {
			$data = explode("-", $file->getFileName());

			$date = "{$data[1]}-{$data[2]}-{$data[3]} {$data[4]}:{$data[5]}:{$data[6]}";
			$info = array("date" => \Nette\DateTime::from($date), "hash" => $data[7], "link" => $file->getFileName());

			$ret[$date] = $info;
		}
		ksort($ret);
		return array_reverse($ret);
	}
}
