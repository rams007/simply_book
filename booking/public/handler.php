<?php
require_once 'config.php';
require_once 'JsonRpcClient.php';


$loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
$token = $loginClient->getToken(COMPANY_LOGIN, API_KEY);
$client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
    'headers' => array(
        'X-Company-Login: ' . COMPANY_LOGIN,
        'X-Token: ' . $token
    )
));


/*

$loginClientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
$tokenAdmin = $loginClientAdmin->getUserToken(COMPANY_LOGIN, USER_LOGIN, USER_PASSWORD);


$clientAdmin = new JsonRpcClient('https://user-api.simplybook.me' . '/admin/', array(
    'headers' => array(
        'X-Company-Login: ' . COMPANY_LOGIN,
        'X-User-Token: ' . $tokenAdmin
    )
));




$bookings = $clientAdmin->getBookings(['client_id'=>14]);

$bookDetails = $client->getBooking();

$t=1;
exit();
*/
//$fields = $client->getAdditionalFields (1);

switch ($_GET['action']) {
    case'init':
        $services = $client->getEventList();

//$providers = $client->getUnitList();

        $firstWorkingDay = $client->getFirstWorkingDay(['unit_group_id' => 1, 'event_id' => 1]);

        $dateFrom = '2021-04-01';
        $dateTo = '2021-04-31';
        $serviceId = 1;
        $performerId = 1;
        $qty = 1;
        $availableTime = $client->getStartTimeMatrix($firstWorkingDay, $dateTo, $serviceId, $performerId, $qty);


        $allAwaliableDates = [];

        foreach ($availableTime as $date => $val) {
            if (!empty($val)) {
                $allAwaliableDates[] = $date;
            }

        }

        $t = 1;
        header('Content-Type: application/json');
        echo json_encode(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);
        exit();


        break;


    case'next':

        //   echo date('Y-m-d',$_GET['date']/1000);

        $date = new DateTime();
        $date->setTimestamp($_GET['date'] / 1000);
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

        $t = 1;
        header('Content-Type: application/json');
        echo json_encode(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);
        exit();


        break;


    case'prev':

        //   echo date('Y-m-d',$_GET['date']/1000);

        $date = new DateTime();
        $date->setTimestamp($_GET['date'] / 1000);
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

        $t = 1;
        header('Content-Type: application/json');
        echo json_encode(['avaliableDates' => $allAwaliableDates, 'avaliableTimes' => $availableTime]);
        exit();


        break;


    case 'startBooking':


        $additionalFields = array();

        $clientData = array(
            'name' => 'Рома Говтвян',
            'email' => 'sramsiks@gmail.com',
            'phone' => '+380968231385'
        );

        $eventId = 1;
        $unitId = 1;
        $countRepeat = $_POST['countRepeat']; // @todo search plan for this count
        $date = $_POST['selectedDay'];
        $time = $_POST['selectedTime'];
        try {
            $bookingsInfo = $client->book($eventId, $unitId, $date, $time, $clientData, $additionalFields);


           // https://testrams-test.chargebee.com/hosted_pages/plans/recuring-cleaning
          //  https://testrams-test.chargebee.com/hosted_pages/plans/one-time-cleaning

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
        } catch (Throwable $e) {
            echo $e->getMessage();

            if ($e->getMessage() == 'Request error: Selected time start is not available') {
                //we need another date
            }
        }


        $t = 1;


        break;

    default:

        break;


}


