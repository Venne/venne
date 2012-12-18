<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms\Controls;

use Nette;
use Nette\Utils\Html;
use Venne\Tools\Objects;
use CmsModule\Content\Entities\FileEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileEntityControl extends \Nette\Forms\Controls\UploadControl
{

	/** @var FileEntity */
	protected $fileEntity;

	/** @var bool */
	protected $multi = false;


	protected function attached($form)
	{
		parent::attached($form);

		$this->fileEntity = Objects::hasProperty($this->parent->data, $this->name) ? Objects::getProperty($this->parent->data, $this->name) : NULL;

		if ($this->fileEntity instanceof \Doctrine\Common\Collections\Collection) {
			$this->multi = true;
		}
	}


	public function getValue()
	{
		$values = $this->getForm()->getHttpData();

		// remove photos
		if ($this->multi) {
			foreach ($this->fileEntity as $file) {
				$delete = isset($values[$this->name . '_delete_' . $file->id]) && $values[$this->name . '_delete_' . $file->id] == 'on';
				if ($delete) {
					$coll = $this->fileEntity;
					$coll->removeElement($file);
				}
			}
		} else if ($this->fileEntity) {
			$delete = isset($values[$this->name . '_delete_' . $this->fileEntity->id]) && $values[$this->name . '_delete_' . $this->fileEntity->id] == 'on';
			if ($delete) {
				return NULL;
			}
		}

		// get photos
		$photos = array();
		if ($this->multi) {
			for ($i = 0; $i < 20; $i++) {
				$photos[$this->name . '-' . $i] = $values[$this->name . '-' . $i];
			}
		} else {
			$photos[$this->name . '-0'] = $values[$this->name];
		}

		// add photo
		foreach ($photos as $file) {
			if ($file->isOk()) {
				if ($this->multi) {
					$this->fileEntity[] = $entity = new FileEntity();
					$entity->setFile($file);
				} else {
					if (!$this->fileEntity) {
						$this->fileEntity = new FileEntity();
					}

					$this->fileEntity->setFile($file);
				}
			}
		}

		return $this->fileEntity;
	}


	public function getControl()
	{
		$control = Html::el();

		if ($this->multi) {
			for ($i = 0; $i < 20; $i++) {
				$parent = parent::getControl();
				$parent->name .= '-' . $i;
				$parent->onChange = 'if($(this).val()) { $("#' . $parent->id . '-' . ($i + 1) . '").parent().show(); }';
				$parent->id .= '-' . $i;

				$control->add($d = Html::el('div'));
				$d->add($parent);

				if ($i > 0) {
					$d->style = 'display: none;';
				}
			}
		} else {
			$control->add(parent::getControl());
		}

		if ($this->fileEntity) {
			if ($this->multi) {
				$files = $this->fileEntity;
			} else {
				$files = array();
				if ($this->fileEntity) {
					$files[] = $this->fileEntity;
				}
			}

			$div = $control->create('div', array('style' => 'margin: 5px 0;'));
			foreach ($files as $file) {
				$div2 = $div->create('div', array('style' => 'align: center;', 'class' => 'caption'));
				$div2->create('img', array(
					'src' => $file->getFileUrl(),
					'style' => 'height: 64px; width: 64px;',
					'class' => 'img-polaroid img-rounded',
				));
				$div2->create('br');
				$div2->create('input', array('type' => 'checkbox', 'name' => $this->name . '_delete_' . $file->id));
				$div2->create('span')->setText(' delete');
			}
		}

		return $control;
	}
}
