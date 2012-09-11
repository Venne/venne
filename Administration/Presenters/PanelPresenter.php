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
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Content\Entities\RouteEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class PanelPresenter extends BasePresenter
{


	/** @persistent */
	public $elementName;

	/** @persistent */
	public $elementId;

	/** @persistent */
	public $elementRouteId;

	/** @persistent */
	public $elementView;


	/** @var RouteEntity */
	public $route;


	public function startup()
	{
		parent::startup();

		$this->validateControl('content');
		$this->validateControl('panel');
		$this->validateControl('header');
		$this->validateControl('toolbar');
		$this->validateControl('navigation');
		$this['panel']->validateControl('tabs');

		$this->template->elementName = $this->elementName;
		$this->template->elementId = $this->elementId;
		$this->template->elementView = $this->elementView;
		$this->template->elementRouteId = $this->elementRouteId;
		if ($this->elementRouteId) {
			$this->route = $this->getContext()->cms->routeRepository->find($this->elementRouteId);
		}

		$this->invalidateControl('elementView');
		$this->invalidateControl('element');
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	public function handleElement()
	{
		$this->template->showElementView = true;
	}


	public function handleRefreshElement()
	{
		$this->template->showElement = true;
		$this->elementView = NULL;
	}


	public function createComponent($name)
	{
		if (strpos($name, \CmsModule\Content\ElementManager::ELEMENT_PREFIX) === 0) {
			$name = substr($name, strlen(\CmsModule\Content\ElementManager::ELEMENT_PREFIX));
			$name = explode('_', $name, 2);

			$route = $this->getContext()->cms->routeRepository->find($this->elementRouteId);

			/** @var $component \CmsModule\Content\IElement */
			$component = $this->context->cms->elementManager->createInstance($name[1]);
			$component->setRoute($route);
			$component->setKey($name[0]);
			return $component;
		}

		return parent::createComponent($name);
	}
}
