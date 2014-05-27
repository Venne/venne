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

use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Presenter;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\DefaultType\RegistrationFormFactory;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\AdminPresenterTrait;
use Venne\Security\AdminUserFormFactory;
use Venne\Security\DefaultType\IAdminFormFactory;
use Venne\Security\IFormFactory;
use Venne\Security\LoginEntity;
use Venne\Security\SecurityManager;
use Grido\DataSources\ArraySource;
use Grido\DataSources\Doctrine;
use Nette\Http\Session;
use Nette\Utils\Html;
use Venne\Forms\Form;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RequestPresenter extends Presenter
{

	use AdminPresenterTrait;

	private $requestFormFactory;

}
