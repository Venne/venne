<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications;

use Nette\Application\IPresenterFactory;
use Nette\Application\Request as NetteApplicationRequest;
use Nette\Application\Responses\TextResponse;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Utils\Strings;
use Venne\Notifications\AdminModule\EmailPresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EmailManager extends \Nette\Object
{

	/** @var \Nette\Mail\IMailer */
	private $mailer;

	/** @var \Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/** @var string */
	private $senderEmail;

	/** @var string */
	private $senderName;

	/**
	 * @param string $senderEmail
	 * @param string $senderName
	 * @param \Nette\Mail\IMailer $mailer
	 * @param \Nette\Application\IPresenterFactory $presenterFactory
	 */
	public function __construct(
		$senderEmail,
		$senderName,
		IMailer $mailer,
		IPresenterFactory $presenterFactory
	) {
		$this->senderEmail = $senderEmail;
		$this->senderName = $senderName;
		$this->mailer = $mailer;
		$this->presenterFactory = $presenterFactory;
	}

	/**
	 * @param string $user
	 * @param string $name
	 * @param string $type
	 * @param string $action
	 * @param mixed[] $templateArgs
	 */
	public function send($user, $name, $type, $action, array $templateArgs = array())
	{
		$presenter = $this->createPresenter();
		$request = $this->getRequest($type, $action);
		$response = $presenter->run($request);

		$presenter->template->user = $user;
		$presenter->template->name = $name;
		foreach ($templateArgs as $key => $val) {
			$presenter->template->$key = $val;
		}

		if (!$response instanceof TextResponse) {
			throw new InvalidArgumentException(sprintf('Type \'%s\' does not exist.', $type));
		}

		try {
			$data = (string) $response->getSource();
		} catch (\Nette\Application\BadRequestException $e) {
			if (Strings::startsWith($e->getMessage(), 'Page not found. Missing template')) {
				throw new InvalidArgumentException(sprintf('Type \'%s\' does not exist.', $type));
			}
		}

		$message = new Message;
		$message->setHtmlBody($data);
		$message->setSubject(ucfirst($action));
		$message->setFrom($this->senderEmail, $this->senderName ?: null);
		$message->addTo($user, $name);

		$this->mailer->send($message);
	}

	/**
	 * @param string $type
	 * @param string $action
	 * @return \Nette\Application\Request
	 */
	private function getRequest($type, $action)
	{
		return new NetteApplicationRequest('Admin:Notifications:Email', 'GET', array('type' => $type, 'action' => $action));
	}

	/**
	 * @return \Venne\Notifications\AdminModule\EmailPresenter
	 */
	private function createPresenter()
	{
		$presenter = $this->presenterFactory->createPresenter('Admin:Notifications:Email');

		if ($presenter instanceof EmailPresenter) {
			return $presenter;
		}

		throw new InvalidStateException('Presenter must be instance of \'Venne\Notification\AdminModule\EmailPresenter\'.');
	}

}
