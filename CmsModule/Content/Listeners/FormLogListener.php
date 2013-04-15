<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Listeners;

use CmsModule\Content\Entities\LogEntity;
use CmsModule\Content\Repositories\LogRepository;
use CmsModule\Forms\ILoggableForm;
use CmsModule\Security\Repositories\UserRepository;
use Nette\Callback;
use Nette\Security\User;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FormLogListener
{

	/** @var User */
	private $user;

	/** @var UserRepository */
	private $userRepository;

	/** @var LogRepository */
	private $logRepository;

	/** @var callable */
	private $checkConnection;


	public function __construct($checkConnection, User $user, UserRepository $userRepository, LogRepository $logRepository)
	{
		$this->user = $user;
		$this->userRepository = $userRepository;
		$this->logRepository = $logRepository;
		$this->checkConnection = $checkConnection;
	}


	public function onSuccess(Form $form)
	{
		if (!Callback::create($this->checkConnection)->invoke()) {
			return;
		}

		$presenter = $form->presenter;

		$logEntity = new LogEntity($this->getUser(), 'Venne\\Forms\\Form', NULL, LogEntity::ACTION_OTHER);
		$logEntity->setType($presenter->link('this'));
		$logEntity->setMessage('Configuration has been updated');

		$this->logRepository->save($logEntity);
	}


	private function getUser()
	{
		return $this->userRepository->findOneBy(array('email' => $this->user->identity->id));
	}
}
