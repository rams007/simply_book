<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link href='bootstrap/css/bootstrap.css' rel='stylesheet'/>
    <script src='bootstrap/js/bootstrap.min.js'></script>
    <script src='lib/main.js'></script>


    <style>


        @font-face {
            font-family: fcicons;
            src: url("data:application/x-font-ttf;charset=utf-8;base64,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") format('truetype');
            font-weight: 400;
            font-style: normal
        }


        @import url('https://fonts.googleapis.com/css2?family=Petit+Formal+Script&display=swap');

        * {
            font-family: 'Petit Formal Script', cursive;
        }
        .day-off {
            opacity: .4;
            color: #81889a;
        }

        .fc-day-today .fc-daygrid-day-top {
            background-color: hsl(310, 93%, 88%);
            border-radius: 50%;
        }

        td.fc-day {
            min-width: 30px;
            line-height: 30px;
            font-size: 14px;
            text-align: center;
        }

        body {
            margin: 40px 10px;
            padding: 0;
            font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
            font-size: 14px;
        }

        #calendar {
            max-width: 1100px;
            margin: 0 auto;
        }

        table.fc-col-header {
            width: 100% !important;
        }

        table.fc-scrollgrid, table.fc-scrollgrid-sync-table {
            width: 100% !important;
        }

        .fc-daygrid-day-top {
            width: 30px;
            margin: auto;
        }


        .section {
            border-radius: 5px;
            box-shadow: 0 0 16px 5px rgb(0 0 0 / 5%);
            width: 500px;
            margin: auto;
            padding: 40px;
        }

        .fc-toolbar-title {
            text-align: center;
            font-size: 16px;
        }

        .fc-header-toolbar {
            display: flex;
            justify-content: center;
            align-items: baseline;

        }

        a.fc-col-header-cell-cushion, a.fc-daygrid-day-number {
            color: black;
        }

        .fc-button-primary{
            padding-left: 15px;
            padding-right: 15px;
            background-color: transparent;
            border: none;
        }

        .fc-myCustomNextButton-button:before{
            font-family: fcicons;
            content: "\e901";
        }

        .fc-myCustomPrevButton-button:before{
            font-family: fcicons;
            content: "\e900";
        }

    </style>


    <script>

        var allowedDates = ["2021-04-23", "2021-04-25"];
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
                            calendar.next();
                        }
                    },
                    myCustomPrevButton: {
                        text: '',
                        click: function () {
                            calendar.prev();
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

<div class="section">
    <div id='calendar'></div>
</div>


<!--
<div id="content">
    <div class="row">
    <div class="col-sm-6"><button id="prev"> Prev</button></div>
        <div class="col-sm-6"><button id="next"> Next</button></div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div id='calendar'></div>

        </div>




    </div>



</div>
-->

<style>
    /*  #calendar{
          max-width: 80%;
          margin: auto;
      }

      .fc-other-month{
          color:white;
          background-color: white;

      }


      .day-off{

          opacity: .4;
          color: #81889a;
      }
      */
</style>


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


                <button id="StartBooking" type="button" class="btn btn-primary">select</button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
            </div>
        </div>
    </div>


</div>


</body>
</html>
