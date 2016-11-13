<?php

namespace App\Console\Commands;

use App\Models\Bid;
use App\Models\Round;
use App\Models\Insurer;
use App\Jobs\ProcessNewBid;
use Carbon\Carbon;
use Psr\Log\LoggerInterface;
use Illuminate\Console\Command;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

class BidOnMeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bidonme';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run BidOnMe';


    /**
     * Create a new command instance.
     *
     * @param  DripEmailer  $drip
     * @return void
     */
    public function __construct(LoggerInterface $log)
    {
        parent::__construct();

        $this->log = $log;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->log->pushHandler(
            new ConsoleHandler($this->getOutput())
        );

        while (true) {

            $this->log->info("Started new loop...");

            $this->doNewRounds();

            sleep(5);
        }
    }

    protected function doNewRounds()
    {
        $rounds = Round::where('expires', '<', date('Y-m-d H:i:s'))
            ->where('closed', false);

        $this->info("Found {$rounds->count()} rounds to process");

        foreach ($rounds->get() as $round) {

            $transaction = Bid::where('round_id', $round->id)->first()->transaction;

            if ($round->number < 3) {

                $r          = new Round;
                $r->number  = $round->number + 1;
                $r->expires = (Carbon::now())->addMinutes(env('ROUND_DURATION'));
                $r->save();

                \Log::info('Created new round', [
                    'id'      => $r->id,
                    'expires' => $r->expires,
                ]);

                $insurers = Insurer::all();

                $insurers->each(function($insurer) use ($r, $transaction) {
                    $bid              = new Bid;
                    $bid->round_id    = $r->id;
                    $bid->offer_price = null;
                    $bid->insurer_id  = $insurer->id;

                    $transaction->bids()->save($bid);

                    dispatch(new ProcessNewBid($bid));
                });

                $round->closed = true;
                $round->save();
            } else {
                \Log::info('Bidding on transaction closed', [
                    'transaction_id' => $transaction->id,
                    'transaction_hash' => $transaction->id_hash,
                ]);

                // @todo: trigger bidding closed
            }
        }
    }
}
