<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Invitation;

use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;
use Venne\Security\User\User;
use Venne\System\Registration\Registration;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="invitations")
 * @ORM\EntityListeners({
 *        "\Venne\System\Invitation\InvitationStateListener"
 * })
 */
class Invitation extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\System\Registration\Registration
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\System\Registration\Registration")
	 * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
	 */
	private $registration;

	/**
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User")
	 * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
	 */
	private $author;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $hash;

	/**
	 * @param \Venne\Security\User\User $author
	 * @param \Venne\System\Registration\Registration $registration
	 * @param string $email
	 */
	public function __construct(User $author, Registration $registration, $email)
	{
		$this->author = $author;
		$this->registration = $registration;
		$this->email = (string) $email;
		$this->hash = Random::generate(20);
	}

	/**
	 * @return \Venne\System\Registration\Registration
	 */
	public function getRegistration()
	{
		return $this->registration;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return string
	 */
	public function getHash()
	{
		return $this->hash;
	}

}
