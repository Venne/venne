<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use Nette\Application\Responses\JsonResponse;
use Nette\Callback;
use Nette\InvalidArgumentException;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BrowserControl extends Control
{

	/** @var callable */
	public $onExpand;

	/** @var array */
	public $onClick;

	/** @var callable */
	protected $loadCallback;

	/** @var callable */
	protected $dropCallback;

	/** @var array */
	protected $contentMenu = array();


	/**
	 * @param $name
	 * @param $callback
	 * @throws InvalidArgumentException
	 */
	public function addContentMenu($name, $callback)
	{
		if (isset($this->contentMenu[$name])) {
			throw new InvalidArgumentException("Content menu '$name' is already exists.");
		}

		$this->contentMenu[$name] = Callback::create($callback);
	}


	/**
	 * @return array
	 */
	public function getContentMenu()
	{
		return $this->contentMenu;
	}


	/**
	 * @param callable $dropCallback
	 */
	public function setDropCallback($dropCallback)
	{
		$this->dropCallback = $dropCallback;
	}


	/**
	 * @return callable
	 */
	public function getDropCallback()
	{
		return $this->dropCallback;
	}


	/**
	 * @param callable $loadCallback
	 */
	public function setLoadCallback($loadCallback)
	{
		$this->loadCallback = $loadCallback;
	}


	/**
	 * @return callable
	 */
	public function getLoadCallback()
	{
		return $this->loadCallback;
	}


	public function handleClick($key)
	{
		$this->onClick($key);
	}


	public function render()
	{
		$this->template->render();
	}


	public function getPages($parent = NULL)
	{
		return Callback::create($this->loadCallback)->invoke($parent);
	}


	public function handleContentMenu($name)
	{
		$this->contentMenu[$name]->invoke();
	}


	public function handleGetPages($parent = NULL)
	{
		$this->getPresenter()->sendResponse(new JsonResponse($this->getPages($parent)));
	}


	public function handleSetParent($from = NULL, $to = NULL, $dropmode = NULL)
	{
		Callback::create($this->dropCallback)->invoke($from, $to, $dropmode);
	}


	public function handleExpand($key, $open)
	{
		$this->onExpand($key, $open === 'true');
	}
}
