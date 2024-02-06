<?php

namespace App\Actions;

use andreskrey\Readability\Configuration;
use andreskrey\Readability\Readability;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use Probots\Pinecone\Client as Pinecone;
use Spatie\PdfToText\Pdf;

class EmbedPdf
{
    use AsAction;

    public $commandSignature = 'embed:pdf';

    public function handle()
    {
        $fileText = Pdf::getText(
            storage_path('app/public/WEF_Global_Risks_Report_2023.pdf'),
            config('services.pdftotext.path')
        );
        
        $content = Str::of($fileText)
            ->split("/\f/")
            ->toArray();
        
        $pinecone = new Pinecone('3f8ef451-3913-4e1b-bb83-00259dd2febb', 'gcp-starter');
        $embeddings = OpenAI::embeddings()->create([
            'model' => 'text-embedding-ada-002',
            'input' => $content,
        ])->embeddings;

        // Namespace!!!
        $pinecone->index('chatbox')->vectors()->delete(deleteAll: true, namespace: 'wef');

        collect($embeddings)->chunk(20)->each(function (Collection $chunk, $chunkIndex) use ($pinecone, $content) {
            $pinecone->index('chatbox')->vectors()->upsert(
                vectors: $chunk->pluck('embedding')->map(fn ($embedding, $index) => [
                    'id' => (string) ($chunkIndex * 20 + $index),
                    'values' => $embedding,
                    'metadata' => [
                        'text' => $content[$chunkIndex * 20 + $index],
                        'page' => $chunkIndex * 20 + $index + 1,
                    ]
                ])->toArray(), 
                namespace: 'wef'
            );
        });
    }
}
