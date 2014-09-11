<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait AjaxControlTrait
{

	/**
	 * @param integer|string $code
	 * @param string|mixed[] $destination
	 * @param mixed[]|null $args
	 */
	public function redirect($code, $destination = NULL, $args = array())
	{
		$presenter = $this->getPresenter();

		if (!$presenter->isAjax()) {
			if ($destination === null) {
				parent::redirect($code);
			}

			if (count($args) === 0) {
				parent::redirect($code, $destination);
			}

			parent::redirect($code, $destination, $args);
		}

		if (!is_numeric($code)) {
			$args = $destination;
			$destination = $code;
		}

		$args = (array) $args;
		$presenter->payload->redirect = $this->link($destination, $args);

		foreach ($this->getPersistentParams() as $name) {
			if (array_key_exists($name, $args)) {
				$this->$name = $args[$name];
			}
		}
	}

}
