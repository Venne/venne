<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_type")
 */
class NotificationType extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $action;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $message;

	public function __construct($type, $action, $message)
	{
		$this->type = $type;
		$this->action = $action;
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type . ' - ' . $this->action . ' - ' . $this->message;
	}

	/**
	 * @return null|string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return null|string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @return null|string
	 */
	public function getMessage()
	{
		return $this->message;
	}



}
