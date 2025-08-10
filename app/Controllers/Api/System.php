<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class System extends ResourceController
{
	public function environment()
	{
		$configPath ='../../config';
		$env = ENVIRONMENT;

		$map = [
			'development' => 'dev',
			'production'  => 'prod',
		];

		$iniSection = $map[$env] ?? $env;
		$redisPort = null;

		if (file_exists($configPath)) {
			$config = parse_ini_file($configPath, true);
			if (isset($config[$iniSection]['redis_port'])) {
				$redisPort = (int) $config[$iniSection]['redis_port'];
			}
		}

		return $this->respond([
			'environment'     => ENVIRONMENT,
			'php_version'     => PHP_VERSION,
			'codeigniter'     => \CodeIgniter\CodeIgniter::CI_VERSION,
			'redis_port'      => $redisPort,
		]);
	}
}

