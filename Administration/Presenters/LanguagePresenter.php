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
use CmsModule\Services\ConfigBuilder;
use Nette\Callback;

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

	/** @var ConfigBuilder */
	protected $configService;

	/** @var Callback */
	protected $form;


	function __construct(BaseRepository $languageRepository, ConfigBuilder $configService, $form)
	{
		$this->languageRepository = $languageRepository;
		$this->configService = $configService;
		$this->form = $form;
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
			} else {
				$presenter->payload->url = $presenter->link('edit', array('id' => $entity->id));
				$presenter->setView('edit');
				$presenter->id = $entity->id;
			}
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
			if (!$presenter->isAjax()) {
				$presenter->redirect('default', array('id' => NULL));
			} else {
				$presenter->payload->url = $presenter->link('default', array('id' => NULL));
			}
		});

		$table->addGlobalAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
		});

		return $table;
	}


	public function createComponentForm($name)
	{
		$repository = $this->languageRepository;
		$entity = $repository->createNew();
		$config = $this->configService;

		$form = $this->form->invoke();
		$form->setEntity($entity);
		$form['_submit']->onClick[] = $this->processForm;
		return $form;
	}


	public function processForm($button)
	{
		$form = $button->getForm();
		$repository = $this->languageRepository;
		$config = $this->configService;

		try {
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Language has been created", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->presenter->flashMessage("Language is not unique", "warning");
				return;
			} else {
				throw $e;
			}
		}

		$languages = array();
		foreach ($repository->findAll() as $entity) {
			$languages[] = $entity->alias;
		}
		$config["parameters"]["website"]["languages"] = $languages;
		$config->save();

		if (!$this->isAjax() || count($this->context->parameters['website']['languages']) == 0) {
			$this->redirect("default");
		}
		$this->payload->url = $this->link('default', array('id' => NULL));
		$this->forward('default', array('id' => NULL, 'do' => ''));
	}


	public function createComponentFormEdit($name)
	{
		$repository = $this->languageRepository;
		$entity = $repository->find($this->id);
		$config = $this->configService;

		$form = $this->form->invoke();
		$form->setEntity($entity);
		$form['_submit']->onClick[] = $this->processFormEdit;
		return $form;
	}


	public function processFormEdit($button)
	{
		$form = $button->getForm();
		$repository = $this->languageRepository;
		$config = $this->configService;

		try {
			$repository->save($form->entity);
			$form->getPresenter()->flashMessage("Language has been updated", "success");
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->presenter->flashMessage("Language is not unique", "warning");
				return;
			} else {
				throw $e;
			}
		}

		$languages = array();
		foreach ($repository->findAll() as $entity) {
			$languages[] = $entity->alias;
		}
		$config["parameters"]["website"]["languages"] = $languages;
		if ($form->entity->id == 1) {
			$config["parameters"]["website"]["defaultLanguage"] = $form->entity->alias;
		}
		$config->save();

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
