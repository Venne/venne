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
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Forms\TagFormFactory;
use CmsModule\Pages\Tags\TagEntity;
use CmsModule\Pages\Tags\TagRepository;
use CmsModule\Pages\Tags\PageEntity;
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

	/** @var PageRepository */
	protected $pageRepository;

	/** @var TagFormFactory */
	protected $formFactory;

	/** @var TagsPageEntity */
	protected $extendedPage;


	/**
	 * @param TagRepository $tagRepository
	 */
	public function injectLanguageRepository(TagRepository $tagRepository)
	{
		$this->tagRepository = $tagRepository;
	}


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
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

		if (($page = $this->pageRepository->findOneBy(array('special' => 'tags'))) === NULL) {
			$this->flashMessage('Tag page does not exist.', 'warning');
		} else {
			$this->extendedPage = $this->getEntityManager()->getRepository($page->class)->findOneBy(array('page' => $page));
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
			->getCellPrototype()->width = '50%';

//		$table->addColumnDate('created', 'Created')
//			->setCustomRender(function($entity){
//				return $entity->route->created;
//			})
//			->setSortable()
//			->getCellPrototype()->width = '25%';
//
//		$table->addColumnDate('updated', 'Updated')
//			->setCustomRender(function($entity){
//				return $entity->route->updated;
//			})
//			->setSortable()
//			->getCellPrototype()->width = '25%';

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$extendedPage = $this->extendedPage;
			$form = $admin->createForm($this->formFactory, 'Tag', function () use ($extendedPage) {
				return new TagEntity($extendedPage);
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
