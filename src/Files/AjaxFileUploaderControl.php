<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files;

use Venne\System\Content\Control;
use Venne\System\Content\Repositories\DirRepository;
use Nette\Http\SessionSection;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AjaxFileUploaderControl extends Control
{

	/** @var array */
	public $onFileUpload;

	/** @var array */
	public $onAfterFileUpload;

	/** @var array */
	public $onError;

	/** @var array */
	public $onSuccess;

	/** @var string */
	protected $ajaxDir;

	/** @var string */
	protected $ajaxPath;

	/** @var array */
	private $errors = array();

	/** @var SessionSection */
	private $sessionSection;


	/**
	 * @param \Nette\ComponentModel\IContainer $ajaxDir
	 * @param null $ajaxPath
	 * @param SessionSection $sessionSection
	 */
	public function __construct($ajaxDir, $ajaxPath, SessionSection $sessionSection)
	{
		parent::__construct();

		$this->ajaxDir = $ajaxDir;
		$this->ajaxPath = $ajaxPath;
		$this->sessionSection = $sessionSection;
	}


	/**
	 * @param $file
	 * @param \Exception $e
	 */
	protected function addError($class, $message, $code)
	{
		if (!isset($this->sessionSection->errors)) {
			$this->sessionSection->errors = array();
		}

		$this->sessionSection->errors[] = array(
			'class' => $class,
			'message' => $message,
			'code' => $code,
		);
	}


	protected function cleanErrors()
	{
		$this->sessionSection->errors = array();
	}


	/**
	 * @return array
	 */
	public function getErrors()
	{
		if (!isset($this->sessionSection->errors)) {
			$this->sessionSection->errors = array();
		}

		return $this->sessionSection->errors;
	}


	public function handleUpload()
	{
		$this->cleanErrors();

		if (!file_exists($this->ajaxDir)) {
			mkdir($this->ajaxDir, 0777, TRUE);
		}

		ob_start();
		new \UploadHandler(array(
			'upload_dir' => $this->ajaxDir . '/',
			'upload_url' => $this->ajaxPath . '/',
			'script_url' => $this->ajaxPath . '/',

		));
		$data = json_decode(ob_get_clean(), TRUE);

		foreach ($data['files'] as $file) {
			try {
				$this->onFileUpload($this, $file['name']);
			} catch (\Exception $e) {
				$this->addError(get_class($e), $e->getMessage(), $e->getCode());
			}

			try {
				$this->onAfterFileUpload($this, $file['name']);
			} catch (\Exception $e) {
				$this->addError(get_class($e), $e->getMessage(), $e->getCode());
			}
		}

		$this->presenter->terminate();
	}


	public function handleSuccess()
	{
		if (count($this->getErrors())) {
			$this->onError($this);
		} else {
			$this->onSuccess($this);
		}
	}
}
