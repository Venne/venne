<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class InvitationPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var InvitationsTableFactory */
	private $invitationTableFactory;


	public function __construct(InvitationsTableFactory $invitationTableFactory)
	{
		$this->invitationTableFactory = $invitationTableFactory;
	}


	public function createComponentTable()
	{
		return $this->invitationTableFactory->create();
	}

}
