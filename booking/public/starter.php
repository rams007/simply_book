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


    <script>

        function showCalendar(eventId) {
            console.log(eventId);
            $("#loader").show();
            $.get("initCalendar?eventId=" + eventId, function (data) {
                console.log(data);
                $("#loader").hide();
                data.avaliableDates.forEach(curDate => {

                    if (allowedDates.indexOf(curDate) === -1) {
                        allowedDates.push(curDate);

                    }

                });
                allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                console.log(allowedTime);
                $("#eventLists").hide();
                $("#calendarBlock").show();
                LoadCalendar();
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

                        $('#myModal').modal();
                        selectedDay = arg.startStr;
                        var allowedTimeForDay = allowedTime[selectedDay];
                        $('#avaliableTimes').html('');
                        if (allowedTimeForDay !== undefined) {
                            allowedTimeForDay.forEach(time => {

                                $('#avaliableTimes')
                                    .append($("<option></option>")
                                        .attr("value", time)
                                        .text(time));


                            })


                        }
                    }
                    calendar.unselect()
                },

                dayCellClassNames: function (arg) {
                    console.log(arg)


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
                    console.log(arg)
                },
                dayCellDidMount: function (arg) {
                    console.log(arg)
                },
                dayCellWillUnmount: function (arg) {
                    console.log(arg)
                },
            });

            calendar.render();


        }


        function FirstCalendar() {
            LoadCalendar();
        }

        document.addEventListener('DOMContentLoaded', FirstCalendar);


        $(document).ready(function () {


            $('#StartBooking').click(function () {

                var postData = {
                    selectedDay: selectedDay,
                    selectedTime: $('#avaliableTimes').val()
                    //     countRepeat: $('#countRepeat').val()
                };
                $.post("handler.php?action=startBooking", postData).done(function (data) {
                    console.log(data);
                });


            })

        });


    </script>


    <script>

        /*
                var allowedDates = ["2021-04-23", "2021-04-25"];
                var calendar = null;
                var allowedTime = {};
                var selectedDay = null;

                function LoadCalendar() {

                    if (calendar != null) {
                        document.getElementById("calendar").innerHTML = "";
                    }

                    var calendarEl = document.getElementById('calendar');
                    calendar = new FullCalendar.Calendar(calendarEl, {
                        plugins: ['interaction', 'dayGrid'],
                        header: {
                            left: 'today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek'
                        },
                        selectable: true,
                        selectMirror: true,
                        datesSet: function (arg) {
                            console.log(arg);
                        },
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

                                $('#myModal').modal();
                                selectedDay = arg.startStr;
                                var allowedTimeForDay = allowedTime[selectedDay];
                                $('#avaliableTimes').html('');
                                if (allowedTimeForDay !== undefined) {
                                    allowedTimeForDay.forEach(time => {

                                        $('#avaliableTimes')
                                            .append($("<option></option>")
                                                .attr("value", time)
                                                .text(time));


                                    })


                                }
                            }
                            calendar.unselect()
                        },
                        dayRender: function (date) {
                            var founded = false;
                            allowedDates.forEach(dateInList => {
                                var convertedDate = new Date(dateInList);
                                var convertedTime = convertedDate.getTime() + convertedDate.getTimezoneOffset() * 60 * 1000
                                if (convertedTime == date.date.getTime()) {
                                    console.log('can click');
                                    founded = true;
                                }
                            });
                            console.log(date);
                            if (founded) {
                                date.el.style.cssText = "background-color:#d5d5d5";
                                date.el.classList.add("day-on");
                            } else {
                               date.el.style.cssText = "opacity: .4;color: #81889a;";
                                date.el.classList.add("day-off");
                            }
                        }
                    });
                    calendar.render();
                }


                function FirstCalendar() {
                    LoadCalendar();
                }

                document.addEventListener('DOMContentLoaded', FirstCalendar);


                $(document).ready(function () {

                    $.get("handler.php?action=init", function (data) {
                        console.log(data);
                        data.avaliableDates.forEach(curDate => {

                            if (allowedDates.indexOf(curDate) === -1) {
                                allowedDates.push(curDate);

                            }

                        });

                        // data.avaliableTimes.forEach(curElement=>{
        //console.log(curElement);
                        //   allowedTime[]
                        allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                        console.log(allowedTime);

                        //    });

                        //    allowedDates = data.avaliableDates;
                        LoadCalendar();
                    });

                    $("#next").click(function () {

                        console.log(calendar.getDate());

                        var calendarTime = calendar.getDate().getTime() - calendar.getDate().getTimezoneOffset() * 60 * 1000;
                        $.get("handler.php?action=next&date=" + calendarTime, function (data) {
                            console.log(data);
                            console.log(data);
                            data.avaliableDates.forEach(curDate => {

                                if (allowedDates.indexOf(curDate) === -1) {
                                    allowedDates.push(curDate);
                                }

                            });
                            allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                            console.log(allowedTime);
                            calendar.next();
                        });
                    })

                    $("#prev").click(function () {

                        console.log(calendar.getDate());
                        $.get("handler.php?action=prev&date=" + calendar.getDate().getTime(), function (data) {
                            console.log(data);
                            console.log(data);
                            data.avaliableDates.forEach(curDate => {
                                if (allowedDates.indexOf(curDate) === -1) {
                                    allowedDates.push(curDate);
                                }
                            });
                            allowedTime = Object.assign(allowedTime, data.avaliableTimes);
                            console.log(allowedTime);
                            calendar.prev();
                        });
                    })


                    $('#StartBooking').click(function () {

                        var postData = {
                            selectedDay: selectedDay,
                            selectedTime: $('#avaliableTimes').val(),
                            countRepeat: $('#countRepeat').val()
                        };
                        $.post("handler.php?action=startBooking", postData).done(function (data) {
                            console.log(data);
                        });


                    })

                });

        */
    </script>
</head>
<body>

<div class="wrapper">

<div class="row">
    <div class="col-sm-12">

        <div id="eventLists" class="section" style="    margin-bottom: 40px;">
            <div class="row">
                <div id="sb_booking_content">
                    <div>
                        <div class="service-step step-content content-mode-list" id="sb_service_step_container">

                            <?php foreach ($services as $service) {
                                if ($service->id == EXTENDED_SERVICE_ID) {
                                    continue;
                                }

                                ?>

                                <div class="service-item item panel">

                                    <div class="mobile-title">
                                        <h4 class="title">
                                            <a role="button" data-toggle="collapse" data-parent="#sb_service_step_container"
                                               href="#service1" aria-expanded="false" aria-controls="service1"
                                               class="collapsed">
                                                <?php echo $service->name; ?>
                                            </a>
                                        </h4>
                                    </div>


                                    <div class="one-line no-image">
                                        <div class="content">
                                            <h4 class="cap title">
                                                <a role="button" data-toggle="collapse" data-parent="#sb_service_step_container"
                                                   href="#service1" aria-expanded="false" aria-controls="service1"
                                                   class="collapsed">
                                                    <?php echo $service->name; ?>
                                                </a>
                                            </h4>

                                            <div class="info-bar bar-service">
                                                <div class="d-flex">

                                                    <div class="bar-flex-item price price">
                                                        <i class="fal fa-wallet ico"></i>
                                                        <span class="txt">$ <?php echo $service->price_with_tax; ?></span>
                                                    </div>

                                                    <div class="bar-flex-item sb_group_booking_count"></div>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="btn-bar has-read-more">
                                            <div class="btn-round-mask">
                                                <a class="btn select custom"
                                                        onClick="showCalendar(<?php echo $service->id; ?>)">Select
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
            </div>
        </div>



    </div>
</div>
    <div class="row">
        <div class="col-sm-12">

<div id="calendarBlock" style="display: none" class="section">
    <div id='calendar'></div>
</div>

        </div>
    </div>


    <div class="row">
        <div class="col-sm-12">
<div id="calendarBlock" style=" margin-top: 40px; padding:40px" class="section">
    <div class="row">

        <form>
            <div class="form-group">
                <label for="exampleInputEmail1">Email address</label>
                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
            </div>
            <div class="form-group">
                <label for="username">User Name</label>
                <input type="text" class="form-control" id="username" placeholder="User name">
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" placeholder="Phone">
            </div>



            <button type="submit" class="btn btn-primary">Submit</button>
        </form>



    </div>
</div>
        </div>
    </div>

</div>


<div class="modal" tabindex="-1" id="myModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Please select time</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <div class="form-group">
                    <label for="avaliableTimes">Avaliable times</label>
                    <select class="form-control" id="avaliableTimes"></select>
                </div>
                <!--
                                <div class="form-group">
                                    <label for="countRepeat">Count repeat</label>
                                    <select class="form-control" id="countRepeat">
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="11">11</option>
                                        <option value="12">12</option>
                                        <option value="13">13</option>
                                        <option value="14">14</option>
                                        <option value="15">15</option>
                                        <option value="16">16</option>
                                        <option value="17">17</option>
                                        <option value="18">18</option>
                                        <option value="19">19</option>
                                        <option value="20">20</option>

                                    </select>
                                </div>
                -->

                <button id="StartBooking" type="button" class="btn btn-primary">select</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <!--  <button type="button" class="btn btn-primary">Save changes</button>  -->
            </div>
        </div>
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
