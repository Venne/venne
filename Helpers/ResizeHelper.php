<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Helpers;

use Venne;
use Nette;
use Nette\Object;
use Venne\Templating\BaseHelper;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ResizeHelper extends BaseHelper
{


	/** @var Container */
	protected $container;



	/**
	 * @param Container $container 
	 */
	public function __construct(Container $container)
	{
		parent::__construct();
		$this->container = $container;
	}



	/**
	 * @param $text
	 * @return string
	 */
	public function run($file, $width, $height = NULL, $flags = NULL, $crop = NULL)
	{
		if (!$width && !$height) {
			return $file;
		}

		if (!$width) {
			$width = NULL;
		}
		if (!$height) {
			$height = NULL;
		}

		// paths
		$filePath = $this->container->parameters["wwwDir"] . "/" . $file;
		$thumbName = self::getThumbName($file, $width, $height, filemtime($filePath), $flags, $crop);
		$cacheDir = $this->container->parameters["wwwCacheDir"] . "/thumbs";
		$relativeCacheDir = str_replace($this->container->parameters["wwwDir"], "", $cacheDir);
		$relativeFilePath = substr($relativeCacheDir, 1) . "/" . $thumbName;
		$thumbPath = $cacheDir . "/" . $thumbName;

		// image exists
		if (file_exists($thumbPath)) {
			return $relativeFilePath;
		}

		// resize
		try {
			$image = \Nette\Image::fromFile($filePath);

			// Transparency
			$image->alphaBlending(FALSE);
			$image->saveAlpha(TRUE);

			$origWidth = $image->getWidth();
			$origHeight = $image->getHeight();

			$image->resize($width, $height, $flags);
			$image->sharpen();

			$newWidth = $image->getWidth();
			$newHeight = $image->getHeight();

			if ($crop) {
				$image->crop('50%', '50%', $width, $height);
			}

			if ($newWidth !== $origWidth || $newHeight !== $origHeight) {
				$image->save($thumbPath);

				if (is_file($thumbPath)) {
					return $relativeFilePath;
				}
			}
		} catch (\Exception $e) {
			
		}

		return $file;
	}



	/**
	 * Get thumb name.
	 * 
	 * @param type $fileName
	 * @param type $width
	 * @param type $height
	 * @param type $mtime
	 * @param type $flags
	 * @param type $crop
	 * @return string 
	 */
	private static function getThumbName($fileName, $width, $height, $mtime, $flags, $crop)
	{
		$sep = '.';
		$tmp = explode($sep, $fileName);
		$ext = array_pop($tmp);

		$fileName = implode($sep, $tmp);
		$fileName .= $width . 'x' . $height . '-' . $mtime . '.' . $flags . '.' . $crop;
		$fileName = md5($fileName) . $sep . $ext;

		return $fileName;
	}

}

