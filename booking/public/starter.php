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

    <link href='//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css' rel='stylesheet'/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>


        function changePage (pageName){

            switch(pageName){
                case 'Reviews':
                    $("#sb-timeline").hide();
                    $("#myBookings").hide();
                    $("#contactUs").hide();
                    $("#reviews-view").show();
                    break;

                case 'Contact Us':
                    $("#sb-timeline").hide();
                    $("#reviews-view").hide();
                    $("#myBookings").hide();
                    $("#contactUs").show();
                    break;

                case 'My Bookings':
                    $("#sb-timeline").hide();
                    $("#contactUs").hide();
                    $("#reviews-view").hide();
                    $("#myBookings").show();
                    break;

            }


        }

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
         var usDate =   selectedDay.split("-");

            $('#bookingInfoDate').html(usDate[1]+'-'+usDate[2]+'-'+usDate[0])
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
                $(".nav-trigger.on").show();
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
                $("#additionalFields").html("");
                data.allAdditionalFields.forEach(field => {

                    if (field.value === null) {
                        field.value = '';
                    }
                    var labelClass="";
if(field.is_null!=="1"){

    labelClass=" control-label  required";
}
                    if (field.type === "text") {

                        if (field.title == 'Address Line 1') {
                            $("#additionalFields").append(' <div class="form-group">\n' +
                                '                            <label for="field' + field.id + '" class="'+labelClass+'">' + field.title + '</label>\n' +
                                '                            <input type="text" name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" placeholder="' + field.title + '"  autocomplete="address-line1"  >\n' +
                                '                        </div>');
                        }else if (field.title == 'Address Line 2'){
                            $("#additionalFields").append(' <div class="form-group">\n' +
                                '                            <label for="field' + field.id + '" class="'+labelClass+'">' + field.title + '</label>\n' +
                                '                            <input type="text" name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" placeholder="' + field.title + '"  autocomplete="address-line2"  >\n' +
                                '                        </div>');


                        } else {

                            var autocomplete = 'autocomplete="on"';
                            switch(field.title){
                                case 'City':
                                    autocomplete = 'autocomplete="shipping locality"';
                                    break;
                                case 'State':
                                    autocomplete = 'autocomplete="shipping region"';
                                    break;

                                case 'Zip':
                                    autocomplete = 'autocomplete="postal-code"';
                                    break;
                                case 'Zip':
                                    autocomplete = 'autocomplete="postal-code"';
                                    break;


                            }



                            $("#additionalFields").append(' <div class="form-group">\n' +
                                '                            <label for="field' + field.id + '" class="'+labelClass+'">' + field.title + '</label>\n' +
                                '                            <input type="text" name="' + field.name + '" value="' + field.value + '" class="form-control" id="field' + field.id + '" placeholder="' + field.title + '"  '+autocomplete+'  >\n' +
                                '                        </div>');
                        }


                    } else if (field.type === 'select') {

                        var htmlSelect = '<div class="form-group">\n' +
                            '    <label for="field' + field.id + '" class="'+labelClass+'">' + field.title + '</label>\n' +
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
                            '                            <label for="field' + field.id + '" class="'+labelClass+'">' + field.title + '</label>\n' +
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
                           var eventId =   $("#eventId").val();
                            var calendarTime = calendar.getDate().getTime() - calendar.getDate().getTimezoneOffset() * 60 * 1000;
                            $.get("nextMonth?date=" + calendarTime+ "&eventId="+eventId, function (data) {
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
                            var eventId =   $("#eventId").val();
                            console.log(calendar.getDate());
                            $.get("prevMonth?date=" + calendar.getDate().getTime()+ "&eventId="+eventId, function (data) {
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
                initialDate: '<?php echo date("Y-m-d");?>',
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

            $('body').click(function (event) {
                if ($('body').hasClass('hasModal')) {
                    $(".full-info").toggleClass('active');
                    if ($(".full-info").hasClass('active')) {
                        $('body').addClass('hasModal');
                    } else {
                        $('body').removeClass('hasModal')
                    }
                }
            });


            $("#sb_sign_in_btn").click(function () {
                console.log($("sb_sign_in_email").val());
                if ($("#sb_sign_in_email").val() === "") {
                    toastr.warning("Please enter email");
                    return false;
                }
                if ($("#sb_sign_in_password").val() === "") {
                    toastr.warning("Please enter password");
                    return false;
                }
                $("#loader").show();
                var postData = {email: $("#sb_sign_in_email").val(), password: $("#sb_sign_in_password").val()};
                $.post("login", postData).done(function (data) {
                    console.log(data);
                    $("#loader").hide();
                    if (data.error === true) {
                        //  alert(data.msg);
                        toastr.error(data.msg, 'error!')
                    } else {
                        // window.location.href = data.hostedPageUrl;
                        $(".not-logged").hide();
                        $(".is-logged").show();
                        $("#loginName").html(data.userData.name)
                        $("#sb_client_info img").attr('src', data.userData.openid_img);
                        $("#loginedUserId").val(data.userData.id);
                        $("#loginedUserHash").val(data.userData.client_hash);
                    }

                });


            });

            $('.full-info').click(function (event) {
                console.log('clicked');
                event.stopPropagation();
            });


            $("#sb_client_info").click(function (event) {
                console.log('click');
                $(".full-info").toggleClass('active');
                if ($(".full-info").hasClass('active')) {
                    $('body').addClass('hasModal');
                } else {
                    $('body').removeClass('hasModal')
                }
                event.stopPropagation();
            });

            $(".nav-trigger.on").click(function () {

                $(this).toggleClass('opened');

                if ($(this).hasClass('opened')) {
                    $('#sb_menu').show();
                    $('#steps-nav').hide();
                } else {
                    $('#sb_menu').hide();
                    $('#steps-nav').show();
                }
            })

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
                        $(".nav-trigger.on").hide();

                    }

                }


            });


            $('#StartBooking').click(function () {


                if($('#acceptTOS').prop('checked')===false){
                    toastr.error("Please accept our Cancellation Policy!", 'error!')
                    return false;
                }

                //gloves field
                if($("#field11").val().trim()==""){
                    toastr.error("Please answer: Do you request that gloves be worn during your sessions?", 'error!')
                    return false;
                }

                if($("#field12").val().trim()==""){
                    toastr.error("Please answer: Do you request that a mask be worn during your sessions?", 'error!')
                    return false;
                }


                var postData = {
                    selectedDay: selectedDay,
                    selectedTime: $('#avaliableTimes').val(),
                    email: $('#email').val(),
                  //  username: $('#username').val(),
                    firstName: $('#firstName').val(),
                    lastName: $('#lastName').val(),
                    phone: $('#phone').val(),
                    eventId: $("#eventId").val(),
                    formData: $("#addiTionalFieldsForm").serialize()
                    //     countRepeat: $('#countRepeat').val()
                };
                $("#loader").show();
                $.post("startBooking", postData).done(function (data) {
                    console.log(data);
                    $("#loader").hide();
                    if (data.error === true) {
                        //  alert(data.msg);
                        toastr.error(data.msg, 'error!')
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
                                <div id="sb_client_info" class="nav-item">
                                    <div class="login-container">
                                        <button class="avatar item-container" id="sb_client_info"><img
                                                src="/img/user-default-image.png"
                                                alt="User profile menu item icon"></button>
                                        <div class="full-info">
                                            <div class="tab-pd">
                                                <div id="sb_login_form">
                                                    <div class="main-form">

                                                        <div class="not-logged">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div id="sb_sign_in_form">
                                                                        <div class="inputs">
                                                                            <div class="email ">
                                                                                <input type="email" class="form-control"
                                                                                       id="sb_sign_in_email"
                                                                                       name="email" placeholder="Email">
                                                                                <p class="help-block"></p>
                                                                            </div>
                                                                            <div class="password ">
                                                                                <input type="password"
                                                                                       class="form-control"
                                                                                       id="sb_sign_in_password"
                                                                                       name="password"
                                                                                       placeholder="Password"
                                                                                       style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAYAAABSO15qAAAAAXNSR0IArs4c6QAAAXtJREFUOBGVUj2LwkAUnIjBICIIKe8gWKRKo2BvYXMgWNlYWZ3gn1B/htekibWVcH1aIVV+wQULCxsRtMrtrGYv8RLUB8nuvjczu+9DWywWH3EcL8X3jidM07QfAfucz+ffhJdeIZNwu+iLexoFnrr5Cr/+05xSOvBoX61WYdt2BvaSgGVZ6PV6+QKGYahApVKBKJY6p2PKeduUufb7fbTbbaxWKxwOB0ynU+x2O7ium4ndk3l+KYU8AW02m8UM8Jnn81limMLlcsnDK59IMRKHiXpBQibiEZkY0co3sSxlDegoMsdx0O12Ua/XEUUR1us1jsejhFNEvaBIgK07nU4IwxDNZhOdTicDLXO205OViYrDZrORLg5Qq9VSdUpwJSEwoUjiuF+FOEzTxGAwwH6/x3a7zUD+piXjBpLukDwej2XenufJdNLQhzUYjUao1WpoNBpywIbDYZqPwi6wz6xyEATQdV2ROKmJEVMoIECszdL3ffb7n5EsnJNf8S6HAZZBgLIAAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;"
                                                                                       autocomplete="off">
                                                                                <span class="password-toggler"
                                                                                      tabindex="0"><i
                                                                                        class="fa fa-eye"></i></span>
                                                                                <p class="help-block"></p>
                                                                            </div>
                                                                        </div>
                                                                        <div class="bar">
                                                                            <div class="txt">
                                                                                <span
                                                                                    class="remind-pass sb-client-remind-popup">Remind password</span>
                                                                                <div class="btn-bar--row">
                                                                                    <button type="button"
                                                                                            class="btn btn--sign-in custom popup-hide"
                                                                                            id="sb_sign_in_btn">Sign in
                                                                                    </button>
                                                                                    <a type="button"
                                                                                       href="#client/sign-in"
                                                                                       class="btn btn--sign-up custom popup-hide"
                                                                                       id="sign_up_btn">Sign up</a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="is-logged" style="display: none">
                                                            <div class="cap">
                                                                You are logged in as: <b id="loginName">Рома Говтвян</b>
                                                                <input type="hidden" id="loginedUserId">
                                                                <input type="hidden" id="loginedUserHash">

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
                                            <a class="popup-hide" href="/">Home</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#" onclick="changePage('Reviews')">Reviews</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#"  onclick="changePage('Contact Us')">Contact Us</a>
                                        </li>
                                        <li class="menu-item clearfix ">
                                            <a class="popup-hide" href="#" onclick="changePage('My Bookings')">My Bookings</a>
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

                                <!--    <div id="time-settings">
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
!-->
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

                                                                                            <div id="formBlock">
                                                                                                <div>

                    <div       class="form-group">
                        <label  class=" control-label  required"
                            for="firstName">First name</label>
                        <input
                            type="text"
                            name="firstName"
                            class="form-control"
                            id="firstName"
                            autocomplete="name given-name"
                            placeholder="Enter first name">
                    </div>
                    <div       class="form-group">
                        <label  class=" control-label  required"
                            for="lastName">Last name</label>
                        <input
                            type="text"
                            name="lastName"
                            class="form-control"
                            id="lastName"
                            autocomplete=" family-name"
                            placeholder="Enter last name">
                    </div>





                                                                                                    <div
                                                                                                        class="form-group">
                                                                                                        <label  class=" control-label  required"
                                                                                                            for="email">Email
                                                                                                            address</label>
                                                                                                        <input
                                                                                                            type="email"
                                                                                                            name="email"
                                                                                                            class="form-control"
                                                                                                            id="email"
                                                                                                            autocomplete="email"
                                                                                                            aria-describedby="emailHelp"
                                                                                                            placeholder="Enter email">
                                                                                                    </div>
                                                                                             <!--       <div
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
                                                                                                    </div> -->
                                                                                                    <div
                                                                                                        class="form-group">
                                                                                                        <label class=" control-label  required"
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
                                                                                                    id="addiTionalFieldsForm">
                                                                                                    <div
                                                                                                        id="additionalFields"></div>
                                                                                                </form>


                                                                                                <div class="form-group">
                                                                                                    <label class="control-label required" for="acceptTOS" role="button" >I agree to the following terms:</label>
                                                                                                    <div class="custom-checkbox">
                                                                                                        <input type="checkbox" name="acceptTOS" id="acceptTOS" value="1">
                                                                                                        <div class="custom-label"></div>

                                                                                                    </div>
                                                                                                    <div><p style="
    font-weight: bold;
    margin-top: 20px;
    margin-bottom: 15px;
"
                                                                                                        >Cancellation Policy:</p>
                                                                                                        <p>All cancellations must be processed at least 24 hours in advance in order to receive a full refund. Any cancellations within 24 hours of the appointment will not warrant any full or partial refund.</p></div>
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
                                                                                                                    <?php echo date("d.m.Y");?>
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

                        <section id="reviews-view" style="display: none">
                            <div class="page-pd">
                                <div class="container-fluid column">
                                    <div class="row">
                                        <div class="reviews-toggle">
                                            <a class="reviews-toggle__link active" data-block="reviews">Reviews</a>
                                            <a class="reviews-toggle__link" data-block="leave_review">Leave a review</a>
                                        </div>
                                        <div class="col-sm-12 reviews-view-tab" id="leave_review">
                                            <div class="row">
                                                <div id="sb_reviews_add_container">
                                                    <div>
                                                        <div class="title-main">Leave a review</div>
                                                        <div class="add-review">
                                                            <div class="avatar">
                                                                <div class="photo">
                                                                    <img
                                                                        src="/v2/themes/assets/img/user-default-image.png"
                                                                        align="" alt="User empty image">
                                                                </div>
                                                                <div class="info">
                                                                </div>
                                                                <div class="btn-bar">
                                                                </div>
                                                            </div>
                                                            <div class="form">

                                                                <div class="form-group">
                                                                    <div class="form-row required">
                                                                        <input id="feedback__subject" value=""
                                                                               name="subject" placeholder="Review Title"
                                                                               type="text">
                                                                        <p class="help-block"></p>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group">
                                                                    <div class="form-row required">
                                                                        <textarea id="feedback__message" name="message"
                                                                                  placeholder="Message"></textarea>
                                                                        <p class="help-block"></p>
                                                                    </div>
                                                                </div>

                                                                <div class="stars-container">
                                                                    <div class="rating-container">
                                                                        <form>
                                                                            <input type="radio" name="rate"
                                                                                   id="group-1-0" value="5"
                                                                                   checked="checked">
                                                                            <label for="group-1-0"></label>

                                                                            <input type="radio" name="rate"
                                                                                   id="group-1-1" value="4">
                                                                            <label for="group-1-1"></label>

                                                                            <input type="radio" name="rate"
                                                                                   id="group-1-2" value="3">
                                                                            <label for="group-1-2"></label>

                                                                            <input type="radio" name="rate"
                                                                                   id="group-1-3" value="2">
                                                                            <label for="group-1-3"></label>

                                                                            <input type="radio" name="rate"
                                                                                   id="group-1-4" value="1">
                                                                            <label for="group-1-4"></label>
                                                                        </form>
                                                                    </div>
                                                                    <p class="help-block"></p>
                                                                </div>

                                                                <div class="social-container">
                                                                    <div class="cap">Please log in to leave a review
                                                                    </div>
                                                                    <div class="line-arrow line-arrow-top"></div>
                                                                    <div class="buttons brand">
                                                                        <a href="/v2/review/login/provider/facebook"
                                                                           target="_blank" class="fb">

                                                                        </a>
                                                                        <a href="/v2/review/login/provider/google"
                                                                           target="_blank" class="gl">

                                                                        </a>
                                                                        <a href="/v2/review/login/provider/twitter"
                                                                           target="_blank" class="tw">

                                                                        </a>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 reviews-view-tab active" id="reviews">
                                            <div class="row">
                                                <div id="sb_reviews_list_container">
                                                    <div>
                                                        <div class="title-main">Reviews</div>

                                                        <div id="sb_reviews_list_items_container">

                                                        </div>

                                                        <div id="sb_reviews_page_pagination">

                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="section-divider"></div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section id="contactUs" style="display: none">
                            <div class="page-pd">
                                <div class="sb-widget-form">
                                    <form>
                                        <div class="title">
                                            You can book an appointment or leave us a message and we will contact you
                                            back
                                        </div>
                                        <ul class="form-fields form-horizontal custom-form">
                                            <li>
                                                <div class="form-group">
                                                    <label for="name" class="col-sm-12 control-label">
                                                        Your name
                                                    </label>
                                                    <div class="col-sm-12">
                                                        <input type="text" class="form-control" value=""
                                                               id="contact_widget__name" name="contact_widget__name"
                                                               placeholder="Your name"
                                                               style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAASCAYAAABSO15qAAAAAXNSR0IArs4c6QAAAXtJREFUOBGVUj2LwkAUnIjBICIIKe8gWKRKo2BvYXMgWNlYWZ3gn1B/htekibWVcH1aIVV+wQULCxsRtMrtrGYv8RLUB8nuvjczu+9DWywWH3EcL8X3jidM07QfAfucz+ffhJdeIZNwu+iLexoFnrr5Cr/+05xSOvBoX61WYdt2BvaSgGVZ6PV6+QKGYahApVKBKJY6p2PKeduUufb7fbTbbaxWKxwOB0ynU+x2O7ium4ndk3l+KYU8AW02m8UM8Jnn81limMLlcsnDK59IMRKHiXpBQibiEZkY0co3sSxlDegoMsdx0O12Ua/XEUUR1us1jsejhFNEvaBIgK07nU4IwxDNZhOdTicDLXO205OViYrDZrORLg5Qq9VSdUpwJSEwoUjiuF+FOEzTxGAwwH6/x3a7zUD+piXjBpLukDwej2XenufJdNLQhzUYjUao1WpoNBpywIbDYZqPwi6wz6xyEATQdV2ROKmJEVMoIECszdL3ffb7n5EsnJNf8S6HAZZBgLIAAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%;"
                                                               autocomplete="off">
                                                        <p class="help-block"></p>
                                                    </div>
                                                </div>
                                            </li>

                                            <li>
                                                <div class="form-group">
                                                    <label for="email" class="col-sm-12 control-label">
                                                        E-mail
                                                    </label>
                                                    <div class="col-sm-12">
                                                        <input type="text" class="form-control" value=""
                                                               id="contact_widget__email" name="contact_widget__email"
                                                               placeholder="E-mail">
                                                        <p class="help-block"></p>
                                                    </div>
                                                </div>
                                            </li>

                                            <li>
                                                <div class="form-group ">
                                                    <label for="phone" class="col-sm-12 control-label">
                                                        Contact phone
                                                    </label>
                                                    <div class="col-sm-12">
                                                        <input type="text" class="form-control" value=""
                                                               id="contact_widget__phone" name="contact_widget__phone"
                                                               placeholder="Contact phone">
                                                        <p class="help-block"></p>
                                                    </div>
                                                </div>
                                            </li>

                                            <li>
                                                <div class="form-group ">
                                                    <label for="message" class="col-sm-12 control-label">
                                                        Message
                                                    </label>
                                                    <div class="col-sm-12">
                                                        <textarea class="form-control" id="contact_widget__message"
                                                                  name="contact_widget__message" placeholder="Message"
                                                                  rows="6"></textarea>
                                                        <p class="help-block"></p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="buttons">
                                                    <a class="open-booking-widget-button" href="#book">
                                                        Make an appointment
                                                    </a>
                                                    <input type="submit" class="send-message-button btn blue"
                                                           value="Send message">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </section>

                        <section id="myBookings" style="display: none">
                            <div>
                                <div id="booking-result-view">
                                    <div id="booking-result-tabs">
                                        <div class="container-fluid column">
                                            <div class="tabs-container">
                                                <div class="tab-link active">
                                                    <a href="#client/bookings/type/upcoming">upcoming bookings</a>
                                                </div>
                                                <div class="tab-link">
                                                    <a href="#client/bookings/type/all">all bookings</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="container-fluid column">
                                        <div class="row">
                                            <div class="section-pd">
                                                <div id="sb_message_container"></div>
                                                <div id="sb_push_notification_container"></div>
                                                <div id="sb_back_to_bookings">
                                                    <a href="#book" class="back-to-services">
                                                        <span class="fa fa-angle-left"></span>
                                                        <span>
                            Back to services
                        </span>
                                                    </a>
                                                </div>
                                                <div id="sb_bookings_list">
                                                    <div>
                                                        <div class="alert alert-info alert-dismissible" role="alert">
                                                            There are no appointments yet. Press the "Book Now" button
                                                            to make an appointment.
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="clearfix"></div>


                                            </div>
                                            <div id="sb_back_btns_plugin"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </section>


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
