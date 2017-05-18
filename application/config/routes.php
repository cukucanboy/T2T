<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$route['default_controller'] = "home";
$route['404_override'] = 'home';
$route['translate_uri_dashes'] = FALSE;
$route['supplier-register'] = 'home/supplier_register';
$route['properties'] = 'Ean';
$route['properties/search'] = 'Ean/search';
$route['properties/reservation'] = 'Ean/reservation';
$route['properties/itin'] = 'Ean/itin';
$route['properties/hotel/(:any)/(:any)'] = 'Ean/hotel/$1/$2';
$route['car'] = 'Cartrawler';
$route['flightsd'] = 'Flightsdohop';
$route['flightst'] = 'Travelstart';
$route['flightsw'] = 'Wegoflights';
$route['hotelsc'] = 'Hotelscombined';
$route['sitemap\.xml'] = "Sitemap";
$route['getCities'] = "Home/getCities";
