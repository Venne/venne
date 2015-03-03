<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

use Doctrine\ORM\EntityManager;
use Venne\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Tracy\Debugger;
use Venne\Forms\Controls\EventControl;
use Venne\Forms\FormFactory;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class DoctrineFormService extends \Nette\Object
{

	const SUBMIT_NAME = '_submit';

	const SUBMIT_CAPTION = 'Save';

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\DoctrineForms\EntityFormMapper */
	private $entityFormMapper;

	public function __construct(IFormFactory $formFactory, EntityManager $entityManager, EntityFormMapper $entityFormMapper)
	{
		$this->formFactory = $formFactory;
		$this->entityManager = $entityManager;
		$this->entityFormMapper = $entityFormMapper;
	}

	/**
	 * @param mixed|null $primaryKey
	 * @return \Venne\Forms\FormFactory
	 */
	public function getFormFactory($primaryKey = null)
	{
		return $this->createFormFactory($this->formFactory, $this->getEntity($primaryKey));
	}

	/**
	 * @param \Venne\Forms\IFormFactory $formFactory
	 * @param \Venne\Doctrine\Entities\BaseEntity $entity
	 * @return \Venne\Forms\FormFactory
	 */
	protected function createFormFactory(IFormFactory $formFactory, BaseEntity $entity)
	{
		return new FormFactory(function () use ($entity, $formFactory) {
			$form = $formFactory->create();
			$form->setCurrentGroup();
			$form->addSubmit(static::SUBMIT_NAME, static::SUBMIT_CAPTION);

			$form['_eventControl'] = $eventControl = new EventControl('_eventControl');

			$eventControl->onAttached[] = function () use ($form, $entity) {
				$this->load($form, $entity);
			};

			$form->onSuccess[] = function (Form $form) use ($entity) {
				if ($form->isSubmitted() === $form[self::SUBMIT_NAME]) {
					$this->save($form, $entity);
				}
			};

			return $form;
		});
	}

	/**
	 * @param mixed|null $primaryKey
	 * @return \Venne\Doctrine\Entities\BaseEntity
	 */
	protected function getEntity($primaryKey)
	{
		return $primaryKey !== null
			? $this->getRepository()->find($primaryKey)
			: $this->createEntity();
	}

	/**
	 * @return \Venne\Doctrine\Entities\BaseEntity
	 */
	protected function createEntity()
	{
		$class = new \ReflectionClass($this->getEntityClassName());

		return $class->newInstanceArgs();
	}

	/**
	 * @return string
	 */
	abstract protected function getEntityClassName();

	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getRepository()
	{
		return $this->entityManager->getRepository($this->getEntityClassName());
	}

	protected function load(Form $form, $entity)
	{
		$this->entityFormMapper->load($entity, $form);
	}

	protected function save(Form $form, $entity)
	{
//		try {
			$this->entityFormMapper->save($entity, $form);
			$this->entityManager->persist($entity);
			$this->entityManager->flush($entity);
//		} catch (\Exception $e) {
//			$this->error($form, $e);
//		}
	}

	protected function error(Form $form, \Exception $e)
	{
		Debugger::log($e);
		$form->addError($form->getTranslator()->translate('Something went wrong'));
	}

	/**
	 * @return \Kdyby\DoctrineForms\EntityFormMapper
	 */
	protected function getEntityFormMapper()
	{
		return $this->entityFormMapper;
	}

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager()
	{
		return $this->entityManager;
	}

	/**
	 * @return \Venne\Forms\IFormFactory
	 */
	protected function getRawFormFactory()
	{
		return $this->formFactory;
	}

}
