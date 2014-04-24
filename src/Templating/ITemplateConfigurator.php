<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Nette\Bridges\ApplicationLatte\Template;

/**
 * @author     Josef Kříž
 */
interface ITemplateConfigurator
{

	/**
	 * @param Template $template
	 */
	public function configure(Template $template);


	/**
	 * @param Template $template
	 */
	public function prepareFilters(Template $template);
}
