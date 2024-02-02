<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Console\Command;

class FirstPrompt
{
    use AsAction;

    public $commandSignature = 'inspire {prompt : The user prompt}';

    public function handle(array $messages)
    {
        return OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            ]
        );
    }

    public function asCommand(Command $command)
    {
        $command->comment($this->handle($command->argument('prompt')));
    }
}
