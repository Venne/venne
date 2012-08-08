<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\ContentExtension;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentExtensionsControl extends \Venne\Application\UI\Control {

	public function viewDefault()
	{
		$args = new \Venne\ContentExtension\EventArgs;
		$args->presenter = $this;

		$this->presenter->context->eventManager->dispatchEvent(Venne\ContentExtension\Events::onRender, $args);
	}

}
