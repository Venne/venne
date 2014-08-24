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

use Venne\Forms\IFormFactory;
use Venne\Queue\JobEntity;
use Venne\Queue\JobManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	public function __construct(IFormFactory $formFactory, JobManager $jobManager)
	{
		$this->formFactory = $formFactory;
		$this->jobManager = $jobManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup('Job');

		$form->addSelect('type', 'Type')
			->setItems(array_keys($this->jobManager->getJobs()), false)
			->addRule($form::FILLED);

		$form->addSelect('state', 'State')
			->setItems(array(JobEntity::STATE_SCHEDULED, JobEntity::STATE_IN_PROGRESS, JobEntity::STATE_FAILED), false)
			->addRule($form::FILLED);

		$form->addSelect('priority', 'priority')
			->setItems(array(
				JobEntity::PRIORITY_LOW => 'low',
				JobEntity::PRIORITY_NORMAL => 'normal',
				JobEntity::PRIORITY_HIGH => 'high',
			))
			->addRule($form::FILLED);

//		$form->addDateTime('date', 'Date')
//			->addRule($form::FILLED);
//
//		$form->addDateTime('interval', 'Interval')
//			->addRule($form::FILLED);

		$form->addText('round', 'Round')
			->addCondition($form::FILLED)->addRule($form::INTEGER);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
