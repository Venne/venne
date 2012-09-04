<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use Venne;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BrowserControl extends Control
{


	/** @var callable */
	protected $loadItems;

	/** @var callable */
	protected $parentCallback;

	/** @var string */
	protected $onActivateLink;


	/**
	 * @param callable $loadItems
	 */
	function __construct($loadItems, $setParent)
	{
		parent::__construct();

		$this->loadItems = $loadItems;
		$this->parentCallback = $setParent;
	}


	public function render()
	{
		$this->getPresenter()->getContext()->assets->assetManager->addJavaScript("@cmsModule/dynatree/jquery.cookie.js");
		$this->getPresenter()->getContext()->assets->assetManager->addJavaScript("@cmsModule/dynatree/jquery.dynatree.js");
		$this->getPresenter()->getContext()->assets->assetManager->addStylesheet("@cmsModule/dynatree/skin-bootstrap/ui.dynatree.css");

		parent::render();
	}


	public function getPages($parent = NULL)
	{
		return $this->loadItems->invoke($parent);
	}


	public function handleGetPages($parent = NULL)
	{
		$data = $this->getPages($parent);
		$this->getPresenter()->sendResponse(new \Nette\Application\Responses\JsonResponse($data));
	}


	public function handleSetParent($from = NULL, $to = NULL, $dropmode = NULL)
	{
		$this->parentCallback->invoke($from, $to, $dropmode);
	}


	/**
	 * @param string $onActivateLink
	 */
	public function setOnActivateLink($onActivateLink)
	{
		$this->onActivateLink = $onActivateLink;
	}


	/**
	 * @return string
	 */
	public function getOnActivateLink()
	{
		return $this->onActivateLink;
	}
}
