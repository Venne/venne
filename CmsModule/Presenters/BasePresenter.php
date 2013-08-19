<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Presenters;

use CmsModule\Pages\Users\ExtendedUserEntity;
use CmsModule\Pages\Users\UserEntity;
use Doctrine\ORM\EntityManager;
use Nette\InvalidStateException;
use Venne\Application\UI\Presenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read EntityManager $entityManager
 * @property-read ExtendedUserEntity $extendedUser
 */
abstract class BasePresenter extends Presenter
{

	/** @persistent */
	public $lang;

	const MODE_NORMAL = NULL;

	const MODE_EDIT = 1;

	/** @persistent */
	public $mode;

	/** @var EntityManager */
	private $entityManager;

	/** @var ExtendedUserEntity */
	private $extendedUser;


	/**
	 * @param EntityManager $entityManager
	 */
	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}


	/**
	 * @return ExtendedUserEntity
	 * @throws InvalidStateException
	 */
	public function getExtendedUser()
	{
		if (!$this->extendedUser) {
			if (!$this->user->isLoggedIn()) {
				throw new InvalidStateException("User is not logged in.");
			}

			if (!$this->user->identity instanceof UserEntity) {
				throw new InvalidStateException("User must be instance of 'CmsModule\Pages\Users\UserEntity'.");
			}

			$this->extendedUser = $this->getEntityManager()
				->getRepository($this->user->identity->class)
				->findOneBy(array('user' => $this->user->identity->id));
		}
		return $this->extendedUser;
	}


	/**
	 * @return void
	 */
	protected function startup()
	{
		parent::startup();

		// Language
		$this->checkLanguage();

		// Setup translator
		if (($translator = $this->context->getByType('Nette\Localization\ITranslator', FALSE)) !== NULL) {
			$translator->setLang($this->lang);
		}

		// mode
		if ($this->mode && !$this->getUser()->isLoggedIn()) {
			$this->mode = self::MODE_NORMAL;
		}
	}


	protected function checkLanguage()
	{
	}
}
