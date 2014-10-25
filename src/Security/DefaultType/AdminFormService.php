<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\DefaultType;

use Doctrine\ORM\EntityManager;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminFormService extends \Venne\System\DoctrineFormService
{

	public function __construct(
		AdminFormFactory $formFactory,
		EntityManager $entityManager,
		EntityFormMapper $entityFormMapper
	) {
		parent::__construct($formFactory, $entityManager, $entityFormMapper);
	}

	/**
	 * @return string
	 */
	protected function getEntityClassName()
	{
		return User::class;
	}

	protected function save(Form $form, $entity)
	{
		$this->getEntityManager()->beginTransaction();
		try {
			$this->getEntityFormMapper()->save($entity, $form);

			$this->getEntityManager()->persist($entity->getUser());
			$this->getEntityManager()->flush($entity->getUser());
			$this->getEntityManager()->persist($entity);
			$this->getEntityManager()->flush($entity);

			$this->getEntityManager()->commit();
		} catch (\Exception $e) {
			$this->getEntityManager()->rollback();

			$this->error($form, $e);
		}
	}

	protected function error(Form $form, \Exception $e)
	{
		if ($e instanceof \Kdyby\Doctrine\DuplicateEntryException) {
			$form['user']['name']->addError($form->getTranslator()->translate('Name must be unique.'));

			return;
		}

		parent::error($form, $e);
	}

}
