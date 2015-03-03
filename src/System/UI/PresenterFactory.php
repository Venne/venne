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

use Closure;
use Nette;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PresenterFactory extends Nette\Object implements Nette\Application\IPresenterFactory
{

	/** @var \Nette\Application\PresenterFactory */
	private $presenterFactory;

	/** @var mixed[] */
	private $mapping;

	/** @var mixed[] */
	private $defaultMapping = array(
		'*' => array('', '*Module\\', '*Presenter')
	);

	/**
	 * @param  string
	 */
	public function __construct($baseDir, Nette\DI\Container $container)
	{
		$this->presenterFactory = new Nette\Application\PresenterFactory($baseDir, $container);
	}

	/**
	 * @param mixed[] $mapping
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			$this->mapping[$module] = $mask;
		}
	}

	/**
	 * @param string $name
	 * @return \Nette\Application\IPresenter
	 */
	public function createPresenter($name)
	{
		$this->getPresenterClass($name);

		return $this->presenterFactory->createPresenter($name);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getPresenterClass(& $name)
	{
		$mapping = $this->mapping;
		$presenter = $name;
		while ($presenter) {
			if (isset($this->mapping[$presenter])) {
				$module = substr($presenter, 0, strpos($presenter, ':'));
				$mapping = array($module => $this->mapping[$presenter]);
				break;
			}

			$presenter = substr($presenter, 0, strrpos($presenter, ':'));
		}

		$this->resetMappingToPresenterFactory();
		$this->presenterFactory->setMapping($mapping);

		return $this->presenterFactory->getPresenterClass($name);
	}

	private function resetMappingToPresenterFactory()
	{
		$mapping = $this->defaultMapping;
		$closure = Closure::bind(function (Nette\Application\PresenterFactory $presenterFactory) use ($mapping) {
			$presenterFactory->mapping = $mapping;
		}, $this->presenterFactory, $this->presenterFactory);
		$closure($this->presenterFactory);
	}

}
