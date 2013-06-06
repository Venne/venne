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

use CmsModule\Content\Repositories\LanguageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to execute DQL queries in a given EntityManager.
 */
class LanguageCommand extends Command
{

	/** @var LanguageRepository */
	protected $repository;


	public function __construct(LanguageRepository $repository)
	{
		parent::__construct();

		$this->repository = $repository;
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function configure()
	{
		$this
			->setName('cms:language:add')
			->setDescription('Add new language.')
			->setDefinition(array(
				new InputArgument('name', InputArgument::REQUIRED, 'Language name.'),
				new InputArgument('short', InputArgument::REQUIRED, 'Short.'),
				new InputArgument('alias', InputArgument::REQUIRED, 'Alias.')
			));
	}


	/**
	 * @see Console\Command\Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$entity = new \CmsModule\Content\Entities\LanguageEntity();
		$entity->setName($input->getArgument('name'));
		$entity->setShort($input->getArgument('short'));
		$entity->setAlias($input->getArgument('alias'));

		$this->repository->save($entity);
	}
}
