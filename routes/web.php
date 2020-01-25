<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//CARGANDO CLASES
use App\Http\Middleware\ApiAuthMiddleware;
//RUTAS DE PRUEBA
Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre?}',function($nombre){
    $text = '<h2>Texto desde una ruta</h2>';
    $text .= 'Nombre: '.$nombre;
    return view('pruebas',array(
        'texto' => $text
    ));
});

Route::get('/animales','PruebasController@index');
Route::get('/test','PruebasController@testORM');

//RUTAS DEL SISTEMA
    //RUTAS DE PRUEBA
    //Route::get('/usuario/pruebas','UserController@pruebas');
    //Route::get('/post/pruebas','PostController@pruebas');
    //Route::get('/categorias/pruebas','CategoryController@pruebas');

//OFICIALES    

    Route::post('/api/register','UserController@register');
    Route::post('/api/login','UserController@login');
    Route::put('/api/user/update','UserController@update');
    Route::post('/api/user/upload','UserController@upload')->middleware(App\Http\Middleware\ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}','UserController@getImage');
    Route::get('/api/user/detail/{id}','UserController@detail');
            
//RUTAS DE CONTROLADOR DE CATEGORIA
    
    Route::resource('api/category', 'CategoryController');

//RUTAS DE CONTROLADOR DE ENTRADAS
    
    Route::resource('api/post', 'PostController');
    Route::post('/api/post/upload','PostController@upload');
    Route::get('/api/post/image/{filename}','PostController@getImage');
    Route::get('/api/post/category/{id}','PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}','PostController@getPostsByUser');