<?php

namespace App\Console\Commands;

use App\Helpers\JsonRpcClient;
use App\Models\CreatedBookings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteUpaidBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteUpaidBooking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'search and delete not confirmed bookings';

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
        $timeDelayInMinutes = 15;
        $minTs = time() - 60 * $timeDelayInMinutes;
        $minCreatedTime = date('Y-m-d H:i:s', $minTs);
        $allRecords = CreatedBookings::where('status', 'new')->where('created_at', '<', $minCreatedTime)->get();

        $loginClientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $tokenAdmin = $loginClientAdmin->getUserToken(env('COMPANY_LOGIN'), env('USER_LOGIN'), env('USER_PASSWORD'));


        $clientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/admin/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-User-Token: ' . $tokenAdmin
            )
        ));

        foreach ($allRecords as $record) {
            $result = $clientAdmin->cancelBooking($record->booking_id);

            if ($result === true) {
                $record->status = 'canceled';
                $record->save();
            } else {
                Log::debug("Cant delete booking with id=" . $record->booking_id);
            }

        }
    }
}
