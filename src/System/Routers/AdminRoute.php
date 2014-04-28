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

use Nette;
use Nette\Application;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminRoute extends Nette\Application\Routers\Route
{

	public function __construct($presenter, $adminPrefix, $flags = NULL)
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


	public function match(Nette\Http\IRequest $httpRequest)
	{
		if (($appRequest = parent::match($httpRequest)) === NULL) {
			return NULL;
		}

		$name = explode(':', $appRequest->getPresenterName());
		$appRequest->setPresenterName(array_shift($name) . ':Admin:' . (count($name) > 0 ? implode(':', $name) : 'Default'));

		return $appRequest;
	}


	public function constructUrl(Application\Request $appRequest, Nette\Http\Url $refUrl)
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

