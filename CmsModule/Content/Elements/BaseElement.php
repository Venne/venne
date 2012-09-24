<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements;

use Venne;
use CmsModule\Content\Control;
use CmsModule\Content\IElement;
use CmsModule\Content\Entities\LayoutconfigEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Elements\Forms\ClearFormFactory;
use CmsModule\Content\Elements\Forms\BasicFormFactory;
use Doctrine\ORM\EntityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseElement extends Control implements IElement
{

	/** @var LayoutconfigEntity */
	protected $layoutconfigEntity;

	/** @var RouteEntity */
	protected $routeEntity;

	/** @var PageEntity */
	protected $pageEntity;

	/** @var EntityManager */
	protected $entityManager;

	/** @var int */
	protected $name;

	/** @var ClearFormFactory */
	protected $_clearFormFactory;

	/** @var BasicFormFactory */
	protected $_basicFormFactory;


	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager, ClearFormFactory $clearFormFactory, BasicFormFactory $basicFormFactory)
	{
		parent::__construct();

		$this->entityManager = $entityManager;
		$this->_clearFormFactory = $clearFormFactory;
		$this->_basicFormFactory = $basicFormFactory;
	}


	/**
	 * @return array
	 */
	public function getViews()
	{
		return array(
			'basicSetup' => 'Data setup',
			'clear' => 'Clear data',
		);
	}


	/**
	 * @param $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @param RouteEntity $routeEntity
	 */
	public function setRoute(RouteEntity $routeEntity)
	{
		$this->routeEntity = $routeEntity;
		$this->pageEntity = $routeEntity->getPage();
		$this->layoutconfigEntity = $routeEntity->getLayoutconfig();
	}


	/**
	 * @return string
	 */
	protected function getEntityName()
	{
		throw new \Nette\NotImplementedException("Please set entity name in the inherited class.");
	}



	/**
	 * @return \DoctrineModule\Repositories\BaseRepository
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository('CmsModule\\Content\\Entities\\ElementEntity');
	}


	/**
	 * @return \CmsModule\Content\Entities\ElementEntity
	 */
	protected function createEntity()
	{
		$class = '\\' . $this->getEntityName();
		$ret = new $class;
		$ret->setDefaults($this->name, $this->routeEntity);
		return $ret;
	}


	/**
	 * @return \CmsModule\Content\Entities\ElementEntity
	 */
	protected function getEntity()
	{
		if (($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layoutconfig' => $this->layoutconfigEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_LAYOUT)))) {
			return $ret;
		}

		if (($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layoutconfig' => $this->layoutconfigEntity->id, 'page' => $this->pageEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_PAGE)))) {
			return $ret;
		}

		if (($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layoutconfig' => $this->layoutconfigEntity->id, 'page' => $this->pageEntity->id, 'route' => $this->routeEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_ROUTE)))) {
			return $ret;
		}

		$ret = $this->createEntity();
		if (($entity = $this->getRepository()->findOneBy(array('name' => $this->name, 'layoutconfig' => $this->layoutconfigEntity->id)))) {
			$ret->setMode($entity->mode);
		}
		return $ret;
	}


	public function render()
	{
	}


	public function renderBasicSetup()
	{
		echo $this['basicForm']->render();
	}


	public function renderClear()
	{
		echo $this['clearForm']->render();
	}


	protected function createComponentClearForm()
	{
		return $this->_clearFormFactory->invoke($this->getEntity());
	}


	protected function createComponentBasicForm()
	{
		return $this->_basicFormFactory->invoke($this->createEntity());
	}
}
