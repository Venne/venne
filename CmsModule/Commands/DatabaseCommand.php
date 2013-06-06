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

use CmsModule\Services\ConfigBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class DatabaseCommand extends Command
{

	/** @var ConfigBuilder */
	protected $config;

	protected $options = array(
		'driver',
		'host',
		'user',
		'password',
		'dbname',
		'port',
		'path',
		'memory',
	);


	/**
	 * @param \CmsModule\Services\ConfigBuilder $config
	 */
	public function __construct(ConfigBuilder $config)
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
			->setName('cms:database:set')
			->setDescription('Setup database connection.');

		foreach ($this->options as $option) {
			$this->addOption($option, null, InputOption::VALUE_NONE, "Set {$option}.");
		}
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->config->load();

		foreach ($this->options as $option) {
			if ($input->getOption($option)) {
				$this->config['parameters']['database'][$option] = $input->getOption($option);
			}
		}

		$this->config->save();
	}
}
