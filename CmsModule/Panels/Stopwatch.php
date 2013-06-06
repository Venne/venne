<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Panels;

use Nette;

/**
 * Timers
 *
 * Bootstrap:    Debug::addPanel(new Stopwatch());
 *
 * Usage:    Stopwatch::start(); Stopwatch::stop($name);
 *
 * @copyright Maxipes Fík
 */
final class Stopwatch implements \Nette\Diagnostics\IBarPanel
{

	private static $timers = array();


	public static function start()
	{
		\Nette\Diagnostics\Debugger::timer();
	}


	public static function stop($name)
	{
		self::add(\Nette\Diagnostics\Debugger::timer(), $name);
	}


	public static function add($time, $name)
	{
		if ($name === NULL) $name = uniqid();
		self::$timers[$name] = $time;
	}


	/*	 * * IDebugPanel ** */


	public function getTab()
	{
		$sum = number_format(round(array_sum(self::$timers) * 1000, 1), 1);
		return '<span><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAA7EAAAOxAGVKw4bAAACiUlEQVQ4jY2Sy08TYRTFz/fNtDPTMm1hxoKQIg02KUVBBMIjEHXr1sS4cgEhmuCOldsm4iOufEQTWbEy9Q+AhJ0sLdHURSUQXraBaUunzyFDO/O5kkRaEs/q5uTcX27uvQQXaG1tbXR7e3OsVqtDkhxf5+bmE81ytJkZj8cdmaOj2y+fvzp9sfjMMk9qd2OxGNcsyzczXS6XJ184TlOOfiQc5VR/x5NQKCQDKPwXIBKJHEej0a43b9//0A7Tp4QQ/9DQUEPzPwBFUUZs276n6/rTZDIrWyjmchntCqU87/OpX9bXE63T0wP6eQD5WwQCgSWfzzMTjS4uuiTpcdUoK5IogAEolcoo5gul/oHBhampyaWmAABkZWX1U07TZlsVH9oUFZVyGZZVh6r6UTUq+L4RZ+OTt+YnJsY+/G06u0I6nVYcHH+/uyeAcF8EjAGSJMHj8YJ38AgGe3FzZJT8TGy83t/f72wACIL7QSq1L1PKIZPJwu+/BOOkimxWgyRJKBR0CE4BLW7ZVS6XHzYsMZ/LDPu8HiiKihZZhmEYoJSDw+kEIQRtbQryDLgaCiGnHQ43TGCcGE6O55FK/QZjDKIoQhQF8DwHUZRAKUWtdgqe4yCIktAA8Ld3bGqahsudXcjlsgCA7kAPwuF+cBwHXc/DKQioVCto8Xi3GgCmacUYqLWzswVKKEzTBAODZdkwTRO2baNULAKEMIfD9bkBEAx2/Rodn3qXOjhAxahgb28Xuq4jm8ng6OgQej6PulWHW/Yuh8O93xqWCACD1/sWmM3cyWRi1iWJpFDQYVt12CAAI8zXqsZkt/vRRY90puTW7h39+Hgmox0OMgYq+zwJVW1fvnEtvHo++wf3HgDktqhaIgAAAABJRU5ErkJggg=="><strong>Timers</strong> (' . $sum . ' ms)</span>';
	}


	public function getPanel()
	{
		$sum = number_format(round(array_sum(self::$timers) * 1000, 1), 1);
		$buff = '<h1><img style="vertical-align: middle; position: relative; top: -4px; left: -2px;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAA7EAAAOxAGVKw4bAAAHwUlEQVRYhbWX/W9T5xXHv89zX+3r2IntvNiOncR5cUISFsJ4SykKMFhGpaFu04pE0ajYitpqGv/AKm3SpknTpq3VuqpSJ1Z+gqkD0ZWmrLRrCRsElyQ0JeE1sUNix3Zsx+8v995nP0DZWqDElXqk54er89zv+TznPPfqHIKvYMeOHeO8Xu9QLB7ZopZU1WKp+WDz5s1nCCGsUi1S6QuTk5PWubm5v58/f25ycnLypCAI1Ott3tPiaTR3r163d2BgIF+pZkV2bmTk6KFDP91795G/u7B3755DJ47/7ZWvNfj09LT9yBtvjAEgPp/viMvpTDXU21Nut/M4AHr4L69fGRmZqqpEk1ay2WSyupfisSkAjDH2LUHgqnhBqNJ0bRMAnReFGYMh1/K1AaTTS4uSKLgBgDComqpB13QARAZAZNnokiTpdiWaFV/Cl//4h1NjE5d/x3Fy2/d/sPvVhcAMTp1+/2mvt1nbtWvX9wYHB39YiR5fKYAoGw44GxqO2WrrAr09vcxslEkourSltbWlhzG2u1K9h5VAstlsLzmdTtsXHQcPHgxZamqG2ts752/cuI7wYox1dXUzGuO/vXXr1lilAPeVYNWqVaZIJHKyVCoOyrLh55FI5Fef+W5dndtQRPknDNheyBc8sWiU6LpGjLKB8YK4BEre4yleX7++/72vDNDW1nY8mUzszmazRFGMzOfr2nL08NFAgWivpFKpJ67fuEqymRQkSYQkyVBVFZqmIpvNIZtKoqm5nblcTRckA32+r69vrGKA3t7exnw+/9t8Prfnmf0/gsPl8e/cOeT9ZGLMGl4MwdPkhkGWYTQaoJZUUI4D4ShKxRKKxRIujJxFLp/D0K4nC9a62p+t7ul6rSKAu8Y/99wL+zas2/D7VCZbbTHJsNqt0KHD6WyErcYKBoaTJ06A5znsHPoOBEFEPB5HOLQAVS1h+B9v4Yknn2JNLS3Pr+7uevVhAA+7hOqLL/5ydMfOHbwicXA66qCYFJSLJUyMjyGTzYLjeFjMVdA1DaIgQpZlXPr4InydXbDaa/HUvqfx5tEjJLYYeWlq6vrWigAYY4QSdvg//z5ncjQ6YDCZ0NbajuYWLyyWasRiUWQzGTgb3XC7PSgUi8jn83A6XVCMRjQ3t4DnROzbfwBHDr8mUKr/2e/3CysGWFyMfXdpaWnt/FwAlKMwVZlRLJXgdLowMPAYjEYDFhbmoSgmKCYTAIZodBF9a/qhM4ZcNgeOF0CYjk2PbcG5sx92WC32/SsG4Hnhx6fefot0dvtQXW2Ff/Q8Rs59hHg8DlUtw2y2oMHhQCBwA1eufAKO42Gz2lEul0AIwejoeUx/Oom6unqsXduPiYlxAk5/dkUAwWDQwFE6GA7Pw2K2wOl0obvnG2hv7YDFYgEA6LqOaCwKxVQFylFEo4tIJhPQNB2apmHjxgH0rO5DtdUGSgiaPc2IL8XWzM7OOu477H1ElHam00nFpBghyQZwHIfmlhZomgZN0wAQJBIJCLwARbGg3uGC0agglUqjThDAGIPRaISiKAAAQgk6ulYhtHCbs9lr1wAIfWkGJElyL4ZCxO1yQhREJOJLmBgfA2N3ui3GGBwOJxRFQXghiPnbQeg6g9vtBiF3vmpCCIKBAMb9FyHLMurqakEBUMo3PrIE5YIuFEpFgPAoFLK4OHoeY2OXPreH4ziYzRaUSllAzaOqSgGld6R4nr8HEQ4vIJNOg+coOIEDY5r4yBKUtHLSbLYgsZyEbDRh+44h6Lp+N/3/M0IIXO4OSIZFUMp9zscYg6epCZ6mJkQiYahlFQaDCYyRzCMBMpni9br6Bn1mZoZqZRWqqt473f8H53kOHe3taG3zQhSlO+mkFISQe6XQdR0Aw/JyAtXVVqaqpatfjHdfCbq7vXMAnaEcRSaTQj6fx7Vr1zBy9kMsJ5P3AAihECUJBtkIQsg9SF3XMfzuOxgePgW1XIam6kgkkzBXVaclSRp/JAAhhJWL5WPr12/ERb8fN29dR2D2FjhKIUoiKKUPzMhnEIzpqLXbYK2uRiAwCwBQjAoYISc8Hs99LfsDf0T5YvpPjz++LfvxRT+YpqK7pxcDm7dAFCWEQiGkUylQSpFOLyMSWQQAlMtlBAMBRKNR9PevQ2u7D4rRiHBoAY5Gj65p5ZcfFOuBAD6fbx6E/ubAsy+w08PDWIqFkUgkkMlm8K8P3sdCKATGGBgj4DgejDFkMmlcvjyOuWAQ6fQy0qkkUpkMzJYaiLz4166uLv+DYj20KfX7/UKN3Xn68qXRwakrl9D/zQ1QVRU2Wy1qampgMlXhn6ffBaUEvav7YDGbEU/EEQ6HYLfbUSwVoZY11DvdVySeburo6EhVBHAH4qbFXCO+Mxe8uWnkozPo7FyF2to6GAwGmM0WhELzyOeyaG/3IZvPoFgooVxWkUzEYa62wlZbf4MybXtPT0/wYTEe2ZaPj48rslLzciGf2z926QJJLcfhcDTCYBTBczxKpRwkSYGmMSwvL0NRquBwuZgkG9+WePJMZ2fnlzaqK54Lpqdnt+kUvygVipsW5oNcfCmCXC4HQilEQYDJYkFDvZMpJtMYB+7Xvb0db65Et+LBZGJqyseKZFtZL/XpuuZgDAQgMcbUcU7kz6zv6/sUwIrH9P8CNlMtNRG2fHEAAAAASUVORK5CYII=">Stopwatch</h1>';
		$buff .= '<div>';
		$buff .= '<table>';
		foreach (self::$timers as $name => $value) {
			$buff .= "<tr><th>$name</th><td style='text-align: right;'>" . number_format(round($value * 1000, 1), 1) . " ms</td></tr>";
		}
		$buff .= "<tr><th style='color: green;'>&sum;</th><td style='color: green; text-align: right; border-top: 3px double #888;'>" . $sum . " ms</th></tr>";
		$buff .= '</table>';
		$buff .= '</div>';
		return $buff;
	}


	public function getId()
	{
		return 'Stopwatch';
	}
}