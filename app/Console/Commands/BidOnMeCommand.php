<?php

namespace App\Console\Commands;

use App\Models\Round;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rounds = Round::where('expires', '<', date('Y-m-d H:i:s'))
            ->where('closed', false);

        foreach ($rounds->get() as $round) {
            $r          = new Round;
            $r->number  = $round->number + 1;
            $r->expires = (Carbon::now())->addMinutes(env('ROUND_DURATION'));
            $r->save();

            \Log::info('Created new round', [
                'id'      => $r->id,
                'expires' => $r->expires,
            ]);

            $round->closed = true;
            $round->save();

            $insurers = Insurer::all();

            $insurers->each(function($insurer) use ($round) {
                $bid              = new Bid;
                $bid->round_id    = $r->id;
                $bid->offer_price = null;
                $bid->insurer_id  = $insurer->id;

                $this->bid->transaction->bids()->save($bid);

                dispatch(new ProcessNewBid($bid));
            });
        }
    }
}
