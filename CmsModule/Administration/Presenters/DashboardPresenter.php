<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Content\Repositories\LogRepository;
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Repositories\UserRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DashboardPresenter extends BasePresenter
{

	/** @var LogRepository */
	protected $logRepository;

	/** @var UserRepository */
	protected $userRepository;


	/**
	 * @param LogRepository $logRepository
	 */
	public function injectLogRepository(LogRepository $logRepository)
	{
		$this->logRepository = $logRepository;
	}


	/**
	 * @param \CmsModule\Security\Repositories\UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		if ($this->user->isLoggedIn() && !$this->user->identity instanceof UserEntity) {
			$this->flashMessage('You are logged as superadministrator. It can be potencialy dangerous.', 'warning', TRUE);
		}
	}


	public function renderDefault()
	{
		$this->template->logRepository = $this->logRepository;
		$this->template->userRepository = $this->userRepository;
	}
}
