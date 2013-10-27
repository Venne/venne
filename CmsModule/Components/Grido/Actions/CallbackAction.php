<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Grido\Actions;


use Grido\Components\Actions\Href;
use Nette\Application\BadRequestException;
use Nette\Utils\Html;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CallbackAction extends Href
{

	/** @var array */
	public $onClick;

	const TYPE_CALLBACK = 'CmsModule\Components\Grido\Actions\CallbackAction';


	/**
	 * @param $item
	 * @throws \Nette\Application\BadRequestException
	 */
	public function handleClick($item)
	{
		$this->onClick($this, $item);
	}


	/**
	 * Return element.
	 * @param $item
	 * @return \Nette\Utils\Html
	 */
	public function getElement($item)
	{
		$pk = $this->getPrimaryKey();

		$text = $this->translate($this->label);

		$el = clone $this->getElementPrototype();
		$el->setText($text);

		if ($this->customHref) {
			$el->href(callback($this->customHref)->invokeArgs(array($item)));
		} else {
			$this->arguments[$pk] = $this->getGrid()->getPropertyAccessor()->getProperty($item, $pk);
			$el->href($this->link('click!', $this->arguments[$pk]));
		}

		if (($o = $this->getOption('confirm')) !== NULL) {
			$el->attrs['data-grido-confirm'] = $this->translate(
				is_callable($o)
					? callback($o)->invokeArgs(array($item))
					: $o
			);
		}

		return $el;
	}


	public function getElementPrototype()
	{
		$el = parent::getElementPrototype();
		$el->class[] = 'btn';
		$el->class[] = 'btn-default';
		$el->class[] = 'btn-xs';
		return $el;
	}


	/**
	 * @param mixed $item
	 * @return void
	 */
	public function render($item)
	{
		if ($this->disable && callback($this->disable)->invokeArgs(array($item))) {
			return;
		}

		$el = $this->getElement($item);

		if ($this->customRender) {
			echo callback($this->customRender)->invokeArgs(array($item, $el));
			return;
		}

		echo $el->render();
	}
}
