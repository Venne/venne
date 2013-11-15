<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Login;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="login_page")
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var \CmsModule\Pages\Registration\PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Pages\Registration\PageEntity")
	 * @ORM\joinColumn(onDelete="SET NULL")
	 **/
	protected $registration;


	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $resetEnabled = FALSE;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $resetSubject = 'Password reset';

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $resetText = 'Reset your passord on address %link%.';

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $resetSender = 'Venne:CMS';

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $resetFrom = 'info@venne.cz';


	protected function getSpecial()
	{
		return 'login';
	}


	/**
	 * @param \CmsModule\Pages\Registration\PageEntity $registration
	 */
	public function setRegistration($registration)
	{
		$this->registration = $registration;
	}


	/**
	 * @return \CmsModule\Pages\Registration\PageEntity
	 */
	public function getRegistration()
	{
		return $this->registration;
	}


	/**
	 * @param boolean $resetEnabled
	 */
	public function setResetEnabled($resetEnabled)
	{
		$this->resetEnabled = $resetEnabled;
	}


	/**
	 * @return boolean
	 */
	public function getResetEnabled()
	{
		return $this->resetEnabled;
	}


	/**
	 * @param string $resetFrom
	 */
	public function setResetFrom($resetFrom)
	{
		$this->resetFrom = $resetFrom;
	}


	/**
	 * @return string
	 */
	public function getResetFrom()
	{
		return $this->resetFrom;
	}


	/**
	 * @param string $resetSender
	 */
	public function setResetSender($resetSender)
	{
		$this->resetSender = $resetSender;
	}


	/**
	 * @return string
	 */
	public function getResetSender()
	{
		return $this->resetSender;
	}


	/**
	 * @param string $resetSubject
	 */
	public function setResetSubject($resetSubject)
	{
		$this->resetSubject = $resetSubject;
	}


	/**
	 * @return string
	 */
	public function getResetSubject()
	{
		return $this->resetSubject;
	}


	/**
	 * @param string $resetText
	 */
	public function setResetText($resetText)
	{
		$this->resetText = $resetText;
	}


	/**
	 * @return string
	 */
	public function getResetText()
	{
		return $this->resetText;
	}

}
