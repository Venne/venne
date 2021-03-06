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

use Kdyby\Events\DI\EventsExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Statement;
use Nette\PhpGenerator\PhpLiteral;
use Venne\DataTransfer\DI\DataTransferExtension;
use Venne\System\Events\InvitationEvent;
use Venne\System\Forms\DoctrineForms\Controls\TextControl;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemExtension extends \Nette\DI\CompilerExtension implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\System\DI\IPresenterProvider,
	\Kdyby\Translation\DI\ITranslationProvider,
	\Venne\System\DI\ICssProvider,
	\Venne\System\DI\IJsProvider,
	\Venne\System\DI\IFormMapperProvider,
	\Venne\Notifications\DI\IEventProvider
{

	const TAG_TRAY_COMPONENT = 'venne.trayComponent';

	const TAG_ROUTE = 'venne.route';

	const TAG_ADMINISTRATION = 'venne.administration';

	const TAG_SIDE_COMPONENT = 'venne.sideComponent';

	/** @var mixed[] */
	public $defaults = array(
		'session' => array(),
		'administration' => array(
			'routePrefix' => '',
			'defaultPresenter' => 'System:Dashboard',
			'authentication' => array(
				'autologin' => null,
				'autoregistration' => null,
			),
			'theme' => null, //'venne/venne',
		),
		'paths' => array(
			'publicDir' => '%wwwDir%/public',
			'dataDir' => '%appDir%/data',
			'logDir' => '%appDir%/../log',
		),
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$this->compiler->parseServices(
			$container,
			$this->loadFromFile(__DIR__ . '/services.neon')
		);
		$config = $this->getConfig($this->defaults);

		foreach ($config['paths'] as $name => $path) {
			if (!isset($container->parameters[$name])) {
				$container->parameters[$name] = $container->expand($path);
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
			->setClass(\Venne\Security\NetteUser::class);

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
		$container->getDefinition('nette.templateFactory')
			->setClass('Venne\Latte\TemplateFactory');

		$container->getDefinition('nette.latteFactory')
			->addSetup('$service->getCompiler()->addMacro(\'cache\', new Venne\Latte\Macros\GlobalCacheMacro(?->getCompiler()))', array('@self'));

		// Administration
		$presenter = explode(':', $config['administration']['defaultPresenter']);
		$container->addDefinition($this->prefix('adminRoute'))
			->setClass('Venne\System\Routers\AdminRoute', array($presenter, $config['administration']['routePrefix']))
			->addTag(static::TAG_ROUTE, array('priority' => 100001));

		$am = $container->addDefinition($this->prefix('administrationManager'))
			->setClass('Venne\System\AdministrationManager', array(
				$config['administration']['routePrefix'],
				$config['administration']['defaultPresenter'],
				$config['administration']['theme']
			));
		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof ICssProvider) {
				foreach ($extension->getCssFiles() as $file) {
					$am->addSetup('addCssFile', array(' ' . $file));
				}
			}
			if ($extension instanceof IJsProvider) {
				foreach ($extension->getJsFiles() as $file) {
					$am->addSetup('addJsFile', array(' ' . $file));
				}
			}
		}

		$container->addDefinition($this->prefix('authenticationFormFactory'))
			->setArguments(array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.administration.authentication')),
			))
			->setClass('Venne\System\AdminModule\AuthenticationFormFactory');

		$container->addDefinition($this->prefix('admin.loginPresenter'))
			->setClass('Venne\System\AdminModule\LoginPresenter')
			->addSetup('$service->setAutologin(?)', array($config['administration']['authentication']['autologin']))
			->addSetup('$service->setAutoregistration(?)', array($config['administration']['authentication']['autoregistration']));

		$container->addDefinition($this->prefix('invitationStateListener'))
			->setClass('Venne\System\Listeners\InvitationStateListener');

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
			->setClass('Venne\System\UI\PresenterFactory', array(
				isset($container->parameters['appDir']) ? $container->parameters['appDir'] : null
			));
		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof IPresenterProvider) {
				$presenterFactory->addSetup('setMapping', array($extension->getPresenterMapping()));
			}
		}

		$container->addDefinition($this->prefix('doctrineForms.textControl'))
			->setClass('Venne\System\Forms\DoctrineForms\Controls\TextControl');

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof \Kdyby\DoctrineForms\DI\FormsExtension) {
				$entityFormMapper = $container->getDefinition($extension->prefix('entityFormMapper'));

				foreach ($this->compiler->extensions as $extension) {
					if ($extension instanceof IFormMapperProvider) {
						foreach ($extension->getFormMappers() as $definition) {
							$entityFormMapper->addSetup('?->setEntityFormMapper($service);$service->registerMapper(?)', array('@' . $definition, '@' . $definition));
						}
					}
				}

				break;
			}
		}

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof DataTransferExtension) {
				$extension->setDriverClass('Venne\Bridges\Kdyby\Doctrine\DataTransfer\EntityDriver');
			}
		}

		$container->addDefinition($this->prefix('trayComponent'))
			->setImplement('Venne\System\AdminModule\Components\ITrayControlFactory')
			->setInject(true);

		$container->addDefinition($this->prefix('sideComponentsComponent'))
			->setImplement('Venne\System\AdminModule\Components\SideComponentsControlFactory')
			->setInject(true);

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
				'link' => 'Admin:System:Logs:',
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
				'link' => 'Admin:System:Cache:',
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

		$container->addDefinition($this->prefix('system.application.systemFormFactory'))
			->setClass('Venne\System\AdminModule\AdministrationFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), 'system.administration'))
			));

		$container->addDefinition($this->prefix('system.application.applicationFormFactory'))
			->setClass('Venne\System\AdminModule\ApplicationFormFactory', array(
				new Statement('@system.admin.configFormFactory', array($container->expand('%configDir%/config.neon'), ''))
			));

		$container->addDefinition($this->prefix('system.applicationPresenter'))
			->setClass('Venne\System\AdminModule\ApplicationPresenter')
			->addTag(static::TAG_ADMINISTRATION, array(
				'link' => 'Admin:System:Application:',
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
			->setArguments(array(null, null))
			->setImplement('Venne\Forms\IFormFactory')
			->addSetup('setRenderer', array(new Statement($this->prefix('@formRenderer'))))
			->addSetup('setTranslator', array(new Statement('@Nette\Localization\ITranslator')))
			->setAutowired(false);

		$container->addDefinition($this->prefix('admin.ajaxFormFactory'))
			->setClass('Nette\Application\UI\Form')
			->setArguments(array(null, null))
			->setImplement('Venne\Forms\IFormFactory')
			->addSetup('setRenderer', array(new Statement($this->prefix('@formRenderer'))))
			->addSetup('setTranslator', array(new Statement('@Nette\Localization\ITranslator')))
			->addSetup('$service->getElementPrototype()->class[] = ?', array('ajax'))
			->setAutowired(false);

		$container->addDefinition($this->prefix('admin.configFormFactory'))
			->setClass('Venne\System\UI\ConfigFormFactory', array(new PhpLiteral('$configFile'), new PhpLiteral('$section')))
			->addSetup('setFormFactory', array(new Statement('@system.admin.basicFormFactory')))
			->setAutowired(false)
			->setParameters(array('configFile', 'section'));

		$container->addDefinition($this->prefix('registrationControlFactory'))
			->setClass('Venne\Security\Registration\RegistrationControl', array(
				new PhpLiteral('$invitations'),
				new PhpLiteral('$userType'),
				new PhpLiteral('$mode'),
				new PhpLiteral('$loginProviderMode'),
				new PhpLiteral('$roles'),
			))
			->setImplement('Venne\Security\Registration\IRegistrationControlFactory')
			->setInject(true);

		$container->addDefinition($this->prefix('system.loginFormFactory'))
			->setClass('Venne\System\AdminModule\LoginFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('system.dashboardPresenter'))
			->setClass('Venne\System\AdminModule\DashboardPresenter');

		$container->addDefinition($this->prefix('cssControlFactory'))
			->setClass('Venne\System\Components\CssControl')
			->setImplement('Venne\System\Components\CssControlFactory')
			->setArguments(array(null))
			->setInject(true);

		$container->addDefinition($this->prefix('jsControlFactory'))
			->setClass('Venne\System\Components\JsControl')
			->setImplement('Venne\System\Components\JsControlFactory')
			->setArguments(array(null))
			->setInject(true);

		$container->addDefinition($this->prefix('navbarControlFactory'))
			->setImplement('Venne\System\Components\INavbarControlFactory')
			->setArguments(array(null))
			->setInject(true);

		$container->addDefinition($this->prefix('loginControlFactory'))
			->setImplement('Venne\Security\Login\ILoginControlFactory')
			->setInject(true);

		$container->addDefinition($this->prefix('gridoFactory'))
			->setImplement('Venne\System\Components\IGridoFactory')
			->setArguments(array(null, null))
			->setInject(true);

		$container->addDefinition($this->prefix('gridControlFactory'))
			->setImplement('Venne\System\Components\AdminGrid\IAdminGridFactory')
			->setArguments(array(new PhpLiteral('$repository')))
			->setParameters(array('repository'))
			->setInject(true);

		$container->addDefinition($this->prefix('flashMessageControlFactory'))
			->setImplement('Venne\System\Components\IFlashMessageControlFactory')
			->setArguments(array(null))
			->setInject(true)
			->addTag(WidgetsExtension::TAG_WIDGET, 'flashMessage');
	}

	public function beforeCompile()
	{
		$this->registerRoutes();
		$this->registerAdministrationPages();
		$this->registerTrayComponents();
		$this->registerSideComponents();
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
			$definition->setAutowired(false);

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
				isset($tags['name']) ? $tags['name'] : null,
				isset($tags['description']) ? $tags['description'] : null,
				isset($tags['category']) ? $tags['category'] : null,
			));
		}
	}

	private function registerTrayComponents()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('administrationManager'));

		foreach ($container->findByTag(static::TAG_TRAY_COMPONENT) as $item => $tags) {
			$def = $container->getDefinition($item);
			$name = 'tray__' . str_replace('\\', '_', $def->class ?: $def->implement);

			$config->addSetup('$service->trayWidgetManager->addWidget(?, ?)', array($name, $item));
		}
	}

	private function registerSideComponents()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('administrationManager'));

		foreach ($container->findByTag(static::TAG_SIDE_COMPONENT) as $item => $tags) {
			$config->addSetup('$service->addSideComponent(?, ?, ?, ?)', array(
				$tags['name'],
				$tags['description'],
				new Statement('@' . $item),
				$tags['args'],
			));
		}
	}

	/**
	 * @return string[]
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\System' => dirname(__DIR__) . '/*.php',
		);
	}

	/**
	 * @return string[]
	 */
	public function getPresenterMapping()
	{
		return array(
			'Admin:System' => 'Venne\*\AdminModule\*Presenter',
		);
	}

	/**
	 * @return string[]
	 */
	public function getTranslationResources()
	{
		return array(
			__DIR__ . '/../../../Resources/lang',
		);
	}

	/**
	 * @param $tag
	 * @return string[]
	 */
	private function getSortedServices($tag)
	{
		$container = $this->getContainerBuilder();

		$items = array();
		$ret = array();
		foreach ($container->findByTag($tag) as $route => $meta) {
			$priority = isset($meta['priority']) ? $meta['priority'] : (int) $meta;
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

	/**
	 * @return string[]
	 */
	public function getCssFiles()
	{
		return array(
			'@venne.venne/vendor/bootstrap/css/bootstrap.min.css',
			'@venne.venne/vendor/jasny-bootstrap/css/jasny-bootstrap.min.css',
			'@venne.venne/vendor/font-awesome/css/font-awesome.min.css',
			'@venne.venne/vendor/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css',
			'@o5.grido/grido.css',
			'@venne.venne/vendor/select2/select2.css',
			'@venne.venne/vendor/select2/select2-bootstrap.css',
			'@venne.venne/css/style.css',
		);
	}

	/**
	 * @return string[]
	 */
	public function getJsFiles()
	{
		return array(
			'@venne.venne/vendor/jquery/jquery.min.js',
			'@venne.venne/vendor/jquery/jquery-migrate.js',
			'@venne.venne/vendor/jquery-ui/jquery-ui.min.js',
			'@venne.venne/vendor/bootstrap/js/bootstrap.min.js',
			'@venne.venne/vendor/jasny-bootstrap/js/jasny-bootstrap.min.js',
			'@venne.venne/vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js',

			'@venne.venne/vendor/jquery-hashchange/jquery.ba-hashchange.min.js',
			'@o5.grido/grido.js',

			'@venne.venne/vendor/select2/select2.min.js',

			'@venne.venne/vendor/typeahead.js/typeahead.bundle.min.js',

			'@venne.venne/vendor/nette.ajax.js/nette.ajax.js',
			'@venne.venne/vendor/history.ajax.js/history.ajax.js',
			'@venne.venne/js/spinner.ajax.js',

			'@venne.venne/typing/jquery.typing-0.2.0.min.js',
			'@venne.venne/vendor/nette-forms/netteForms.js',
			'@venne.venne/textWithSelect/textWithSelect.js',

			'@venne.venne/js/application.js',
		);
	}

	/**
	 * @return string[]
	 */
	public function getEventTypes()
	{
		return array(
			InvitationEvent::getName(),
		);
	}

	/**
	 * @return string[]
	 */
	public function getFormMappers()
	{
		return array(
			TextControl::class,
		);
	}

}
