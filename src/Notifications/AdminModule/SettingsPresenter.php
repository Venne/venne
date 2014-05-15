<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\AdminModule;

use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Presenter;
use Venne\Notifications\NotificationManager;
use Venne\Notifications\NotificationSettingEntity;
use Venne\System\AdminPresenterTrait;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class SettingsPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var EntityDao */
	private $notificationSettingDao;

	/** @var NotificationManager */
	private $notificationManager;

	/** @var IAdminGridFactory */
	private $adminGridFactory;


	/**
	 * @param EntityDao $notificationSettingDao
	 * @param NotificationManager $notificationManager
	 * @param IAdminGridFactory $adminGridFactory
	 */
	public function __construct(EntityDao $notificationSettingDao, NotificationManager $notificationManager,IAdminGridFactory $adminGridFactory)
	{
		$this->notificationSettingDao = $notificationSettingDao;
		$this->notificationManager = $notificationManager;
		$this->adminGridFactory = $adminGridFactory;
	}


	/**
	 * @return \Venne\Notifications\NotificationManager
	 */
	public function getNotificationManager()
	{
		return $this->notificationManager;
	}


	protected function createComponentTable()
	{
		$admin = $this->adminGridFactory->create($this->notificationSettingDao);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setModel(new Doctrine($this->notificationSettingDao->createQueryBuilder('a')
				->andWhere('a.user = :user')->setParameter('user', $this->presenter->user->identity)
		));

		// columns
		$table->addColumnText('type', 'Type')
			->setSortable()
			->setCustomRender(function(NotificationSettingEntity $entity) {
				return $this->notificationManager->getType($entity->type->type)->getHumanName();
			})
			->getCellPrototype()->width = '25%';

		$table->addColumnText('action', 'Action')
			->setCustomRender(function(NotificationSettingEntity $entity) {
				return $entity->type->action;
			})
			->getCellPrototype()->width = '25%';

		$table->addColumnText('target', 'Target')
			->getCellPrototype()->width = '20%';

		$table->addColumnText('targetKey', 'Target key')
			->getCellPrototype()->width = '10%';

		$table->addColumnText('user', 'Target user')
			->getCellPrototype()->width = '20%';

		// actions
			$table->addActionEvent('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';


		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
