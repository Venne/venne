<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine;

use Doctrine;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Kdyby;
use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class Enum extends Doctrine\DBAL\Types\Type
{

	/**
	 * @param mixed[] $fieldDeclaration
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return string
	 */
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
	}

	/**
	 * @param mixed $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return mixed
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if (!$value instanceof Venne\Utils\Type\Enum) {
			throw ConversionException::conversionFailedFormat($value, $this->getName(), Venne\Utils\Type\Enum::class);
		}

		return $value->getValue();
	}

	/**
	 * @param mixed $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return mixed
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		$enumClass = $this->getName();

		return $enumClass::get($value);
	}

}
