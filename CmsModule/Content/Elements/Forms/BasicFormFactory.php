<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use DoctrineModule\Forms\Mappers\EntityMapper;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasicFormFactory extends FormFactory
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


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addSelect('mode', 'Share data with', \CmsModule\Content\Entities\ElementEntity::getModes());
		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave($form)
	{
		/** @var $entity \CmsModule\Content\Entities\ElementEntity */
		$entity = clone $form->data;
		$repository = $this->mapper->getEntityManager()->getRepository(get_class($entity));

		foreach ($repository->findBy(array('name' => $entity->getName(), 'layoutconfig' => $entity->getLayoutconfig()->id)) as $item) {
			$repository->delete($item);
		}

		$repository->save($entity);
	}
}
