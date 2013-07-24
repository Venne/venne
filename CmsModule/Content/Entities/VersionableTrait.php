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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property VersionEntity[]|ArrayCollection $versions
 * @property $currentVersion
 */
trait VersionableTrait
{

	/**
	 * @var VersionEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="VersionEntity", indexBy="version" fetch="EXTRA_LAZY", cascade={"persist"})
	 * @ORM\JoinTable(
	 *      joinColumns={@ORM\JoinColumn(referencedColumnName="id")},
	 *      inverseJoinColumns={@ORM\JoinColumn(unique=true)}
	 *      )
	 **/
	private $versions;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $currentVersion;


	/**
	 * @param int $currentVersion
	 */
	public function setCurrentVersion($currentVersion)
	{
		$this->currentVersion = $currentVersion;
	}


	/**
	 * @return int
	 */
	public function getCurrentVersion()
	{
		return $this->currentVersion;
	}


	/**
	 * @param $versions
	 */
	public function setVersions($versions)
	{
		$this->versions = $versions;
	}


	/**
	 * @return VersionEntity[]|ArrayCollection
	 */
	public function getVersions()
	{
		if ($this->versions === NULL) {
			$this->versions = new ArrayCollection;
		}

		return $this->versions;
	}
}
