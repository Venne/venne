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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutesFormFactory extends FormFactory
{

	/** @var EntityMapper */
	protected $mapper;


	/**
	 * @param EntityMapper $mapper
	 */
	public function __construct(EntityMapper $mapper)
	{
		$this->mapper = $mapper;
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

		$form->addMany('routes', function(\Nette\Forms\Container $container)
		{
			$container->setCurrentGroup($container->getForm()->addGroup('Route' . $container->data->url));
			$container->addText('title', 'Title');
			$container->addText('keywords', 'Keywords');
			$container->addText('description', 'Description');
			$container->addText('author', 'Author');
			$container->addSelect('robots', 'Robots', \CmsModule\Content\Entities\RouteEntity::$robotsValues);
			$container->addCheckbox('copyLayoutFromParent', 'Layout from parent');
			$container->addSelect('layout', 'Layout', $container->form->presenter->context->cms->scannerService->getLayoutFiles())->setPrompt('-------');
		});

		$form->setCurrentGroup();

		$form->addSubmit('_submit', 'Save');
	}
}
