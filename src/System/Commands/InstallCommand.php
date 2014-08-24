<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Commands;

use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Venne\Security\DefaultType\UserEntity;
use Venne\Security\PermissionEntity;
use Venne\Security\RoleEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InstallCommand extends \Symfony\Component\Console\Command\Command
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $roleDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $permissionDao;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	public function __construct(EntityDao $roleDao, EntityDao $permissionDao, EntityManager $entityManager)
	{
		parent::__construct();

		$this->roleDao = $roleDao;
		$this->permissionDao = $permissionDao;
		$this->entityManager = $entityManager;
	}

	protected function configure()
	{
		$this
			->setName('venne:install')
			->setDescription('Create administration account and default roles.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');

		$email = $dialog->askAndValidate($output, 'Please enter administrator email: ', function ($email) {
			if (!Validators::isEmail($email)) {
				throw new \RuntimeException(
					'Email is not valid'
				);
			}

			return $email;
		}, false);

		$output->writeln(sprintf('Creating role "<info>%s</info>"', $email));

		$password = $dialog->askHiddenResponseAndValidate($output, 'Please enter password: ', function ($password) {
			if (strlen($password) < 5) {
				throw new \RuntimeException(
					'Password is too short'
				);
			}

			return $password;
		}, false);

		$dialog->askHiddenResponseAndValidate($output, 'Please confirm password: ', function ($password2) use ($password) {
			if ($password != $password2) {
				throw new \RuntimeException(
					'Invalid re password'
				);
			}
		}, false);

		$roles = array();
		foreach (array('guest' => null, 'authenticated' => 'guest', 'admin' => 'authenticated') as $name => $parent) {

			$output->writeln(sprintf('Creating role "<info>%s</info>"', $name));

			$roles[$name] = $role = new RoleEntity;
			$role->setName($name);

			if ($parent) {
				$role->setParent($roles[$parent]);
			}

			$this->roleDao->save($role);
		}

		$output->writeln('Setting permission for administrator');

		$permission = new PermissionEntity($roles['admin']);
		$this->permissionDao->save($permission);

		$output->writeln('Creating administrator account');

		$user = new UserEntity;
		$user->user->setPassword($password);
		$user->user->setEmail($email);
		$user->user->addRoleEntity($roles['admin']);

		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

}
