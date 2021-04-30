<?php
require_once 'config.php';
require_once '../app/Helpers/JsonRpcClient.php';

use App\Helpers\JsonRpcClient;

$loginClient = new JsonRpcClient('https://user-api.simplybook.me' . '/login/');
$token = $loginClient->getToken(COMPANY_LOGIN, API_KEY);
$client = new JsonRpcClient('https://user-api.simplybook.me' . '/', array(
    'headers' => array(
        'X-Company-Login: ' . COMPANY_LOGIN,
        'X-Token: ' . $token
    )
));


$services = $client->getEventList();
//print_r($services);

?>


<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link href='bootstrap/css/bootstrap.css' rel='stylesheet'/>
    <script src='bootstrap/js/bootstrap.min.js'></script>
    <script src='lib/main.js'></script>
    <link href='css/custom.css' rel='stylesheet'/>
    <link href='html/css/styles.css' rel='stylesheet'/>
    <link href='html/css/webpage.css' rel='stylesheet'/>

    <script>


        function fillallowedTimes(allowedTimeForDay) {
            $('#sb_time_slots_container').html('');
            if (allowedTimeForDay !== undefined) {
                allowedTimeForDay.forEach(time => {
                    var timeConverted = new Date('Wed, 09 Aug 1995 ' + time + '');
                    console.log(timeConverted);


                    var hours = timeConverted.getHours();
                    var minutes = timeConverted.getMinutes();
                    var ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    var strTimeFrom = hours + ':' + minutes + ' ' + ampm;

                    timeConverted.setTime(timeConverted.getTime() + (2 * 60 * 60 * 1000));
                    hours = timeConverted.getHours();
                    minutes = timeConverted.getMinutes();
                    ampm = hours >= 12 ? 'PM' : 'AM';
                    hours = hours % 12;
                    hours = hours ? hours : 12; // the hour '0' should be '12'
                    minutes = minutes < 10 ? '0' + minutes : minutes;
                    var strTimeTo = hours + ':' + minutes + ' ' + ampm;


                    var slotHtml = '<div class="slot">' +
                        '<a class="sb-cell free "  onclick=selectTime("' + time + '","' + selectedDay + '")>' +
                        strTimeFrom + ' - <span class="end-time">&nbsp; ' + strTimeTo + ' </span>                      </a>                                </div>';
                    $('#sb_time_slots_container').append(slotHtml);
                })
            }


        }


        function selectTime(time, selectedDay) {
            console.log(time);
            $('#avaliableTimes').val(time);
            $('#sb_datetime_step_container').hide();
            $("#details").show();

            $('#timeMenu').addClass('filled  passed');
            $('#timeMenu').removeClass('active');
            $('#timeMenu .title-sub').html(selectedDay)
            $('#bookingInfoDate').html(selectedDay)
            $('#bookingInfoTime').html(time)
            $('#clientMenu').addClass('active');
        }

        function showCalendar(eventId, eventName, eventPrice) {
            console.log(eventId);

            $('#serviceMenu').addClass('filled  passed');
            $('#serviceMenu').removeClass('active');
            $('#serviceMenu .title-sub').html(eventName)
            $('#sb_booking_info .cap ').html(eventName)
            $('#timeMenu').addClass('active');

            $('#totalPrice').html("Total :$" + eventPrice);

            $("#eventId").val(eventId);
            $("#loader").show();

            $.get("initCalendar?eventId=" + eventId, function (data) {
                console.log(data);
                $("#loader").hide();
                $("#sb_menu").hide();
                $("#steps-nav").show();
                $("#sb_back_button a").removeClass('hidden');
                data.avaliableDates.forEach(curDate => {

                    if (allowedDates.indexOf(curDate) === -1) {
                        allowedDates.push(curDate);

                    }

                });
                allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                console.log(allowedTime);
                $("#eventLists").hide();
                $("#sb_datetime_step_container").show();
                LoadCalendar();


                var allowed = false;
                var timeNow = new Date();
                timeNow.setHours(0);
                timeNow.setMinutes(0);
                timeNow.setSeconds(0);
                timeNow.setMilliseconds(0);

                allowedDates.forEach(dateInList => {
                    var convertedDate = new Date(dateInList);
                    var convertedTime = convertedDate.getTime() + convertedDate.getTimezoneOffset() * 60 * 1000

                    //         var k = t.getTime();
                    if (convertedTime == timeNow.getTime()) {
                        console.log('can click');
                        allowed = true;
                    }
                });

                if (allowed) {

                    // $('#myModal').modal();

                    var month = '' + (timeNow.getMonth() + 1),
                        day = '' + timeNow.getDate(),
                        year = timeNow.getFullYear();

                    if (month.length < 2)
                        month = '0' + month;
                    if (day.length < 2)
                        day = '0' + day;

                    selectedDay = [year, month, day].join('-');


                    var allowedTimeForDay = allowedTime[selectedDay];

                    fillallowedTimes(allowedTimeForDay);

                }


            });

            $.get("customFields?eventId=" + eventId, function (data) {
                console.log(data);

                data.allAdditionalFields.forEach(field => {

                    if (field.value === null) {
                        field.value = '';
                    }

                    if (field.type === "text") {

                        if(field.title=='Address Line 1'){
                            $("#additionalFields").append(' <div class="form-group">\n' +
                                '                            <label for="field' + field.id + '">' + field.title + '</label>\n' +
                                '                            <input type="text" name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" placeholder="'+field.title+'"  autocomplete="shipping street-address"  >\n' +
                                '                        </div>');
                        }else{
                            $("#additionalFields").append(' <div class="form-group">\n' +
                                '                            <label for="field' + field.id + '">' + field.title + '</label>\n' +
                                '                            <input type="text" name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" placeholder="'+field.title+'"   >\n' +
                                '                        </div>');
                        }



                    } else if (field.type === 'select') {

                        var htmlSelect = '<div class="form-group">\n' +
                            '    <label for="field' + field.id + '">' + field.title + '</label>\n' +
                            '    <select class="form-control" id="field' + field.id + '"  name="' + field.name + '" >\n';


                        var listValues = field.values.split(',');

                        listValues.forEach(val => {

                            if (field.default == val) {
                                htmlSelect += '      <option value="' + val + '" selected>' + val + '</option>\n';
                            } else {
                                htmlSelect += '      <option value="' + val + '">' + val + '</option>\n';
                            }
                        })

                        htmlSelect += '    </select>\n' +
                            '  </div>';
                        $("#additionalFields").append(htmlSelect);

                    } else if (field.type === 'textarea') {
                        $("#additionalFields").append(' <div class="form-group">\n' +
                            '                            <label for="field' + field.id + '">' + field.title + '</label>\n' +
                            '                            <textarea  name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" ></textarea>\n' +
                            '                        </div>');
                    }
                });

            });

        }


        var allowedDates = [];
        var calendar = null;
        var allowedTime = {};
        var selectedDay = null;

        function LoadCalendar() {

            if (calendar != null) {
                document.getElementById("calendar").innerHTML = "";
            }

            var calendarEl = document.getElementById('calendar');

            var calendar = new FullCalendar.Calendar(calendarEl, {


                customButtons: {
                    myCustomNextButton: {
                        text: '',
                        click: function () {
                            //  calendar.next();
                            $("#loader").show();
                            console.log(calendar.getDate());

                            var calendarTime = calendar.getDate().getTime() - calendar.getDate().getTimezoneOffset() * 60 * 1000;
                            $.get("nextMonth?date=" + calendarTime, function (data) {
                                console.log(data);
                                $("#loader").hide();
                                data.avaliableDates.forEach(curDate => {

                                    if (allowedDates.indexOf(curDate) === -1) {
                                        allowedDates.push(curDate);
                                    }

                                });
                                allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                                console.log(allowedTime);
                                calendar.next();
                            });


                        }
                    },
                    myCustomPrevButton: {
                        text: '',
                        click: function () {
                            $("#loader").show();
                            console.log(calendar.getDate());
                            $.get("prevMonth?date=" + calendar.getDate().getTime(), function (data) {
                                console.log(data);
                                $("#loader").hide();
                                data.avaliableDates.forEach(curDate => {
                                    if (allowedDates.indexOf(curDate) === -1) {
                                        allowedDates.push(curDate);
                                    }
                                });
                                allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                                console.log(allowedTime);
                                calendar.prev();
                            });
                        }
                    },


                },


                headerToolbar: {

                    start: 'myCustomPrevButton',
                    center: 'title',
                    end: 'myCustomNextButton' // will normally be on the right. if RTL, will be on the left
                },
                initialDate: '2021-04-12',
                showNonCurrentDates: false,
                eventDisplay: 'none',
                selectable: true,
                longPressDelay: 100,
                select: function (arg) {


                    var allowed = false;
                    allowedDates.forEach(dateInList => {
                        var convertedDate = new Date(dateInList);
                        var convertedTime = convertedDate.getTime() + convertedDate.getTimezoneOffset() * 60 * 1000
                        if (convertedTime == arg.start.getTime()) {
                            console.log('can click');
                            allowed = true;
                        }
                    });

                    if (allowed) {

                        // $('#myModal').modal();
                        selectedDay = arg.startStr;
                        var allowedTimeForDay = allowedTime[selectedDay];
                        fillallowedTimes(allowedTimeForDay);

                    }
                    calendar.unselect()
                },
                dayCellClassNames: function (arg) {
                    var founded = false;
                    allowedDates.forEach(dateInList => {
                        var convertedDate = new Date(dateInList);
                        var convertedTime = convertedDate.getTime() + convertedDate.getTimezoneOffset() * 60 * 1000
                        if (convertedTime == arg.date.getTime()) {
                            console.log('can click');
                            founded = true;
                        }
                    });

                    if (founded) {
                        return ['day-on'];
                    } else {
                        return ['day-off'];
                    }

                },
                dayCellContent: function (arg) {
                    //        console.log(arg)
                },
                dayCellDidMount: function (arg) {
                    //         console.log(arg)
                },
                dayCellWillUnmount: function (arg) {
                    //       console.log(arg)
                },
            });

            calendar.render();


        }


        function FirstCalendar() {
            LoadCalendar();
        }

        document.addEventListener('DOMContentLoaded', FirstCalendar);


        $(document).ready(function () {

            $("#sb_back_button").click(function () {
                console.log('clicked');

                if ($("#clientMenu").hasClass('active')) {
                    console.log('client stage');
                    $('#avaliableTimes').val('');
                    $('#sb_datetime_step_container').show();
                    $("#details").hide();
                    $('#timeMenu').removeClass('filled  passed');
                    $('#timeMenu').addClass('active');
                    $('#timeMenu .title-sub').html('')
                    $('#bookingInfoDate').html('')
                    $('#bookingInfoTime').html('')
                    $('#clientMenu').removeClass('active');
                } else {

                    if ($("#timeMenu").hasClass('active')) {
                        console.log('time stage');

                        $('#serviceMenu').removeClass('filled  passed');
                        $('#serviceMenu').addClass('active');
                        $('#serviceMenu .title-sub').html('')
                        $('#sb_booking_info .cap ').html('')
                        $('#timeMenu').removeClass('active');
                        $("#sb_back_button a").addClass('hidden');
                        $('#totalPrice').html('');

                        $("#eventId").val('');
                        $("#sb_menu").show();
                        $("#steps-nav").hide();
                        $("#eventLists").show();
                        $("#sb_datetime_step_container").hide();

                    }

                }


            });


            $('#StartBooking').click(function () {

                var postData = {
                    selectedDay: selectedDay,
                    selectedTime: $('#avaliableTimes').val(),
                    email: $('#email').val(),
                    username: $('#username').val(),
                    phone: $('#phone').val(),
                    eventId: $("#eventId").val(),
                    formData: $("#addiTionalFields").serialize()
                    //     countRepeat: $('#countRepeat').val()
                };
                $.post("startBooking", postData).done(function (data) {
                    console.log(data);
                    if (data.error === true) {
                        alert(data.msg);

                    } else {
                        window.location.href = data.hostedPageUrl;
                    }

                });


            })

        });


    </script>


</head>
<body>

<div id="sb_main" class="sb-layout  ">
    <div id="sb-main-container">
        <header id="header" class="web" style="height: 402px;">
            <div class="container-fluid column">
                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <div class="nav-wrapper clearfix ">
                            <div class="items-wrapper">
                                <div id="book-btn" class="nav-item ">
                                    <div class="book-btn-container">
                                        <div class="item-container">
                                            <a class="popup-hide" href="#book"></a>
                                        </div>
                                    </div>
                                </div>
                                <div id="sb_client_info" class="nav-item" style="display: none;">
                                    <div class="login-container">
                                        <button class="avatar item-container" id="sb_client_info"><img
                                                src="https://graph.facebook.com/4138230779533258/picture?width=150&amp;height=150"
                                                alt="User profile menu item icon"></button>
                                        <div class="full-info">
                                            <div class="tab-pd">
                                                <div id="sb_login_form">
                                                    <div class="main-form">
                                                        <div class="is-logged">
                                                            <div class="cap">
                                                                You are logged in as: <b>Рома Говтвян</b>
                                                            </div>
                                                            <div class="bar-with-btn">
                                                                <button
                                                                    class="sb-client-info-popup btn profile btn-primary">
                                                                    My profile
                                                                </button>
                                                                <button class="popup-hide btn" id="sb_sign_out_btn">
                                                                    Logout
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="nav-trigger on" style="display: none;">
                                    <span></span>
                                </div>
                                <div id="sb_multiple_book_cart" class="nav-item without-menu"></div>
                            </div>

                            <div id="sb_menu">
                                <div>
                                    <ul class="nav clearfix" id="sb_menu_list_items_container">
                                        <li class="menu-item clearfix  active">
                                            <a class="popup-hide" href="#">Home</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#reviews">Reviews</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#contact-widget">Contact Us</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#client/bookings/type/upcoming">My Bookings</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main id="main">

            <div>
                <section id="main-content">
                    <div id="sb_content">
                        <div id="sb-timeline">

                            <nav id="steps-nav" style="display: none">
                                <div id="menu-active-bg"></div>
                                <div class="container-fluid column">
                                    <div class="row">
                                        <div id="sb_booking_info">
                                            <div class="booking-info">
                                                <ul class="clearfix">


                                                    <li class="step_info_item  active " id="serviceMenu">
                                                        <a href="#book/count/1/">
                                                            <div class="content">
                                                                <div class="title-small">
                                                                    Service
                                                                </div>
                                                                <div class="title-sub">
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>


                                                    <li class="step_info_item   " id="timeMenu">
                                                        <a href="#book/count/1/">
                                                            <div class="content">
                                                                <div class="title-small">
                                                                    Time
                                                                </div>
                                                                <div class="title-sub">
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>


                                                    <li class="step_info_item  " id="clientMenu">
                                                        <a href="#book/count/1/">
                                                            <div class="content">
                                                                <div class="title-small">
                                                                    Client
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>


                                                </ul>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </nav>


                            <div id="steps">

                                <div id="time-settings">
                                    <div class="container-fluid column">
                                        <div class="row">
                                            <div id="sb_booking_company_time">
                                                <div class="col-xs-12" translate="no">
                                                    <div class="time">
                                                        <div><b>Our time</b>: <?php echo date('h:i A '); ?></div>
                                                    </div>

                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="sb_booking_header"></div>

                                <div id="steps-content">
                                    <div class="container-fluid column">
                                        <div class="row">
                                            <div id="sb_back_button"><a href="#" class="hidden">
                                                    <span class="fa fa-angle-left"></span>
                                                    <span>Back</span>
                                                </a>
                                            </div>
                                            <div id="sb_booking_content" style="width: 100%;">


                                                <div id="eventLists" class="service-step step-content content-mode-list"
                                                     id="sb_service_step_container">
                                                    <div style="width:100%">

                                                        <div>

                                                            <?php foreach ($services as $service) {
                                                                if ($service->id == EXTENDED_SERVICE_ID) {
                                                                    continue;
                                                                }

                                                                ?>

                                                                <div
                                                                    class="service-item item panel">

                                                                    <div class="mobile-title">
                                                                        <h4 class="title">
                                                                            <a role="button"
                                                                               data-toggle="collapse"
                                                                               data-parent="#sb_service_step_container"
                                                                               href="#service1"
                                                                               aria-expanded="false"
                                                                               aria-controls="service1"
                                                                               class="collapsed">
                                                                                <?php echo $service->name; ?>
                                                                            </a>
                                                                        </h4>
                                                                    </div>


                                                                    <div class="one-line no-image">
                                                                        <div class="content">
                                                                            <h4 class="cap title">
                                                                                <a role="button"
                                                                                   data-toggle="collapse"
                                                                                   data-parent="#sb_service_step_container"
                                                                                   href="#service1"
                                                                                   aria-expanded="false"
                                                                                   aria-controls="service1"
                                                                                   class="collapsed">
                                                                                    <?php echo $service->name; ?>
                                                                                </a>
                                                                            </h4>

                                                                            <div
                                                                                class="info-bar bar-service">
                                                                                <div class="d-flex">

                                                                                    <div
                                                                                        class="bar-flex-item price price">
                                                                                        <i class="fal fa-wallet ico"></i>
                                                                                        <span
                                                                                            class="txt">$ <?php echo $service->price_with_tax; ?></span>
                                                                                    </div>

                                                                                    <div
                                                                                        class="bar-flex-item sb_group_booking_count"></div>

                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div
                                                                            class="btn-bar has-read-more">
                                                                            <div
                                                                                class="btn-round-mask">
                                                                                <a class="btn select custom"
                                                                                   onClick="showCalendar(<?php echo $service->id; ?>, '<?php echo $service->name; ?>', '<?php echo $service->price_with_tax; ?>' )">Select
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                </div>


                                                            <?php } ?>


                                                            <!--
                                                                                <div class="service-item item panel">

                                                                                    <div class="mobile-title">
                                                                                        <h4 class="title">
                                                                                            <a role="button" data-toggle="collapse" data-parent="#sb_service_step_container"
                                                                                               href="#service6" aria-expanded="false" aria-controls="service6" class="collapsed">
                                                                                                Monthly Cleaning (2 Hours)
                                                                                            </a>
                                                                                        </h4>
                                                                                    </div>


                                                                                    <div class="one-line no-image">
                                                                                        <div class="content">
                                                                                            <h4 class="cap title">
                                                                                                <a role="button" data-toggle="collapse" data-parent="#sb_service_step_container"
                                                                                                   href="#service6" aria-expanded="false" aria-controls="service6"
                                                                                                   class="collapsed">
                                                                                                    Monthly Cleaning (2 Hours)
                                                                                                </a>
                                                                                            </h4>

                                                                                            <div class="info-bar bar-service">
                                                                                                <div class="d-flex">

                                                                                                    <div class="bar-flex-item price price">
                                                                                                        <i class="fal fa-wallet ico"></i>
                                                                                                        <span class="txt">$100.00</span>
                                                                                                    </div>

                                                                                                    <div class="bar-flex-item sb_group_booking_count"></div>

                                                                                                    <div class="bar-flex-item recurring-block">
                                                                                                        <div class="service-bar">
                                                                                                            <div class="service-bar__wrapper">
                                                                                                                <div class="service-bar__icon">
                                                                                <span class="icon icon-reccuring ">
                                                                                    <i class="fa fa-sync"></i>
                                                                                </span>
                                                                                                                </div>
                                                                                                                <div class="service-bar__text">
                                                                                                                    Recurring
                                                                                                                </div>
                                                                                                                <div class="service-bar__recurring-hint">
                                                                                                                    <div class="dropdown recurring-hint__dropdown">
                                                                                                                        <button class="recurring-hint__btn" type="button"
                                                                                                                                id="recurring-hint__6" data-toggle="dropdown"
                                                                                                                                aria-haspopup="true" aria-expanded="false">
                                                                                                                            <i class="fal ico fa-info-circle"></i>
                                                                                                                        </button>
                                                                                                                        <div class="dropdown-menu recurring-hint__dropdown-menu"
                                                                                                                             aria-labelledby="recurring-hint__6">
                                                                                                                            <p class="recurring-hint__dropdown-txt">6 sessions</p>
                                                                                                                            <p class="recurring-hint__dropdown-txt">Repeat every 28
                                                                                                                                days</p>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                </div>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>

                                                                                        <div class="btn-bar has-read-more">
                                                                                            <div class="btn-round-mask">
                                                                                                <a class="btn select custom" href="#book/service/6/count/1/provider/1/">Select</a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="wrap-collapse-content collapse" id="service6" aria-expanded="false">
                                                                                        <div class="collapse-content">

                                                                                            <p>Monthly cleaning<br></p>

                                                                                            <div class="btn-bar btn-bar-full-info">
                                                                                                <a class="btn btn-hide collapsed" role="button" data-toggle="collapse"
                                                                                                   data-parent="#sb_service_step_container" href="#service6" aria-expanded="false"
                                                                                                   aria-controls="service6">
                                                                                                    <span class="hide-txt">Show less</span>
                                                                                                </a>

                                                                                                <a class="btn select custom" href="#book/service/6/count/1/provider/1/">Select</a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                </div>

                                                            -->
                                                        </div>


                                                    </div>
                                                </div>

                                                <!--calendar -->
                                                <div class="datetime-step step-content" id="sb_datetime_step_container"
                                                     style="display: none">

                                                    <div class="col-sm-12">
                                                        <div id="sb_dateview_container" class="section">
                                                            <div class="section-pd">
                                                                <div class="top-date-select">
                                                                    <div id='calendar'></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="col-sm-12">


                                                        <div class="col-md-9 col-md-12 ">
                                                            <div id="sb_group_booking_container"
                                                                 class="classes-plugin-group"></div>
                                                            <div id="sb_timeview_container" class="section">
                                                                <div>
                                                                    <div class="slots-view">
                                                                        <div class="timeline-wrapper">
                                                                            <div class="tab-pd">
                                                                                <div class="container-caption">
                                                                                    Available start times
                                                                                </div>


                                                                                <div id="sb_time_slots_container">

                                                                                </div>
                                                                                <div class="time-legend">
                                                                                    <div class="available">
                                                                                        <div class="circle"></div>
                                                                                        - Available
                                                                                    </div>

                                                                                    <input type="hidden"
                                                                                           class="form-control"
                                                                                           id="avaliableTimes">

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- end calendar -->

                                                <div>
                                                    <div id="details" style="display: none">

                                                        <div class="column">
                                                            <div class="left-side section">
                                                                <div class="add-details section-pd">
                                                                    <div class="custom-form">
                                                                        <div class="row">
                                                                            <div class="col-sm-12">
                                                                                <div class="form-horizontal">
                                                                                    <div
                                                                                        id="sb_additional_fields_container">

                                                                                        <div id="sb_additional_fields">

                                                                                            <div id="formBlock"
                                                                                            >
                                                                                                <div>


                                                                                                    <div
                                                                                                        class="form-group">
                                                                                                        <label
                                                                                                            for="email">Email
                                                                                                            address</label>
                                                                                                        <input
                                                                                                            type="email"
                                                                                                            name="email"
                                                                                                            class="form-control"
                                                                                                            id="email"
                                                                                                            aria-describedby="emailHelp"
                                                                                                            placeholder="Enter email">
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="form-group">
                                                                                                        <label
                                                                                                            for="username">User
                                                                                                            Name</label>
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            name="username"
                                                                                                            class="form-control"
                                                                                                            id="username"
                                                                                                            placeholder="User name">
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="form-group">
                                                                                                        <label
                                                                                                            for="phone">Phone</label>
                                                                                                        <input
                                                                                                            type="text"
                                                                                                            name="phone"
                                                                                                            class="form-control"
                                                                                                            id="phone"
                                                                                                            placeholder="Phone">
                                                                                                    </div>

                                                                                                    <input type="hidden"
                                                                                                           id="eventId">
                                                                                                </div>
                                                                                                <form
                                                                                                    id="addiTionalFields">
                                                                                                    <div
                                                                                                        id="additionalFields"></div>
                                                                                                </form>

                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="detail-step-wrap section">
                                                            <div class="section-pd">
                                                                <div class="title-main">
                                                                    Please, confirm details
                                                                </div>
                                                                <div class="detail-step clearfix">
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <div class="right-side">
                                                                                <div class="confirm-details">
                                                                                    <div
                                                                                        class="highlighted-current-booking"
                                                                                        id="sb_booking_info">
                                                                                        <div>
                                                                                            <div
                                                                                                class="current-booking-info">
                                                                                                <div class="cap mg">
                                                                                                    One-Time Cleaning (2
                                                                                                    Hours)
                                                                                                </div>
                                                                                                <div
                                                                                                    class="booking-info mg">
                                                                                                    <div
                                                                                                        class="booking-overview">
                                                                                                        <table>
                                                                                                            <tbody>
                                                                                                            <tr>
                                                                                                                <td class="label">
                                                                                                                    Date:
                                                                                                                </td>
                                                                                                                <td class="info"
                                                                                                                    id="bookingInfoDate">
                                                                                                                    04.29.2021
                                                                                                                </td>
                                                                                                            </tr>


                                                                                                            <tr>
                                                                                                                <td class="label">
                                                                                                                    Starts
                                                                                                                    at:
                                                                                                                </td>
                                                                                                                <td class="info"
                                                                                                                    id="bookingInfoTime">
                                                                                                                    11:00
                                                                                                                    AM
                                                                                                                </td>
                                                                                                            </tr>


                                                                                                            <tr>
                                                                                                                <td class="label">
                                                                                                                    Provider:
                                                                                                                </td>
                                                                                                                <td class="info">
                                                                                                                    Lingerie
                                                                                                                    Housekeeper
                                                                                                                    LA
                                                                                                                </td>
                                                                                                            </tr>

                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </div>
                                                                                                    <div
                                                                                                        class="booking-calendar">
                                                                                                        <div
                                                                                                            class="calendar">
                                                                                                            <div
                                                                                                                class="header">
                                                                                                                Apr
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="body">
                                                                                                                29
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <!--
                                                                                                <div class="mg">
                                                                                                    <div
                                                                                                        class="accordion">
                                                                                                        <a class="title"
                                                                                                           data-toggle="collapse"
                                                                                                           href="javascript:;"
                                                                                                           data-target="#collapseInvoice_details"
                                                                                                           aria-expanded="true">
                                                                                                            Purchase
                                                                                                            details:
                                                                                                        </a>
                                                                                                        <div
                                                                                                            class="collapse in"
                                                                                                            id="collapseInvoice_details"
                                                                                                            aria-expanded="true"
                                                                                                            style="">
                                                                                                            <p class="booking-info__details">
                                                                                                                <b class="booking-info__details-name">One-Time
                                                                                                                    Cleaning
                                                                                                                    (2
                                                                                                                    Hours):</b>

                                                                                                                <span
                                                                                                                    class="booking-info__details-row">
                                <span class="booking-info__details-count-price">
                                    1 x $170.00
                                </span>


                            </span>
                                                                                                            </p>

                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                                -->
                                                                                                <div
                                                                                                    class="booking-price mg">
                                                                                                    <div class="row">
                                                                                                        <div
                                                                                                            class="col-sm-12">
                                                                                                            <div
                                                                                                                class="wrapper">


                                                                                                                <div
                                                                                                                    class="full-price"
                                                                                                                    id="totalPrice">
                                                                                                                    Total
                                                                                                                    :
                                                                                                                    $170.00
                                                                                                                </div>


                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div
                                                                                        id="sb_multiple_booking_list_container"></div>
                                                                                    <div
                                                                                        id="is_pay_full_price_without_deposit_container"
                                                                                        class="deposit-checkbox-container">
                                                                                    </div>
                                                                                    <div class="license-links-container"
                                                                                         id="sb_terms_and_conditions">
                                                                                        <div>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="buttons-container">
                                                                                        <div
                                                                                            id="sb_multiple_booking_button_container"></div>
                                                                                        <div
                                                                                            class="sb-book-btn-container">
                                                                                            <div id="sb_book_btn"
                                                                                                 class="btn"
                                                                                                 role="button"
                                                                                                 tabindex="0">


                                                                                                <button
                                                                                                    style="
    background-color: transparent;
    border: none;
"
                                                                                                    id="StartBooking"
                                                                                                    type="button">

                                         <span>
                                                                                                    Confirm booking

                                           </span>
                                                                                                </button>


                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>


        </main>
        <footer id="footer">
            <div id="sb_scroll_top_btn" class="scroll-top-button">
                <i class="fa fa-angle-up"></i>
            </div>
            <div id="sb_cookies_block" class="cookies">
                <div class="container-fluid column">
                    <div class="wrapper">
                        <div class="text">
                            By clicking the Accept button you agree to the use of cookies. Please contact us if you'd
                            like to learn more about how we use cookies.
                        </div>
                        <div class="buttons">
                            <a href="javascript:;" class="btn" id="sb_accept_cookies">
                                I accept cookies
                            </a>
                            <a href="javascript:;" class="link" id="sb_disagree_cookies">
                                I disagree
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="copyright">
                            © 2013-2021
                            <a href="https://simplybook.me" target="_blank">
                                SimplyBook.me
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div>
</div>


<div id="loader" style="
    position: absolute;
    top: 50%;
    left: 50%;
    display:none;
">
    <img src="img/ajax-loader.gif" style="width: 70px;">
</div>

</body>
</html>
