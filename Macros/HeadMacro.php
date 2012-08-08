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

use Venne;
use Nette\Latte\MacroNode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class HeadMacro extends \Nette\Latte\Macros\MacroSet
{

	public static function install(\Nette\Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('head', array($me, "headBegin"), array($me, "headEnd"));
		$me->addMacro('body', array($me, "bodyBegin"), array($me, "bodyEnd"));
		$me->addMacro('content', array($me, 'contentBegin'));
		$me->addMacro('extensions', array($me, 'extensionsBegin'));
	}



	public function headBegin(MacroNode $node, $writer)
	{
		return $writer->write('ob_start();');
	}



	public function headEnd(MacroNode $node, $writer)
	{
		return $writer->write('$_headMacroData = ob_get_clean();');
	}



	public function bodyBegin(MacroNode $node, $writer)
	{
		return $writer->write('ob_start();'/* echo $presenter["vennePanel"]->render();'*/);
	}



	public function bodyEnd(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('$_bodyMacroData = ob_get_clean();?><head>
<?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onHeadBegin); ?>
<?php echo $presenter["head"]->render(); echo $_headMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onHeadEnd); ?>
</head>

<body<?php if($basePath){?> data-venne-basepath="<?php echo $basePath;?>"<?php } ?>><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onBodyBegin); ?>
<?php echo $_bodyMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onBodyEnd); ?>
</body>
<?php
');
	}



	public function contentBegin(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('Nette\Latte\Macros\UIMacros::callBlock' . "(\$_l, 'content', " . '$template->getParameters())');
	}



	public function extensionsBegin(\Nette\Latte\MacroNode $node, $writer)
	{
		return $writer->write('$presenter->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionRender);');
	}

}

