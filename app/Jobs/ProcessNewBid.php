<?php

namespace App\Jobs;

use App\Models\Bid;
use Postmark\PostmarkClient;

class ProcessNewBid extends Job
{
    protected $bid;
    protected $email;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Bid $bid)
    {
        $this->bid = $bid;

        $this->email = new PostmarkClient(env('POSTMARK_SECRET'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = $this->bid->transaction;

        \Log::info('Send out offer request', [
            'bid_id'   => $this->bid->id,
            'bid_hash' => $this->bid->id_hash,
        ]);

        $from = "{$this->bid->id_hash}@bidonme.eu";
        $to   = $this->bid->insurer->email;

        $prev_round_offer = '';
        if ($this->bid->round->number > 1) {
            \Log::info('Recurring bid');

            $your_price = $transaction->bids()
                ->whereNotNull('offer_price')
                ->where('insurer_id', $this->bid->insurer_id)
                ->orderBy('id', 'desc')
                ->first();

            if (null === $your_price) {
                $your_price = 'None given';
            } else {
                $your_price = $your_price->offer_price / 100;
            }

            $best_price = $transaction->bids()->whereNotNull('offer_price')->orderBy('offer_price', 'asc')->first()->offer_price;
            $best_price = $best_price / 100;

            $prev_round_offer = "<b>Previous round offers:</b><br>\n".
                "Yours: {$your_price} <br>\n".
                "Price to match: {$best_price}".
                "<br><br>\n";
        }

        $data = str_replace("\n", "<br>\n", json_encode($transaction->data, JSON_PRETTY_PRINT));

        $sendResult = $this->email->sendEmail(
            'bidonme@bidonme.eu',
            $to,
            'Insurance quote request!',
            "Transaction: {$transaction->id_hash} <br>Round: {$this->bid->round->number} <br><br>".
            $prev_round_offer.
            "Policy requirements:<br>".$data,
            null,
            null,
            null,
            null,
            $from
        );
    }
}
