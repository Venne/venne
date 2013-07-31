<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Forms\LanguageFormFactory;
use CmsModule\Forms\SystemAccountFormFactory;
use CmsModule\Forms\SystemDatabaseFormFactory;
use CmsModule\Pages\Text\PageEntity;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Caching\Storages\MemoryStorage;
use Nette\InvalidArgumentException;
use Nette\Loaders\RobotLoader;
use Nette\Reflection\ClassType;
use Venne\Security\Authenticator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InstallationPresenter extends BasePresenter
{

	/** @var string */
	protected $appDir;

	/** @var string */
	protected $wwwDir;

	/** @var string */
	protected $tempDir;

	/** @var string */
	protected $dataDir;

	/** @var string */
	protected $resourcesDir;

	/** @var string */
	protected $configDir;

	/** @var string */
	protected $wwwCacheDir;

	/** @var string */
	protected $publicDir;

	/** @var array */
	protected $administration;

	/** @var SystemAccountFormFactory */
	protected $accountForm;

	/** @var IStorage */
	protected $cacheStorage;

	/** @var bool */
	protected $confirmation = false;

	/** @var LanguageFormFactory */
	protected $languageFormFactory;

	/** @var SystemDatabaseFormFactory */
	protected $databaseFormFactory;


	/**
	 * @param IStorage $cacheStorage
	 * @param $parameters
	 */
	public function __construct(IStorage $cacheStorage, $administration, $appDir, $wwwDir, $tempDir, $dataDir, $resourcesDir, $configDir, $wwwCacheDir, $publicDir)
	{
		parent::__construct();

		$this->appDir = $appDir;
		$this->wwwDir = $wwwDir;
		$this->tempDir = $tempDir;
		$this->dataDir = $dataDir;
		$this->resourcesDir = $resourcesDir;
		$this->configDir = $configDir;
		$this->wwwCacheDir = $wwwCacheDir;
		$this->publicDir = $publicDir;
		$this->cacheStorage = $cacheStorage;
		$this->administration = $administration;
	}


	public function injectAccountForm(SystemAccountFormFactory $accountForm)
	{
		$this->accountForm = $accountForm;
	}


	/**
	 * @param LanguageFormFactory $languageFormFactory
	 */
	public function injectLanguageFormFactory(LanguageFormFactory $languageFormFactory)
	{
		$this->languageFormFactory = $languageFormFactory;
	}


	/**
	 * @param SystemDatabaseFormFactory $databaseFormFactory
	 */
	public function injectDatabaseFormFactory(SystemDatabaseFormFactory $databaseFormFactory)
	{
		$this->databaseFormFactory = $databaseFormFactory;
	}


	public function startup()
	{
		parent::startup();

		if (!isset($this->__installation) || $this->__installation !== TRUE) {
			$this->setView('finish');
		}

		// Resources dir
		if (!file_exists($this->resourcesDir . '/cmsModule')) {
			@symlink("../../vendor/venne/cms-module/Resources/public", $this->resourcesDir . '/cmsModule');
		}

		// Extensions
		$modules = array('iconv', 'json', "pdo");
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage("Module {$item} is not enabled.", 'warning');
			}
		}

		// Writable
		$paths = array($this->resourcesDir, $this->dataDir, $this->configDir, $this->configDir . '/config.neon', $this->tempDir, $this->wwwCacheDir, $this->publicDir);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path {$item} is not writable.", 'warning');
			}
		}
	}


	public function handleInstall()
	{
		if ($this->context->doctrine->createCheckConnection() && count($this->context->schemaManager->listTables()) == 0) {
			/** @var $em \Doctrine\ORM\EntityManager */
			$em = $this->context->entityManager;
			$tool = new SchemaTool($em);

			$robotLoader = new RobotLoader;
			$robotLoader->setCacheStorage(new MemoryStorage);
			$robotLoader->addDirectory($this->context->parameters['modules']['cms']['path'] . '/CmsModule');
			$robotLoader->register();

			$classes = array();
			foreach ($robotLoader->getIndexedClasses() as $item => $a) {
				$ref = ClassType::from($item);
				if ($ref->hasAnnotation('ORM\Entity')) {
					$classes[] = $em->getClassMetadata('\\' . $item);
				}
			}

			$tool->createSchema($classes);

			/** @var $installer CmsInstaller */
			$installer = $this->context->createInstance('CmsModule\Module\Installers\CmsInstaller');
			$installer->install($this->context->venne->moduleManager->modules['cms']);
		}

		$this->redirect('Installation:');
	}


	public function handleCreateStructure()
	{
		$em = $this->context->entityManager;

		if (!$this->isEmptyStructure()) {
			throw new InvalidArgumentException("Page structure must be empty");
		}

		$cacheDriver = $em->getConfiguration()->getMetadataCacheImpl();
		$cacheDriver->deleteAll();

		$layout = $em->getRepository('CmsModule\Content\Entities\LayoutEntity')->find(1);

		// pages
		$textPage = new PageEntity;
		$textPage
			->getExtendedMainRoute()
			->setName('Main page')
			->getRoute()
			->setCopyLayoutFromParent(FALSE)
			->setLayout($layout)
			->setText('Hello, this is main page of this website.')
			->setPublished(TRUE);

		$userPage = new \CmsModule\Pages\Users\PageEntity;
		$userPage
			->getPage()
			->setParent($textPage->getPage());
		$userPage
			->getExtendedMainRoute()
			->setName('Users')
			->getRoute()
			->setText('List of users.')
			->setPublished(TRUE);

		$tagsPage = new \CmsModule\Pages\Tags\PageEntity;
		$tagsPage
			->getPage()
			->setParent($textPage->getPage());
		$tagsPage
			->getExtendedMainRoute()
			->setName('Tags')
			->getRoute()
			->setText('List of tags.')
			->setPublished(TRUE);

		$em->persist($textPage);
		$em->persist($userPage);
		$em->persist($tagsPage);
		$em->flush();

		$this->redirect('Dashboard:');
	}


	/**
	 * @return bool
	 */
	public function isEmptyStructure()
	{
		return !$this->context->entityManager->getRepository('CmsModule\Content\Entities\RouteEntity')->createQueryBuilder('a')->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
	}


	protected function createComponentSystemAccountForm()
	{
		$form = $this->accountForm->invoke();
		$form->onSuccess[] = $this->accountFormSuccess;
		return $form;
	}


	protected function createComponentDatabaseForm()
	{
		$form = $this->databaseFormFactory->invoke();
		$form->onSuccess[] = $this->databaseFormSuccess;
		return $form;
	}


	protected function createComponentLanguageForm()
	{
		$form = $this->languageFormFactory->invoke(new LanguageEntity);
		$form->onSuccess[] = $this->languageFormSuccess;
		return $form;
	}


	public function accountFormSuccess($form)
	{
		$user = $this->getUser();
		$values = $form->getValues();

		// login
		$user->setAuthenticator(new Authenticator($values['name'], $values['password']));
		$user->login($values['name'], $values['password']);

		$cache = new Cache($this->cacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect('Installation:');
	}


	public function databaseFormSuccess()
	{
		$this->redirect('install!');
	}


	public function languageFormSuccess()
	{
		$this->redirect('Installation:');
	}
}
