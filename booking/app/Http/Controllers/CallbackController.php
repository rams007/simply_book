<?php

namespace App\Http\Controllers;

use App\Models\BatchBooking;
use App\Models\CreatedBookings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallbackController extends Controller
{
    public function ChargebeeCallback(Request $request)
    {
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
}
