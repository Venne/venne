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
use Kdyby\Doctrine\Entities\BaseEntity;

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
 * @property \Venne\Security\User $user
 *
 * @ORM\Entity
 * @ORM\Table(name="jobs")
 */
class Job extends \Kdyby\Doctrine\Entities\BaseEntity
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
	protected $type;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="text")
	 */
	protected $arguments;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $state;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="integer")
	 */
	protected $priority;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $date;

	/**
	 * @var \DateTime|null
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $dateInterval;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $round;

	/**
	 * @var \Nette\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

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

}
