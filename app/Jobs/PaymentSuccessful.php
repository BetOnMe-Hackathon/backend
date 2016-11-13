<?php

namespace App\Jobs;

use App\Models\Bid;
use App\Models\Round;
use App\Models\Insurer;
use App\Jobs\ProcessNewBid;
use Postmark\PostmarkClient;
use Carbon\Carbon;

class PaymentSuccessful extends Job
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
        \Log::info('Sending payment email to cutomer', [
            'cutomer_email' => $this->bid->transaction->customer_email,
        ]);

        $sendResult = $this->email->sendEmail(
            'noreply@bidonme.eu',
            $this->bid->transaction->customer_email,
            "Policy purchased!",
            str_replace(
                '{{ link }}',
                "https://www.bidonme.eu/fight.html?id={$this->bid->transaction->id_hash}",
                file_get_contents(base_path().'/resources/views/payment.php')
            )
        );
    }
}
