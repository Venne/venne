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

use Doctrine\ORM\EntityManager;
use Grido\DataSources\Doctrine;
use Venne\Notifications\NotificationManager;
use Venne\Notifications\NotificationSetting;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SettingsPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationSettingRepository;

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Venne\Notifications\AdminModule\NotificationSettingFormService */
	private $notificationSettingFormService;

	public function __construct(
		EntityManager $entityManager,
		NotificationManager $notificationManager,
		IAdminGridFactory $adminGridFactory,
		NotificationSettingFormService $notificationSettingFormService
	) {
		$this->notificationSettingRepository = $entityManager->getRepository(NotificationSetting::class);
		$this->notificationManager = $notificationManager;
		$this->adminGridFactory = $adminGridFactory;
		$this->notificationSettingFormService = $notificationSettingFormService;
	}

	/**
	 * @return \Venne\Notifications\NotificationManager
	 */
	public function getNotificationManager()
	{
		return $this->notificationManager;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$qb = $this
			->notificationSettingRepository
			->createQueryBuilder('a')
			->andWhere('a.user = :user')->setParameter('user', $this->presenter->user->identity->getId());

		$admin = $this->adminGridFactory->create($this->notificationSettingRepository);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setModel(new Doctrine($qb));

		// columns
		$table->addColumnText('user', 'User')
			->getCellPrototype()->width = '15%';

		$table->addColumnText('type', 'Type')
			->setSortable()
			->setCustomRender(function (NotificationSetting $entity) {
				return $entity->type !== null
					? $this->notificationManager->getType($entity->type->type)->getHumanName()
					: '';
			})
			->getCellPrototype()->width = '20%';

		$table->addColumnText('action', 'Action')
			->setCustomRender(function (NotificationSetting $entity) {
				return $entity->type !== null
					? $entity->type->action
					: '';
			})
			->getCellPrototype()->width = '15%';

		$table->addColumnText('target', 'Target')
			->getCellPrototype()->width = '20%';

		$table->addColumnText('targetKey', 'Target key')
			->getCellPrototype()->width = '10%';

		$table->addColumnText('targetUser', 'Target user')
			->getCellPrototype()->width = '20%';

		// actions
		$event = $table->addActionEvent('edit', 'Edit');
		$event->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('notification', 'Notification setting', function (NotificationSetting $notificationSetting = null) {
			return $this->notificationSettingFormService->getFormFactory($notificationSetting !== null ? $notificationSetting->getId() : null);
		});

		$admin->connectFormWithAction($form, $event);

		// Toolbar
		$toolbar = $admin->getNavbar();
		$section = $toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $section);

		$deleteEvent = $table->addActionEvent('delete', 'Delete');
		$deleteEvent->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($deleteEvent);

		return $admin;
	}

}
