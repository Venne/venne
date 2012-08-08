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
use DoctrineModule\ORM\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguageForm extends BaseDoctrineForm
{



	public function startup()
	{
		parent::startup();
		$this->addGroup("Language");
		$this->addTextWithSelect("name", "Name")->setItems(array("English", "Deutsch", "Čeština"), false)->setOption("description", "(enhlish, deutsch,...)")->addRule(self::FILLED, "Please set name");
		$this->addTextWithSelect("short", "Short")->setItems(array("en", "de", "cs"), false)->setOption("description", "(en, de,...)")->addRule(self::FILLED, "Please set short");
		$this->addTextWithSelect("alias", "Alias")->setItems(array("en", "de", "cs", "www"), false)->setOption("description", "(www, en, de,...)")->addRule(self::FILLED, "Please set alias");
	}

}
