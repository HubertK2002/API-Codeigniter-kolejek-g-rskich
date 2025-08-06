<?php
namespace App\Schemas;

class RequestSchemas
{
	public static function castInput(array $input, array $schema): array
	{
		$output = [];

		foreach ($schema as $field => $type) {
			$value = $input[$field];

			switch ($type) {
				case 'int':
					$output[$field] = (int)$value;
					break;
				case 'float':
					$output[$field] = (float)$value;
					break;
				default:
					$output[$field] = $value;
			}
		}

		return $output;
	}

	public const CREATE_QUEUE = [
		'liczba_personelu' => 'int',
		'liczba_klientow' => 'int',
		'dl_trasy' => 'int',
		'godzina_od' => 'time_HM',
		'godzina_do' => 'time_HM',
		'predkosc_wagonu' => 'float',
	];

	public const COASTER_UPDATE_FIELDS = [
		'liczba_personelu' => 'int',
		'liczba_klientow' => 'int',
		'godzina_od' => 'time_HM',
		'godzina_do' => 'time_HM',
		'predkosc_wagonu' => 'float',
	];

	public const CREATE_WAGON = [
		'ilosc_miejsc' => 'int',
	];
}