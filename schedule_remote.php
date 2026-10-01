<?php

/* =========================================================
   ESP-SWITCH7
   schedule.php

   DATABASE:
       esp_switch7

   TABLE:
       weekly_schedule

   DATETIME ORDER:

       Period 1 Start < Period 1 End
       Period 1 End   <= Period 2 Start
       Period 2 Start < Period 2 End
       Period 2 End   <= Period 3 Start
       Period 3 Start < Period 3 End
   ========================================================= */


/* =========================================================
   DATABASE CONNECTION
   Render + TiDB Cloud
   ========================================================= */

$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port = intval(getenv("DB_PORT"));

if (
    empty($host) ||
    empty($user) ||
    empty($database) ||
    $port <= 0
) {
    die("Database environment variables are not configured.");
}

$conn = mysqli_init();

/*
   TiDB Cloud connection uses TLS.
*/
mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);

if (!mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    $port,
    NULL,
    MYSQLI_CLIENT_SSL
)) {
    die(
        "Database connection failed: "
        . mysqli_connect_error()
    );
}

mysqli_set_charset($conn, "utf8mb4");

/* INDIA TIME */
date_default_timezone_set("Asia/Kolkata");



/* =========================================================
   CONTROLLER
   ========================================================= */

$controller_id = isset($_POST["controller_id"])
    ? trim($_POST["controller_id"])
    : (
        isset($_GET["controller_id"])
        ? trim($_GET["controller_id"])
        : "ESP0001"
    );

if ($controller_id === "") {
    $controller_id = "ESP0001";
}

$controller_sql =
    mysqli_real_escape_string(
        $conn,
        $controller_id
    );

/* =========================================================
   2. FUNCTION
      COMBINE DATE + TIME
   ========================================================= */

function make_datetime($date, $time)
{

    if (
        empty($date) ||
        empty($time)
    ) {

        return NULL;

    }

    return $date . " " . $time . ":00";

}


/* =========================================================
   3. FUNCTION
      ESCAPE DATABASE VALUE
   ========================================================= */

function sql_datetime($conn, $datetime)
{

    if ($datetime === NULL) {

        return "NULL";

    }

    return "'" .
        mysqli_real_escape_string(
            $conn,
            $datetime
        )
        . "'";

}


/* =========================================================
   4. SAVE SCHEDULE
   ========================================================= */

if (isset($_POST['save_schedule'])) {


    /* -----------------------------------------------------
       ID
       ----------------------------------------------------- */

    $id = intval($_POST['id']);


    /* =====================================================
       PERIOD 1
       ===================================================== */

    $start_date_1 =
        isset($_POST['start_date_1'])
        ? $_POST['start_date_1']
        : "";

    $start_time_1 =
        isset($_POST['start_time_1'])
        ? $_POST['start_time_1']
        : "";

    $end_date_1 =
        isset($_POST['end_date_1'])
        ? $_POST['end_date_1']
        : "";

    $end_time_1 =
        isset($_POST['end_time_1'])
        ? $_POST['end_time_1']
        : "";


    $start_datetime_1 =
        make_datetime(
            $start_date_1,
            $start_time_1
        );


    $end_datetime_1 =
        make_datetime(
            $end_date_1,
            $end_time_1
        );


    /* -----------------------------------------------------
       PERIOD 1 PINS
       ----------------------------------------------------- */

    if (
        isset($_POST['pins_output_1']) &&
        is_array($_POST['pins_output_1'])
    ) {

        $pins_output_1 =
            implode(
                ",",
                $_POST['pins_output_1']
            );

    } else {

        $pins_output_1 = "";

    }


    /* -----------------------------------------------------
       PERIOD 1 ACTIVE
       ----------------------------------------------------- */

    if (
        isset($_POST['period_active_1'])
    ) {

        $period_active_1 =
            intval($_POST['period_active_1']);

    } else {

        $period_active_1 = 0;

    }


    /* =====================================================
       PERIOD 2
       ===================================================== */

    $start_date_2 =
        isset($_POST['start_date_2'])
        ? $_POST['start_date_2']
        : "";

    $start_time_2 =
        isset($_POST['start_time_2'])
        ? $_POST['start_time_2']
        : "";

    $end_date_2 =
        isset($_POST['end_date_2'])
        ? $_POST['end_date_2']
        : "";

    $end_time_2 =
        isset($_POST['end_time_2'])
        ? $_POST['end_time_2']
        : "";


    $start_datetime_2 =
        make_datetime(
            $start_date_2,
            $start_time_2
        );


    $end_datetime_2 =
        make_datetime(
            $end_date_2,
            $end_time_2
        );


    /* -----------------------------------------------------
       PERIOD 2 PINS
       ----------------------------------------------------- */

    if (
        isset($_POST['pins_output_2']) &&
        is_array($_POST['pins_output_2'])
    ) {

        $pins_output_2 =
            implode(
                ",",
                $_POST['pins_output_2']
            );

    } else {

        $pins_output_2 = "";

    }


    /* -----------------------------------------------------
       PERIOD 2 ACTIVE
       ----------------------------------------------------- */

    if (
        isset($_POST['period_active_2'])
    ) {

        $period_active_2 =
            intval($_POST['period_active_2']);

    } else {

        $period_active_2 = 0;

    }


    /* =====================================================
       PERIOD 3
       ===================================================== */

    $start_date_3 =
        isset($_POST['start_date_3'])
        ? $_POST['start_date_3']
        : "";

    $start_time_3 =
        isset($_POST['start_time_3'])
        ? $_POST['start_time_3']
        : "";

    $end_date_3 =
        isset($_POST['end_date_3'])
        ? $_POST['end_date_3']
        : "";

    $end_time_3 =
        isset($_POST['end_time_3'])
        ? $_POST['end_time_3']
        : "";


    $start_datetime_3 =
        make_datetime(
            $start_date_3,
            $start_time_3
        );


    $end_datetime_3 =
        make_datetime(
            $end_date_3,
            $end_time_3
        );


    /* -----------------------------------------------------
       PERIOD 3 PINS
       ----------------------------------------------------- */

    if (
        isset($_POST['pins_output_3']) &&
        is_array($_POST['pins_output_3'])
    ) {

        $pins_output_3 =
            implode(
                ",",
                $_POST['pins_output_3']
            );

    } else {

        $pins_output_3 = "";

    }


    /* -----------------------------------------------------
       PERIOD 3 ACTIVE
       ----------------------------------------------------- */

    if (
        isset($_POST['period_active_3'])
    ) {

        $period_active_3 =
            intval($_POST['period_active_3']);

    } else {

        $period_active_3 = 0;

    }


    /* =====================================================
       DATETIME VALIDATION
       ===================================================== */

    $error = "";


    /* -----------------------------------------------------
       PERIOD 1 START MUST BE BEFORE PERIOD 1 END
       ----------------------------------------------------- */

    if (
        $start_datetime_1 !== NULL &&
        $end_datetime_1 !== NULL
    ) {

        if (
            strtotime($start_datetime_1) >=
            strtotime($end_datetime_1)
        ) {

            $error =
                "Period 1 error: "
                . "Start Date/Time must be earlier than "
                . "End Date/Time.";

        }

    }


    /* -----------------------------------------------------
       PERIOD 2 START MUST BE BEFORE PERIOD 2 END
       ----------------------------------------------------- */

    if (
        $error == "" &&
        $start_datetime_2 !== NULL &&
        $end_datetime_2 !== NULL
    ) {

        if (
            strtotime($start_datetime_2) >=
            strtotime($end_datetime_2)
        ) {

            $error =
                "Period 2 error: "
                . "Start Date/Time must be earlier than "
                . "End Date/Time.";

        }

    }


    /* -----------------------------------------------------
       PERIOD 3 START MUST BE BEFORE PERIOD 3 END
       ----------------------------------------------------- */

    if (
        $error == "" &&
        $start_datetime_3 !== NULL &&
        $end_datetime_3 !== NULL
    ) {

        if (
            strtotime($start_datetime_3) >=
            strtotime($end_datetime_3)
        ) {

            $error =
                "Period 3 error: "
                . "Start Date/Time must be earlier than "
                . "End Date/Time.";

        }

    }


    /* -----------------------------------------------------
       PERIOD 1 MUST FINISH BEFORE PERIOD 2 STARTS
       ----------------------------------------------------- */

    if (
        $error == "" &&
        $end_datetime_1 !== NULL &&
        $start_datetime_2 !== NULL
    ) {

        if (
            strtotime($end_datetime_1) >
            strtotime($start_datetime_2)
        ) {

            $error =
                "Schedule order error: "
                . "Period 1 must finish before Period 2 starts.";

        }

    }


    /* -----------------------------------------------------
       PERIOD 2 MUST FINISH BEFORE PERIOD 3 STARTS
       ----------------------------------------------------- */

    if (
        $error == "" &&
        $end_datetime_2 !== NULL &&
        $start_datetime_3 !== NULL
    ) {

        if (
            strtotime($end_datetime_2) >
            strtotime($start_datetime_3)
        ) {

            $error =
                "Schedule order error: "
                . "Period 2 must finish before Period 3 starts.";

        }

    }


    /* =====================================================
       ONLY SAVE IF THERE IS NO ERROR
       ===================================================== */

    if ($error == "") {


        /* =================================================
           CONVERT DATETIME VALUES FOR SQL
           ================================================= */

        $sql_start_1 =
            sql_datetime(
                $conn,
                $start_datetime_1
            );

        $sql_end_1 =
            sql_datetime(
                $conn,
                $end_datetime_1
            );


        $sql_start_2 =
            sql_datetime(
                $conn,
                $start_datetime_2
            );

        $sql_end_2 =
            sql_datetime(
                $conn,
                $end_datetime_2
            );


        $sql_start_3 =
            sql_datetime(
                $conn,
                $start_datetime_3
            );

        $sql_end_3 =
            sql_datetime(
                $conn,
                $end_datetime_3
            );


        /* =================================================
           UPDATE DATABASE
           ================================================= */

        $sql = "

            UPDATE weekly_schedule

            SET

                start_time_1 = $sql_start_1,
                end_time_1 = $sql_end_1,
                pins_output_1 = '" .
                mysqli_real_escape_string(
                    $conn,
                    $pins_output_1
                )
                . "',
                period_active_1 = $period_active_1,


                start_time_2 = $sql_start_2,
                end_time_2 = $sql_end_2,
                pins_output_2 = '" .
                mysqli_real_escape_string(
                    $conn,
                    $pins_output_2
                )
                . "',
                period_active_2 = $period_active_2,


                start_time_3 = $sql_start_3,
                end_time_3 = $sql_end_3,
                pins_output_3 = '" .
                mysqli_real_escape_string(
                    $conn,
                    $pins_output_3
                )
                . "',
                period_active_3 = $period_active_3

            WHERE id = $id
              AND controller_id = '$controller_sql'

        ";


        $save_result =
            mysqli_query(
                $conn,
                $sql
            );


        /* DATABASE ERROR */

        if (!$save_result) {

            die(
                "Schedule update failed:<br><br>"
                . mysqli_error($conn)
                . "<br><br>"
                . "SQL:<br>"
                . htmlspecialchars($sql)
            );

        }


        /* SUCCESS */

        $message =
            "Schedule saved successfully.";

    }

}


/* =========================================================
   5. READ ALL DAYS
   ========================================================= */

$sql = "

    SELECT *

    FROM weekly_schedule

    WHERE controller_id = '$controller_sql'

    ORDER BY id

";


$result =
    mysqli_query(
        $conn,
        $sql
    );


if (!$result) {

    die(
        "Unable to read weekly_schedule:<br>"
        . mysqli_error($conn)
    );

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>
ESP-SWITCH7 Weekly Schedule
</title>

<style>

body {

    font-family: Arial, sans-serif;

    background: #f2f2f2;

    margin: 20px;

}


h1 {

    text-align: center;

    color: #333;

}


/* SUCCESS */

.success {

    background: #dff0d8;

    border: 1px solid #82b366;

    color: #256b25;

    padding: 12px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 18px;

    font-weight: bold;

}


/* ERROR */

.error {

    background: #ffdede;

    border: 2px solid #cc0000;

    color: #990000;

    padding: 15px;

    margin-bottom: 20px;

    text-align: center;

    font-size: 18px;

    font-weight: bold;

    border-radius: 5px;

}


/* HELP */

.help {

    background: #fff8dc;

    border: 1px solid #e0c060;

    padding: 15px;

    margin-bottom: 20px;

    font-size: 16px;

    line-height: 1.6;

}


/* DAY BOX */

.day-box {

    background: white;

    border: 1px solid #ccc;

    margin-bottom: 25px;

    padding: 20px;

    border-radius: 8px;

}


/* DAY TITLE */

.day-title {

    font-size: 26px;

    font-weight: bold;

    margin-bottom: 20px;

    color: #0055aa;

}


/* PERIOD */

.period {

    border: 1px solid #ddd;

    padding: 18px;

    margin-bottom: 15px;

    background: #fafafa;

    border-radius: 5px;

}


/* PERIOD TITLE */

.period-title {

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 15px;

    color: #444;

}


/* DATE / TIME */

.datetime-row {

    margin-bottom: 12px;

}


/* LABEL */

.datetime-row label {

    display: inline-block;

    width: 100px;

    font-weight: bold;

}


/* DATE */

input[type="date"] {

    padding: 7px;

    width: 155px;

    font-size: 16px;

}


/* TIME */

input[type="time"] {

    padding: 7px;

    width: 110px;

    font-size: 16px;

}


/* PIN */

.pin-area {

    margin-top: 15px;

}


.pin {

    display: inline-block;

    margin-right: 18px;

    margin-bottom: 10px;

    font-size: 17px;

}


.pin input {

    width: 18px;

    height: 18px;

    vertical-align: middle;

}


/* ACTIVE */

.active-area {

    margin-top: 15px;

    padding: 10px;

    background: #eeeeee;

    border-radius: 5px;

}


.active-area label {

    font-weight: bold;

    font-size: 17px;

}


.active-area input {

    width: 18px;

    height: 18px;

    vertical-align: middle;

}


/* SAVE BUTTON */

.save-button {

    margin-top: 10px;

    padding: 12px 30px;

    background: green;

    color: white;

    border: none;

    border-radius: 5px;

    font-size: 17px;

    cursor: pointer;

}


.save-button:hover {

    background: darkgreen;

}

</style>

</head>

<body>

<h1>
ESP-SWITCH7 Weekly Pin Schedule
</h1>

<?php

/* ERROR MESSAGE */

if (isset($error) && $error != "") {

?>

<div class="error">


<?php

echo htmlspecialchars(
    $error
);

?>


</div>

<?php

}


/* SUCCESS MESSAGE */

if (isset($message)) {

?>

<div class="success">


<?php

echo htmlspecialchars(
    $message
);

?>


</div>

<?php

}

?>

<div class="help">


<b>How to create a schedule</b>

<br><br>

1. Select the required <b>start date</b> from the calendar.

<br>

2. Select the required <b>start time</b>.

<br>

3. Select the required <b>end date</b> from the calendar.

<br>

4. Select the required <b>end time</b>.

<br>

5. Select the required <b>D1-D8 pins</b>.

<br>

6. Select <b>Active</b> if the period should operate.

<br>

7. Leave it unchecked for <b>Deactivated</b>.

<br>

8. Click SAVE.

<br><br>

<b>Period order must be:</b>

<br>

Period 1 → Period 2 → Period 3

<br><br>

<b>Example:</b>

<br>

Period 1:

<b>09:00 → 10:00</b>

<br>

Period 2:

<b>10:00 → 12:00</b>

<br>

Period 3:

<b>14:00 → 16:00</b>

<br><br>

Periods must not overlap.


</div>

<?php


/* =========================================================
   6. DISPLAY EACH DAY
   ========================================================= */

while (
    $row =
    mysqli_fetch_assoc($result)
) {


    /* =====================================================
       PERIOD 1 DISPLAY VALUES
       ===================================================== */

    $start_date_1 = "";
    $start_clock_1 = "";
    $end_date_1 = "";
    $end_clock_1 = "";


    if (!empty($row['start_time_1'])) {

        $start_date_1 =
            date(
                "Y-m-d",
                strtotime(
                    $row['start_time_1']
                )
            );

        $start_clock_1 =
            date(
                "H:i",
                strtotime(
                    $row['start_time_1']
                )
            );

    }


    if (!empty($row['end_time_1'])) {

        $end_date_1 =
            date(
                "Y-m-d",
                strtotime(
                    $row['end_time_1']
                )
            );

        $end_clock_1 =
            date(
                "H:i",
                strtotime(
                    $row['end_time_1']
                )
            );

    }


    /* =====================================================
       PERIOD 2 DISPLAY VALUES
       ===================================================== */

    $start_date_2 = "";
    $start_clock_2 = "";
    $end_date_2 = "";
    $end_clock_2 = "";


    if (!empty($row['start_time_2'])) {

        $start_date_2 =
            date(
                "Y-m-d",
                strtotime(
                    $row['start_time_2']
                )
            );

        $start_clock_2 =
            date(
                "H:i",
                strtotime(
                    $row['start_time_2']
                )
            );

    }


    if (!empty($row['end_time_2'])) {

        $end_date_2 =
            date(
                "Y-m-d",
                strtotime(
                    $row['end_time_2']
                )
            );

        $end_clock_2 =
            date(
                "H:i",
                strtotime(
                    $row['end_time_2']
                )
            );

    }


    /* =====================================================
       PERIOD 3 DISPLAY VALUES
       ===================================================== */

    $start_date_3 = "";
    $start_clock_3 = "";
    $end_date_3 = "";
    $end_clock_3 = "";


    if (!empty($row['start_time_3'])) {

        $start_date_3 =
            date(
                "Y-m-d",
                strtotime(
                    $row['start_time_3']
                )
            );

        $start_clock_3 =
            date(
                "H:i",
                strtotime(
                    $row['start_time_3']
                )
            );

    }


    if (!empty($row['end_time_3'])) {

        $end_date_3 =
            date(
                "Y-m-d",
                strtotime(
                    $row['end_time_3']
                )
            );

        $end_clock_3 =
            date(
                "H:i",
                strtotime(
                    $row['end_time_3']
                )
            );

    }

?>

<div class="day-box">

<div class="day-title">

<?php

echo htmlspecialchars(
    $row['day_week']
);

?>

</div>

<form method="POST">

<input
type="hidden"
name="id"
value="<?php
     echo intval(
         $row['id']
     );
 ?>"

> 

<input
type="hidden"
name="controller_id"
value="<?php echo htmlspecialchars($controller_id, ENT_QUOTES); ?>"
>

<!-- =====================================================
     PERIOD 1
     ===================================================== -->

<div class="period">

<div class="period-title">
Period 1
</div>

<div class="datetime-row">

<label>Start Date:</label>

<input
type="date"
name="start_date_1"
value="<?php
     echo htmlspecialchars(
         $start_date_1
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>Start Time:</label>

<input
type="time"
name="start_time_1"
value="<?php
     echo htmlspecialchars(
         $start_clock_1
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Date:</label>

<input
type="date"
name="end_date_1"
value="<?php
     echo htmlspecialchars(
         $end_date_1
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Time:</label>

<input
type="time"
name="end_time_1"
value="<?php
     echo htmlspecialchars(
         $end_clock_1
     );
 ?>"

>

</div>

<div class="pin-area">

<b>Select Pins:</b>

<br><br>

<?php

$saved_pins_1 =
    explode(
        ",",
        $row['pins_output_1']
    );


for ($i = 1; $i <= 8; $i++) {

?>

<label class="pin">

<input
type="checkbox"
name="pins_output_1[]"
value="D<?php echo $i; ?>"


<?php

if (
    in_array(
        "D".$i,
        $saved_pins_1
    )
) {

    echo "checked";

}

?>


>

D<?php echo $i; ?>

</label>

<?php

}

?>

</div>

<div class="active-area">

<label>

<input
type="hidden"
name="period_active_1"
value="0"

>

<input
type="checkbox"
name="period_active_1"
value="1"


<?php

if (
    intval(
        $row['period_active_1']
    ) == 1
) {

    echo "checked";

}

?>


>

Active

</label>

  

<?php

if (
    intval(
        $row['period_active_1']
    ) == 1
) {

    echo "Period is ACTIVE";

} else {

    echo "Period is DEACTIVATED";

}

?>

</div>

</div>

<!-- =====================================================
     PERIOD 2
     ===================================================== -->

<div class="period">

<div class="period-title">
Period 2
</div>

<div class="datetime-row">

<label>Start Date:</label>

<input
type="date"
name="start_date_2"
value="<?php
     echo htmlspecialchars(
         $start_date_2
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>Start Time:</label>

<input
type="time"
name="start_time_2"
value="<?php
     echo htmlspecialchars(
         $start_clock_2
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Date:</label>

<input
type="date"
name="end_date_2"
value="<?php
     echo htmlspecialchars(
         $end_date_2
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Time:</label>

<input
type="time"
name="end_time_2"
value="<?php
     echo htmlspecialchars(
         $end_clock_2
     );
 ?>"

>

</div>

<div class="pin-area">

<b>Select Pins:</b>

<br><br>

<?php

$saved_pins_2 =
    explode(
        ",",
        $row['pins_output_2']
    );


for ($i = 1; $i <= 8; $i++) {

?>

<label class="pin">

<input
type="checkbox"
name="pins_output_2[]"
value="D<?php echo $i; ?>"


<?php

if (
    in_array(
        "D".$i,
        $saved_pins_2
    )
) {

    echo "checked";

}

?>


>

D<?php echo $i; ?>

</label>

<?php

}

?>

</div>

<div class="active-area">

<label>

<input
type="hidden"
name="period_active_2"
value="0"

>

<input
type="checkbox"
name="period_active_2"
value="1"


<?php

if (
    intval(
        $row['period_active_2']
    ) == 1
) {

    echo "checked";

}

?>


>

Active

</label>

  

<?php

if (
    intval(
        $row['period_active_2']
    ) == 1
) {

    echo "Period is ACTIVE";

} else {

    echo "Period is DEACTIVATED";

}

?>

</div>

</div>

<!-- =====================================================
     PERIOD 3
     ===================================================== -->

<div class="period">

<div class="period-title">
Period 3
</div>

<div class="datetime-row">

<label>Start Date:</label>

<input
type="date"
name="start_date_3"
value="<?php
     echo htmlspecialchars(
         $start_date_3
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>Start Time:</label>

<input
type="time"
name="start_time_3"
value="<?php
     echo htmlspecialchars(
         $start_clock_3
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Date:</label>

<input
type="date"
name="end_date_3"
value="<?php
     echo htmlspecialchars(
         $end_date_3
     );
 ?>"

>

</div>

<div class="datetime-row">

<label>End Time:</label>

<input
type="time"
name="end_time_3"
value="<?php
     echo htmlspecialchars(
         $end_clock_3
     );
 ?>"

>

</div>

<div class="pin-area">

<b>Select Pins:</b>

<br><br>

<?php

$saved_pins_3 =
    explode(
        ",",
        $row['pins_output_3']
    );


for ($i = 1; $i <= 8; $i++) {

?>

<label class="pin">

<input
type="checkbox"
name="pins_output_3[]"
value="D<?php echo $i; ?>"


<?php

if (
    in_array(
        "D".$i,
        $saved_pins_3
    )
) {

    echo "checked";

}

?>


>

D<?php echo $i; ?>

</label>

<?php

}

?>

</div>

<div class="active-area">

<label>

<input
type="hidden"
name="period_active_3"
value="0"

>

<input
type="checkbox"
name="period_active_3"
value="1"


<?php

if (
    intval(
        $row['period_active_3']
    ) == 1
) {

    echo "checked";

}

?>


>

Active

</label>

  

<?php

if (
    intval(
        $row['period_active_3']
    ) == 1
) {

    echo "Period is ACTIVE";

} else {

    echo "Period is DEACTIVATED";

}

?>

</div>

</div>

<!-- SAVE -->

<button
type="submit"
name="save_schedule"
class="save-button"

>

SAVE

<?php

echo htmlspecialchars(
    $row['day_week']
);

?>

</button>

</form>

</div>

<?php

}

?>

</body>

</html>

<?php

mysqli_close($conn);

?>
