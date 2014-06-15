<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\AdminModule;

use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class EmailPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @persistent */
	public $type;


	public function __construct()
	{
		$this->autoCanonicalize = FALSE;
		$this->setSecured(FALSE);
	}


	protected function startup()
	{
		$this->setView(str_replace('\\', '.', trim($this->type, '\\')) . '@' . $this->action);

		parent::startup();
	}


	public function formatLayoutTemplateFiles()
	{
		$ret = array();

		if ($this->templateLocator) {
			$ret = $this->templateLocator->formatLayoutTemplateFiles($this);
		} elseif ($this instanceof Presenter) {
			$ret = parent::formatLayoutTemplateFiles();
		}

		foreach ($ret as $key => $val) {
			$ret[$key] = substr($val, 0, -13) . '@email.latte';
		}

		return $ret;
	}

}
