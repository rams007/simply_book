<?php

namespace App\Console\Commands;

use App\Helpers\JsonRpcClient;
use App\Models\AvaliableDates;
use DateTime;
use Illuminate\Console\Command;

class updateAvaliableDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateAvaliableDates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get all avaliable dates and save them into DB';

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
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));


        $services = $client->getEventList();
        $allAwaliableDates = [];

        $savedAvaliable = AvaliableDates::all();
        $allAvaliableSaved = [];
        foreach ($savedAvaliable as $savedDate) {
            $allAvaliableSaved[] = ['eventId' => $savedDate->service_id, 'date' => $savedDate->avaliable_date, 'time' => $savedDate->avaliable_time_start];
        }

        foreach ($services as $service) {
            $eventId = $service->id;
            for ($i = 0; $i < 6; $i++) {
                $dateStart = new DateTime();
                if ($i > 0) {
                    $dateStart->modify('+' . $i . ' month');
                }
                $dateFrom = $dateStart->format('Y-m-d');
                $date = new DateTime();
                if ($i > 0) {
                    $date->modify('+' . $i . ' month');
                }
                $date->modify('+1 month');
                $dateTo = $date->format('Y-m-d');

                $performerId = 1;
                $qty = 1;
                $availableTime = $client->getStartTimeMatrix($dateFrom, $dateTo, $eventId, $performerId, $qty);

                foreach ($availableTime as $date => $val) {
                    if (!empty($val)) {
                        foreach ($val as $time) {
                            $allAwaliableDates[] = ['eventId' => (int)$eventId, 'date' => $date, 'time' => $time];
                        }
                    }
                }
            }
        }


        $newRecords = [];
        foreach ($allAwaliableDates as $date) {
            $founded = false;
            foreach ($allAvaliableSaved as $saved) {
                if ($saved['eventId'] == $date['eventId'] AND $saved['date'] == $date['date'] AND $saved['time'] == $date['time']) {
                    $founded = true;
                }
            }
            if (!$founded) {
                $newRecords[] = $date;
            }
        }

        $mustDeleteRecords = [];
        foreach ($allAvaliableSaved as $date) {
            $founded = false;
            foreach ($allAwaliableDates as $saved) {
                if ($saved['eventId'] == $date['eventId'] AND $saved['date'] == $date['date'] AND $saved['time'] == $date['time']) {
                    $founded = true;
                }
            }
            if (!$founded) {
                $mustDeleteRecords[] = $date;
            }
        }

        //     $result = array_diff($allAwaliableDates, $allAvaliableSaved);
        //       $result2 = array_diff($allAvaliableSaved, $allAwaliableDates);

        foreach ($newRecords as $date) {
            try {
                AvaliableDates::create(['service_id' => $date['eventId'],
                    'avaliable_date' => $date['date'],
                    'avaliable_time_start' => $date['time']
                ]);
            } catch (\Throwable $e) {
                $er = $e->getMessage();
            }
        }
    }
}
