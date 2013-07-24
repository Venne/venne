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
class HeadMacro extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('head', array($me, 'headBegin'), array($me, 'headEnd'));
		$me->addMacro('body', array($me, 'bodyBegin'), array($me, 'bodyEnd'));
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
		return $writer->write('ob_start();');
	}


	public function bodyEnd(MacroNode $node, $writer)
	{
		return $writer->write('$_bodyMacroData = ob_get_clean();?><head>
<?php $_renderEventsArgs = new \CmsModule\Events\RenderArgs; $_renderEventsArgs->setPresenter($presenter); ?>
<?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onHeadBegin, $_renderEventsArgs); ?>
<?php echo $presenter["head"]->render(); echo $_headMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onHeadEnd, $_renderEventsArgs); ?>
</head>

<body<?php if($basePath){?> data-venne-basepath="<?php echo $basePath;?>"<?php } ?>><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onBodyBegin, $_renderEventsArgs); ?>
<?php if ($presenter instanceof \CmsModule\Presenters\FrontPresenter && $presenter->getUser()->isLoggedIn() && $presenter->isAuthorized(":Cms:Admin:Panel:") ) { echo \'<div id="venne-panel-container" style="position: fixed; top: 0; left: 0; z-index: 9999999; width: 100%; height: 43px; overflow: hidden;"><iframe src="\'.$basePath.\'/admin/en/panel?mode=1" scrolling="no" allowtransparency="true" style="width: 100%; height: 100%; overflow: hidden;" frameborder="0" id="venne-panel"></iframe></div>\'; } ?>
<?php echo $_bodyMacroData;?><?php $presenter->context->eventManager->dispatchEvent(\CmsModule\Events\RenderEvents::onBodyEnd, $_renderEventsArgs); ?>
</body>
<?php
');
	}


	public function contentBegin(MacroNode $node, $writer)
	{
		return $writer->write('Nette\Latte\Macros\UIMacros::callBlock' . "(\$_l, 'content', " . '$template->getParameters())');
	}


	public function extensionsBegin(MacroNode $node, $writer)
	{
		return $writer->write('$presenter->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionRender);');
	}
}

