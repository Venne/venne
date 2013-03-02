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

use CmsModule\Content\Repositories\LanguageRepository;
use Nette\Application\UI\Form;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Forms\LanguageFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LanguagePresenter extends BasePresenter
{


	/** @var LanguageRepository */
	protected $languageRepository;

	/** @var \CmsModule\Forms\LanguageFormFactory */
	protected $form;


	/**
	 * @param \CmsModule\Content\Repositories\LanguageRepository $languageRepository
	 */
	public function injectLanguageRepository(LanguageRepository $languageRepository)
	{
		$this->languageRepository = $languageRepository;
	}


	/**
	 * @param \CmsModule\Forms\LanguageFormFactory $form
	 */
	public function injectForm(LanguageFormFactory $form)
	{
		$this->form = $form;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionRemove()
	{
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->languageRepository);

		// forms
		$form = $table->addForm($this->form, 'Language', NULL, \CmsModule\Components\Table\Form::TYPE_LARGE);

		// navbar
		if ($this->isAuthorized('create')) {
			$table->addButtonCreate('create', 'Create new', $form, 'file');
		}

		// redirect on first language
		$_this = $this;
		$repository = $this->languageRepository;
		$table->onAttached[] = function () use ($table, $_this, $repository) {
			if ($table->createForm) {
				$table['createForm']->onSuccess[] = function () use ($_this, $repository) {
					if ($repository->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult() <= 1) {
						$_this->redirect('this');
					}
				};
			}
		};

		// columns
		$table->addColumn('name', 'Name')
			->setWidth('50%');
		$table->addColumn('alias', 'Alias')
			->setWidth('20%');
		$table->addColumn('short', 'Short')
			->setWidth('30%');

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addActionEdit('edit', 'Edit', $form);
		}

		if ($this->isAuthorized('remove')) {
			$table->addActionDelete('delete', 'Delete')->onSuccess[] = function () use ($_this, $repository) {
				if ($repository->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult() == 0) {
					$_this->redirect('this');
				}
			};

			// global actions
			$table->setGlobalAction($table['delete']);
		}

		return $table;
	}
}
