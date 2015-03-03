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

use Venne\Forms\Form;
use Venne\Security\AdminUserFormFactory;
use Venne\Security\User\DefaultType\IAdminFormFactory;
use Venne\Security\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RequestPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	private $requestFormFactory;

}
