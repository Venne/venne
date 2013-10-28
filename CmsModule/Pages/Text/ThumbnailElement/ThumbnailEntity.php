<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\ThumbnailElement;

use CmsModule\Pages\Text\ImageElement\AbstractImageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="thumbnail_element")
 */
class ThumbnailEntity extends AbstractImageEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $description = '';


	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

}
