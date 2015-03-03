<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User\DefaultType;

use Nette\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PasswordContainer extends \Nette\Forms\Container
{

	/** @var bool */
	private $forceChangePassword;

	public function __construct($forceChangePassword = false)
	{
		$this->monitor(Form::class);
		$this->forceChangePassword = $forceChangePassword;
	}

	protected function attached($obj)
	{
		parent::attached($obj);

		$form = $this->getForm();

		$this->setCurrentGroup($form->addGroup());

		if (!$this->forceChangePassword) {
			$passwordNew = $this->addCheckbox('password_new', 'Change password');
			$passwordNew->addCondition($form::EQUAL, true)->toggle('setPasswd');
		}

		$this->setCurrentGroup($form->addGroup()->setOption('container', 'fieldset id=setPasswd'));

		$passwordRules = $this->addPassword('password_set', 'Password');
		if (!$this->forceChangePassword) {
			$passwordRules->addConditionOn($passwordNew, Form::FILLED);
		}
		$passwordRules
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);

		$confirmPasswordRules = $this->addPassword('password_confirm', 'Confirm password');
		if (!$this->forceChangePassword) {
			$confirmPasswordRules->addConditionOn($passwordNew, Form::FILLED);
		}
		$confirmPasswordRules
			->addRule(Form::EQUAL, 'Invalid re password', $this['password_set']);
	}

	/**
	 * @return bool
	 */
	public function isPasswordSet()
	{
		return $this->forceChangePassword || (bool) $this['password_new']->getValue();
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this['password_set']->getValue();
	}

}
