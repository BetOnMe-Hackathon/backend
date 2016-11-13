<?php

namespace App\Jobs;

use App\Models\Bid;
use App\Models\Round;
use App\Models\Insurer;
use App\Models\Transaction;
use App\Jobs\ProcessNewBid;
use Postmark\PostmarkClient;
use Carbon\Carbon;

class SendFirstEmail extends Job
{
    protected $transaction;

    protected $email;

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
            'cutomer_email' => $this->transaction->customer_email,
        ]);

        $sendResult = $this->email->sendEmail(
            'noreply@bidonme.eu',
            $this->transaction->customer_email,
            "Welocome to insurance FightClub! 👊💥",
            str_replace(
                '{{ link }}',
                "https://www.bidonme.eu/fight.html?id={$this->transaction->id_hash}",
                file_get_contents(base_path().'/resources/views/customer.php')
            )
        );
    }
}
