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

use Kdyby\Doctrine\EntityDao;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @Secured
 */
class JobsPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $jobDao;

	/** @var \Venne\Queue\AdminModule\JobFormFactory */
	private $jobFormFactory;

	public function __construct(EntityDao $jobDao, IAdminGridFactory $adminGridFactory, JobFormFactory $jobFormFactory)
	{
		$this->jobDao = $jobDao;
		$this->adminGridFactory = $adminGridFactory;
		$this->jobFormFactory = $jobFormFactory;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$admin = $this->adminGridFactory->create($this->jobDao);
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
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->createForm($this->jobFormFactory, 'Job');

		$admin->connectFormWithAction($form, $table->getAction('edit'));

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
