<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Content\Repositories\RouteRepository;
use Nette\Application\Responses\JsonResponse;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SearchControl extends Control
{

	/** @var RouteRepository */
	private $routeRepository;


	/**
	 * @param RouteRepository $routeRepository
	 */
	public function __construct(RouteRepository $routeRepository)
	{
		parent::__construct();

		$this->routeRepository = $routeRepository;
	}


	public function handleSearch($query, $limit)
	{
		$results = array();
		foreach (array('name', 'title', 'notation', 'text') as $column) {
			if ($this->presenter->context->parameters['website']['defaultLanguage'] !== $this->presenter->lang) {
				$routes = $this->routeRepository->createQueryBuilder('b')
					->leftJoin('b.translations', 'a')
					->where('a.language = :langId')
					->andWhere('(a.' . $column . ' LIKE :query)')
					->setMaxResults($limit)
					->setParameter('query', "%{$query}%")
					->setParameter('langId', $this->presenter->language->id)
					->getQuery()->getResult();
			} else {
				$routes = $this->routeRepository->createQueryBuilder('a')
					->andWhere('a.' . $column . ' LIKE :query')
					->setMaxResults($limit)
					->setParameter('query', "%{$query}%")
					->getQuery()->getResult();
			}

			foreach ($routes as $route) {
				$text = strip_tags(html_entity_decode($route->{$column}));
				if (($len = strlen($text)) > 40) {
					$pos = stripos($text, $query);
					$start = $pos - 20;

					if ($start > 0) {
						$text = '...' . substr($text, $start);
					}

					if ($start + 40 < $len) {
						$text = substr($text, 0, 40) . '...';
					}
				}
				$results[$route->id] = array(
					'url' => $this->presenter->link('Route', array('route' => $route)),
					'name' => str_ireplace($query, "<strong>{$query}</strong>", $route->name),
					'value' => $route->name,
					'description' => str_ireplace($query, "<strong>{$query}</strong>", $text),
					'photo' => $route->photo ? $this->template->basePath . \CmsModule\Content\Macros\MediaMacro::proccessImage($route->photo->getFileUrl(true), array('size' => 'x48')) : NULL,
				);
			}
		}

		$this->presenter->sendResponse(new JsonResponse(array_merge($results)));
	}


	public function renderDefault($limit = 8)
	{
		$this->template->limit = $limit;
	}
}
