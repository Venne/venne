<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use FormsModule\Mappers\ConfigMapper;
use CmsModule\Services\ScannerService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class WebsiteFormFactory extends FormFactory
{

	/** @var ConfigMapper */
	protected $mapper;


	/** @var ScannerService */
	protected $scannerService;


	/**
	 * @param ScannerService $scannerService
	 * @param ConfigMapper $mapper
	 */
	public function __construct(ScannerService $scannerService, ConfigMapper $mapper)
	{
		$this->scannerService = $scannerService;
		$this->mapper = $mapper;
	}


	protected function getMapper()
	{
		$mapper = clone $this->mapper;
		$mapper->setRoot('parameters.website');
		return $mapper;
	}


	protected function getControlExtensions()
	{
		return array(
			new \FormsModule\ControlExtensions\ControlExtension(),
		);
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup("Global meta informations");
		$form->addText("title", "Title")->setOption("description", "(%s - separator, %t - local title)");
		$form->addText("titleSeparator", "Title separator");
		$form->addText("keywords", "Keywords");
		$form->addText("description", "Description");
		$form->addText("author", "Author");
		$form->addSelect('layout', 'Layout', $this->scannerService->getLayoutFiles())->setPrompt('-------');

		$form->addGroup("System");
		$form->addTextWithSelect("routePrefix", "Route prefix");

		$form->addSubmit('_submit', 'Save');
	}


	public function handleAttached($form)
	{
		$url = $form->presenter->context->httpRequest->url;
		$domain = trim($url->host . $url->scriptPath, "/") . "/";
		$params = array("<lang>/", "//$domain<lang>/", "//<lang>.$domain");

		$form['routePrefix']->setItems($params, false);
	}
}
