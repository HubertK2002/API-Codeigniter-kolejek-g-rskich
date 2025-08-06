<?php

namespace App\Libraries;

class ApiLogger
{
	private static ?self $instance = null;

	private string $environment;
	private string $basePath;
	private string $accessLog;
	private string $errorLog;

	private function __construct(string $environment, string $basePath)
	{
		$this->environment = $environment;
		$this->basePath = rtrim($basePath, '/') . '/' . $this->environment;
		$this->accessLog = $this->basePath . '/access.log';
		$this->errorLog = $this->basePath . '/error.log';

	}

	private function ensureReady(): void
	{
		if (!is_dir($this->basePath)) {
			if (!mkdir($this->basePath, 0777, true) && !is_dir($this->basePath)) {
				throw new \RuntimeException("Nie można utworzyć katalogu logów: {$this->basePath}");
			}
		}

		foreach ([$this->accessLog, $this->errorLog] as $file) {
			if (!file_exists($file)) {
				touch($file);
			}
		}
	}

	public static function getInstance(string $environment = ENVIRONMENT, string $basePath = WRITEPATH . 'logs/api'): self
	{
		if (!self::$instance) {
			self::$instance = new self($environment, $basePath);
		}
		return self::$instance;
	}

	public function logAccess(string $message): void
	{
		$this->ensureReady(); // <- tutaj
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$entry = $this->formatEntry("[$ip] $message");
		file_put_contents($this->accessLog, $entry . "\n", FILE_APPEND);
	}

	public function logError(string $message, string $type = ErrorType::PHP): void
	{
		$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$entry = $this->formatEntry("[$type][$ip] $message");
		file_put_contents($this->errorLog, $entry . "\n", FILE_APPEND);
	}

	private function formatEntry(string $message): string
	{
		return '[' . date('Y-m-d H:i:s') . '] ' . $message;
	}
}
