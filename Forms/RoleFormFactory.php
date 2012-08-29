<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use DoctrineModule\Forms\Mappers\EntityMapper;
use DoctrineModule\ORM\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleFormFactory extends FormFactory
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
	public function configure(Form $form)
	{
		$form->addGroup("Role");
		$form->addText("name", "Name");
		$form->addManyToOne("parent", "Parent")->setPrompt("root");

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave(Form $form)
	{
		try {
			$this->repository->save($form->data);
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->addError("Role is not unique");
			} else {
				throw $e;
			}
		} catch (\Nette\InvalidArgumentException $e) {
			$form->addError($e->getMessage());
		}
	}
}
