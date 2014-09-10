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
use Venne\DataTransfer\DataTransferManager;

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

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityDao $logDao,
		EntityDao $userDao,
		DataTransferManager $dataTransferManager
	)
	{
		$this->logDao = $logDao;
		$this->userDao = $userDao;
		$this->dataTransferManager = $dataTransferManager;
	}

	public function renderDefault()
	{
		$this->template->userDto = $this->dataTransferManager
			->createQuery(UserDto::getClassName(), function () {
				return $this->userDao->find($this->getUser()->getIdentity()->getId());
			})
			->enableCache(sprintf('#%s', $this->getUser()->getIdentity()->getId()))
			->fetch();
	}

}
