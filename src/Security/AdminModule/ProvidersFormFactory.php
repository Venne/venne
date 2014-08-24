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

use Nette\Forms\Container;
use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ProvidersFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	public function __construct(IFormFactory $formFactory, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->securityManager = $securityManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup();
		$user = $form->addContainer('user');
		$providers = $user->addDynamic('loginProviders', function (Container $container) use ($form) {
			$container->setCurrentGroup($form->addGroup('Login provider'));
			$container->addSelect('type', 'Type')->setItems($this->securityManager->getLoginProviders(), false);
			$container->addText('uid', 'UID');

			$container->addSubmit('remove', 'Remove')->addRemoveOnClick();
		});

		$providers->addSubmit('add', 'Add')->addCreateOnClick();

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
