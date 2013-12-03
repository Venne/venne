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
			$qb = $routes = $this->routeRepository->createQueryBuilder('r')
				->andWhere('r.published = :true')->setParameter('true', TRUE)
				->andWhere('r.released < :now')->setParameter('now', new \DateTime)
				->setParameter('query', "%{$query}%")
				->setMaxResults($limit);

			if ($this->presenter->context->parameters['website']['defaultLanguage'] !== $this->presenter->lang) {
				$qb
					->leftJoin('r.translations', 'a')
					->andWhere('a.language = :langId')
					->andWhere('(a.' . $column . ' LIKE :query)')
					->setParameter('langId', $this->presenter->language->id);
			} else {
				$qb
					->andWhere('r.' . $column . ' LIKE :query');
			}

			foreach ($qb->getQuery()->getResult() as $route) {
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
					'name' => mb_convert_encoding($this->highlightText($route->name, $query), 'UTF-8', 'UTF-8'),
					'value' => mb_convert_encoding($route->name, 'UTF-8', 'UTF-8'),
					'description' => mb_convert_encoding($this->highlightText($text, $query), 'UTF-8', 'UTF-8'),
					'photo' => $route->photo ? $this->template->basePath . \CmsModule\Content\Macros\MediaMacro::proccessImage($route->photo->getFileUrl(true), array('size' => 'x48')) : NULL,
				);
			}
		}

		$this->presenter->sendResponse(new JsonResponse(array_merge($results)));
	}


	/**
	 * @param $text
	 * @param $word
	 * @return string
	 */
	protected function highlightText($text, $word)
	{
		return preg_replace('#' . preg_quote($word) . '#i', '<strong>\\0</strong>', $text);
	}


	public function renderDefault($limit = 8)
	{
		$this->template->limit = $limit;
	}
}
