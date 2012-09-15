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
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Forms\LanguageFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LanguagePresenter extends BasePresenter
{


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


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->languageRepository);
		$table->setPaginator(10);
		$table->enableSorter();

		// forms
		$form = $table->addForm($this->form, 'Language');

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		// columns
		$table->addColumn('name', 'Name', '50%');
		$table->addColumn('alias', 'Alias', '20%');
		$table->addColumn('short', 'Short', '30%');

		// actions
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}
}
