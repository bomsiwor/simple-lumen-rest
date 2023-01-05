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
    return $router->app->version();
});



$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('login', ['uses' => 'AuthController@login']);
    $router->post('logout', ['uses' => 'AuthController@logout']);
    $router->post('user-profile', ['uses' => 'AuthController@me']);
    $router->post('refresh', ['uses' => 'AuthController@refresh']);
    $router->post('register', ['uses' => 'AuthController@register']);
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('nilai',  ['uses' => 'NilaiController@showAllNilai']);

    $router->post('nilai', ['uses' => 'NilaiController@addNilai']);

    $router->post('upload-nilai', ['uses' => 'NilaiController@uploadNilai']);

    $router->put('nilai/{nim}/{matkul}', ['uses' => 'NilaiController@editNilai']);

    $router->delete('nilai/{nim}/{matkul}', ['uses' => 'NilaiController@deleteNilai']);

    $router->get('avg-nilai', ['uses' => 'NilaiController@avgNilai']);

    $router->get('avg-nilai-jurusan', ['uses' => 'NilaiController@avgByJurusan']);
});
