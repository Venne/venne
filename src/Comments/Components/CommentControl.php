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
use Nette\Security\User;
use Venne\Comments\CommentEntity;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CommentControl extends Control
{

	/** @var User */
	private $user;

	/** @var EntityDao */
	private $commentDao;


	/**
	 * @param EntityDao $commentDao
	 * @param User $user
	 */
	public function __construct(EntityDao $commentDao, User $user)
	{
		parent::__construct();

		$this->commentDao = $commentDao;
		$this->user = $user;
	}


	public function render(CommentEntity $comment)
	{
		$this->template->comment = $comment;
		$this->template->render();
	}

}
