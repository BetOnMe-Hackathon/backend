<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Jobs\SendFirstEmail;
use App\Jobs\ProcessNewTransaction;
use Vinkla\Hashids\Facades\Hashids;

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

    public function show(Request $request, $quoteId)
    {
        $transaction = Transaction::find(Hashids::decode($quoteId))->first();

        $rounds = [];
        foreach ($transaction->bids as $bid) {
            $rounds[$bid->round->number] = [
                'expires' => $bid->round->expires->timestamp,
                'bids'    => [],
            ];
        }

        foreach ($transaction->bids as $bid) {
            $rounds[$bid->round->number]['bids'][] = [
                'offer_id'    => $bid->id_hash,
                'offer_price' => $bid->offer_price,
                'insurer' => [
                    'id' => $bid->insurer->id_hash,
                ],
            ];
        }

        return $rounds;
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
