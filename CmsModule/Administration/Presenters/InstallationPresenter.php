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

use CmsModule\Administration\AdministrationManager;
use CmsModule\Administration\StructureInstallatorManager;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Forms\LanguageFormFactory;
use CmsModule\Forms\SystemAccountFormFactory;
use CmsModule\Forms\SystemDatabaseFormFactory;
use DeploymentModule\DeploymentManager;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineModule\DI\ConnectionCheckerFactory;
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

	/** @persistent */
	public $backup;

	/** @var string */
	private $appDir;

	/** @var string */
	private $wwwDir;

	/** @var string */
	private $tempDir;

	/** @var string */
	private $dataDir;

	/** @var string */
	private $resourcesDir;

	/** @var string */
	private $configDir;

	/** @var string */
	private $wwwCacheDir;

	/** @var string */
	private $publicDir;

	/** @var array */
	private $administration;

	/** @var SystemAccountFormFactory */
	private $accountForm;

	/** @var IStorage */
	private $cacheStorage;

	/** @var bool */
	private $confirmation = false;

	/** @var LanguageFormFactory */
	private $languageFormFactory;

	/** @var SystemDatabaseFormFactory */
	private $databaseFormFactory;

	/** @var StructureInstallatorManager */
	private $installatorManager;

	/** @var DeploymentManager */
	private $deploymentManager;

	/** @var ConnectionCheckerFactory */
	private $connectionCheckerFactory;

	/** @var AdministrationManager */
	private $administrationManager;


	/**
	 * @param IStorage $cacheStorage
	 * @param $parameters
	 */
	public function __construct($appDir, $wwwDir, $tempDir, $dataDir, $resourcesDir, $configDir, $wwwCacheDir, $publicDir, ConnectionCheckerFactory $connectionCheckerFactory, IStorage $cacheStorage, AdministrationManager $administrationManager)
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
		$this->connectionCheckerFactory = $connectionCheckerFactory;
		$this->administrationManager = $administrationManager;
	}


	/**
	 * @param StructureInstallatorManager $installatorManager
	 * @param SystemAccountFormFactory $accountForm
	 * @param LanguageFormFactory $languageFormFactory
	 * @param SystemDatabaseFormFactory $databaseFormFactory
	 * @param DeploymentManager $deploymentManager
	 */
	public function inject(
		StructureInstallatorManager $installatorManager,
		SystemAccountFormFactory $accountForm,
		LanguageFormFactory $languageFormFactory,
		SystemDatabaseFormFactory $databaseFormFactory,
		DeploymentManager $deploymentManager
	) {
		$this->installatorManager = $installatorManager;
		$this->accountForm = $accountForm;
		$this->languageFormFactory = $languageFormFactory;
		$this->databaseFormFactory = $databaseFormFactory;
		$this->deploymentManager = $deploymentManager;
	}


	/**
	 * @return StructureInstallatorManager
	 */
	public function getInstallatorManager()
	{
		return $this->installatorManager;
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
		$modules = array('iconv', 'json', 'pdo');
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage($this->translator->translate('Module %name% is not enabled.', NULL, array('name' => $item)), 'warning');
			}
		}

		// Writable
		$paths = array($this->resourcesDir, $this->dataDir, $this->configDir, $this->configDir . '/config.neon', $this->tempDir, $this->wwwCacheDir, $this->publicDir);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage($this->translator->translate('Path %name% is not writable.', NULL, array('name' => $item)), 'warning');
			}
		}
	}


	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->hideMenuItems = true;
	}


	public function handleInstall()
	{
		if ($this->connectionCheckerFactory->invoke() && count($this->getEntityManager()->getConnection()->getSchemaManager()->listTables()) == 0) {

			if ($this->backup) {
				$this->deploymentManager->loadBackup($this->backup);

			} else {
				/** @var $em \Doctrine\ORM\EntityManager */
				$em = $this->getEntityManager();
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
		}

		$this->redirect('Installation:', array('backup' => NULL));
	}


	public function handleCreateStructure($name)
	{
		$em = $this->entityManager;

		if (!$this->isEmptyStructure()) {
			throw new InvalidArgumentException("Page structure must be empty");
		}

		$cacheDriver = $em->getConfiguration()->getMetadataCacheImpl();
		$cacheDriver->deleteAll();

		$installator = $this->installatorManager->getInstallatorByName($name);
		$installator->run();

		$this->redirect('Dashboard:');
	}


	/**
	 * @return bool
	 */
	public function isEmptyStructure()
	{
		return !$this->entityManager->getRepository('CmsModule\Content\Entities\RouteEntity')->createQueryBuilder('a')->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
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

		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		$cache = new Cache($this->cacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect('Installation:');
	}


	public function databaseFormSuccess($form)
	{
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		if (isset($form['_backup']) && $form['_backup']->value) {
			$this->redirect('install!', array('backup' => $form['_backup']->value));
		}

		$this->redirect('install!');
	}


	public function languageFormSuccess()
	{
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}

		$cache = new Cache($this->cacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect('Installation:');
	}
}
