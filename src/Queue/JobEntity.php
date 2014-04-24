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

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="jobs")
 *
 * @property string $type
 * @property array $arguments
 * @property string $state
 * @property int $priority
 * @property \DateTime $date
 * @property \DateTime $dateInterval
 * @property int|NULL $round
 * @property UserEntity $user
 */
class JobEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	const STATE_SCHEDULED = 'scheduled';

	const STATE_IN_PROGRESS = 'in_progress';

	const STATE_FAILED = 'failed';

	const PRIORITY_LOW = 0;

	const PRIORITY_NORMAL = 1;

	const PRIORITY_HIGH = 2;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $type;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $arguments;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $state;

	/**
	 * @var string
	 * @ORM\Column(type="integer")
	 */
	protected $priority;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $date;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $dateInterval;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $round;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;


	/**
	 * @param $type
	 * @param \DateTime $date
	 * @param array $arguments
	 */
	public function __construct($type, \DateTime $date = NULL, array $arguments = array())
	{
		$this->date = $date ? : new \DateTime;
		$this->setArguments($arguments);
		$this->type = $type;
		$this->state = self::STATE_SCHEDULED;
		$this->priority = self::PRIORITY_NORMAL;
	}


	/**
	 * @param $arguments
	 */
	public function setArguments(array $arguments = array())
	{
		$this->arguments = serialize($arguments);
	}


	/**
	 * @return array
	 */
	public function getArguments()
	{
		return $this->arguments ? unserialize($this->arguments) : NULL;
	}

}
