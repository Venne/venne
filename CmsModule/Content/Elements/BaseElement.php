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
use CmsModule\Content\Entities\LayoutEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Elements\Forms\ClearFormFactory;
use CmsModule\Content\Elements\Forms\BasicFormFactory;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseElement extends Control implements IElement
{

	/** @var LayoutEntity */
	protected $layoutEntity;

	/** @var RouteEntity */
	protected $routeEntity;

	/** @var PageEntity */
	protected $pageEntity;

	/** @var EntityManager */
	protected $entityManager;

	/** @var string */
	protected $name;

	/** @var string */
	protected $nameRaw;

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
		$this->name = Helpers::encodeName($name);
		$this->nameRaw = $name;
	}


	/**
	 * @param \CmsModule\Content\Entities\LayoutEntity $layoutEntity
	 */
	public function setLayout(LayoutEntity $layoutEntity)
	{
		$this->layoutEntity = $layoutEntity;
	}


	/**
	 * @param RouteEntity $routeEntity
	 */
	public function setRoute(RouteEntity $routeEntity)
	{
		$this->routeEntity = $routeEntity;
		$this->pageEntity = $routeEntity->getPage();
		$this->layoutEntity = $routeEntity->getLayout();
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
		$ret->setDefaults($this->nameRaw, $this->layoutEntity, $this->pageEntity, $this->routeEntity);
		return $ret;
	}


	/**
	 * @return \CmsModule\Content\Entities\ElementEntity
	 */
	public function getEntity()
	{
		if (($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layout' => $this->layoutEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_LAYOUT)))) {
			return $ret;
		}

		if ($this->pageEntity && ($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layout' => $this->layoutEntity->id, 'page' => $this->pageEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_PAGE)))) {
			return $ret;
		}

		if ($this->pageEntity && $this->routeEntity && ($ret = $this->getRepository()->findOneBy(array('name' => $this->name, 'layout' => $this->layoutEntity->id, 'page' => $this->pageEntity->id, 'route' => $this->routeEntity->id, 'mode' => \CmsModule\Content\Entities\ElementEntity::MODE_ROUTE)))) {
			return $ret;
		}

		$ret = $this->createEntity();
		if (($entity = $this->getRepository()->findOneBy(array('name' => $this->name, 'layout' => $this->layoutEntity->id)))) {
			$ret->setMode($entity->mode);
		}
		$this->entityManager->persist($ret);
		$this->entityManager->flush($ret);
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
		$form = $this->_clearFormFactory->invoke($this->getEntity());
		return $form;
	}


	protected function createComponentBasicForm()
	{
		$form = $this->_basicFormFactory->invoke($this->createEntity());
		return $form;
	}
}
