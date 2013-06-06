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

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Forms\LanguageFormFactory;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\UI\Form;

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


	protected function createComponentTable()
	{
		$_this = $this;
		$repository = $this->languageRepository;
		$admin = new AdminGrid($this->languageRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '50%';

		$table->addColumn('alias', 'Alias')
			->setSortable()
			->getCellPrototype()->width = '20%';

		$table->addColumn('short', 'Short')
			->setSortable()
			->getCellPrototype()->width = '30%';

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$form = $admin->createForm($this->form, 'Language', NULL, \CmsModule\Components\Table\Form::TYPE_LARGE);
			$admin->connectFormWithAction($form, $table->getAction('edit'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));

			$admin->onAttached[] = function (AdminGrid $admin) use ($table, $_this, $repository) {
				if ($admin->formName && !$admin->id) {
					$admin['navbarForm']->onSuccess[] = function () use ($_this, $repository) {
						if ($repository->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult() <= 1) {
							$_this->redirect('this');
						}
					};
				}
			};
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));

			$table->getAction('delete')->onClick[] = function () use ($_this, $repository) {
				if ($repository->createQueryBuilder('a')->select('count(a.id)')->getQuery()->getSingleScalarResult() == 0) {
					$_this->redirect('this');
				}
			};
		}

		return $admin;
	}
}
