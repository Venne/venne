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
class LayoutForm extends \CmsModule\Forms\BaseForm
{

	/** @var ScannerService */
	protected $scannerService;


	public function attached($obj)
	{
		$layouts = array_keys($this->scannerService->getLayoutFiles());
		if (array_search('app', $layouts) === false) {
			$layouts = array_merge(array('app'), $layouts);
		}

		$this->addGroup('Layout settings');
		$this->addText('name', 'Name');
		$this->addSelect('parent', 'Save to')->setItems($layouts, FALSE);

		parent::attached($obj);
	}


	/**
	 * @param ScannerService $scannerService
	 */
	public function setScannerService(ScannerService $scannerService)
	{
		$this->scannerService = $scannerService;
	}
}
