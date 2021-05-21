<?php

namespace App\Console\Commands;

use App\Models\Subsriptions;
use ChargeBee_Environment;
use ChargeBee_Subscription;
use Illuminate\Console\Command;

class updateSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateSubscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all existed subscription statuses';

    /**
     * Create a new command instance.
     *
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
        ChargeBee_Environment::configure(env("CHARGEBEE_SITE"), env("CHARGEBEE_KEY"));
        $offcet = '';
        do {
            if (empty($offcet)) {
                $all = ChargeBee_Subscription::all(array());
            } else {
                $all = ChargeBee_Subscription::all(array('offset' => $offcet));
            }
            $offcet = $all->nextOffset();
            foreach ($all as $entry) {
                $subscription = $entry->subscription();
                $user = Subsriptions::firstOrNew([
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->planId,
                    'customer_id' => $subscription->customerId,
                    'status' => $subscription->status]);

                $user->status = $subscription->status;
                $user->save();
            }

        } while ($offcet !== null);

    }
}
