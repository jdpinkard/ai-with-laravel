<?php

namespace App\Providers;

use andreskrey\Readability\Readability;
use andreskrey\Readability\Configuration;
use \Probots\Pinecone\Client as Pinecone;
use Lorisleiva\Actions\Facades\Actions;
use Illuminate\Support\ServiceProvider;
use App\Browserless;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Actions::registerCommands();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
