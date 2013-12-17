<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Rss;

use CmsModule\Content\Components\RouteItemsControl;
use CmsModule\Content\SectionControl;
use CmsModule\Pages\Users\UserEntity;
use Grido\DataSources\Doctrine;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TableControl extends SectionControl
{

	/** @var RssRepository */
	private $rssRepository;

	/** @var RssFormFactory */
	private $rssFormFactory;


	/**
	 * @param RssRepository $rssRepository
	 * @param RssFormFactory $rssFormFactory
	 */
	public function inject(RssRepository $rssRepository, RssFormFactory $rssFormFactory)
	{
		$this->rssFormFactory = $rssFormFactory;
		$this->rssRepository = $rssRepository;
	}


	protected function createComponentTable()
	{
		$_this = $this;
		$repository = $this->rssRepository;

		$adminControl = new RouteItemsControl($this->rssRepository, $this->getExtendedPage());
		$admin = $adminControl->getTable();
		$table = $admin->getTable();
		$table->setModel(new Doctrine($this->rssRepository->createQueryBuilder('a')
				->andWhere('a.extendedPage = :page')
				->setParameter('page', $this->extendedPage->id)
		));

		$entity = $this->extendedPage;
		$form = $admin->createForm($this->rssFormFactory, 'RSS', function () use ($repository, $entity, $_this) {
			$entity = $repository->createNew(array($entity));
			if ($_this->presenter->user->identity instanceof UserEntity) {
				$entity->route->author = $_this->presenter->user->identity;
			}
			return $entity;
		}, \CmsModule\Components\Table\Form::TYPE_FULL);

		$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'), $admin::MODE_PLACE);

		$table->addAction('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $adminControl;
	}


	public function render()
	{
		$this->template->render();
	}
}
