<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\UI;

use Nette\Localization\ITranslator;
use Venne\Widgets\WidgetsControlTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read ITranslator $translator
 * @property-read string $templateName
 */
abstract class Control extends \Nette\Application\UI\Control
{

	use ControlTrait;
	use WidgetsControlTrait;

	/** @var string[] */
	private $configZal = array();

	/** @var string */
	private $variant;

	/**
	 * @return string
	 */
	public function getVariant()
	{
		return $this->variant;
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 */
	public function getTranslator()
	{
		return $this->presenter->translator;
	}

	/**
	 * @param string $name
	 * @param string $args
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if ($name === 'render') {
			if (isset($args[0]) && is_array($args[0]) && isset($args[0]['config'])) {
				$this->configureControl($args[0]['config']);
			}

			if (method_exists($this, 'renderDefault')) {
				call_user_func_array(array($this, 'renderDefault'), $args);
			}

			$this->template->setFile($this->formatTemplateFile());
			$this->template->render();

			if (method_exists($this, 'afterRender')) {
				call_user_func_array(array($this, 'afterRender'), array());
			}

			if (isset($args[0]) && is_array($args[0]) && isset($args[0]['config'])) {
				$this->unconfigureControl();
			}

			return;
		}

		return parent::__call($name, $args);
	}

	/**
	 * @param mixed[] $config
	 */
	protected function configureControl(array $config)
	{
		if (isset($config['variant'])) {
			$this->variant = $config['variant'];
		}

		if (isset($config['template'])) {
			$this->configZal['template'] = $this->template->getFile();
			$file = $this->presenter->context->getByType('Venne\Packages\PathResolver')->expandPath($config['template'], 'Resources/layouts');
			$this->template->setFile($file);
		}
	}

	protected function unconfigureControl()
	{
		if (isset($this->configZal['template'])) {
			$this->template->setFile($this->configZal['template']);
		}

		$this->variant = null;
	}

}
