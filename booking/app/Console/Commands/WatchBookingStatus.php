<?php

namespace App\Console\Commands;

use App\Models\Subsriptions;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\CreatedBookings;
use App\Helpers\JsonRpcClient;
use Illuminate\Support\Facades\Log;
use Mailgun\Mailgun;

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
        /*
                $sign = md5("7333d9b97d502e002b6ccae5bf8e0b489ef" . env('API_SECRET'));
                $bookingDetails = $client->getBookingDetails(733, $sign);

                $client = $bookingDetails->client_name;
                $client_email = $bookingDetails->client_email;
                $service = $bookingDetails->event_name;
                $start_date_time = strtotime($bookingDetails->start_date_time);

                $date_start = date('Y-m-d', $start_date_time);
                $time_start = date('H:i:s', $start_date_time);
                $addres1 = "";
                $addres2 = "";
                foreach ($bookingDetails->additional_fields as $field) {
                    if ($field->field_title === 'Address Line 1') {
                        $addres1 = $field->value;
                    } else if ($field->field_title === 'Address Line 2') {
                        $addres2 = $field->value;
                    }


                }

                $clientId=$bookingDetails->client_id;

                $clentDetails = $clientAdmin->getClientList ();
                $hashes=[];
                $sign=[];
        foreach($clentDetails as $c){
            $clientId=$c->id;

            $cd = $clientAdmin->getClientInfo($clientId);
            $hashes[]=$cd->client_hash;
            $sign[]= md5($clientId . $cd->client_hash . env('API_SECRET'));
        }
        $t=2;

                $t = 1;
                $sign = md5($clientId . $clentDetails->client_hash .  env('API_SECRET'));



                $html = '<!DOCTYPE html>
        <html lang="en">
          <head>
            <meta charset="utf-8" />
            <meta http-equiv="x-ua-compatible" content="ie=edge" />
            <meta name="viewport" content="width=device-width, initial-scale=1" />
            <title>Confirm</title>
          </head>

          <body>
        <div style="width: 80%;margin: auto;border: 1px solid gray;padding: 33px;color: gray;">
        <h1 style="text-align: center;color: #9d9b9b;"> Appointment scheduled </h1>
        <h4 style="text-align: center;color: black;" > for [client] </h4><hr><table style="width: 100%;">
        <tr>
        <td style="width: 88px;" >What:</td>
        <td style="color: black;" >[service]</td>
        </tr>
        <tr>
        <td style="vertical-align: baseline;" >When:</td>
        <td style="color: black;" >[date_start] [time_start] <br>
        [date_list] </tr>
        <td style="width: 88px;" >Where:</td>
        <td style="color: black;" >[data_field_6]  [data_field_13]</td>
        </tr>

        </table>
        <p>Thanks for booking</p>
        <p >Looking forward to see you soon <8</p>
        <p style="text-align: center;"> <a href="[client_bookings_link]"><button style="background-color: #9c00f7; color: white; padding: 15px;  font-size: 18px; font-family: Arial;"> RESCHEDULE/EDIT </button></a></p>
        </div>
          </body>
        </html>';

                $html = str_replace('[client]', $client, $html);
                $html = str_replace('[service]', $service, $html);
                $html = str_replace('[date_start]', $date_start, $html);
                $html = str_replace('[time_start]', $time_start, $html);
                $html = str_replace('[time_start]', $time_start, $html);
                $html = str_replace('[data_field_6]', $addres1, $html);
                $html = str_replace('[data_field_13]', $addres2, $html);


                $mg = Mailgun::create('key-7bf2f2e9b53986eb5d3e028b1ba96f00', 'https://api.eu.mailgun.net'); // For EU servers
                $mg->messages()->send('mg.miner-stats.com', [
                    'from' => 'bob@example.com',
                    'to' => $client_email,
                    'subject' => 'Test from sdk',
                    'html' => $html
                ]);













                exit();
                */
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

                if (empty($bookings)) {
                    //just check maybe we miss some appointments
                    $existedBookings = CreatedBookings::where('event_id', $serviceId)->where('client_id', $clientId)->orderBy('start_date_time')->first();
                    if ($existedBookings) {
                        $existedBookings->start_date = $existedBookings->start_date_time;
                        $bookings[] = $existedBookings;
                    }
                    $countExistedBookings = 0;
                } else {
                    $countExistedBookings = count($bookings);
                }

                if (!empty($bookings)) {

                    $subscription = Subsriptions::where('subscription_id',$bookings[0]->booking_id)->first();
                    if($subscription){
                        if($subscription->status ==='cancelled'){
                            echo 'For client=' . $clientId . ' and service =' . $serviceId . ' we have cancelled subscription. Skip it.';
                            continue;
                        }
                    }
                    //need create array of all avaliable dates and time  where book must be placed
                    $allAvaliableDates = [];
                    $repeatCount = $service->recurring_settings->repeat_count;
                    if ($repeatCount <= $countExistedBookings) {
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

                    while ($startedDateForCheck->isPast()) {
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
                                    //var_dump($field->type);
                                    if ($field->type !== "select") {
                                        $additionalFields[$field->name] = $bookingFieldsData[$field->name];
                                    } else {
                                        if (empty(trim($bookingFieldsData[$field->name]))) {
                                            $parts = explode(",", $field->values);
                                            $additionalFields[$field->name] = $parts[1];
                                        } else {
                                            $additionalFields[$field->name] = $bookingFieldsData[$field->name];
                                        }
                                    }
                                } else {
                                    if (empty($field->default)) {
                                        if ($field->type !== "select") {
                                            $additionalFields[$field->name] = 'N/a';
                                        } else {
                                            $parts = explode(",", $field->values);
                                            $additionalFields[$field->name] = $parts[1];
                                        }

                                    } else {
                                        $additionalFields[$field->name] = $field->default;
                                    }
                                }
                            }
                            if (empty($additionalFields[$field->name]) AND $field->type !== "select") {
                                $additionalFields[$field->name] = 'N/a';
                            }

                        }
                        $error = '';
                        try {
                            $bookingsInfo = $client->book(EXTENDED_SERVICE_ID, UNIT_ID, $date, $time, $clientData, $additionalFields);
                            $t = 2;
                        } catch (\Throwable $e) {
                            $error = $e->getMessage();
                            echo $error;
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
                                    'status' => 'active'
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
