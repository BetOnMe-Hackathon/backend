<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOfferFromInsurer;
use Illuminate\Http\Request;

class WebHookController extends Controller
{

    public function receive(Request $request)
    {
        \Log::info('Email webhook received', $request->input());

        dispatch(new ProcessOfferFromInsurer($request->input()));

        return 'ok';
    }
}
