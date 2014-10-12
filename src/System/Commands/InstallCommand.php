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

use Kdyby\Doctrine\EntityManager;
use Nette\Utils\Validators;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;
use Venne\Security\DefaultType\User;
use Venne\Security\Permission;
use Venne\Security\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InstallCommand extends \Symfony\Component\Console\Command\Command
{

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $permissionRepository;

	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();

		$this->roleRepository = $entityManager->getRepository(Role::class);
		$this->permissionRepository = $entityManager->getRepository(Permission::class);
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

		$this->entityManager->beginTransaction();
		try {
			$roles = array();
			foreach (array('guest' => null, 'authenticated' => 'guest', 'admin' => 'authenticated') as $name => $parent) {

				$output->writeln(sprintf('Creating role "<info>%s</info>"', $name));

				$roles[$name] = $role = new Role;
				$role->setName($name);

				if ($parent) {
					$role->setParent($roles[$parent]);
				}

				$this->entityManager->persist($role);
			}
			$this->entityManager->flush($roles);

			$output->writeln('Setting permission for administrator');

			$permission = new Permission($roles['admin']);
			$this->entityManager->persist($permission);

			$output->writeln('Creating administrator account');

			$user = new User;
			$user->getUser()->setPassword($password);
			$user->getUser()->setEmail($email);
			$user->getUser()->addRoleEntity($roles['admin']);

			$this->entityManager->persist($user->getUser());
			$this->entityManager->flush($user->getUser());
			$this->entityManager->persist($user);
			$this->entityManager->flush($user);

			$this->entityManager->commit();
		} catch (\Exception $e) {
			$this->entityManager->rollback();

			throw $e;
		}
	}

}
