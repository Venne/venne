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

use Nette\Application\UI\Presenter;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\System\AdminPresenterTrait;
use Venne\System\InvitationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class InvitationPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var InvitationFormFactory */
	private $invitationFormFactory;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;


	public function __construct(InvitationFormFactory $invitationFormFactory, FormFactoryFactory $formFactoryFactory)
	{
		$this->invitationFormFactory = $invitationFormFactory;
		$this->formFactoryFactory = $formFactoryFactory;
	}


	public function createComponentForm()
	{
		$form = $this->formFactoryFactory->create($this->invitationFormFactory)
			->setEntity(new InvitationEntity)
			->create();

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage($this->translator->translate('Invitation has been sent', 'success'));
		$this->redirect('this');
	}

}
