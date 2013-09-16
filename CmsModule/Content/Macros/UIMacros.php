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

use Nette\Latte\Compiler;
use Nette\Latte\MacroNode;
use Nette\Latte\MacroTokenizer;
use Nette\Latte\PhpWriter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UIMacros extends \Nette\Latte\Macros\MacroSet
{


	/**
	 * @param Compiler $compiler
	 * @return \Nette\Latte\Macros\MacroSet
	 */
	public static function install(Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('isPublished', array($me, 'macroIsPublished'), array($me, 'macroEndIsPublished'));
		return $me;
	}


	/**
	 * {ifLinkExists destination [,] [params]}
	 * n:ifLinkExists="destination [,] [params]"
	 */
	public function macroIsPublished(MacroNode $node, PhpWriter $writer)
	{
		$c = '
		if ($_macroIfLinkExistsRet) {
			$_isPublishedRet = TRUE;

			if (isset($_macroIfLinkExistsArgs[\'special\'])) {
				$_macroIsPublishedPage = $_presenter->entityManager->getRepository(\'CmsModule\Content\Entities\PageEntity\')->findOneBy(array(\'special\' => $_macroIfLinkExistsArgs[\'special\']));
				if (!$_macroIsPublishedPage->published || !$_macroIsPublishedPage->mainRoute->published) {
					$_isPublishedRet = FALSE;
				}
			} else if (isset($_macroIfLinkExistsArgs[\'route\']) && (!$_macroIfLinkExistsArgs[\'route\']->published || !$_macroIfLinkExistsArgs[\'route\']->page->published)) {
				$_isPublishedRet = FALSE;
			}
		} else {
			$_isPublishedRet = FALSE;
		}
		';
		if ($node->prefix === $node::PREFIX_TAG) {
			return $writer->write($c . ($node->htmlNode->closing ? 'if (array_pop($_l->ifs)):' : 'if ($_l->ifs[] = ($_isPublishedRet)):'));
		}
		return $writer->write($c . 'if ($_isPublishedRet):');
	}


	/**
	 * {/ifLinkExists}
	 */
	public function macroEndIsPublished(MacroNode $node, PhpWriter $writer)
	{
		return 'endif';
	}

}
