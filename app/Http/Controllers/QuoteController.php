<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Jobs\SendFirstEmail;
use App\Jobs\ProcessNewTransaction;

class QuoteController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getQuote(Request $request)
    {
        $this->validate($request, [
            'customer_email'   => 'required|email',
            'bidding_duration' => 'required|numeric|min:86400|max:1209600',
            'data'             => 'required|array',
        ]);

        $data = $request->input('data');

        if (!is_array($data)) {
            $data = [$data];
        }

        $transaction                 = new Transaction;
        $transaction->customer_email = $request->input('customer_email');
        $transaction->bidding_ends   = $request->input('bidding_ends');
        $transaction->data           = $data;

        $transaction->save();

        dispatch(new SendFirstEmail($transaction));
        dispatch(new ProcessNewTransaction($transaction));

        return [
            'id' => $transaction->id_hash,
        ];
    }
}
