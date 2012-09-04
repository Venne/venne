<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Repositories;

use Venne;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Services\ConfigBuilder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguageRepository extends BaseRepository
{


	/** @var ConfigBuilder */
	protected $configBuilder;


	/**
	 * @param EntityMapper $mapper
	 * @param BaseRepository $repository
	 */
	public function injectConfigBuilder(ConfigBuilder $configBuilder)
	{
		$this->configBuilder = $configBuilder;
	}


	public function save($entity, $withoutFlush = self::FLUSH)
	{
		$ret = parent::save($entity, $withoutFlush);
		$this->generateConfig();
		return $ret;
	}


	/**
	 * @param LanguageEntity $entity
	 * @param bool $withoutFlush
	 * @return mixed
	 */
	public function delete($entity, $withoutFlush = self::FLUSH)
	{
		foreach ($entity->getPages() as $page) {
			if (count($page->getLanguages()) == 1) {
				throw new \Nette\InvalidArgumentException("Language '{$entity->name}' require some pages which have content only in this language.");
			}
		}

		$ret = parent::delete($entity, $withoutFlush);
		$this->generateConfig();
		return $ret;
	}


	protected function generateConfig()
	{
		$config = $this->configBuilder;
		$languages = array();
		$i = 0;
		foreach ($this->findAll() as $entity) {
			if ($i++ == 0) {
				$config["parameters"]["website"]["defaultLanguage"] = $entity->alias;
			}
			$languages[] = $entity->alias;
		}
		$config["parameters"]["website"]["languages"] = $languages;
		$config->save();
	}
}
