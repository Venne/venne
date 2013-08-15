<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminPermissionsFormFactory extends BasePermissionsFormFactory
{

	protected function getPrivileges(Form $form)
	{
		return $form->data->getAdminPrivileges();
	}


	protected function getPermissionColumnName()
	{
		return 'adminPermissions';
	}


	protected function getPermissionEntityName()
	{
		return 'CmsModule\Content\Entities\AdminPermissionEntity';
	}


	protected function getSecuredColumnName()
	{
		return 'adminSecured';
	}
}
