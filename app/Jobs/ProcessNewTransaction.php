<?php

namespace App\Jobs;

use App\Models\Bid;
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

        $insurers->each(function($insurer) {
            $bid                 = new Bid;
            $bid->round_number   = 1;
            $bid->offer_price    = null;
            $bid->round_expires  = (Carbon::now())->addMinutes(2);
            $bid->insurer_id     = $insurer->id;

            $this->transaction->bids()->save($bid);

            dispatch(new ProcessNewBid($bid));
        });
    }
}
