<?php

namespace App\Libraries;

use App\Libraries\ApiLogger;

class RedisLock
{
	protected \Redis $redis;
	protected string $processId;

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

		$this->logger = ApiLogger::getInstance();
		$this->logger->logAccess("Connecting to redis, host: $host, port: $port");

		$this->redis = new \Redis();
		$this->redis->connect($host, $port);

		$this->processId = gethostname() . ':' . getmypid();
	}

	public function acquire(string | array $key, int $ttl = 5000): bool
	{
		return $this->redis->set($this->formatKey($scope), $this->processId, ['nx', 'px' => $ttl]);
	}

	public function hasLock(string | array $key): bool
	{
		return $this->redis->get($this->formatKey($scope)) === $this->processId;
	}

	public function release(string | array $key): bool
	{
		$key = $this->formatKey($scope);
		if ($this->hasLock($key)) {
			return $this->redis->del($this->formatKey($scope)) > 0;
		}
		return false;
	}

	public function runIfLock(string | array $scope, callable $callback): mixed
	{
		$key = $this->formatKey($scope);
		if (!$this->hasLock($key)) {
			throw new \RuntimeException("Lock '$key' is not held by this process.");
		}
		return $callback();
	}
	protected function formatKey(string|array $scope): string
	{
		if (is_array($scope)) {
			return 'lock:' . implode(':', $scope);
		}
		return 'lock:' . $scope;
	}
}

