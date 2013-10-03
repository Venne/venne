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
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Repositories\UserRepository;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
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

	/** @var AbstractSchemaManager */
	private $schemaManager;


	public function __construct($checkConnection, User $user, UserRepository $userRepository, LogRepository $logRepository, AbstractSchemaManager $schemaManager)
	{
		$this->user = $user;
		$this->userRepository = $userRepository;
		$this->logRepository = $logRepository;
		$this->checkConnection = $checkConnection;
		$this->schemaManager = $schemaManager;
	}


	public function onSuccess(Form $form)
	{
		if (!Callback::create($this->checkConnection)->invoke() || !$this->schemaManager->tablesExist('users')) {
			return;
		}

		$presenter = $form->presenter;

		$logEntity = new LogEntity($this->user instanceof UserEntity ? $this->user : NULL, 'Venne\\Forms\\Form', NULL, LogEntity::ACTION_OTHER);
		$logEntity->setType($presenter->link('this'));
		$logEntity->setMessage('Configuration has been updated');

		$this->logRepository->save($logEntity);
	}
}
