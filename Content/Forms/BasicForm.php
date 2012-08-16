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

use DoctrineModule\Forms\Mapping\EntityFormMapper;
use Doctrine\ORM\EntityManager;
use AssetsModule\Managers\AssetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasicForm extends \CmsModule\Content\Form
{

	/**
	 * Application form constructor.
	 */
	public function create()
	{
		$infoGroup = $this->addGroup('Informations');

		$this->addText('name', 'Name');

		//$this->addCheckbox("mainPage", "Main page");
		if (!$this->entity->translationFor) {
			$this->addManyToOne("parent", "Parent content", NULL, NULL, array("translationFor" => NULL));
		}
		//$this->addText("localUrl", "URL")->setOption("description", "(example: 'contact')")->addRule(self::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/");
		$mainRoute = $this->addOne('mainRoute');
		$mainRoute->setCurrentGroup($infoGroup);
		$mainRoute->addText('localUrl', 'URL');

		$this->addGroup("Dates");
		//$this->addDateTime("created", "Created")->setDefaultValue(new \Nette\DateTime);
		//$this->addDateTime("updated", "Updated")->setDefaultValue(new \Nette\DateTime);
		$this->addDateTime("expired", "Expired");


		// URL can be empty only on main page
		if (!$this->entity->translationFor) {
			$this['mainRoute']["localUrl"]->addConditionOn($this["parent"], ~self::EQUAL, false)->addRule(self::FILLED, "URL can be empty only on main page");
		} else if ($this->entity->translationFor && $this->entity->translationFor->parent) {
			$this['mainRoute']["localUrl"]->addRule(self::FILLED, "URL can be empty only on main page");
		}

		// languages
		/** @var $repository \DoctrineModule\ORM\BaseRepository */
		$repository = $this->entityManager->getRepository('CmsModule\Content\Entities\LanguageEntity');
		if ($repository->createQueryBuilder('a')->select('COUNT(a)')->getQuery()->getSingleScalarResult() > 1) {
			$this->addGroup("Languages");
			$this->addManyToMany("languages", "Content is in");
		}

		$this->setCurrentGroup();
	}

}
