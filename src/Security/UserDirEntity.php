<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="user_dir")
 */
class UserDirEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\NamedEntityTrait;

	/**
	 * @var \Venne\Security\UserEntity
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 */
	protected $user;

	public function __construct(UserEntity $user)
	{
		$this->user = $user;
	}

}
