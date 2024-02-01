<?php

use Illuminate\Support\Facades\Route;
use app\Actions\FirstPrompt;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// a web page
// text input
// form
// show response
// growing list

// a route conversation page
// a route form submission

// conversation model
// message model

// create a conversation
// save prompts and responses

Route::get('/conversations/{id}', function() {
    return view('conversation', [
        'conversation' => null
    ]);
});

Route::get('chat', function(FirstPrompt $prompt) {

});

Route::get('/', function () {
    return view('welcome');
});
