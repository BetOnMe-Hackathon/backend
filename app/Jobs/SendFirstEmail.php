<?php

namespace App\Jobs;

use App\Models\Bid;
use App\Models\Round;
use App\Models\Insurer;
use App\Models\Transaction;
use App\Jobs\ProcessNewBid;
use Carbon\Carbon;

class SendFirstEmail extends Job
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

        $this->email = new PostmarkClient(env('POSTMARK_SECRET'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('Sending first email to cutomer', [
            'cutomer_email' => $transaction->customer_email,
        ]);

        $sendResult = $this->email->sendEmail(
            'noreply@bidonme.eu',
            $transaction->customer_email,
            "Welocome to insurance FightClub! 👊💥",
            file_get_contents(basedir().'/resources/views/customer.php')
        );
    }
}
