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
use DoctrineModule\ORM\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DirFormFactory extends FormFactory
{

	/** @var EntityMapper */
	protected $mapper;

	/** @var BaseRepository */
	protected $repository;


	/**
	 * @param EntityMapper $mapper
	 * @param BaseRepository $repository
	 */
	public function __construct(EntityMapper $mapper, BaseRepository $repository)
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
		);
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addText('name', 'Name');
		$form->addManyToOne('parent', 'Parent');

		$form->addSubmit('_submit', 'Save');
	}


	public function handleValidate(Form $form)
	{
		$this->repository->save($form->data);
	}
}
