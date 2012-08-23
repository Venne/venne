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

use Venne;
use CmsModule\Services\ScannerService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayouteditForm extends \CmsModule\Forms\BaseForm
{


	public function attached($obj)
	{
		$this->addGroup();
		$this->addTextArea('text', NULL, 500, 40)->getControlPrototype()->attrs['class'] = 'input-xxlarge';

		parent::attached($obj);
	}
}
