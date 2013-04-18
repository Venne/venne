<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Macros;

use CmsModule\Content\Elements\Helpers;
use Venne;
use Nette\Utils\Strings;
use Nette\Latte\MacroNode;
use Nette\Latte\PhpWriter;
use Nette\Latte\Compiler;
use Nette\Latte\CompileException;
use Nette\Latte\Macros\MacroSet;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ElementMacro extends MacroSet
{

	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('element', array($me, 'macroElement'));
	}


	/**
	 * {element name[:method] [key]}
	 */
	public function macroElement(MacroNode $node, PhpWriter $writer)
	{
		$rawName = $node->tokenizer->fetchWord();
		if ($rawName === FALSE) {
			throw new CompileException("Missing element type in {element}");
		}
		$rawName = explode(':', $rawName, 2);
		$name = $writer->formatWord($rawName[0]);
		$method = isset($rawName[1]) ? ucfirst($rawName[1]) : '';
		$method = Strings::match($method, '#^\w*$#') ? "render$method" : "{\"render$method\"}";
		$idRaw = $node->tokenizer->fetchWord();

		if (!$idRaw) {
			throw new CompileException("Missing element title in {element}");
		}

		if (substr($idRaw, 0, 1) !== '$') {
			$id = Helpers::encodeName($idRaw);
		} else {
			$id = $idRaw;
		}

		return
			'$_ctrl = $_presenter->getComponent(\CmsModule\Content\ElementManager::ELEMENT_PREFIX . ' . '"' . $id . '"' . ' . \'_\' . ' . $name . '); '
			. '$_ctrl->setName("' . trim($idRaw, '"\'') . '");'
			. 'if ($presenter->mode == \CmsModule\Presenters\BasePresenter::MODE_EDIT) { echo "<span id=\"' . \CmsModule\Content\ElementManager::ELEMENT_PREFIX . (substr($id, 0, 1) === '$' ? '{' . $id . '}' : $id) . '_' . $rawName[0] . '\" style=\"display: inline-block; min-width: 50px; min-height: 25px;\" class=\"venne-element-container\" data-venne-element-id=\"' . trim($id, '"\'') . '\" data-venne-element-name=\"' . $rawName[0] . '\" data-venne-element-route=\"" . $presenter->route->id . "\" data-venne-element-language=\"" . $presenter->language->id . "\" data-venne-element-buttons=\"" . (str_replace(\'"\', "\'", json_encode($_ctrl->getViews()))) . "\">"; }'
			. 'if ($_ctrl instanceof Nette\Application\UI\IRenderable) $_ctrl->validateControl(); '
			. "\$_ctrl->$method();"
			. 'if ($presenter->mode == \CmsModule\Presenters\BasePresenter::MODE_EDIT) { echo "</span>"; }';
	}
}
