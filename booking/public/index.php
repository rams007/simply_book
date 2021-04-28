<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
            integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <link href='packages/core/main.css' rel='stylesheet'/>
    <link href='packages/daygrid/main.css' rel='stylesheet'/>
    <link href='packages/timegrid/main.css' rel='stylesheet'/>
    <link href='bootstrap/css/bootstrap.css' rel='stylesheet'/>
    <script src='packages/core/main.js'></script>
    <script src='packages/interaction/main.js'></script>
    <script src='packages/daygrid/main.js'></script>
    <script src='packages/timegrid/main.js'></script>
    <script src='bootstrap/js/bootstrap.min.js'></script>
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


                        //    var modalEl =  document.getElementById('myModal');
                        //           var myModal = new bootstrap.Modal(modalEl, {})

                        /*
                        console.log(arg.start);
                        var title = prompt('Event Title:');
                        if (title) {
                            calendar.addEvent({
                                title: title,
                                start: arg.start,
                                end: arg.end,
                                allDay: arg.allDay
                            })
                        }
                        */
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
            //    MesEvents = "$events"; // Ajax script is executed and give $events
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
</head>
<body>

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


<style>
    #calendar{
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
