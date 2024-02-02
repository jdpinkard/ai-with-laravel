<?php

use Probots\Pinecone\Client as Pinecone;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Conversation;
use App\Actions\StreamingPrompt;
use App\Actions\FirstPrompt;
use App\Actions\EmbedWeb;
use App\SearchClient;
use App\Actions\GetWebpageContent;
use App\Actions\CondenseText;
use App\Actions\AssessWebAccessRequirement;
use Illuminate\Support\Facades\Process;

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
})->name('conversation');

Route::post('chat/{id}', function(Request $request, FirstPrompt $prompt, $id) {
    if ($id == 'new') {
        $conversation = Conversation::create();
    } else {
        $conversation = Conversation::find($id);
    }

    $conversation->messages()->create([
        'content' => $request->input('prompt'),
        'role' => 'user',
    ]);

    $messages = $conversation->messages->map(function (Message $message) {
        return [
            'content' => $message->content,
            'role' => 'user',
        ];
    })->toArray();

    $systemMessage = [
        'role' => 'system',
        'content' => 'The user\'s name is Justin',
    ];

    $result = $prompt->handle(array_merge([$systemMessage], $messages));

    $conversation->messages()->create([
        'content' => $result->choices[0]->message->content,
        'role' => 'assistant',
    ]);

    return redirect()->route('conversation', ['id' => $conversation->id]);
})->name('chat');




Route::get('/', function () {
    $pinecone = new Pinecone('3f8ef451-3913-4e1b-bb83-00259dd2febb', 'gcp-starter');
   
    $values1 = [
        'My name is Justin',
        // 'I live in MN',
        'Honey badgers just don\'t care and eat bee larvae.',
    ];

    $values2 = [
        'My name is Luke',
        'I live on Tatooine',
        // 'Honey badgers just don\'t care and eat bee larvae.',
    ];

    $embeddings =OpenAI::embeddings()->create([
        'model' => 'text-embedding-ada-002',
        'input' => $values1,
    ])->embeddings;

    $result = $pinecone->index('chatbox')->vectors()->upsert(
        collect($embeddings)->map(fn ($embedding, $idx) => [
            'id' => (string) $idx,
            'values' => $embedding->embedding,
            'metadata' => [
                'text' => $values1[$idx],
            ]
        ])->toArray()
    , namespace: 'justin');

    $embeddings =OpenAI::embeddings()->create([
        'model' => 'text-embedding-ada-002',
        'input' => $values2,
    ])->embeddings;

    $result = $pinecone->index('chatbox')->vectors()->upsert(
        collect($embeddings)->map(fn ($embedding, $idx) => [
            'id' => (string) $idx,
            'values' => $embedding->embedding,
            'metadata' => [
                'text' => $values2[$idx],
            ]
        ])->toArray()
    , namespace: 'luke');

    $question = OpenAI::embeddings()->create([
        'model' => 'text-embedding-ada-002',
        'input' => [
            'Tell me something about me.',
        ]
        ]);

    $result = $pinecone->index('chatbox')->vectors()->query(vector: $question->embeddings[0]->embedding, namespace: 'justin', topK: 4)->json();

        dd($result);

});
