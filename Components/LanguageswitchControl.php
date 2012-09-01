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

use Venne;
use CmsModule\Content\Control;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguageswitchControl extends Control
{


	/** @var BaseRepository */
	protected $languageRepository;


	public function __construct($languageRepository)
	{
		parent::__construct();

		$this->languageRepository = $languageRepository;
	}


	public function getItems()
	{
		return $this->languageRepository->findAll();
	}


	public function render()
	{
		$this->template->language = $this->getPresenter()->lang;

		parent::render();
	}

}
