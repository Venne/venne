<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette\DateTime;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="version")
 */
class VersionEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	private $version;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	private $data;


	/**
	 * @param $version
	 * @param null $data
	 */
	public function __construct($version, $data = NULL)
	{
		$this->version = $version;
		$this->data = json_encode($data);
	}


	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}


	/**
	 * @return string
	 */
	public function getData()
	{
		return json_decode($this->data);
	}
}

