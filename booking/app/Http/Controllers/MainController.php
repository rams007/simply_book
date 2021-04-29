<?php

namespace App\Http\Controllers;

use App\Models\CreatedBookings;
use DateTime;
use Illuminate\Http\Request;
use App\Helpers\JsonRpcClient;

class MainController extends Controller
{
    public function initCalendar(Request $request)
    {

        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));

        $eventId = $request->eventId;
        $firstWorkingDay = $client->getFirstWorkingDay(['unit_group_id' => 1, 'event_id' => $eventId]);

        $date = new DateTime();
        $date->modify('last day of this month');
        $dateTo = $date->format('Y-m-d');

        $performerId = 1;
        $qty = 1;
        $availableTime = $client->getStartTimeMatrix($firstWorkingDay, $dateTo, $eventId, $performerId, $qty);
        $allAwaliableDates = [];
        foreach ($availableTime as $date => $val) {
            if (!empty($val)) {
                $allAwaliableDates[] = $date;
            }
        }
        return response()->json(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);
    }


    public function nextMonth(Request $request)
    {

        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));


        $date = new DateTime();
        $date->setTimestamp($request->date / 1000);
        $date->modify('last day of next month');
        $dateTo = $date->format('Y-m-d');

        $date->modify('first day of this month');
        $dateFrom = $date->format('Y-m-d');

        $serviceId = 1;
        $performerId = 1;
        $qty = 1;
        $availableTime = $client->getStartTimeMatrix($dateFrom, $dateTo, $serviceId, $performerId, $qty);

        $allAwaliableDates = [];

        foreach ($availableTime as $date => $val) {
            if (!empty($val)) {
                $allAwaliableDates[] = $date;
            }

        }
        return response()->json(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);
    }


    public function prevMonth(Request $request)
    {

        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));


        $date = new DateTime();
        $date->setTimestamp($request->date / 1000);
        $date->modify('last day of previous month');
        $dateTo = $date->format('Y-m-d');

        $date->modify('first day of this month');
        $dateFrom = $date->format('Y-m-d');

        $serviceId = 1;
        $performerId = 1;
        $qty = 1;
        $availableTime = $client->getStartTimeMatrix($dateFrom, $dateTo, $serviceId, $performerId, $qty);


        $allAwaliableDates = [];

        foreach ($availableTime as $date => $val) {
            if (!empty($val)) {
                $allAwaliableDates[] = $date;
            }

        }

        return response()->json(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);

    }


    public function getCustomFields(Request $request)
    {
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));
        $eventId = $request->eventId;
        $allAdditionalFields = $client->getAdditionalFields($eventId);
        return response()->json(['allAdditionalFields' => $allAdditionalFields,]);


    }

    public function startBooking(Request $request)
    {

        $errorMsg = '';
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));


        parse_str($request->formData, $formData);
        //    print_r($formData);
        $additionalFields = $formData;

        $clientData = array(
            'name' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone
        );


        //check if client exist


        $loginClientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $tokenAdmin = $loginClientAdmin->getUserToken(env('COMPANY_LOGIN'), env('USER_LOGIN'), env('USER_PASSWORD'));


        $clientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/admin/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-User-Token: ' . $tokenAdmin
            )
        ));


        $users = $clientAdmin->getClientList($request->email, null);
        if (!empty($users)) {
            $clientId = $users[0]->id;
            $ourClient = $clientAdmin->getClientInfo($clientId);

            $clientData['client_id'] = $clientId;
            $client_sign = md5($clientId . $ourClient->client_hash . env('API_SECRET'));
            $clientData['client_sign'] = $client_sign;
        } else {

            $clientId = $clientAdmin->addClient($clientData, false);
            $ourClient = $clientAdmin->getClientInfo($clientId);
            $clientData['client_id'] = $clientId;
            $client_sign = md5($clientId . $ourClient->client_hash . env('API_SECRET'));
            $clientData['client_sign'] = $client_sign;

        }

        $t = 1;


        $eventId = $request->eventId;
        $unitId = 1;
        $date = $request->selectedDay;
        $time = $request->selectedTime;
        try {
            $bookingsInfo = $client->book($eventId, $unitId, $date, $time, $clientData, $additionalFields);

            $t = 1;
            $bookIdList = "";
            try {
                if (empty($errorMsg)) {

                    foreach ($bookingsInfo->bookings as $booking) {

                        $record = CreatedBookings::create([
                            'booking_id' => $booking->id,
                            'event_id' => $booking->event_id,
                            'unit_id' => $booking->unit_id,
                            'client_id' => $booking->client_id,
                            'client_hash' => $booking->client_hash,
                            'start_date_time' => $booking->start_date_time,
                            'end_date_time' => $booking->end_date_time,
                            'time_offset' => $booking->time_offset,
                            'is_confirmed' => $booking->is_confirmed,
                            'require_payment' => $booking->require_payment,
                            'code' => $booking->code,
                            'hash' => $booking->hash,
                            'status' => 'new'
                        ]);

                        $bookIdList .= $record->id . ",";
                    }
                }
                $t = 2;
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
                $t = 3;
            }

            //HARDCODED ID of events and links to chargebee
            $hostedPageUrl = "";
            switch ($eventId) {

                case 1:
                    $hostedPageUrl = "https://testrams-test.chargebee.com/hosted_pages/plans/one-time-cleaning";
                    break;

                case 2:
                    $hostedPageUrl = "https://testrams-test.chargebee.com/hosted_pages/plans/daily_cleaning";
                    break;
                case 4:
                    $hostedPageUrl = "https://testrams-test.chargebee.com/hosted_pages/plans/recuring-cleaning";
                    break;

                case 5:
                    $hostedPageUrl = "https://testrams-test.chargebee.com/hosted_pages/plans/be_weekly_cleaning";
                    break;

                case 6:
                    $hostedPageUrl = "https://testrams-test.chargebee.com/hosted_pages/plans/monthly_weekly_cleaning";
                    break;

            }

            if (empty($hostedPageUrl)) {
                $errorMsg = "Unknown eventId";
            } else {

                $hostedPageUrl .= "?subscription[cf_bookid]=" . $bookIdList;
            }


            //  https://testrams-test.chargebee.com/hosted_pages/plans/one-time-cleaning
            // https://testrams-test.chargebee.com/hosted_pages/plans/recuring-cleaning
            //https://testrams-test.chargebee.com/hosted_pages/plans/monthly_weekly_cleaning
            // https://testrams-test.chargebee.com/hosted_pages/plans/daily_cleaning
            // https://testrams-test.chargebee.com/hosted_pages/plans/be_weekly_cleaning
            /*
            stdClass Object
     (
         [require_confirm] =>
         [bookings] => Array
             (
                 [0] => stdClass Object
                     (
                         [id] => 1
                         [event_id] => 1
                         [unit_id] => 1
                         [client_id] => 2
                         [client_hash] => e24fe7f5cc137e8e2df214b8694d17c0
                         [start_date_time] => 2021-04-22 09:00:00
                         [end_date_time] => 2021-04-22 10:00:00
                         [time_offset] => 0
                         [is_confirmed] => 1
                         [require_payment] =>
                         [code] => nhy0pky
                         [hash] => 56c1c5926b01a4a099704bec3b9c2bdb
                     )

                 [1] => stdClass Object
                     (
                         [id] => 2
                         [event_id] => 1
                         [unit_id] => 1
                         [client_id] => 2
                         [client_hash] => e24fe7f5cc137e8e2df214b8694d17c0
                         [start_date_time] => 2021-04-29 09:00:00
                         [end_date_time] => 2021-04-29 10:00:00
                         [time_offset] => 0
                         [is_confirmed] => 1
                         [require_payment] =>
                         [code] => nhy1ho3
                         [hash] => 604fd51c819202e972311b8a2bfd1b54
                     )

                 [2] => stdClass Object
                     (
                         [id] => 3
                         [event_id] => 1
                         [unit_id] => 1
                         [client_id] => 2
                         [client_hash] => e24fe7f5cc137e8e2df214b8694d17c0
                         [start_date_time] => 2021-05-06 09:00:00
                         [end_date_time] => 2021-05-06 10:00:00
                         [time_offset] => 0
                         [is_confirmed] => 1
                         [require_payment] =>
                         [code] => nhy26v2
                         [hash] => 9646cad9218bb605bda44729812a39d5
                     )

                 [3] => stdClass Object
                     (
                         [id] => 4
                         [event_id] => 1
                         [unit_id] => 1
                         [client_id] => 2
                         [client_hash] => e24fe7f5cc137e8e2df214b8694d17c0
                         [start_date_time] => 2021-05-13 09:00:00
                         [end_date_time] => 2021-05-13 10:00:00
                         [time_offset] => 0
                         [is_confirmed] => 1
                         [require_payment] =>
                         [code] => nhy35el
                         [hash] => 3fc9d215637cfa688ed5e4adc6a5ec35
                     )

             )

         [batch_type] => batch_recurrent_booking
         [recurrent_batch_id] => 1
         [batch_hash] => 357178bce290381bb7235080941ec143
         [invoice] =>
     )


            */
        } catch (\Throwable $e) {
            echo $e->getMessage();
            $errorMsg = $e->getMessage();
            if ($e->getMessage() == 'Request error: Selected time start is not available') {
                //we need another date
            }
        }


        $t = 1;

        if (empty($errorMsg)) {
            $IsError = false;
        } else {
            $IsError = true;
        }

        return response()->json(['error' => $IsError, 'msg' => $errorMsg, 'hostedPageUrl' => $hostedPageUrl]);


    }

}
