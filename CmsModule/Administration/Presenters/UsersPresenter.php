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
use CmsModule\Components\Table\Form;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Forms\UserSocialFormFactory;

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

	/** @var UserSocialFormFactory */
	protected $socialForm;


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


	/**
	 * @param UserSocialFormFactory $socialForm
	 */
	public function injectSocialForm(UserSocialFormFactory $socialForm)
	{
		$this->socialForm = $socialForm;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->userRepository);

		// forms
		$form = $table->addForm($this->form, 'User', NULL, Form::TYPE_LARGE);
		$socialForm = $table->addForm($this->socialForm, 'Social logins', NULL, Form::TYPE_LARGE);

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		// columns
		$table->addColumn('email', 'E-mail')
			->setWidth('60%');
		$table->addColumn('roles', 'Roles')
			->setWidth('40%')
			->setCallback(function ($entity) {
				return implode(", ", $entity->roles);
			});

		// actions
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionEdit('socialLogins', 'Social Logins', $socialForm);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}
}
