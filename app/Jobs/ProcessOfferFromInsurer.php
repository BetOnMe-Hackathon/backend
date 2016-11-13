<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Bid;
use App\Models\Round;
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

            if ($this->bid->round->expires->timestamp < time()) {
                \Log::info('Received and offer on expired bid', [
                    'bid_id'   => $this->bid->id,
                    'bid_hash' => $this->bid->id_hash,
                ]);
                // return;
            }

            $body = $this->input['TextBody'];
            $lines = explode("\n", $body);

            foreach ($lines as $line) {
                \Log::info('Line', [
                    'data' => $line,
                ]);

                $offerPrice = $line * 100;
                if ($offerPrice > 0) {
                    break;
                }
            }

            $this->bid->offer_price = $offerPrice;
            $this->bid->save();

            $bids = Bid::where('transaction_id', $this->bid->transaction->id)
                ->where('round_id', $this->bid->round->id)
                ->whereNull('offer_price');

            // If true, means round has closed, because all insurers have replied.
            // We start a new round, unless there have been 2 rounds.
            if ($bids->count() === 0 && $this->bid->round_number < 2) {
                $round          = new Round;
                $round->number  = $this->bid->round->number + 1;
                $round->expires = (Carbon::now())->addMinutes(env('ROUND_DURATION'));
                $round->save();

                \Log::info('Created new round', [
                    'id'      => $round->id,
                    'expires' => $round->expires,
                ]);

                $insurers = Insurer::all();

                $insurers->each(function($insurer) use ($round) {
                    $bid              = new Bid;
                    $bid->round_id    = $round->id;
                    $bid->offer_price = null;
                    $bid->insurer_id  = $insurer->id;

                    $this->bid->transaction->bids()->save($bid);

                    dispatch(new ProcessNewBid($bid));
                });

                $this->bid->round->closed = true;
                $this->bid->round->save();

                \Log::info('Closed round', [
                    'round_id'   => $this->bid->round->id,
                    'round_hash' => $this->bid->round->id_hash,
                ]);
            } else {
                // \Log::info('Round closed');

                // @todo: trigger bidding closed
            }
        }
    }
}
