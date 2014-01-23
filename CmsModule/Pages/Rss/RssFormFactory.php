<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Rss;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RssFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$route = $form->addContainer('mainRoute');

		$group = $form->addGroup('Settings');
		$form->addText('name', 'Name')
			->addRule($form::FILLED);

		$route->setCurrentGroup($group);
		$route->addTextArea('notation', 'Notation');

		$form->addText('items', 'Items')
			->addRule($form::FILLED)
			->addRule($form::INTEGER);

		$form->addGroup('Target');
		$form->addSelect('class', 'Route type', $this->getClasses())
			->setPrompt('--------');
		$form->addManyToMany('targetPages', 'Target pages');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	/**
	 * @return array
	 */
	private function getClasses()
	{
		$entities = array();

		$em = $this->getMapper()->getEntityManager();
		$meta = $em->getMetadataFactory()->getAllMetadata();
		foreach ($meta as $m) {
			if (is_subclass_of($m->getName(), 'CmsModule\Content\Entities\ExtendedRouteEntity')) {
				$entities[$m->getName()] = $m->getName();
			}
		}

		return $entities;
	}

}
