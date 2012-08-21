<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WebsiteForm extends BaseConfigForm
{


	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		$this->addGroup("Global meta informations");
		$this->addText("title", "Title")->setOption("description", "(%s - separator, %t - local title)");
		$this->addText("titleSeparator", "Title separator");
		$this->addText("keywords", "Keywords");
		$this->addText("description", "Description");
		$this->addText("author", "Author");
		$this->addSelect('layout', 'Layout', $this->presenter->context->cms->scannerService->getLayoutFiles())->setPrompt('-------');

		$this->addGroup("System");
		$this->addTextWithSelect("routePrefix", "Route prefix");

		$url = $this->presenter->context->httpRequest->url;
		$domain = trim($url->host . $url->scriptPath, "/") . "/";
		$params = array("<lang>/", "//$domain<lang>/", "//<lang>.$domain");

		$this['routePrefix']->setItems($params, false);

		parent::attached($obj);
	}
}
