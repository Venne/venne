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

use Venne;
use Nette\Application\UI\Form;
use DoctrineModule\ORM\BaseRepository;
use CmsModule\Forms\LanguageFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LanguagePresenter extends BasePresenter
{


	/** @persistent */
	public $id;

	/** @var BaseRepository */
	protected $languageRepository;

	/** @var \CmsModule\Forms\LanguageFormFactory */
	protected $form;


	public function __construct(BaseRepository $languageRepository)
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
	 * @secured(privilege="edit")
	 */
	public function actionEdit()
	{

	}


	/**
	 * @secured(privilege="create")
	 */
	public function actionCreate()
	{

	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->languageRepository);
		$table->setPaginator(10);
		$table->enableSorter();

		$table->addColumn('name', 'Name', '50%');
		$table->addColumn('alias', 'Alias', '20%');
		$table->addColumn('short', 'Short', '30%');

		$presenter = $this;
		$table->addAction('edit', 'Edit', function($entity) use ($presenter)
		{
			if (!$presenter->isAjax()) {
				$presenter->redirect('edit', array('id' => $entity->id));
			}
			$this->invalidateControl('content');
			$presenter->payload->url = $presenter->link('edit', array('id' => $entity->id));
			$presenter->setView('edit');
			$presenter->id = $entity->id;
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
			if (!$presenter->isAjax()) {
				$presenter->redirect('default', array('id' => NULL));
			}
			$presenter->invalidateControl('content');
			$presenter->payload->url = $presenter->link('default', array('id' => NULL));
		});

		$table->addGlobalAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
		});

		return $table;
	}


	public function createComponentForm()
	{
		$form = $this->form->invoke($this->languageRepository->createNew());
		$form->onSuccess[] = $this->processForm;
		return $form;
	}


	public function processForm($button)
	{
		$this->flashMessage("Language has been created", "success");

		if (!$this->isAjax() || count($this->context->parameters['website']['languages']) == 0) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit($name)
	{
		$form = $this->form->invoke($this->languageRepository->find($this->id));
		$form->onSuccess[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($button)
	{
		$this->flashMessage("Language has been updated", "success");

		if (!$this->isAjax()) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function delete($entity)
	{
		$repository = $this->languageRepository;
		$repository->delete($entity);

		$config = $this->configService;
		$languages = array();
		foreach ($repository->findAll() as $entity) {
			$languages[] = $entity->alias;
		}
		$config["parameters"]["website"]["languages"] = $languages;
		$config->save();

		$this->flashMessage("Language has been deleted", "success");

		if (!$this->isAjax() || count($languages) == 0) {
			$this->redirect("this", array("id" => NULL));
		}
		$this->invalidateControl('content');
	}


	public function renderDefault()
	{
		$this->template->table = $this->languageRepository->findAll();
	}
}
