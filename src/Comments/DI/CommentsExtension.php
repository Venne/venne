<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Comments\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Venne\System\DI\IPresenterProvider;
use Venne\System\DI\SystemExtension;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CommentsExtension extends CompilerExtension implements IEntityProvider, IPresenterProvider
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('commentControlFactory'))
			->setImplement('Venne\Comments\Components\ICommentControlFactory')
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Comments\CommentEntity'))))
			->setInject(TRUE);

		$container->addDefinition($this->prefix('commentsControlFactory'))
			->setImplement('Venne\Comments\Components\ICommentsControlFactory')
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Comments\CommentEntity'))))
			->addTag(SystemExtension::TRAY_COMPONENT_TAG)
			->addTag(WidgetsExtension::WIDGET_TAG, 'comments')
			->setInject(TRUE);

		$container->addDefinition($this->prefix('chatControlFactory'))
			->setImplement('Venne\Comments\Components\IChatControlFactory')
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Comments\CommentEntity'))))
			->addTag(WidgetsExtension::WIDGET_TAG, 'chat')
			->setInject(TRUE);

		$container->addDefinition($this->prefix('commentFormFactory'))
			->setClass('Venne\Comments\Components\CommentFormFactory', array(new Statement('@system.admin.basicFormFactory', array())));
	}


	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'Comments' => 'Venne\Comments\*Module\*Presenter',
		);
	}


	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Comments' => dirname(__DIR__) . '/*Entity.php',
		);
	}

}
