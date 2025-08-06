<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setAutoRoute(false);
$routes->get('/', 'Home::index');
$routes->group('api', function($routes) {
	$routes->post('coasters', 'Api\Coasters::create');
	$routes->put('coasters/(:num)', 'Api\Coasters::update/$1');

	$routes->post('coasters/(:num)/wagons', 'Api\Wagons::create/$1');
	$routes->delete('coasters/(:num)/wagons/(:num)', 'Api\Wagons::delete/$1/$2');

	$routes->get('environment', 'Api\System::environment');
});

return $routes;
