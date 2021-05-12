<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\CreatedBookings;
use App\Helpers\JsonRpcClient;
use Illuminate\Support\Facades\Log;

class WatchBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'WatchBookingStatus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watch active bookings and create new ';

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
        define('COMPANY_LOGIN', env('COMPANY_LOGIN'));
        define('USER_LOGIN', env('USER_LOGIN'));
        define('USER_PASSWORD', env('USER_PASSWORD'));


        define('API_KEY', env('API_KEY'));
        define('API_SECRET', env('API_SECRET'));
        define('EXTENDED_SERVICE_ID', env('EXTENDED_SERVICE_ID')); //id of service used only for extended book
        define('UNIT_ID', env('UNIT_ID'));


        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(COMPANY_LOGIN, API_KEY);
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . COMPANY_LOGIN,
                'X-Token: ' . $token
            )
        ));


        $loginClientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $tokenAdmin = $loginClientAdmin->getUserToken(COMPANY_LOGIN, USER_LOGIN, USER_PASSWORD);


        $clientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/admin/', array(
            'headers' => array(
                'X-Company-Login: ' . COMPANY_LOGIN,
                'X-User-Token: ' . $tokenAdmin
            )
        ));


        $services = $client->getEventList();

        $clients = $clientAdmin->getClientList("", null);
        $t = 1;


        $allAdditionalFields = $client->getAdditionalFields(EXTENDED_SERVICE_ID);

//$c = $clientAdmin->getClientInfo(1);

        //   $t = $clientAdmin->getBookingDetails (350);

        foreach ($services as $service) {
            if ($service->is_recurring === "0") {
                continue;
            }
            if ($service->is_active === "0") {
                continue;
            }

            $serviceId = $service->id;
            if ($serviceId == EXTENDED_SERVICE_ID) {
                continue;
            }

            foreach ($clients as $clientDetails) {
                $clientId = $clientDetails->id;
                $clientData = array(
                    'client_id' => $clientDetails->id,
                    'name' => $clientDetails->name,
                    'email' => $clientDetails->email,
                    'phone' => $clientDetails->phone
                );
                $bookings = $clientAdmin->getBookings(['event_id' => $serviceId, 'date_from' => date('Y-m-d'), 'client_id' => $clientId, 'is_confirmed' => 1]);
                $bookingsExtended = $clientAdmin->getBookings(['event_id' => EXTENDED_SERVICE_ID, 'date_from' => date('Y-m-d'), 'client_id' => $clientId, 'is_confirmed' => 1]);

                if (!empty($bookings)) {
                    $t = 2;
                    //need create array of all avaliable dates and time  where book must be placed
                    $allAvaliableDates = [];
                    $repeatCount = $service->recurring_settings->repeat_count;
                    if ($repeatCount <= count($bookings)) {
                        echo 'For client=' . $clientId . ' and service =' . $serviceId . ' we already have all needed bookings';
                        continue;
                    }

                    $bookingStartTime = Carbon::parse($bookings[0]->start_date);
                    $startedHours = $bookingStartTime->hour;
                    $startedMins = $bookingStartTime->minute;

                    $bookingEndTime = Carbon::parse($bookings[0]->end_date);
                    $endedHours = $bookingEndTime->hour;
                    $endedMins = $bookingEndTime->minute;


                    $startedDateForCheck = Carbon::now();
                    $startedDateForCheck->hour = $startedHours;
                    $startedDateForCheck->minute = $startedMins;
                    $startedDateForCheck->second = 0;

                    if ($startedDateForCheck->isPast()) {
                        $startedDateForCheck->addDays(1);
                    }

                    /*      $endDateForCheck = $startedDateForCheck->copy();
                          $endDateForCheck->hour = $endedHours;
                          $endDateForCheck->minute = $endedMins;
                          $endDateForCheck->second = 0;
      */

                    if ($service->recurring_settings->type === "weekly") {
                        do {
                            //check if day avaliable
                            if (array_search($startedDateForCheck->englishDayOfWeek, $service->recurring_settings->days_names) !== false) {
                                //   $allAvaliableDates[] = ['start_date' => $startedDateForCheck->format('Y-m-d H:i:s'), 'end_date' => $endDateForCheck->format('Y-m-d H:i:s')];
                                $allAvaliableDates[] = ['start_date' => $startedDateForCheck->format('Y-m-d H:i:s')];
                            }
                            $startedDateForCheck->addDays(1);
                            //      $endDateForCheck->addDays(1);
                        } while (count($allAvaliableDates) < $repeatCount);
                    }

                    //check  maybe we already have  book for this time
                    foreach ($bookings as $book) {
                        $allAvaliableDates = array_filter($allAvaliableDates, function ($k) use ($book) {
                            return $k['start_date'] !== $book->start_date;
                        });
                    }
                    foreach ($bookingsExtended as $book) {
                        $allAvaliableDates = array_filter($allAvaliableDates, function ($k) use ($book) {
                            return $k['start_date'] !== $book->start_date;
                        });
                    }


                    $t = 2;

                    //if we dont - try create book

                    $clientExtended = $clientAdmin->getClientInfo($clientId);
                    $client_sign = md5($clientId . $clientExtended->client_hash . API_SECRET);
                    $clientData['client_sign'] = $client_sign;

                    $additionalFieldFilledByClient = $clientAdmin->getBookingDetails($bookings[0]->id)->additional_fields;
                    $bookingFieldsData = [];
                    foreach ($additionalFieldFilledByClient as $field) {
                        $bookingFieldsData[$field->field_name] = $field->value;
                    }

                    foreach ($allAvaliableDates as $date) {

                        $dateTimeParts = explode(" ", $date['start_date']);
                        $date = $dateTimeParts[0];
                        $time = $dateTimeParts[1];

                        $additionalFields = [];

                        foreach ($allAdditionalFields as $field) {
                            if ($field->is_null === "0" OR $field->is_null === null) {  // dont have default value
                                if (isset($bookingFieldsData[$field->name])) {
                                    $additionalFields[$field->name] = $bookingFieldsData[$field->name];
                                } else {
                                    if (empty($field->default)) {
                                        $additionalFields[$field->name] = ' ';
                                    } else {
                                        $additionalFields[$field->name] = $field->default;
                                    }
                                }
                            }

                        }

                        $error = '';
                        try {
                            $bookingsInfo = $client->book(EXTENDED_SERVICE_ID, UNIT_ID, $date, $time, $clientData, $additionalFields);
                            $t = 2;
                        } catch (\Throwable $e) {
                            $error = $e->getMessage();
                            $t = 3;
                            if ($error === 'Request error: Selected time start is not available') {
                                Log::debug($clientData['client_id'] . ' cant create book on ' . $date . ' ' . $time);
                                break;
                            }
                        }


                        try {
                            if (empty($error)) {

                                CreatedBookings::create([
                                    'booking_id' => $bookingsInfo->bookings[0]->id,
                                    'event_id' => $bookingsInfo->bookings[0]->event_id,
                                    'unit_id' => $bookingsInfo->bookings[0]->unit_id,
                                    'client_id' => $bookingsInfo->bookings[0]->client_id,
                                    'client_hash' => $bookingsInfo->bookings[0]->client_hash,
                                    'start_date_time' => $bookingsInfo->bookings[0]->start_date_time,
                                    'end_date_time' => $bookingsInfo->bookings[0]->end_date_time,
                                    'time_offset' => $bookingsInfo->bookings[0]->time_offset,
                                    'is_confirmed' => $bookingsInfo->bookings[0]->is_confirmed,
                                    'require_payment' => $bookingsInfo->bookings[0]->require_payment,
                                    'code' => $bookingsInfo->bookings[0]->code,
                                    'hash' => $bookingsInfo->bookings[0]->hash,
                                ]);

                            }

                            $t = 2;
                        } catch (\Throwable $e) {
                            $error = $e->getMessage();
                            $t = 3;
                        }
                        /*
                         stdClass Object
    (
        [require_confirm] =>
        [bookings] => Array
            (
                [0] => stdClass Object
                    (
                        [id] => 289
                        [event_id] => 7
                        [unit_id] => 1
                        [client_id] => 1
                        [client_hash] => e6b09a1b4b2ff7210e65cf6649ed24ae
                        [start_date_time] => 2021-04-28 13:00:00
                        [end_date_time] => 2021-04-28 15:00:00
                        [time_offset] => 0
                        [is_confirmed] => 1
                        [require_payment] =>
                        [code] => nhy7wkg3
                        [hash] => c6db56489de495f6807cf06f594d671d
                    )

            )

        [invoice] =>
    )

                         */


                    }


                    //on error log event and  go to next


                }


            }


            $t = 2;
        }

    }
}
