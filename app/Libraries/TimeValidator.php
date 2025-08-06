<?php
namespace App\Libraries;

class TimeValidator
{
	public static function assertHourMinuteLessThan(string $start, string $end, string $fieldStart, string $fieldEnd): void
	{
		[$h1, $m1] = explode(':', $start);
		[$h2, $m2] = explode(':', $end);

		$min1 = ((int)$h1) * 60 + (int)$m1;
		$min2 = ((int)$h2) * 60 + (int)$m2;

		if ($min2 <= $min1) {
			throw new \InvalidArgumentException("Pole '$fieldEnd' musi być późniejsze niż '$fieldStart'.");
		}
	}
}