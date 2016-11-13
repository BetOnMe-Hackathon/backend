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

        if ($this->bid->round->number > 1) {
            \Log::info('Recurring bid');
        }

        $data = str_replace("\n", "<br>\n", json_encode($transaction->data, JSON_PRETTY_PRINT));

        $sendResult = $this->email->sendEmail(
            $from,
            $to,
            'Insurance quote request!',
            "Transaction: {$transaction->id_hash} <br>Round: {$this->bid->round->number} <br><br>".
            "Policy requirements:<br>".$data
        );
    }
}
