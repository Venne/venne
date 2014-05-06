<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Comments\Components;

use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Multiplier;
use Nette\Security\User;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CommentsControl extends Control
{

	/** @var User */
	private $user;

	/** @var EntityDao */
	private $commentDao;

	/** @var IChatControlFactory */
	private $chatControlFactory;


	public function __construct(
		EntityDao $commentDao,
		User $user,
		IChatControlFactory $chatControlFactory
	)
	{
		parent::__construct();

		$this->commentDao = $commentDao;
		$this->user = $user;
		$this->chatControlFactory = $chatControlFactory;
	}


	public function handleOpenChat($tag = NULL)
	{
		$this->template->chat = $tag ?: 'null';
		$this->redrawControl('chat-container');
	}


	public function countComments()
	{
		$ret = $this->getDql()
			->select('COUNT(a.id)')
			->getQuery()
			->getScalarResult();

		return count($ret);
	}


	public function getComments()
	{
		return $this->getDql()
			->orderBy('a.created', 'DESC')
			->getQuery()
			->getResult();
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getDql()
	{
		return $this->commentDao->createQueryBuilder('a')
			->andWhere('a.recipient IS NULL OR a.recipient = :recipient')->setParameter('recipient', $this->user->identity)
			->groupBy('a.tag')
			->addGroupBy('a.recipient');
	}


	public function createComponentChat()
	{
		return new Multiplier(function ($tag) {
			$chat = $this->chatControlFactory->create();
			$chat->setTag($tag == 'null' ? NULL : $tag);
			return $chat;
		});
	}


	public function render()
	{
		$this->template->render();
	}

}

