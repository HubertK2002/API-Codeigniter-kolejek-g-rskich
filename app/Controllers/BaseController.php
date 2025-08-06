<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\ApiLogger;

abstract class BaseController extends ResourceController
{
	protected $request;
	protected $helpers = [];
	protected $logger;

	public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);
		$this->logger = ApiLogger::getInstance();
		$this->logger->logAccess($_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);
	}
}
