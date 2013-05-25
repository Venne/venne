<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsTests\Frontend;

use CmsModule\Administration\Presenters\AdministratorPresenter;
use CmsTests\PresenterCase;
use Nette\Application\IResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\DI\Container;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;
use Venne\Config\Configurator;

require __DIR__ . '/../PresenterCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FrontendCase extends PresenterCase
{

}
