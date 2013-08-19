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

use CmsModule\Content\Control;
use CmsModule\Content\Elements\Forms\BasicFormFactory;
use CmsModule\Content\Elements\Forms\ClearFormFactory;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\LayoutEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\IElement;
use Doctrine\ORM\EntityManager;
use Nette\NotImplementedException;

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

	/** @var  LanguageEntity */
	protected $languageEntity;

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

	/** @var ElementEntity */
	private $element;

	/** @var ExtendedElementEntity */
	private $extendedElementEntity;


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
	 * @param LanguageEntity $languageEntity
	 */
	public function setLanguage(LanguageEntity $languageEntity)
	{
		$this->languageEntity = $languageEntity;
	}


	/**
	 * @return string
	 */
	protected function getEntityName()
	{
		throw new NotImplementedException("Please set entity name in the inherited class.");
	}


	/**
	 * @return \DoctrineModule\Repositories\BaseRepository
	 */
	protected function getElementRepository()
	{
		return $this->entityManager->getRepository('CmsModule\\Content\\Elements\\ElementEntity');
	}


	/**
	 * @return ExtendedElementEntity
	 */
	protected function createEntity()
	{
		$class = '\\' . $this->getEntityName();
		$ret = new $class($this->nameRaw, $this->layoutEntity, $this->pageEntity, $this->routeEntity, $this->languageEntity);
		return $ret;
	}


	/**
	 * @return ElementEntity
	 */
	public function getElement()
	{
		if (!$this->element) {
			$data = array(
				ElementEntity::LANGMODE_SPLIT => array(
					'langMode' => ElementEntity::LANGMODE_SPLIT,
					'language' => $this->languageEntity->id,
				),
				ElementEntity::LANGMODE_SHARE => array(
					'langMode' => ElementEntity::LANGMODE_SHARE,
				),
			);

			foreach ($data as $i) {
				$this->element = $this->getElementRepository()->findOneBy(array(
					'name' => $this->name,
					'layout' => $this->layoutEntity->id,
					'mode' => ElementEntity::MODE_LAYOUT,
				) + $i);

				if ($this->element) {
					break;
				}

				if ($this->pageEntity) {
					$this->element = $this->getElementRepository()->findOneBy(array(
						'name' => $this->name,
						'layout' => $this->layoutEntity->id,
						'page' => $this->pageEntity->id,
						'mode' => ElementEntity::MODE_PAGE,
					) + $i);

					if ($this->element) {
						break;
					}
				}

				if ($this->pageEntity && $this->routeEntity) {
					$this->element = $this->getElementRepository()->findOneBy(array(
						'name' => $this->name,
						'layout' => $this->layoutEntity->id,
						'page' => $this->pageEntity->id,
						'route' => $this->routeEntity->id,
						'mode' => ElementEntity::MODE_ROUTE,
					) + $i);

					if ($this->element) {
						break;
					}
				}
			}

			if (!$this->element) {
				$ret = $this->createEntity();
				if (($entity = $this->getElementRepository()->findOneBy(array('name' => $this->name, 'layout' => $this->layoutEntity->id)))) {
					$ret->setMode($entity->mode);
				}
				$this->entityManager->persist($ret);
				$this->entityManager->flush($ret);
				$this->element = $ret->getElement();
			}
		}
		return $this->element;
	}


	/**
	 * @return ExtendedElementEntity
	 */
	public function getExtendedElement()
	{
		if (!$this->extendedElementEntity) {
			$element = $this->getElement();
			$this->extendedElementEntity = $this->entityManager->getRepository($element->getClass())->findOneBy(array('element' => $element->id));
		}
		return $this->extendedElementEntity;
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
		$form = $this->_clearFormFactory->invoke($this->getExtendedElement());
		return $form;
	}


	protected function createComponentBasicForm()
	{
		$form = $this->_basicFormFactory->invoke($this->getExtendedElement());
		return $form;
	}
}
