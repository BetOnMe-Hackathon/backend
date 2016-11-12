<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Bid;
use App\Models\Insurer;
use Postmark\PostmarkClient;
use Vinkla\Hashids\Facades\Hashids;

class ProcessOfferFromInsurer extends Job
{
    protected $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $from = substr($this->input['To'], 0, 32);

        $this->bid = Bid::find(Hashids::decode($from))->first();

        if (null !== $this->bid) {

            if ($this->bid->round_expires->timestamp > time()) {
                \Log::info('Received and offer on expired bid');
            }

            $body = $this->input['TextBody'];
            $lines = explode("\n", $body);

            foreach ($lines as $line) {
                $offerPrice = $line * 100;
                if ($offerPrice > 0) {
                    break;
                }
            }

            $this->bid->offer_price = $offerPrice;
            $this->bid->save();

            $bids = Bid::where('transaction_id', $this->bid->transaction->id)
                ->where('round_number', $this->bid->round_number)
                ->whereNull('offer_price');

            if ($bids->count() === 0 && $this->bid->round_number < 3) {
                $insurers = Insurer::all();

                $insurers->each(function($insurer) {
                    $bid                = new Bid;
                    $bid->round_number  = $this->bid->round_number + 1;
                    $bid->offer_price   = null;
                    $bid->round_expires = (Carbon::now())->addMinutes(2);
                    $bid->insurer_id    = $insurer->id;

                    $this->bid->transaction->bids()->save($bid);

                    dispatch(new ProcessNewBid($bid));
                });
            }
        }
    }
}
