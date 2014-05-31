<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\Random;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="invitations")
 * @ORM\EntityListeners({
 *        "\Venne\System\Listeners\InvitationStateListener"
 * })
 *
 * @property RegistrationEntity $registration
 * @property UserEntity $author
 * @property string $email
 * @property string $hash
 */
class InvitationEntity extends BaseEntity
{

	use IdentifiedEntityTrait;


	/**
	 * @var RegistrationEntity
	 * @ORM\ManyToOne(targetEntity="RegistrationEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $registration;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $author;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $hash;


	public function __construct(UserEntity $author)
	{
		$this->author = $author;
		$this->hash = Random::generate(20);
	}

}
