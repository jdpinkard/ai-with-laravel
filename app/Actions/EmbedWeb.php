<?php

namespace App\Actions;

use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use \Probots\Pinecone\Client as Pinecone;
use OpenAI\Laravel\Facades\OpenAI;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;

class EmbedWeb
{
    use AsAction;

    public $commandSignature = 'embed:web {url}';

    public function handle(string $url)
    {
        $readability = new Readability(new Configuration);

        $readability->parse(Http::get($url));

        $pinecone = new Pinecone('3f8ef451-3913-4e1b-bb83-00259dd2febb', 'gcp-starter');

        $content = Str::of(strip_tags($readability->getContent()))
            ->split(1000)
            ->toArray();

        $count = count($content);

        if($count > 1 && strlen($content[$count - 1 ]) < 500) {
            $content[$count - 2] .= $content[$count -1];
            array_pop($content);
        }

            
        $embeddings =OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $content,
        ])->embeddings;
        
        // Namespace!!!
        $pinecone->index('chatbox')->vectors()->delete(deleteAll: true, namespace: 'podcast'); 

        $pinecone->index('chatbox')->vectors()->upsert( 
            vectors: collect($embeddings)->map(fn ($embedding, $index) => [
                'id' => (string) $index,
                'values' => $embedding->embedding,
                'metadata' => [
                    'text' => $content[$index],
                ]
            ])->toArray(),
                namespace: 'podcast'
        );
                       
    }

    public function asCommand(Command $command)
    {
        $this->handle($command->argument('url'));
    }
}
