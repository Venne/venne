<?php

namespace VenneTests\Utils\Type;

use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EnumTest extends TestCase
{

	public function testGetAvailableValues()
	{
		Assert::equal(array('string', 'integer'), FooEnum::getAvailableValues());
	}

	public function testConstructor()
	{
		Assert::throws(function () {
			new FooEnum(FooEnum::INTEGER);
		}, 'Nette\StaticClassException');
	}

	public function testGet()
	{
		Assert::same(FooEnum::get(FooEnum::INTEGER), FooEnum::get(FooEnum::INTEGER));
		Assert::notSame(FooEnum::get(FooEnum::STRING), FooEnum::get(FooEnum::INTEGER));
		Assert::notSame(BarEnum::get(BarEnum::INTEGER), FooEnum::get(FooEnum::INTEGER));

		Assert::throws(function () {
			FooEnum::get('invalid');
		}, 'Nette\InvalidArgumentException', '\'invalid\' [string] is not valid argument. Accepted values are: string, integer');
	}

	public function testGetValue()
	{
		$enum = FooEnum::get(FooEnum::INTEGER);
		Assert::same(FooEnum::INTEGER, $enum->getValue());
	}

}

class FooEnum extends \Venne\Utils\Type\Enum
{

	const STRING = 'string';

	const INTEGER = 'integer';

}

class BarEnum extends \Venne\Utils\Type\Enum
{

	const BOOLEAN = 'boolean';

	const INTEGER = 'integer';

}

$testCase = new EnumTest;
$testCase->run();
