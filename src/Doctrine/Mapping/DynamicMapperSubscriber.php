<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Mapping;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DynamicMapperSubscriber implements EventSubscriber
{


	/** @var bool */
	private $_l = FALSE;

	/** @var string */
	private $_lName;


	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::loadClassMetadata,
		);
	}


	/**
	 * @param LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();

		if ($this->_l) {
			if (Strings::endsWith($meta->associationMappings[$this->_lName]['targetEntity'], '::dynamic')) {
				$meta->associationMappings[$this->_lName]['targetEntity'] = $this->getTargetEntity($meta->name, $this->_l);
			}

			return;
		}

		foreach ($meta->getAssociationNames() as $name) {

			if (!Strings::endsWith($meta->associationMappings[$name]['targetEntity'], '::dynamic')) {
				continue;
			}

			$em = $args->getEntityManager();
			$target = $this->getTargetEntity($meta, $name);

			$this->_l = $meta->name;
			$this->_lName = $meta->associationMappings[$name]['inversedBy'];

			if (!$this->_lName) {
				$this->_lName = $meta->associationMappings[$name]['mappedBy'];
			}

			if ($this->_lName) {
				$targetMeta = $em->getClassMetadata($target);
			}

			$this->_l = FALSE;

			$meta->associationMappings[$name]['targetEntity'] = $target;

			if ($this->_lName) {
				$targetMeta->associationMappings[$this->_lName]['targetEntity'] = $meta->name;
			}

		}
	}


	/**
	 * @param $meta
	 * @param $association
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	private function getTargetEntity($meta, $association)
	{
		$method = 'get' . ucfirst($association) . 'Name';

		if (($ret = call_user_func(array($meta->name, $method))) === NULL) {
			throw new InvalidArgumentException("Entity '{$meta->name}' must implemented method '{$method}'.");
		}
		if (!class_exists($ret)) {
			throw new InvalidArgumentException("Class '{$ret}' does not exist.");
		}
		return $ret;
	}

}

