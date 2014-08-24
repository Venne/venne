<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Nette\Application\UI\Form;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\Http\Session;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CacheFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Nette\Caching\IStorage */
	private $cacheStorage;

	/** @var \Nette\Http\Session */
	private $session;

	public function __construct(IFormFactory $formFactory, IStorage $cacheStorage, Session $session)
	{
		$this->formFactory = $formFactory;
		$this->cacheStorage = $cacheStorage;
		$this->session = $session;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup('Options');
		$form->addRadioList('section', 'Section', array('all' => 'All', 'cache' => 'Cache', 'sessions' => 'Sessions'))
			->setDefaultValue('all')
			->addCondition($form::EQUAL, 'namespace')->toggle('namespace');

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Clear');

		$form->onSuccess[] = $this->success;

		return $form;
	}

	public function success(Form $form)
	{
		$values = $form->getValues();

		if ($values['section'] === 'all') {
			$this->cacheStorage->clean(array(Cache::ALL => true));
			$this->session->destroy();
		} elseif ($values['section'] === 'cache') {
			$this->cacheStorage->clean(array(array(Cache::ALL => true)));
		} elseif ($values['section'] === 'sessions') {
			$this->session->destroy();
		}
	}

}
