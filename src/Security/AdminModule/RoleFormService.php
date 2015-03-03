<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Doctrine\ORM\EntityManager;
use Venne\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Venne\Security\Role\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleFormService extends \Venne\System\DoctrineFormService
{

	public function __construct(
		RoleFormFactory $formFactory,
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
		return Role::class;
	}

	protected function error(Form $form, \Exception $e)
	{
		if ($e instanceof \Kdyby\Doctrine\DuplicateEntryException) {
			$form['name']->addError($form->getTranslator()->translate('Name must be unique.'));

			return;
		}

		parent::error($form, $e);
	}

}
