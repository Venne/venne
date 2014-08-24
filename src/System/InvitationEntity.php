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
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property \Venne\System\RegistrationEntity $registration
 * @property \Venne\Security\UserEntity $author
 * @property string $email
 * @property string $hash
 *
 * @ORM\Entity
 * @ORM\Table(name="invitations")
 * @ORM\EntityListeners({
 *        "\Venne\System\Listeners\InvitationStateListener"
 * })
 */
class InvitationEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\System\RegistrationEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\System\RegistrationEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $registration;

	/**
	 * @var \Venne\Security\UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $author;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $hash;

	public function __construct(UserEntity $author)
	{
		$this->author = $author;
		$this->hash = Random::generate(20);
	}

}
