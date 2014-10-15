<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\AdminModule;

use Doctrine\ORM\EntityManager;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Venne\Notifications\NotificationSetting;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationSettingFormService extends \Venne\System\DoctrineFormService
{

	public function __construct(
		NotificationSettingFormFactory $formFactory,
		EntityManager $entityManager,
		EntityFormMapper $entityFormMapper
	) {
		parent::__construct($formFactory, $entityManager, $entityFormMapper);
	}

	/**
	 * @return string
	 */
	protected function getEntityClassName()
	{
		return NotificationSetting::class;
	}

}
