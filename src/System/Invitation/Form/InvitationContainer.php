<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Invitation\Form;

use Nette\Forms\Container;
use Nette\Forms\Form;
use Venne\Security\User\UserFacade;
use Venne\System\Registration\RegistrationFacade;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationContainer extends Container
{

	public function __construct(
		RegistrationFacade $registrationFacade,
		UserFacade $userFacade
	) {
		parent::__construct();

		$this->addSelect('registration', 'Registration', $registrationFacade->getRegistrationOptions())
			->addRule(Form::FILLED);

		$this->addText('email', 'Email')
			->addRule(Form::FILLED)
			->addRule(Form::EMAIL);
	}

}
