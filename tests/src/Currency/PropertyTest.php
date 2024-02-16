<?php

declare(strict_types=1);

namespace h4kuna\Exchange\Tests\Currency;

require_once __DIR__ . '/../../bootstrap.php';

use h4kuna\Exchange\Currency\Property;
use Tester\Assert;
use Tester\TestCase;

final class PropertyTest extends TestCase
{

	public function testBasic(): void
	{
		$property = new Property(10, 1, 'DOO');
		Assert::same(.1, $property->rate);
		Assert::same('DOO', (string) $property);
	}

}

(new PropertyTest())->run();
