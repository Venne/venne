<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Routers;

use Nette\Application\Request;
use Nette\Http\IRequest;
use Nette\Http\Url;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminRoute extends \Nette\Application\Routers\Route
{

	const SUBMODULE_NAME = 'Admin';

	const DEFAULT_PRESENTER = 'Default';

	/**
	 * @param string $presenter
	 * @param string $adminPrefix
	 * @param int|null $flags
	 */
	public function __construct($presenter, $adminPrefix, $flags = null)
	{
		$mask = $adminPrefix . '[<presenter .+>]?action=<action>[&id=<id>]';
		$metadata = array(
			'locale' => array(
				self::PATTERN => '.+',
			),
			'presenter' => array(
				self::VALUE => implode(':', $presenter),
				self::FILTER_IN => function ($s) {
					$s = str_replace('/', '.', $s);
					$s = strtolower($s);
					$s = preg_replace('#([.-])(?=[a-z])#', '$1 ', $s);
					$s = ucwords($s);
					$s = str_replace('. ', ':', $s);
					$s = str_replace('- ', '', $s);

					return $s;
				},
				self::FILTER_OUT => function ($s) {
					$s = strtr($s, ':', '.');
					$s = preg_replace('#([^.])(?=[A-Z])#', '$1-', $s);
					$s = strtolower($s);
					$s = rawurlencode($s);

					return str_replace('.', '/', $s);
				},
			),
			'action' => 'default',
		);

		parent::__construct($mask, $metadata, $flags);
	}

	/**
	 * @param \Nette\Http\IRequest $httpRequest
	 * @return \Nette\Application\Request|null
	 */
	public function match(IRequest $httpRequest)
	{
		if (($appRequest = parent::match($httpRequest)) === null) {
			return null;
		}

		$appRequest->setPresenterName(self::SUBMODULE_NAME . ':' . $appRequest->getPresenterName() . (substr_count($appRequest->getPresenterName(), ':') === 0 ? ':' . self::DEFAULT_PRESENTER : ''));

		return $appRequest;
	}

	/**
	 * @param \Nette\Application\Request $appRequest
	 * @param \Nette\Http\Url $refUrl
	 * @return string|null
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		$presenter = $appRequest->getPresenterName();

		if (!Strings::startsWith($presenter, self::SUBMODULE_NAME . ':')) {
			return null;
		}

		if (Strings::endsWith($presenter, ':' . self::DEFAULT_PRESENTER)) {
			$presenter = substr($presenter, 0, -strlen(':' . self::DEFAULT_PRESENTER));
		}

		$appRequest->setPresenterName(substr($presenter, strlen(self::SUBMODULE_NAME . ':')));

		return parent::constructUrl($appRequest, $refUrl);
	}

}
