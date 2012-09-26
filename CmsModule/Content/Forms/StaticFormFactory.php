<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use DoctrineModule\Forms\Mappers\EntityMapper;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class StaticFormFactory extends FormFactory
{

	/** @var EntityMapper */
	protected $mapper;

	/** @var PageRepository */
	protected $repository;


	/**
	 * @param EntityMapper $mapper
	 */
	public function __construct(EntityMapper $mapper, PageRepository $repository)
	{
		$this->mapper = $mapper;
		$this->repository = $repository;
	}


	protected function getMapper()
	{
		return $this->mapper;
	}


	protected function getControlExtensions()
	{
		return array(
			new \DoctrineModule\Forms\ControlExtensions\DoctrineExtension(),
			new \CmsModule\Content\ControlExtension(),
		);
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup()->setOption('class', 'full');
		$form->addContentEditor("text", NULL, Null, 20);

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave($form)
	{
		$this->repository->save($form->data);
	}
}
