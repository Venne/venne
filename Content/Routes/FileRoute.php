<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Routes;

use Nette\Object;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\Routers\Route;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\ContentManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileRoute extends Route
{

	/** @var BaseRepository */
	protected $fileRepository;


	/**
	 * @param BaseRepository $fileRepository
	 */
	public function __construct(BaseRepository $fileRepository)
	{
		$this->fileRepository = $fileRepository;

		parent::__construct("public/media/_cache/<size>/<format>/<type>/<url .+>", array(
			"presenter" => 'Cms:File',
			'action' => 'default',
			"url" => array(
				self::VALUE => "",
				self::FILTER_IN => NULL,
				self::FILTER_OUT => NULL,
			)
		));
	}
}
