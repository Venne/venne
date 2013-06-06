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

use CmsModule\Security\Entities\UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\DateTime;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\LogRepository")
 * @ORM\Table(name="log", indexes={@ORM\Index(name="created_idx", columns={"created"})})
 */
class LogEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const ACTION_CREATED = 'created';

	const ACTION_UPDATED = 'updated';

	const ACTION_REMOVED = 'removed';

	const ACTION_OTHER = 'other';

	/**
	 * @var \CmsModule\Security\Entities\UserEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Security\Entities\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var \CmsModule\Content\Entities\PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $page;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $target;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $targetKey;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $type;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $action;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $message = '';


	/**
	 * @param \CmsModule\Security\Entities\UserEntity $user
	 */
	public function __construct($user, $target, $targetKey, $action, $type = NULL)
	{
		$this->user = $user;
		$this->target = $target;
		$this->targetKey = $targetKey;
		$this->action = $action;
		$this->type = $type;
		$this->created = new DateTime;
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param string $action
	 */
	public function setAction($action)
	{
		$this->action = $action;
	}


	/**
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}


	/**
	 * @param \DateTime $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}


	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}


	/**
	 * @param string $message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}


	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}


	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param string $target
	 */
	public function setTarget($target)
	{
		$this->target = $target;
	}


	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}


	/**
	 * @param int $targetKey
	 */
	public function setTargetKey($targetKey)
	{
		$this->targetKey = $targetKey;
	}


	/**
	 * @return int
	 */
	public function getTargetKey()
	{
		return $this->targetKey;
	}


	/**
	 * @param \CmsModule\Security\Entities\UserEntity $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}


	/**
	 * @return \CmsModule\Security\Entities\UserEntity
	 */
	public function getUser()
	{
		return $this->user;
	}
}

