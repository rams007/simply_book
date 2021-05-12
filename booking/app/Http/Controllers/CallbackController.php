<?php

namespace App\Http\Controllers;

use App\Helpers\JsonRpcClient;
use App\Models\BatchBooking;
use App\Models\CreatedBookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function ChargebeeCallback(Request $request)
    {
        $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
        $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
        $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
            'headers' => array(
                'X-Company-Login: ' . env('COMPANY_LOGIN'),
                'X-Token: ' . $token
            )
        ));


        try {
            //    Log::debug( print_r($request->all(),true));

            if ($request->event_type === 'subscription_created') {
                Log::debug(print_r($request->input('content')['subscription'], true));
                if (isset($request->input('content')['subscription']['cf_bookid'])) {
                    $batchRecord = BatchBooking::find($request->input('content')['subscription']['cf_bookid']);
                    if ($batchRecord) {
                        $bookIdList = explode(",", $batchRecord->bookIdList);
                        foreach ($bookIdList as $bookId) {
                            $record = CreatedBookings::find($bookId);
                            if ($record) {
                                $record->status = 'active';
                                $record->subscription_id = $request->input('content')['subscription']['id'];
                                try {
                                    $sign = md5($record->booking_id . $record->hash . env('API_SECRET'));
                                    $resultConfirmation = $client->confirmBooking($record->booking_id, $sign);
                                    if ($resultConfirmation) {
                                        $record->confirmed_at = date('Y-m-d H:i:s');
                                    }
                                } catch (\Throwable $e) {
                                    Log::error('Callback ' . $e->getMessage());
                                }


                                $record->save();
                            } else {
                                Log::error('BookId ' . $bookId . ' not found in database');
                            }
                        }
                    }

                } else {
                    Log::error('BookId not found');
                }
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }

        return response()->json(['error' => false]);
    }

    public function SimplyBookCallback(Request $request)
    {

        //    $rawData = file_get_contents("php://input");
        Log::debug('Simplybook ' . print_r($request->all(), true));
        //     Log::debug('Simplybook2 '.print_r($rawData,true));

        /*      [booking_id] => 733
          [booking_hash] => 3d9b97d502e002b6ccae5bf8e0b489ef
              [company] => lingeriehousekeeper
              [notification_type] => create
              */
        try {
            $loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
            $token = $loginClient->getToken(env('COMPANY_LOGIN'), env('API_KEY'));
            $client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
                'headers' => array(
                    'X-Company-Login: ' . env('COMPANY_LOGIN'),
                    'X-Token: ' . $token
                )
            ));


            switch ($request->notification_type) {
                case "create":
                    $sign = md5($request->booking_id . $request->booking_hash . env('API_SECRET'));
                    $bookingDetails = $client->getBookingDetails($request->booking_id, $sign);

                    $client = $bookingDetails->client_name;
                    $service = $bookingDetails->event_name;
                    $start_date_time = date_parse($bookingDetails->start_date_time);

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

                    $t = 1;
             //       exit();
                    break;

                case "notify":

                    break;

                case "cancel":

                    break;

                case "remind":

                    break;

                case "change":

                    break;


            }
        } catch (\Throwable $e) {

        }


        return response()->json(['error' => false]);
    }
}
