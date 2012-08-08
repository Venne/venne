<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Helpers;

use Venne;
use Nette;
use Nette\Object;
use Venne\Templating\BaseHelper;
use ITemplateConfigurator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LatteHelper extends BaseHelper
{


	/** @var ITemplateConfigurator */
	protected $templateConfigurator;

	/** @var \Nette\Application\Application */
	protected $application;



	/**
	 * @param \Nette\Application\Application $application
	 */
	public function __construct(\Nette\Application\Application $application, \Venne\Templating\ITemplateConfigurator $templateConfigurator)
	{
		parent::__construct();
		$this->application = $application;
		$this->templateConfigurator = $templateConfigurator;
	}



	/**
	 * @param $text
	 * @return string
	 */
	public function run($text)
	{
		$template = $this->application->getPresenter()->createTemplate("\Nette\Templating\Template");
		$template->setSource($text);
		return $template->__toString();
	}

}

