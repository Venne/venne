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
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Comments\CommentEntity;
use Venne\Security\UserEntity;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ChatControl extends Control
{

	/** @var UserEntity|NULL */
	private $recipient;

	/** @var CommentFormFactory */
	private $commentFormFactory;

	/** @var EntityDao */
	private $commentDao;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;

	/** @var ICommentControlFactory */
	private $commentControlFactory;

	/** @var CommentEntity */
	private $olderThan;

	/** @var Session */
	private $session;


	public function __construct(
		EntityDao $commentDao,
		CommentFormFactory $commentFormFactory,
		FormFactoryFactory $formFactoryFactory,
		ICommentControlFactory $commentsControlFactory,
		Session $session
	)
	{
		parent::__construct();

		$this->commentDao = $commentDao;
		$this->commentFormFactory = $commentFormFactory;
		$this->formFactoryFactory = $formFactoryFactory;
		$this->commentControlFactory = $commentsControlFactory;
		$this->session = $session;
	}


	public function handleComment()
	{
		$this->redrawControl('comment');
	}


	public function handleLoad($id)
	{
		$this->olderThan = $this->commentDao->find($id);
		$this->redrawControl('content');
	}


	public function handleCheck($id)
	{
		$this->session->close();

		$this->redrawControl('check');
		$this->template->last = $newerThan = $this->commentDao->find($id);

		for ($x = 0; $x < 100; $x++) {
			if ($this->countNewComments($newerThan)) {
				$this->template->new = $this->getNewComments($newerThan);
				$this->template->last = end($this->template->new);
				$this->redrawControl('new');
				break;
			}

			sleep(1);
		}
	}


	public function createComponentForm()
	{
		$form = $this->formFactoryFactory
			->create($this->commentFormFactory)
			->setEntity($this->createEntity())
			->create();

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	protected function createEntity()
	{
		$entity = new CommentEntity;
		$entity->author = $this->presenter->user->identity;
		return $entity;
	}


	public function formSuccess(Form $form)
	{
		$this->flashMessage($this->presenter->translator->translate('Comment has been saved'), 'success');

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->template->focus = TRUE;
		$this->redrawControl('form');
	}


	public function countComments()
	{
		return $this->getDql()
			->select('COUNT(a.id)')
			->getQuery()
			->getSingleScalarResult();
	}


	public function getComments($limit = 10)
	{
		$qb = $this->getDql()
			->orderBy('a.created', 'DESC')
			->setMaxResults($limit);

		if ($this->olderThan) {
			$qb->andWhere('a.created < :older')->setParameter('older', $this->olderThan->created);
		}

		return $qb
			->getQuery()
			->getResult();
	}


	public function countNewComments(CommentEntity $newerThen)
	{
		return $this->getDql()
			->select('COUNT(a.id)')
			->andWhere('a.created > :newer')->setParameter('newer', $newerThen->created)
			->getQuery()
			->getSingleScalarResult();
	}


	public function getNewComments(CommentEntity $newerThen)
	{
		return $this->getDql()
			->orderBy('a.created', 'DESC')
			->andWhere('a.created > :newer')->setParameter('newer', $newerThen->created)
			->getQuery()
			->getResult();
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getDql()
	{
		$qb = $this->commentDao->createQueryBuilder('a');

		if ($this->recipient) {
			$qb = $qb->andWhere('a.recipient = :recipient')->setParameter('recipient', $this->recipient);
		} else {
			$qb = $qb->andWhere('a.recipient IS NULL');
		}

		return $qb;
	}


	public function render()
	{
		$this->template->render();
	}


	protected function createComponentComment()
	{
		return $this->commentControlFactory->create();
	}

}

