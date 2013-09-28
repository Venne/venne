<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\RouteEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Security\Repositories\UserRepository")
 * @ORM\Table(name="default_user")
 */
class DefaultUserEntity extends ExtendedUserEntity
{

}
