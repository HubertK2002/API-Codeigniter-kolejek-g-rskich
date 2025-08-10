<?php

namespace App\Libraries;

use Redis;

class RedisPublisher
{
	protected Redis $redis;

	public function __construct()
	{
		$config = parse_ini_file('config', true);

		$map = [
			'development' => 'dev',
			'production'  => 'prod',
		];
		$env = ENVIRONMENT;
		$envSection = $map[$env];

		$host = $config[$envSection]['host'];
		$port = $config[$envSection]['redis_port'];

		$this->redis = new Redis();
		$this->redis->connect($host, $port);
	}

	public function publish(string $channel, string $message): void
	{
		$this->redis->publish($channel, $message);
	}
}

?>