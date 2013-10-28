<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\ImageElement;

use CmsModule\Content\Elements\ExtendedElementEntity;
use CmsModule\Content\Entities\DirEntity;
use CmsModule\Content\Entities\FileEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AbstractImageEntity extends ExtendedElementEntity
{

	/**
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\DirEntity", cascade={"all"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $dir;

	/**
	 * @var FileEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\FileEntity", cascade={"all"}, orphanRemoval=true)
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $image;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $alt = '';

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $width;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $height;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $format = 0;

	/**
	 * @var int
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $type;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $hideWidth = FALSE;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $hideHeight = FALSE;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $hideFormat = FALSE;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $hideType = FALSE;


	public function startup()
	{
		parent::startup();

		$this->dir = new DirEntity;
		$this->dir->setInvisible(TRUE);
		$this->dir->setName(Strings::webalize(get_class($this)) . Strings::random());
	}


	/**
	 * @param FileEntity $image
	 */
	public function setImage(FileEntity $image = NULL)
	{
		$this->image = $image;

		if ($this->image) {
			$this->image->setParent($this->dir);
			$this->image->setInvisible(TRUE);
		}
	}


	/**
	 * @return FileEntity
	 */
	public function getImage()
	{
		return $this->image;
	}


	/**
	 * @param string $alt
	 */
	public function setAlt($alt)
	{
		$this->alt = $alt;
	}


	/**
	 * @return string
	 */
	public function getAlt()
	{
		return $this->alt;
	}


	/**
	 * @param int $height
	 */
	public function setHeight($height)
	{
		$this->height = $height ? $height : NULL;
	}


	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->height;
	}


	/**
	 * @param int $width
	 */
	public function setWidth($width)
	{
		$this->width = $width ? $width : NULL;
	}


	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->width;
	}


	/**
	 * @param int $format
	 */
	public function setFormat($format)
	{
		$this->format = $format;
	}


	/**
	 * @return int
	 */
	public function getFormat()
	{
		return $this->format;
	}


	/**
	 * @param int $type
	 */
	public function setType($type)
	{
		$this->type = $type ? $type : NULL;
	}


	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param boolean $hideFormat
	 */
	public function setHideFormat($hideFormat)
	{
		$this->hideFormat = $hideFormat;
	}


	/**
	 * @return boolean
	 */
	public function getHideFormat()
	{
		return $this->hideFormat;
	}


	/**
	 * @param boolean $hideHeight
	 */
	public function setHideHeight($hideHeight)
	{
		$this->hideHeight = $hideHeight;
	}


	/**
	 * @return boolean
	 */
	public function getHideHeight()
	{
		return $this->hideHeight;
	}


	/**
	 * @param boolean $hideType
	 */
	public function setHideType($hideType)
	{
		$this->hideType = $hideType;
	}


	/**
	 * @return boolean
	 */
	public function getHideType()
	{
		return $this->hideType;
	}


	/**
	 * @param boolean $hideWidth
	 */
	public function setHideWidth($hideWidth)
	{
		$this->hideWidth = $hideWidth;
	}


	/**
	 * @return boolean
	 */
	public function getHideWidth()
	{
		return $this->hideWidth;
	}

}
