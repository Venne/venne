<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Helpers;

use Nette\Application\Application;
use Venne\Templating\BaseHelper;
use Venne\Templating\ITemplateConfigurator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LatteHelper extends BaseHelper
{

	/** @var ITemplateConfigurator */
	private $templateConfigurator;

	/** @var Application */
	private $application;


	/**
	 * @param Application $application
	 * @param ITemplateConfigurator $templateConfigurator
	 */
	public function __construct(Application $application, ITemplateConfigurator $templateConfigurator)
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

