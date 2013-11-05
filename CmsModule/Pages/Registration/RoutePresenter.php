<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Registration;

use CmsModule\Components\RegistrationControl;
use CmsModule\Components\RegistrationControlFactory;
use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @var RegistrationControlFactory */
	protected $registrationControlFactory;

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param \CmsModule\Components\RegistrationControlFactory $registrationControlFactory
	 */
	public function injectRegistrationControlFactory(RegistrationControlFactory $registrationControlFactory)
	{
		$this->registrationControlFactory = $registrationControlFactory;
	}


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->pageRepository->findOneBy(array('special' => 'users'))) {
			$this->template->hideForm = true;
			$this->flashMessage($this->translator->translate('User page does not exist.'), 'warning', false);
		}

		if (!$this->extendedPage->userType) {
			$this->template->hideForm = true;
			$this->flashMessage($this->translator->translate('Userform has not been set'), 'warning', false);
		}
	}


	public function createComponentRegistration()
	{
		$roles = array();

		foreach ($this->extendedPage->roles as $role) {
			$roles[] = $role->name;
		}

		/** @var RegistrationControl $control */
		$control = $this->registrationControlFactory->invoke(
			$this->extendedPage->userType,
			$this->extendedPage->mode,
			$this->extendedPage->socialMode,
			$roles,
			$this->extendedPage->sender,
			$this->extendedPage->mailFrom,
			$this->extendedPage->subject,
			$this->extendedPage->email
		);

		$control->onSuccess[] = $this->registrationSuccess;
		$control->onEnable[] = $this->registrationEnable;
		$control->onError[] = $this->registrationError;

		return $control;
	}


	public function registrationSuccess()
	{
		$this->flashMessage($this->translator->translate('Your registration is complete'), 'success');
		$this->redirect('this');
	}


	public function registrationEnable()
	{
		$this->setView('confirm');
	}


	public function registrationError($control, $message)
	{
		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}


	public function renderDefault()
	{
		if ($this->user->isLoggedIn()) {
			$this->flashMessage($this->translator->translate('You are already logged in.'), 'info');
		}
	}

}
