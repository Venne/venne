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

use Venne;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageTagRepository")
 * @ORM\Table(name="pageTag")
 */
class PageTagEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const TAG_LOGIN = 'login';

	const TAG_ERROR_403 = 'error_403';

	const TAG_ERROR_404 = 'error_404';

	const TAG_ERROR_405 = 'error_405';

	const TAG_ERROR_410 = 'error_410';

	const TAG_ERROR_500 = 'error_500';

	/** @var array */
	protected static $tags = array(
		self::TAG_LOGIN => 'login page',
		self::TAG_ERROR_403 => 'Forbidden page',
		self::TAG_ERROR_404 => 'Not Found page',
		self::TAG_ERROR_405 => 'Method Not Allowed',
		self::TAG_ERROR_410 => 'Gone',
		self::TAG_ERROR_500 => 'Internal Server Error page',
	);


	/**
	 * @var PageEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 */
	protected $page;

	/**
	 * @var string
	 * @ORM\Column(type="string", unique=true)
	 */
	protected $tag;


	public function __construct()
	{
		parent::__construct();

		$this->tag = '';
	}


	/**
	 * @param PageEntity|NULL $page
	 */
	public function setPage(PageEntity $page = NULL)
	{
		$this->page = $page;
	}


	/**
	 * @return PageEntity|NULL
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param string $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}


	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}


	/**
	 * @return array
	 */
	public static function getTags()
	{
		return self::$tags;
	}
}
