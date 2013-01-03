<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements\Entities;

use Venne;
use Doctrine\ORM\Mapping as ORM;
use CmsModule\Content\Entities\ElementEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="textElement")
 * @ORM\DiscriminatorEntry(name="textElement")
 */
class TextEntity extends ElementEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $text;


	public function __construct()
	{
		$this->text = '';
	}


	/**
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}


	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}
}
