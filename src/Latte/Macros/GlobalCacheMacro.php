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

use Latte\MacroNode;
use Latte\PhpWriter;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Latte;
use Nette\Utils\Random;
use stdClass;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class GlobalCacheMacro extends \Nette\Object implements \Latte\IMacro
{

	/** @var bool */
	private $used;

	/** @var string[] */
	private static $parents = array();

	/** @var string */
	private static $template;

	/**
	 * Initializes before template parsing.
	 */
	public function initialize()
	{
		$this->used = false;
	}

	/**
	 * Finishes template parsing.
	 *
	 * @return string[]
	 */
	public function finalize()
	{
		if ($this->used) {
			return array('Venne\Latte\Macros\GlobalCacheMacro::initRuntime($control, $template, $presenter->template->_g);');
		}
	}

	public function nodeOpened(MacroNode $node)
	{
		$this->used = true;
		$node->isEmpty = false;
		$node->openingCode = PhpWriter::using($node)
			->write('<?php if (Venne\Latte\Macros\GlobalCacheMacro::createCache($netteCacheStorage, %var, $presenter->template->_g->caches, ' . var_export(self::$template, true) . ', %node.array?)) { ?>',
				Random::generate()
			);
	}

	public function nodeClosed(MacroNode $node)
	{
		$node->closingCode = '<?php $_l->tmp = array_pop($presenter->template->_g->caches); if (!$_l->tmp instanceof stdClass) $_l->tmp->end(); Venne\Latte\Macros\GlobalCacheMacro::closeCache(); } ?>';
	}

	/********************* run-time helpers ****************d*g**/

	public static function initRuntime(Control $control, Template $template, stdClass $global)
	{
		self::$template = $template->getFile();
		if (!$control instanceof Presenter && count(self::$parents)) {
			end(self::$parents)->dependencies[Cache::FILES][] = $template->getFile();
		}
	}

	/**
	 * @param \Nette\Caching\IStorage $cacheStorage
	 * @param string $key
	 * @param mixed $parents
	 * @param $template
	 * @param mixed[] $args
	 * @return \Nette\Caching\OutputHelper
	 */
	public static function createCache(IStorage $cacheStorage, $key, & $parents, $template, array $args = null)
	{
		if ($args) {
			if (array_key_exists('if', $args) && !$args['if']) {
				return self::$parents[] = $parents[] = new \stdClass;
			}
			$key = array_merge(array($key), array_intersect_key($args, range(0, count($args))));
		}
		if ($parents) {
			end($parents)->dependencies[Cache::ITEMS][] = $key;
		}
		if (count(self::$parents)) {
			end(self::$parents)->dependencies[Cache::ITEMS][] = $key;
		}

		$cache = new Cache($cacheStorage, 'Nette.Templating.Cache');
		if ($helper = $cache->start($key)) {
			if (isset($args['expire'])) {
				$args['expiration'] = $args['expire']; // back compatibility
			}
			$helper->dependencies = array(
				Cache::TAGS => isset($args['tags']) ? $args['tags'] : null,
				Cache::EXPIRATION => isset($args['expiration']) ? $args['expiration'] : '+ 7 days',
				Cache::FILES => array($template),
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
