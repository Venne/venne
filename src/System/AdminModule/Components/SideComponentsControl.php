<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule\Components;

use Nette\Http\Session;
use Venne\System\AdministrationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SideComponentsControl extends \Venne\System\UI\Control
{

	const SESSION_SECTION = 'Venne.System.AdminModule.Components.SideComponentsControl';

	/** @var string|null */
	private $sideComponent;

	/** @var \Venne\System\AdministrationManager */
	private $administrationManager;

	/** @var \Nette\Http\SessionSection */
	private $sessionSection;

	public function __construct(AdministrationManager $administrationManager, Session $session)
	{
		$this->administrationManager = $administrationManager;
		$this->sessionSection = $session->getSection(self::SESSION_SECTION);
	}

	public function handleLoadSideComponent()
	{
		$this->redirect('this');
		$this->redrawControl('side');
	}

	public function handleCloseSideComponent()
	{
		$this->redirect('this', array(
			'sideComponent' => null,
		));

		$this->sessionSection->sideComponent = null;
		$this->getPresenter()->sendPayload();
	}

	/**
	 * @return null|string
	 */
	public function getCurrentSideComponentName()
	{
		return $this->sideComponent;
	}

	public function render()
	{
		if ($this->getParameter('do') === null) {
			$this->template->sideComponents = $this->administrationManager->getSideComponents();
		}

		$this->template->currentSideComponentName = $this->sideComponent;
		parent::render();
	}

	/**
	 * @return \Nette\Application\UI\Control
	 */
	protected function createComponentSideComponent()
	{
		$sideComponents = $this->administrationManager->getSideComponents();

		return $sideComponents[$this->sideComponent]['factory']->create();
	}

	/**
	 * @param mixed[] $params
	 */
	public function loadState(array $params)
	{
		$this->sideComponent = array_key_exists('sideComponent', $params)
			? $params['sideComponent']
			: $this->sessionSection->sideComponent;

		parent::loadState($params);
	}

	/**
	 * @param mixed[] $params
	 */
	public function saveState(array & $params, $reflection = null)
	{
		$this->sessionSection->sideComponent = $this->sideComponent;

		parent::saveState($params, $reflection);
	}

}
