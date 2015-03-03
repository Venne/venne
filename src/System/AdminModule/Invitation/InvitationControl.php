<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule\Invitation;

use Venne\Forms\IFormFactory;
use Venne\System\Invitation\Form\InvitationContainerFactory;
use Venne\System\Invitation\InvitationFacade;
use Venne\System\Invitation\InvitationMapper;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @method onSave(\Venne\System\AdminModule\Invitation\InvitationControl $control)
 */
class InvitationControl extends Control
{

	/** @var \Closure[] */
	public $onSave = array();

	/** @var \Venne\Forms\FormFactory */
	private $formFactory;

	/** @var \Venne\System\Invitation\InvitationFacade */
	private $invitationFacade;

	/** @var \Venne\System\Invitation\Form\InvitationContainerFactory */
	private $invitationContainerFactory;

	/** @var \Venne\System\Invitation\InvitationMapper */
	private $invitationMapper;

	/**
	 * @param \Venne\Forms\IFormFactory $formFactory
	 * @param \Venne\System\Invitation\InvitationFacade $invitationFacade
	 * @param \Venne\System\Invitation\Form\InvitationContainerFactory $invitationContainerFactory
	 * @param \Venne\System\Invitation\InvitationMapper $invitationMapper
	 */
	public function __construct(
		IFormFactory $formFactory,
		InvitationFacade $invitationFacade,
		InvitationContainerFactory $invitationContainerFactory,
		InvitationMapper $invitationMapper
	) {
		$this->formFactory = $formFactory;
		$this->invitationFacade = $invitationFacade;
		$this->invitationContainerFactory = $invitationContainerFactory;
		$this->invitationMapper = $invitationMapper;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$form = $this->formFactory->create();
		$form['invitation'] = $invitationContainer = $this->invitationContainerFactory->create();

		$form->addSubmit('_submit', 'Save')->onClick[] = function () use ($invitationContainer) {
			$this->invitationFacade->saveInvitation(
				$this->invitationMapper->create($invitationContainer->getValues(true))
			);

			$this->onSave($this);
			$this->redirect('this');
		};

		return $form;
	}

	public function render()
	{
		echo $this['form'];
	}

}
