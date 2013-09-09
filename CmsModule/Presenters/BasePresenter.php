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

	/** @var EntityManager */
	private $entityManager;

	/** @var ExtendedUserEntity */
	private $extendedUser;

	/** @var bool|NULL */
	private $_isPanelOpened;


	public function handleOpenPanel()
	{
		$panelSection = $this->getPanelSession();
		$panelSection->opened = TRUE;
		$this->redirect('this');
	}


	public function handleClosePanel()
	{
		$panelSection = $this->getPanelSession();
		$panelSection->opened = FALSE;
		$this->redirect('this');
	}


	/**
	 * @return bool
	 */
	public function isPanelOpened()
	{
		if ($this->_isPanelOpened === NULL) {
			$panelSection = $this->getPanelSession();
			$this->_isPanelOpened = (isset($panelSection->opened) && $panelSection->opened);
		}

		return $this->_isPanelOpened;
	}


	/**
	 * @return \Nette\Http\Session
	 */
	private function getPanelSession()
	{
		return $this->getSession('_Venne.panel');
	}


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
	}


	protected function checkLanguage()
	{
	}
}
