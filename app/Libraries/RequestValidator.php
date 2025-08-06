<?php
namespace App\Libraries;

class RequestValidator {
	public static function validate_input(array $required_keys, array $input_data): void {
		foreach ($required_keys as $field => $type) {
			if (!isset($input_data[$field]) || $input_data[$field] === '') {
				throw new \InvalidArgumentException("Brakuje wymaganego pola: $field");
			}

			$value = $input_data[$field];

			switch ($type) {
				case 'int':
					if (!filter_var($value, FILTER_VALIDATE_INT)) {
						throw new \InvalidArgumentException("Pole '$field' musi być liczbą całkowitą.");
					}
					break;

				case 'float':
					if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
						throw new \InvalidArgumentException("Pole '$field' musi być liczbą zmiennoprzecinkową.");
					}
					break;

				case 'string':
					if (!is_string($value)) {
						throw new \InvalidArgumentException("Pole '$field' musi być tekstem.");
					}
					break;

				case 'time_HM':
					if (!preg_match('/^\d{2}:\d{2}$/', $value)) {
						throw new \InvalidArgumentException("Pole '$field' musi być w formacie HH:MM.");
					}
					list($h, $m) = explode(':', $value);
					if ((int)$h < 0 || (int)$h > 23 || (int)$m < 0 || (int)$m > 59) {
						throw new \InvalidArgumentException("Pole '$field' zawiera nieprawidłową godzinę (0–23:0–59). Podana wartość $value");
					}
					break;

				default:
					throw new \InvalidArgumentException("Nieznany typ walidacji dla pola: $field");
			}
		}
	}

	public static function assertHourMinuteLessThanSecond(string $start, string $end, string $fieldStart, string $fieldEnd): void {
		[$h1, $m1] = explode(':', $start);
		[$h2, $m2] = explode(':', $end);

		$min1 = ((int)$h1) * 60 + (int)$m1;
		$min2 = ((int)$h2) * 60 + (int)$m2;

		if ($min2 <= $min1) {
			throw new \InvalidArgumentException("Pole '$fieldEnd' musi być późniejsze niż '$fieldStart'.");
		}
	}

	public static function validateNumericId($id, string $label = 'ID'): void {
		if (!is_numeric($id)) {
			throw new \InvalidArgumentException("Nieprawidłowe $label.");
		}
	}
}

?>