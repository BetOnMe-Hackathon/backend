<?php

namespace App\Jobs;

use App\Models\Bid;
use App\Models\Round;
use App\Models\Insurer;
use App\Models\Transaction;
use App\Jobs\ProcessNewBid;
use Carbon\Carbon;

class ProcessNewTransaction extends Job
{
    protected $transaction;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $insurers = Insurer::all();

        $round          = new Round;
        $round->number  = 1;
        $round->expires = (Carbon::now())->addMinutes(env('ROUND_DURATION'));
        $round->save();

        $insurers->each(function($insurer) use ($round) {
            $bid              = new Bid;
            $bid->round_id    = $round->id;
            $bid->offer_price = null;
            $bid->insurer_id  = $insurer->id;

            $this->transaction->bids()->save($bid);

            dispatch(new ProcessNewBid($bid));
        });
    }
}
