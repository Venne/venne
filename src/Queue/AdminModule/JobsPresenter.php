<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue\AdminModule;

use Doctrine\ORM\EntityManager;
use Venne\Queue\Job;
use Venne\Queue\JobFailedException;
use Venne\Queue\JobManager;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobsPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $jobRepository;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Venne\Queue\AdminModule\JobFormService */
	private $jobFormService;

	public function __construct(
		EntityManager $entityManager,
		JobManager $jobManager,
		IAdminGridFactory $adminGridFactory,
		JobFormService $jobFormService
	)
	{
		$this->jobRepository = $entityManager->getRepository(Job::class);
		$this->jobManager = $jobManager;
		$this->adminGridFactory = $adminGridFactory;
		$this->jobFormService = $jobFormService;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$admin = $this->adminGridFactory->create($this->jobRepository);
		$table = $admin->getTable();

		$table->addColumnText('type', 'Type')
			->getCellPrototype()->width = '35%';

		$table->addColumnText('state', 'State')
			->getCellPrototype()->width = '15%';

		$table->addColumnText('priority', 'Priority')
			->getCellPrototype()->width = '10%';

		$table->addColumnDate('date', 'Date')
			->getCellPrototype()->width = '15%';

		$table->addColumnDate('dateInterval', 'Interval')
			->getCellPrototype()->width = '15%';

		$table->addColumnDate('round', 'Round')
			->getCellPrototype()->width = '10%';

		// actions
		$table->addActionEvent('run', 'Run')->onClick[] = function ($id) {
			$job = $this->jobRepository->find($id);

			try {
				$this->jobManager->scheduleJob($job, JobManager::PRIORITY_REALTIME);
				$this->flashMessage($this->getTranslator()->translate('Job has been done.'), 'success');
			} catch (JobFailedException $e) {
				$this->flashMessage($this->getTranslator()->translate('Job failed.'), 'warning');
			}

			$this->redirect('this');
		};
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('job', 'Job', function (Job $job = null) {
			return $this->jobFormService->getFormFactory($job !== null ? $job->getId() : null);
		});

		$admin->connectFormWithAction($form, $table->getAction('edit'));

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
