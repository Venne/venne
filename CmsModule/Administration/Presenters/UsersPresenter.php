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
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Forms\UserFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 * @persistent (vp)
 */
class UsersPresenter extends BasePresenter
{


	/** @persistent */
	public $page;

	/** @var \DoctrineModule\Repositories\BaseRepository */
	protected $userRepository;

	/** @var UserFormFactory */
	protected $form;


	/**
	 * @param BaseRepository $userRepository
	 */
	public function __construct(BaseRepository $userRepository)
	{
		parent::__construct();

		$this->userRepository = $userRepository;
	}


	/**
	 * @param UserFormFactory $form
	 */
	public function injectForm(UserFormFactory $form)
	{
		$this->form = $form;
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->userRepository);
		$table->setPaginator(10);

		// forms
		$form = $table->addForm($this->form, 'User');

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		// columns
		$table->addColumn('email', 'E-mail', '60%');
		$table->addColumn('roles', 'Roles', '40%', function ($entity) {
			return implode(", ", $entity->roles);
		});

		// actions
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}
}
