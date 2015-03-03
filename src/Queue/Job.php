<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property string $type
 * @property mixed[] $arguments
 * @property string $state
 * @property int $priority
 * @property \DateTime $date
 * @property \DateTime|null $dateInterval
 * @property int|null $round
 * @property \Venne\Security\User\User $user
 *
 * @ORM\Entity
 * @ORM\Table(name="jobs")
 */
class Job extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	const STATE_SCHEDULED = 'scheduled';

	const STATE_IN_PROGRESS = 'in_progress';

	const STATE_FAILED = 'failed';

	const PRIORITY_LOW = 0;

	const PRIORITY_NORMAL = 1;

	const PRIORITY_HIGH = 2;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $type;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text")
	 */
	private $arguments;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $state;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="integer")
	 */
	private $priority;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $date;

	/**
	 * @var \DateTime|null
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $dateInterval;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $round;

	/**
	 * @var \Nette\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @param string $type
	 * @param \DateTime $date
	 * @param mixed[] $arguments
	 */
	public function __construct($type, \DateTime $date = null, array $arguments = array())
	{
		$this->date = $date ?: new \DateTime;
		$this->setArguments($arguments);
		$this->type = $type;
		$this->state = self::STATE_SCHEDULED;
		$this->priority = self::PRIORITY_NORMAL;
	}

	/**
	 * @param \Venne\Security\User\User|null $user
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;
	}

	/**
	 * @param mixed[] $arguments
	 */
	public function setArguments(array $arguments = array())
	{
		$this->arguments = serialize($arguments);
	}

	/**
	 * @return mixed[]
	 */
	public function getArguments()
	{
		return $this->arguments ? unserialize($this->arguments) : null;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return int
	 */
	public function getPriority()
	{
		return $this->priority;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateInterval()
	{
		return $this->dateInterval;
	}

	/**
	 * @return int|null
	 */
	public function getRound()
	{
		return $this->round;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $state
	 */
	public function updateState($state)
	{
		$this->state = (string) $state;
	}

}
