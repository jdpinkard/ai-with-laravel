<?php

use Illuminate\Support\Facades\Route;
use app\Actions\FirstPrompt;
use App\Models\Conversation;
use Illuminate\Http\Request;

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

Route::get('/conversations/{id}', function($id) {
    $conversation = $id == 'new' ? null : Conversation::find($id);

    return view('conversation', [
        'conversation' => $conversation,
    ]);
})->name('coversation');

Route::post('chat/{id}', function(Request $request, FirstPrompt $prompt, $id) {
    if ($id == 'new') {
        $conversation = Conversation::create();
    } else {
        $conversation = Conversation::find($id);
    }

    $conversation->messages()->create([
        'content' => $request->input('prompt')
    ]);

    $result = $prompt->handle($request->input('prompt'));

    $conversation->messages()->create([
        'content' => $result->choices[0]->message->content
    ]);

    return redirect()->route('conversation', ['id' => $conversation->id]);
})->name('chat');

Route::get('/', function () {
    return view('welcome');
});
