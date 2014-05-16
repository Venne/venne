<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Events\DI\EventsExtension;
use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Statement;
use Nette\PhpGenerator\PhpLiteral;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemExtension extends CompilerExtension implements IEntityProvider, IPresenterProvider, ITranslationProvider
{

	const TAG_TRAY_COMPONENT = 'venne.trayComponent';

	const TAG_USER = 'venne.user';

	const TAG_LOGIN_PROVIDER = 'venne.loginProvider';

	const TAG_ROUTE = 'venne.route';

	const TAG_ADMINISTRATION = 'venne.administration';

	/** @var array */
	public $defaults = array(
		'session' => array(),
		'administration' => array(
			'routePrefix' => '',
			'defaultPresenter' => 'System:Admin:Dashboard',
			'authentication' => array(
				'autologin' => NULL,
				'autoregistration' => NULL,
			),
			'registrations' => array(),
			'theme' => 'venne/venne',
		),
		'website' => array(
			'name' => 'Blog',
			'title' => '%n %s %t',
			'titleSeparator' => '|',
			'keywords' => '',
			'description' => '',
			'author' => '',
			'robots' => 'index, follow',
			'routePrefix' => '',
			'oneWayRoutePrefix' => '',
			'languages' => array(),
			'defaultLanguage' => 'cs',
			'defaultPresenter' => 'Homepage',
			'errorPresenter' => 'Cms:Error',
			'layout' => '@cms/bootstrap',
			'cacheMode' => '',
			'cacheValue' => '10',
			'theme' => '',
		),
		'paths' => array(
			'publicDir' => '%wwwDir%/public',
			'dataDir' => '%appDir%/data',
			'logDir' => '%appDir%/../log',
		),
	);


	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 * @return void
	 */
	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		foreach ($config['paths'] as $name => $path) {
			if (!isset($container->parameters[$name])) {
				$container->parameters[$name] = $container->expand($path);
			}
		}

		foreach ($config['administration']['registrations'] as $key => $values) {
			if (isset($values['name']) && $values['name']) {
				$config['administration']['registrations'][$values['name']] = $values;
				unset($config['administration']['registrations'][$key]);
			}
		}

		$container->addDependency($container->parameters['tempDir'] . '/installed');

		// http
		$httpResponse = $container->getDefinition('httpResponse');
		foreach ($httpResponse->setup as $setup) {
			if ($setup->entity == 'setHeader' && $setup->arguments[0] == 'X-Powered-By') {
				$httpResponse->addSetup('setHeader', array('X-Powered-By', $setup->arguments[1] . ' && Venne'));
			}
		}

		$container->addDefinition($this->prefix('controlVerifier'))
			->setClass('Venne\Security\ControlVerifiers\ControlVerifier');

		$container->addDefinition($this->prefix('controlVerifierReader'))
			->setClass('Venne\Security\ControlVerifierReaders\AnnotationReader');

		$container->getDefinition('user')
			->setClass('Venne\Security\User');

		// http
		$container->getDefinition('httpResponse')
			->addSetup('setHeader', array('X-Powered-By', 'Nette Framework && Venne:Framework'));

		// session
		$session = $container->getDefinition('session');
		foreach ($config['session'] as $key => $val) {
			if ($val) {
				$session->addSetup('set' . ucfirst($key), $val);
			}
		}

		// template
		$container->getDefinition('nette.latteFactory')
			->addSetup('$service->getCompiler()->addMacro(\'cache\', new Venne\Latte\Macros\GlobalCacheMacro(?->getCompiler()))', array('@self'));

		// security
		$container->getDefinition('nette.userStorage')
			->setClass('Venne\Security\UserStorage', array('@session', new Statement('@doctrine.dao', array('Venne\Security\LoginEntity')), new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))));

		$container->addDefinition($this->prefix('securityManager'))
			->setClass('Venne\Security\SecurityManager');

		// Application
		$application = $container->getDefinition('application');
		$application->addSetup('$service->errorPresenter = ?', array($config['website']['errorPresenter']));

		$container->addDefinition('authorizatorFactory')
			->setFactory('Venne\Security\AuthorizatorFactory', array(
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\PermissionEntity'))
			, '@session'));

		$container->addDefinition('installCommand')
			->setFactory('Venne\System\Commands\InstallCommand', array(
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\PermissionEntity'))
			))
			->addTag(ConsoleExtension::COMMAND_TAG);

		$container->getDefinition('packageManager.packageManager')
			->addSetup('$service->onInstall[] = ?->clearPermissionSession', array('@authorizatorFactory'))
			->addSetup('$service->onUninstall[] = ?->clearPermissionSession', array('@authorizatorFactory'));

		$container->addDefinition('authorizator')
			->setClass('Nette\Security\Permission')
			->setFactory('@authorizatorFactory::getPermissionsByUser', array('@user', TRUE));

		$container->addDefinition('authenticator')
			->setClass('Venne\Security\Authenticator', array(new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))));

		// detect prefix
		$prefix = $config['website']['routePrefix'];
		$adminPrefix = $config['administration']['routePrefix'];
		$languages = $config['website']['languages'];

		// parameters
		$parameters = array();
		$parameters['lang'] = count($languages) > 1 || $config['website']['routePrefix'] ? NULL : $config['website']['defaultLanguage'];

		// Sitemap
		$container->addDefinition($this->prefix('robotsRoute'))
			->setClass('Nette\Application\Routers\Route', array('robots.txt',
				array('presenter' => 'Cms:Sitemap', 'action' => 'robots', 'lang' => NULL)
			))
			->addTag(static::TAG_ROUTE, array('priority' => 999999999));
		$container->addDefinition($this->prefix('sitemapRoute'))
			->setClass('Nette\Application\Routers\Route', array('[lang-<lang>/][page-<page>/]sitemap.xml',
				array('presenter' => 'Cms:Sitemap', 'action' => 'sitemap',)
			))
			->addTag(static::TAG_ROUTE, array('priority' => 999999998));

		// Administration
		$presenter = explode(':', $config['administration']['defaultPresenter']);
		unset($presenter[1]);
		$container->addDefinition($this->prefix('adminRoute'))
			->setClass('Venne\System\Routers\AdminRoute', array($presenter, $adminPrefix))
			->addTag(static::TAG_ROUTE, array('priority' => 100001));

		if ($config['website']['oneWayRoutePrefix']) {
			$container->addDefinition($this->prefix('oneWayPageRoute'))
				->setClass('Venne\System\Content\Routes\PageRoute', array('@container', '@cacheStorage', '@doctrine.checkConnection', $config['website']['oneWayRoutePrefix'], $parameters, $config['website']['languages'], $config['website']['defaultLanguage'], TRUE)
				)
				->addTag(static::TAG_ROUTE, array('priority' => 99));
		}

		$container->addDefinition($this->prefix('administrationManager'))
			->setClass('Venne\System\AdministrationManager', array(
				$config['administration']['routePrefix'],
				$config['administration']['defaultPresenter'],
				$config['administration']['theme']
			));

		$container->addDefinition($this->prefix('authenticationFormFactory'))
			->setArguments(array(new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.administration.authentication')), $config['administration']['registrations']))
			->setClass('Venne\System\AdminModule\AuthenticationFormFactory');

		$container->addDefinition($this->prefix('admin.loginPresenter'))
			->setClass('Venne\System\AdminModule\LoginPresenter', array(new Statement('@doctrine.dao', array('Venne\Security\RoleEntity'))))
			->addSetup('$service->setAutologin(?)', array($config['administration']['authentication']['autologin']))
			->addSetup('$service->setAutoregistration(?)', array($config['administration']['authentication']['autoregistration']))
			->addSetup('$service->setRegistrations(?)', array($config['administration']['registrations']));

		foreach ($this->compiler->getExtensions('Venne\Assets\DI\AssetsExtension') as $extension) {
			$container->getDefinition($extension->prefix('cssLoaderFactory'))
				->addTag(WidgetsExtension::TAG_WIDGET, 'css');

			$container->getDefinition($extension->prefix('jsLoaderFactory'))
				->addTag(WidgetsExtension::TAG_WIDGET, 'js');

			break;
		}


		$container->addDefinition($this->prefix('templateLocator'))
			->setClass('Venne\System\UI\AdminTemplateLocator', array(array(
				$container->parameters['appDir'] . '/templates',
				$container->parameters['packages']['venne/venne']['path'] . '/Resources/templates',
			)));


		$container->removeDefinition('nette.presenterFactory');
		$presenterFactory = $container->addDefinition($this->prefix('presenterFactory'))
			->setClass('Nette\Application\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : NULL
			));
		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof IPresenterProvider) {
				$presenterFactory->addSetup('setMapping', array($extension->getPresenterMapping()));
			}
		}


		$container->addDefinition($this->prefix('trayComponent'))
			->setImplement('Venne\System\AdminModule\Components\ITrayControlFactory')
			->setInject(TRUE)
			->addTag(WidgetsExtension::TAG_WIDGET, 'tray');


		$this->setupSystemLogs($container, $config);
		$this->setupSystemCache($container, $config);
		$this->setupSystemApplication($container, $config);
		$this->setupSystem($container, $config);
	}


	public function setupDoctrine(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('dynamicMapperSubscriber'))
			->setClass('Venne\Doctrine\Mapping\DynamicMapperSubscriber')
			->addTag(EventsExtension::TAG_SUBSCRIBER);
	}


	public function setupSystemLogs(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('system.logsPresenter'))
			->setClass('Venne\System\AdminModule\LogsPresenter', array($container->expand('%logDir%')))
			->addTag(static::TAG_ADMINISTRATION, array(
				'link' => 'System:Admin:Logs:',
				'category' => 'System',
				'name' => 'Log browser',
				'description' => 'Show logs, errors, warnings,...',
				'priority' => 5,
			));
	}


	public function setupSystemCache(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('system.cache.formFactory'))
			->setClass('Venne\System\AdminModule\CacheFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('system.cachePresenter'))
			->setClass('Venne\System\AdminModule\CachePresenter')
			->addTag(static::TAG_ADMINISTRATION, array(
				'link' => 'System:Admin:Cache:',
				'category' => 'System',
				'name' => 'Cache',
				'description' => 'Clear cache',
				'priority' => 0,
			));
	}


	public function setupSystemApplication(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('system.application.mailerFormFactory'))
			->setClass('Venne\System\AdminModule\MailerFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), ''))
			));

		$container->addDefinition($this->prefix('system.application.registrationFormFactory'))
			->setClass('Venne\System\AdminModule\RegistrationFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.administration')),
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity'))
			));

		$container->addDefinition($this->prefix('system.application.systemFormFactory'))
			->setClass('Venne\System\AdminModule\AdministrationFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.application'))
			));

		$container->addDefinition($this->prefix('system.application.applicationFormFactory'))
			->setClass('Venne\System\AdminModule\ApplicationFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), ''))
			));

		$container->addDefinition($this->prefix('system.application.accountFormFactory'))
			->setClass('Venne\Security\AdminModule\AccountFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.administration.login'))
			));

		$container->addDefinition($this->prefix('system.applicationPresenter'))
			->setClass('Venne\System\AdminModule\ApplicationPresenter')
			->addTag(static::TAG_ADMINISTRATION, array(
				'link' => 'System:Admin:Application:',
				'category' => 'System',
				'name' => 'System settings',
				'description' => 'Set up database, environment,...',
				'priority' => 15,
			));
	}


	public function setupSystem(ContainerBuilder $container, array $config)
	{
		$container->addDefinition($this->prefix('formRenderer'))
			->setClass('Venne\System\Forms\Bootstrap3Renderer');

		$container->addDefinition($this->prefix('admin.basicFormFactory'))
			->setClass('Nette\Application\UI\Form')
			->setArguments(array(NULL, NULL))
			->setImplement('Venne\Forms\IFormFactory')
			->addSetup('setRenderer', array(new Statement($this->prefix('@formRenderer'))))
			->addSetup('setTranslator', array(new Statement('@Nette\Localization\ITranslator')))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('admin.ajaxFormFactory'))
			->setClass('Nette\Application\UI\Form')
			->setArguments(array(NULL, NULL))
			->setImplement('Venne\Forms\IFormFactory')
			->addSetup('setRenderer', array(new Statement($this->prefix('@formRenderer'))))
			->addSetup('setTranslator', array(new Statement('@Nette\Localization\ITranslator')))
			->addSetup("\$service->getElementPrototype()->class[] = ?", array('ajax'))
			->setAutowired(FALSE);

		$container->addDefinition($this->prefix('admin.configFormFactory'))
			->setClass('Venne\System\UI\ConfigFormFactory', array(new PhpLiteral('$configFile'), new PhpLiteral('$section')))
			->addSetup('setFormFactory', array(new Statement('@system.admin.basicFormFactory')))
			->setAutowired(FALSE)
			->setParameters(array('configFile', 'section'));

		$container->addDefinition($this->prefix('registrationControlFactory'))
			->setClass('Venne\Security\Registration\RegistrationControl', array(
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity')),
				'%userType%', '%mode%', '%loginProviderMode%', '%roles%', '%emailSender%', '%emailFrom%', '%emailSubject%', '%emailText%'
			))
			->setImplement('Venne\Security\Registration\IRegistrationControlFactory')
			->addSetup('inject')
			->setParameters(array('userType', 'mode', 'loginProviderMode', 'roles', 'emailSender', 'emailFrom', 'emailSubject', 'emailText'));

		$container->addDefinition($this->prefix('system.loginFormFactory'))
			->setClass('Venne\System\AdminModule\LoginFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('system.dashboardPresenter'))
			->setClass('Venne\System\AdminModule\DashboardPresenter', array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))
			));

		$container->addDefinition($this->prefix('navbarControlFactory'))
			->setImplement('Venne\System\Components\INavbarControlFactory')
			->setArguments(array(NULL))
			->setInject(TRUE);

		$container->addDefinition($this->prefix('loginControlFactory'))
			->setImplement('Venne\Security\Login\ILoginControlFactory')
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))))
			->setInject(TRUE);

		$container->addDefinition($this->prefix('gridoFactory'))
			->setImplement('Venne\System\Components\IGridoFactory')
			->setArguments(array(NULL, NULL))
			->setInject(TRUE);

		$container->addDefinition($this->prefix('gridControlFactory'))
			->setImplement('Venne\System\Components\AdminGrid\IAdminGridFactory')
			->setArguments(array(new PhpLiteral('$repository')))
			->setParameters(array('repository'))
			->setInject(TRUE);

		$container->addDefinition($this->prefix('flashMessageControlFactory'))
			->setImplement('Venne\System\Components\IFlashMessageControlFactory')
			->setArguments(array(NULL))
			->setInject(TRUE)
			->addTag(WidgetsExtension::TAG_WIDGET, 'flashMessage');
	}


	public function beforeCompile()
	{
		$this->registerRoutes();
		$this->registerAdministrationPages();
		$this->registerUsers();
		$this->registerLoginProvider();
		$this->registerTrayComponents();
	}


	public function afterCompile(\Nette\PhpGenerator\ClassType $class)
	{
		parent::afterCompile($class);

		$initialize = $class->methods['initialize'];

		foreach ($this->getSortedServices('subscriber') as $item) {
			$initialize->addBody('$this->getService("eventManager")->addEventSubscriber($this->getService(?));', array($item));
		}

		$initialize->addBody('$this->parameters[\'baseUrl\'] = rtrim($this->getService("httpRequest")->getUrl()->getBaseUrl(), "/");');
		$initialize->addBody('$this->parameters[\'basePath\'] = preg_replace("#https?://[^/]+#A", "", $this->parameters["baseUrl"]);');
	}


	private function registerRoutes()
	{
		$container = $this->getContainerBuilder();
		$router = $container->getDefinition('router');

		foreach ($this->getSortedServices(static::TAG_ROUTE) as $route) {
			$definition = $container->getDefinition($route);
			$definition->setAutowired(FALSE);

			$router->addSetup('$service[] = $this->getService(?)', array($route));
		}
	}


	private function registerAdministrationPages()
	{
		$container = $this->getContainerBuilder();
		$manager = $container->getDefinition($this->prefix('administrationManager'));

		foreach ($this->getSortedServices(static::TAG_ADMINISTRATION) as $item) {
			$tags = $container->getDefinition($item)->tags[static::TAG_ADMINISTRATION];
			$manager->addSetup('addAdministrationPage', array(
				$tags['link'],
				isset($tags['name']) ? $tags['name'] : NULL,
				isset($tags['description']) ? $tags['description'] : NULL,
				isset($tags['category']) ? $tags['category'] : NULL,
			));
		}
	}


	private function registerUsers()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('securityManager'));

		foreach ($container->findByTag(static::TAG_USER) as $item => $tags) {
			$arguments = $container->getDefinition($item)->factory->arguments;

			$container->getDefinition($item)->factory->arguments = array(
				0 => is_array($tags) ? $tags['name'] : $tags,
				1 => $arguments[0],
			);

			$config->addSetup('addUserType', array("@{$item}"));
		}
	}


	private function registerLoginProvider()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('securityManager'));

		foreach ($container->findByTag(static::TAG_LOGIN_PROVIDER) as $item => $tags) {
			$class = '\\' . $container->getDefinition($item)->class;
			$type = $class::getType();

			$config->addSetup('addLoginProvider', array($type, "{$item}"));
		}
	}


	private function registerTrayComponents()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('administrationManager'));

		foreach ($container->findByTag(static::TAG_TRAY_COMPONENT) as $item => $tags) {
			$def = $container->getDefinition($item);
			$name = 'tray__' . str_replace('\\', '_', $def->class ? : $def->implement);

			$config->addSetup('$service->trayWidgetManager->addWidget(?, ?)', array($name, $item));
		}
	}


	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\System' => dirname(__DIR__) . '/*Entity.php',
			'Venne\Comments' => dirname(dirname(__DIR__)) . '/Comments/*Entity.php',
		);
	}


	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'System' => 'Venne\System\*Module\*Presenter',
		);
	}


	/**
	 * @return array
	 */
	public function getTranslationResources()
	{
		return array(
			__DIR__ . '/../../../Resources/lang',
		);
	}


	/**
	 * @param $tag
	 * @return array
	 */
	private function getSortedServices($tag)
	{
		$container = $this->getContainerBuilder();

		$items = array();
		$ret = array();
		foreach ($container->findByTag($tag) as $route => $meta) {
			$priority = isset($meta['priority']) ? $meta['priority'] : (int)$meta;
			$items[$priority][] = $route;
		}

		krsort($items);

		foreach ($items as $items2) {
			foreach ($items2 as $item) {
				$ret[] = $item;
			}
		}
		return $ret;
	}

}
