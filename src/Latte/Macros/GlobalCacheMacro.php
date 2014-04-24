<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Latte\Macros;

use Latte\IMacro;
use Latte\MacroNode;
use Latte\PhpWriter;
use Nette;
use Nette\Latte;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class GlobalCacheMacro extends Nette\Object implements IMacro
{

	/** @var bool */
	private $used;

	/** @var array */
	private static $parents = array();

	/** @var string */
	private static $template;


	/**
	 * Initializes before template parsing.
	 * @return void
	 */
	public function initialize()
	{
		$this->used = FALSE;
	}


	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		if ($this->used) {
			return array('Venne\Latte\Macros\GlobalCacheMacro::initRuntime($control, $template, $presenter->template->_g);');
		}
	}


	/**
	 * New node is found.
	 * @return bool
	 */
	public function nodeOpened(MacroNode $node)
	{
		$this->used = TRUE;
		$node->isEmpty = FALSE;
		$node->openingCode = PhpWriter::using($node)
			->write('<?php if (Venne\Latte\Macros\GlobalCacheMacro::createCache($netteCacheStorage, %var, $presenter->template->_g->caches, ' . var_export(self::$template, TRUE) . ', %node.array?)) { ?>',
				Nette\Utils\Strings::random()
			);
	}


	/**
	 * Node is closed.
	 * @return void
	 */
	public function nodeClosed(MacroNode $node)
	{
		$node->closingCode = '<?php $_l->tmp = array_pop($presenter->template->_g->caches); if (!$_l->tmp instanceof stdClass) $_l->tmp->end(); Venne\Latte\Macros\GlobalCacheMacro::closeCache(); } ?>';
	}


	/********************* run-time helpers ****************d*g**/


	/**
	 * @return void
	 */
	public static function initRuntime(Nette\Application\UI\Control $control, Nette\Templating\FileTemplate $template, \stdClass $global)
	{
		self::$template = $template->getFile();
		if (!$control instanceof Nette\Application\UI\Presenter && count(self::$parents)) {
			end(self::$parents)->dependencies[Nette\Caching\Cache::FILES][] = $template->getFile();
		}
	}


	/**
	 * Starts the output cache. Returns Nette\Caching\OutputHelper object if buffering was started.
	 * @param  Nette\Caching\IStorage
	 * @param  string
	 * @param  Nette\Caching\OutputHelper[]
	 * @param  array
	 * @return Nette\Caching\OutputHelper
	 */
	public static function createCache(Nette\Caching\IStorage $cacheStorage, $key, & $parents, $template, array $args = NULL)
	{
		if ($args) {
			if (array_key_exists('if', $args) && !$args['if']) {
				return self::$parents[] = $parents[] = new \stdClass;
			}
			$key = array_merge(array($key), array_intersect_key($args, range(0, count($args))));
		}
		if ($parents) {
			end($parents)->dependencies[Nette\Caching\Cache::ITEMS][] = $key;
		}
		if (count(self::$parents)) {
			end(self::$parents)->dependencies[Nette\Caching\Cache::ITEMS][] = $key;
		}

		$cache = new Nette\Caching\Cache($cacheStorage, 'Nette.Templating.Cache');
		if ($helper = $cache->start($key)) {
			if (isset($args['expire'])) {
				$args['expiration'] = $args['expire']; // back compatibility
			}
			$helper->dependencies = array(
				Nette\Caching\Cache::TAGS => isset($args['tags']) ? $args['tags'] : NULL,
				Nette\Caching\Cache::EXPIRATION => isset($args['expiration']) ? $args['expiration'] : '+ 7 days',
				Nette\Caching\Cache::FILES => array($template),
			);
			self::$parents[] = $parents[] = $helper;
		}
		return $helper;
	}


	public static function closeCache()
	{
		array_pop(self::$parents);
	}

}
