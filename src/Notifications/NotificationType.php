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
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_type")
 */
class NotificationType extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $action;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $message;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type . ' - ' . $this->action . ' - ' . $this->message;
	}

}
