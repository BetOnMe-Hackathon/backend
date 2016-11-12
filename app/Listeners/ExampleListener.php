<?php

namespace App\Listeners;

use App\Events\NewTransaction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct()
    {
        //
    }

    public function handle(NewTransaction $event)
    {
        dd($event);
    }
}
