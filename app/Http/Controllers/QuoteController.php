<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Jobs\SendFirstEmail;
use App\Jobs\ProcessNewTransaction;
use Vinkla\Hashids\Facades\Hashids;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        if (null === $transaction) {
            throw new NotFoundHttpException;
        }

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

    // curl 'https://decc27d7.eu.ngrok.io/quotes' --data 'customer_email=msipenko%40kasko.io&bidding_duration=86400&data%5Bproperty_type%5D=apartment&data%5Bconstruction_type%5D=brick&data%5Bproperty_size%5D=0&data%5Bdisaster%5D=9000&data%5Brobbery%5D=10000&data%5Bsecurity%5D=false&data%5Breconstruction%5D=true&data%5Bbuilding_year%5D=false'

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
        $transaction->bidding_ends   = time() + $request->input('bidding_duration');
        $transaction->data           = $data;

        $transaction->save();

        dispatch(new SendFirstEmail($transaction));
        dispatch(new ProcessNewTransaction($transaction));

        return [
            'id' => $transaction->id_hash,
        ];
    }
}
