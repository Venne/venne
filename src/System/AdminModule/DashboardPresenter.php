<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Kdyby\Doctrine\EntityDao;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DashboardPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $logDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	public function __construct(
		EntityDao $logDao,
		EntityDao $userDao
	)
	{
		$this->logDao = $logDao;
		$this->userDao = $userDao;
	}

	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		if ($this->user->isLoggedIn() && !$this->user->identity instanceof UserEntity) {
			$this->flashMessage($this->translator->translate('You are logged as superadministrator. It can be potencialy dangerous.'), 'warning', true);
		}
	}

	public function renderDefault()
	{
		$this->template->logRepository = $this->logDao;
		$this->template->userRepository = $this->userDao;
	}

}
