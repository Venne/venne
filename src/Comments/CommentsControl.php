<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Comments;

use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Form;
use Nette\InvalidStateException;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\UserEntity;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CommentsControl extends Control
{

	/** @var string|NULL */
	private $tag;

	/** @var UserEntity|NULL */
	private $recipient;

	/** @var CommentFormFactory */
	private $commentFormFactory;

	/** @var EntityDao */
	private $commentDao;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;


	/**
	 * @param EntityDao $commentDao
	 * @param CommentFormFactory $commentFormFactory
	 * @param FormFactoryFactory $formFactoryFactory
	 */
	public function __construct(EntityDao $commentDao, CommentFormFactory $commentFormFactory, FormFactoryFactory $formFactoryFactory)
	{
		parent::__construct();

		$this->commentDao = $commentDao;
		$this->commentFormFactory = $commentFormFactory;
		$this->formFactoryFactory = $formFactoryFactory;
	}


	/**
	 * @param NULL|string $tag
	 */
	public function setTag($tag)
	{
		$this->tag = $tag;
	}


	public function handleComment()
	{
		$this->template->showComment = TRUE;
		$this->redrawControl('comment');
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
		$entity->tag = $this->tag;
		return $entity;
	}


	public function formSuccess(Form $form)
	{
		$this->flashMessage($this->presenter->translator->translate('Comment has been saved'), 'success');

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->redrawControl('comments');
	}


	public function getComments()
	{
		$qb = $this->commentDao->createQueryBuilder('a')
			->andWhere('a.tag = :tag')->setParameter('tag', $this->tag)
			->orderBy('a.created', 'DESC');

		if ($this->recipient) {
			$qb = $qb->andWhere('a.recipient = :recipient')->setParameter('recipient', $this->recipient);
		} else {
			$qb = $qb->andWhere('a.recipient IS NULL');
		}

		return $qb->getQuery()->getResult();
	}


	public function render()
	{
		$this->template->render();
	}


	protected function createComponent($name)
	{
		if ($control = parent::createComponent($name)) {
			return $control;
		}

		$control = clone $this;
		$control->setTag($name);
		return $control;
	}

}

