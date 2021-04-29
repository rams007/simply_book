<?php
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {

return redirect ("/starter.php");
});

$router->get('/initCalendar', "MainController@initCalendar");
$router->get('/nextMonth', "MainController@nextMonth");
$router->get('/prevMonth', "MainController@prevMonth");
$router->post('/startBooking', "MainController@startBooking");
$router->get('/customFields', "MainController@getCustomFields");

$router->get('/api/ChargebeeCallback', "CallbackController@ChargebeeCallback");



