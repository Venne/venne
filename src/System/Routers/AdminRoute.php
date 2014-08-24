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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminRoute extends \Nette\Application\Routers\Route
{

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

		$name = explode(':', $appRequest->getPresenterName());
		$appRequest->setPresenterName(array_shift($name) . ':Admin:' . (count($name) > 0 ? implode(':', $name) : 'Default'));

		return $appRequest;
	}

	/**
	 * @param \Nette\Application\Request $appRequest
	 * @param \Nette\Http\Url $refUrl
	 * @return string|null
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		$name = explode(':', $appRequest->getPresenterName());
		unset($name[1]);

		if ($name[2] == 'Default') {
			unset($name[2]);
		}

		$appRequest->setPresenterName(implode(':', $name));

		return parent::constructUrl($appRequest, $refUrl);
	}

}

