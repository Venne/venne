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

use CmsModule\Administration\AdministrationManager;
use CmsModule\Content\WebsiteManager;
use CmsModule\Pages\Users\ExtendedUserEntity;
use CmsModule\Pages\Users\UserEntity;
use Doctrine\ORM\EntityManager;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Venne\Application\UI\Presenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read EntityManager $entityManager
 * @property-read ExtendedUserEntity $extendedUser
 * @property-read ITranslator $translator
 * @property-read WebsiteManager $websiteManager
 * @property-read AdministrationManager $administrationManager
 */
abstract class BasePresenter extends Presenter
{

	/** @persistent */
	public $lang;

	/** @var EntityManager */
	private $entityManager;

	/** @var ITranslator */
	private $translator;

	/** @var ExtendedUserEntity */
	private $extendedUser;

	/** @var bool|NULL */
	private $_isPanelOpened;

	/** @var WebsiteManager */
	private $websiteManager;

	/** @var AdministrationManager */
	private $administrationManager;


	/**
	 * @param \CmsModule\Content\WebsiteManager $websiteManager
	 */
	public function injectWebsiteManager(WebsiteManager $websiteManager)
	{
		$this->websiteManager = $websiteManager;
	}


	/**
	 * @param \CmsModule\Administration\AdministrationManager $administrationManager
	 */
	public function injectAdministrationManager(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}


	/**
	 * @return \CmsModule\Content\WebsiteManager
	 */
	public function getWebsiteManager()
	{
		return $this->websiteManager;
	}


	/**
	 * @return \CmsModule\Administration\AdministrationManager
	 */
	public function getAdministrationManager()
	{
		return $this->administrationManager;
	}


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
	 * @param ITranslator $translator
	 */
	public function injectTranslator(ITranslator $translator)
	{
		$this->translator = $translator;
	}


	/**
	 * @return ITranslator
	 */
	public function getTranslator()
	{
		return $this->translator;
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
		$this->translator->setLang($this->lang);
	}


	protected function checkLanguage()
	{
	}
}
