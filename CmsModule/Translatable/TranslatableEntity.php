<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\RouteTranslationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait Translatable
{

	/**
	 * @var RouteTranslationEntity[]
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\RouteTranslationEntity", mappedBy="object", fetch="EXTRA_LAZY")
	 */
	protected $translations;

	/**
	 * @param $field
	 * @param LanguageEntity $language
	 * @return mixed
	 */
	protected function getTranslatedValue($field, LanguageEntity $language = NULL)
	{
		$language = $language ? : $this->locale;

		if ($language && $this->translations[$language->id]) {
			if (($ret = $this->translations[$language->id]->{$field}) !== NULL) {
				return $ret;
			}
		}

		return $this->{$field};
	}


	/**
	 * @param $field
	 * @param $value
	 * @param LanguageEntity $language
	 */
	protected function setTranslatedValue($field, $value, LanguageEntity $language = NULL)
	{
		$language = $language ? : $this->locale;

		if ($language) {
			if (!isset($this->translations[$language->id])) {
				$this->translations[$language->id] = new RouteTranslationEntity($this, $language);
				$this->translations[$language->id]->{$field} = $value;
			}
		}

		$this->{$field} = $value;
	}


	public function &__get($name)
	{
		if (substr($name, 0, 10) === 'translated') {
			$name = lcfirst(substr($name, 10));
			return $this->getTranslatedValue($name);
		}

		return parent::__get($name);
	}


	public function __set($name, $value)
	{
		if (substr($name, 0, 10) === 'translated') {
			$name = lcfirst(substr($name, 10));
			return $this->setTranslatedValue($name, $value);
		}

		return parent::__set($name, $value);
	}


	public function __call($name, $args)
	{
		if (substr($name, 0, 13) === 'getTranslated') {
			$name = lcfirst(substr($name, 13));
			return $this->getTranslatedValue($name);
		}


		if (substr($name, 0, 13) === 'setTranslated') {
			$name = lcfirst(substr($name, 13));
			return $this->setTranslatedValue($name, $args[0]);
		}

		return parent::__call($name, $args);
	}
}

