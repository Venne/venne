<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Macros;

use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\Macros\MacroSet;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfirmMacro extends MacroSet
{

	public static function filter(MacroNode $node, $writer)
	{
		$content = $node->args;
		return $writer->write('echo " data-confirm=\"' . $content . '\"" ');
	}


	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('confirm', NULL, NULL, array($me, "filter"));
	}
}

