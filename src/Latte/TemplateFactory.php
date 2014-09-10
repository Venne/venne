<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Latte;

use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\Loader;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\Bridges\CacheLatte\CacheMacro;
use Nette\Bridges\FormsLatte\FormMacros;
use Nette\Caching\IStorage;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Security\User;
use Venne\Packages\PathResolver;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TemplateFactory extends \Nette\Object implements \Nette\Application\UI\ITemplateFactory
{

	/** @var \Nette\Bridges\ApplicationLatte\ILatteFactory */
	private $latteFactory;

	/** @var \Nette\Http\IRequest */
	private $httpRequest;

	/** @var \Nette\Http\IResponse */
	private $httpResponse;

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var \Venne\Packages\PathResolver */
	private $pathResolver;

	public function __construct(
		ILatteFactory $latteFactory,
		IRequest $httpRequest = null,
		IResponse $httpResponse = null,
		User $user = null,
		IStorage $cacheStorage = null,
		PathResolver $pathResolver = null
	)
	{
		$this->latteFactory = $latteFactory;
		$this->httpRequest = $httpRequest;
		$this->httpResponse = $httpResponse;
		$this->user = $user;
		$this->cacheStorage = $cacheStorage;
		$this->pathResolver = $pathResolver;
	}

	/**
	 * @param \Nette\Application\UI\Control $control
	 * @return \Nette\Bridges\ApplicationLatte\Template
	 */
	public function createTemplate(\Nette\Application\UI\Control $control)
	{
		$latte = $this->latteFactory->create();
		$template = new Template($latte);
		$presenter = $control->getPresenter(false);

		if ($control instanceof \Nette\Application\UI\Presenter) {
			$latte->setLoader(new Loader($control));
		}

		if ($latte->onCompile instanceof \Traversable) {
			$latte->onCompile = iterator_to_array($latte->onCompile);
		}

		array_unshift($latte->onCompile, function ($latte) use ($control, $template) {
			$latte->getParser()->shortNoEscape = true;
			$latte->getCompiler()->addMacro('cache', new CacheMacro($latte->getCompiler()));
			UIMacros::install($latte->getCompiler());
			FormMacros::install($latte->getCompiler());
			$control->templatePrepareFilters($template);
		});

		$latte->addFilter('url', 'rawurlencode'); // back compatiblity
		foreach (array('normalize', 'toAscii', 'webalize', 'padLeft', 'padRight', 'reverse') as $name) {
			$latte->addFilter($name, 'Nette\Utils\Strings::' . $name);
		}
		$latte->addFilter('null', function () {
		});
		$latte->addFilter('length', function ($var) {
			return is_string($var) ? \Nette\Utils\Strings::length($var) : count($var);
		});
		$latte->addFilter('modifyDate', function ($time, $delta, $unit = null) {
			return $time == null ? null : \Nette\Utils\DateTime::from($time)->modify($delta . $unit); // intentionally ==
		});

		// default parameters
		$template->control = $template->_control = $control;
		$template->presenter = $template->_presenter = $presenter;
		$template->netteHttpResponse = $this->httpResponse;
		$template->netteCacheStorage = $this->cacheStorage;
		$template->pathResolver = $this->pathResolver;
		$template->netteUser = $this->user;
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $this->httpRequest ? rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/') : null);
		$template->flashes = array();

		if ($presenter instanceof \Nette\Application\UI\Presenter && $presenter->hasFlashSession()) {
			$id = $control->getParameterId('flash');
			$template->flashes = (array) $presenter->getFlashSession()->$id;
		}

		return $template;
	}

}
