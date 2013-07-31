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
use CmsModule\Forms\TagFormFactory;
use CmsModule\Pages\Tags\TagRepository;
use Nette\Application\UI\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class TagPresenter extends BasePresenter
{

	/** @var TagRepository */
	protected $tagRepository;

	/** @var TagFormFactory */
	protected $formFactory;

	/** @var TagsPageEntity */
	protected $extendedPage;


	/**
	 * @param TagRepository $tagRepository
	 */
	public function injectTagRepository(TagRepository $tagRepository)
	{
		$this->tagRepository = $tagRepository;
	}


	/**
	 * @param TagFormFactory $formFactory
	 */
	public function injectForm(TagFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function startup()
	{
		parent::startup();

		if (($this->extendedPage = $this->tagRepository->findOneBy(array())) === NULL) {
			$this->flashMessage('Tag page does not exist.', 'warning');
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->extendedPage = $this->extendedPage;
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
		$admin = new AdminGrid($this->tagRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$repository = $this->tagRepository;
			$form = $admin->createForm($this->formFactory, 'Tag', function () use ($repository) {
				return $repository->createNew();
			}, \CmsModule\Components\Table\Form::TYPE_LARGE);
			$admin->connectFormWithAction($form, $table->getAction('edit'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		return $admin;
	}
}
