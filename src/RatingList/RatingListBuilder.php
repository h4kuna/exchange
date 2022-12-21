<?php declare(strict_types=1);

namespace h4kuna\Exchange\RatingList;

use h4kuna\Exchange\Driver\Driver;
use h4kuna\Exchange\Exceptions\InvalidStateException;
use Psr\Http\Client;

final class RatingListBuilder implements Builder
{
	private const DAY = 86400; // 60 * 60 * 24


	/** @param array<string, int> $allowedCurrencies */
	public function __construct(private array $allowedCurrencies)
	{
	}


	/**
	 * @throws Client\ClientExceptionInterface
	 */
	public function create(Driver $driver, ?\DateTimeInterface $date = null): RatingList
	{
		$driver->initRequest($date);

		$ratingList = new RatingList($driver->getDate());

		foreach ($driver->properties($this->allowedCurrencies) as $property) {
			$ratingList->addProperty($property);
		}

		if ($date === null) {
			$ratingList->setTTL($this->countTTL($driver));
		}

		if ($ratingList->isValid() === false) {
			throw new InvalidStateException('Empty exchange rate. Or time to live is past.');
		}

		return $ratingList;
	}


	private function countTTL(Driver $driver): int
	{
		$refresh = (int) (new \DateTime($driver->getRefresh()))->format('U');
		if (time() >= $refresh) {
			$refresh += self::DAY;
		}

		return $refresh;
	}

}
