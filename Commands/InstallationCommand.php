<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Commands;

use Venne;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use CmsModule\Services\ConfigBuilder;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class InstallationCommand extends Command
{

	/** @var ConfigBuilder */
	protected $config;

	function __construct(ConfigBuilder $config)
	{
		parent::__construct();

		$this->config = $config;
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('venne:admin:account')
			->setDescription('Setup administrator account.')
			->setDefinition(array(
			new InputArgument('login', InputArgument::REQUIRED, 'Administrator name.'),
			new InputArgument('password', InputArgument::REQUIRED, 'Password.')
		));
	}

	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->config->load();
		$this->config['parameters']['administration']['login']['name'] = $input->getArgument('login');
		$this->config['parameters']['administration']['login']['password'] = $input->getArgument('password');
		$this->config->save();
	}


	protected function getDialogHelper()
	{
		$dialog = $this->getHelperSet()->get('dialog');
		return $dialog;
	}
}
