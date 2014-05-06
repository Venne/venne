<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Comments;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="comments", indexes={
 * @ORM\Index(name="tag_idx", columns={"tag"}),
 * @ORM\Index(name="read_idx", columns={"read"}),
 * })
 */
class CommentEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $text = '';

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $updated;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $author;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $recipient;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $tag;


	public function __construct()
	{
		$this->created = new \DateTime;
	}

}

