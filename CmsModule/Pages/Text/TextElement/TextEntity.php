<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\TextElement;

use CmsModule\Content\Elements\ExtendedElementEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="text_element")
 */
class TextEntity extends ExtendedElementEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $text = '';


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
