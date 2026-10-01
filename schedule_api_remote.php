<?php

/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$password = getenv("DB_PASSWORD");
$database = getenv("DB_NAME");
$port = intval(getenv("DB_PORT"));

header("Content-Type: application/json");

if (
    empty($host) ||
    empty($user) ||
    empty($database) ||
    $port <= 0
) {
    echo json_encode([
        "status" => "error",
        "message" => "Database environment variables are not configured"
    ]);
    exit;
}

$conn = mysqli_init();

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
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}

mysqli_set_charset($conn, "utf8mb4");


/* =========================================================
   TIMEZONE
   ========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   CONTROLLER
   ========================================================= */

$controller_id = isset($_GET["controller_id"])
    ? trim($_GET["controller_id"])
    : "ESP0001";

if ($controller_id === "") {
    $controller_id = "ESP0001";
}

$controller_sql =
    mysqli_real_escape_string(
        $conn,
        $controller_id
    );


/* =========================================================
   CURRENT DATE AND TIME
   ========================================================= */

$current_datetime = date("Y-m-d H:i:s");

$current_day = date("l");


/* =========================================================
   FUNCTION
   CHECK WHETHER A PERIOD IS ACTIVE
   ========================================================= */

function isPeriodActive(
    $period_active,
    $start,
    $end,
    $current
) {

    /*
       1 = Active
       0 = Deactivated
    */

    if ((int)$period_active != 1) {
        return false;
    }


    /*
       Start or end missing
    */

    if (empty($start) || empty($end)) {
        return false;
    }


    /*
       Convert FULL DATE + TIME
       into timestamps.

       Example:

       current = 2026-10-01 12:19:30

       start   = 2026-10-01 12:19:00

       end     = 2026-10-01 12:21:00
    */

    $current_timestamp = strtotime($current);

    $start_timestamp = strtotime($start);

    $end_timestamp = strtotime($end);


    /*
       Check whether current time
       is inside the period.
    */

    if (
        $current_timestamp >= $start_timestamp &&
        $current_timestamp <= $end_timestamp
    ) {

        return true;
    }


    return false;
}


/* =========================================================
   READ TODAY'S WEEKLY SCHEDULE
   ========================================================= */

$sql = "

SELECT

    id,
    controller_id,
    day_week,

    start_time_1,
    end_time_1,
    pins_output_1,
    period_active_1,

    start_time_2,
    end_time_2,
    pins_output_2,
    period_active_2,

    start_time_3,
    end_time_3,
    pins_output_3,
    period_active_3

FROM weekly_schedule

WHERE day_week = '$current_day'
  AND controller_id = '$controller_sql'

ORDER BY id

";


$result = mysqli_query($conn, $sql);


if (!$result) {

    header("Content-Type: application/json");

    echo json_encode([
        "status" => "error",
        "message" => mysqli_error($conn)
    ]);

    exit;
}


/* =========================================================
   ARRAYS
   ========================================================= */

$periods = [];

$active_periods = [];

$active_pins = [];


/* =========================================================
   READ TODAY'S SCHEDULE
   ========================================================= */

while ($row = mysqli_fetch_assoc($result)) {


    /* =====================================================
       PERIOD 1
       ===================================================== */

    $active1 = isPeriodActive(
        $row["period_active_1"],
        $row["start_time_1"],
        $row["end_time_1"],
        $current_datetime
    );


    $period1 = [

        "id" =>
            $row["id"],

        "controller_id" =>
            $row["controller_id"],

        "day_week" =>
            $row["day_week"],

        "period" =>
            1,

        "start" =>
            $row["start_time_1"],

        "end" =>
            $row["end_time_1"],

        "pins" =>
            $row["pins_output_1"],

        "period_active" =>
            (int)$row["period_active_1"],

        "active" =>
            $active1,

        "status" =>
            $active1 ? "ACTIVE" : "INACTIVE"
    ];


    $periods[] = $period1;


    /* =====================================================
       ADD PERIOD 1 PINS IF ACTIVE
       ===================================================== */

    if ($active1) {

        $active_periods[] = $period1;


        if (!empty($row["pins_output_1"])) {

            $pins = explode(
                ",",
                $row["pins_output_1"]
            );


            foreach ($pins as $pin) {

                $pin = trim($pin);


                if (
                    $pin != "" &&
                    !in_array($pin, $active_pins)
                ) {

                    $active_pins[] = $pin;
                }
            }
        }
    }



    /* =====================================================
       PERIOD 2
       ===================================================== */

    $active2 = isPeriodActive(
        $row["period_active_2"],
        $row["start_time_2"],
        $row["end_time_2"],
        $current_datetime
    );


    $period2 = [

        "id" =>
            $row["id"],

        "controller_id" =>
            $row["controller_id"],

        "day_week" =>
            $row["day_week"],

        "period" =>
            2,

        "start" =>
            $row["start_time_2"],

        "end" =>
            $row["end_time_2"],

        "pins" =>
            $row["pins_output_2"],

        "period_active" =>
            (int)$row["period_active_2"],

        "active" =>
            $active2,

        "status" =>
            $active2 ? "ACTIVE" : "INACTIVE"
    ];


    $periods[] = $period2;


    /* =====================================================
       ADD PERIOD 2 PINS IF ACTIVE
       ===================================================== */

    if ($active2) {

        $active_periods[] = $period2;


        if (!empty($row["pins_output_2"])) {

            $pins = explode(
                ",",
                $row["pins_output_2"]
            );


            foreach ($pins as $pin) {

                $pin = trim($pin);


                if (
                    $pin != "" &&
                    !in_array($pin, $active_pins)
                ) {

                    $active_pins[] = $pin;
                }
            }
        }
    }



    /* =====================================================
       PERIOD 3
       ===================================================== */

    $active3 = isPeriodActive(
        $row["period_active_3"],
        $row["start_time_3"],
        $row["end_time_3"],
        $current_datetime
    );


    $period3 = [

        "id" =>
            $row["id"],

        "controller_id" =>
            $row["controller_id"],

        "day_week" =>
            $row["day_week"],

        "period" =>
            3,

        "start" =>
            $row["start_time_3"],

        "end" =>
            $row["end_time_3"],

        "pins" =>
            $row["pins_output_3"],

        "period_active" =>
            (int)$row["period_active_3"],

        "active" =>
            $active3,

        "status" =>
            $active3 ? "ACTIVE" : "INACTIVE"
    ];


    $periods[] = $period3;


    /* =====================================================
       ADD PERIOD 3 PINS IF ACTIVE
       ===================================================== */

    if ($active3) {

        $active_periods[] = $period3;


        if (!empty($row["pins_output_3"])) {

            $pins = explode(
                ",",
                $row["pins_output_3"]
            );


            foreach ($pins as $pin) {

                $pin = trim($pin);


                if (
                    $pin != "" &&
                    !in_array($pin, $active_pins)
                ) {

                    $active_pins[] = $pin;
                }
            }
        }
    }

}


/* =========================================================
   JSON RESPONSE
   ========================================================= */

$response = [

    "status" =>
        "success",

    "current_datetime" =>
        $current_datetime,

    "current_day" =>
        $current_day,

    "controller_id" =>
        $controller_id,

    "periods" =>
        $periods,

    "active_periods" =>
        $active_periods,

    /*
       Pins belonging to currently ACTIVE periods.
    */

    "active_pins" =>
        implode(",", $active_pins)

];


/* =========================================================
   SEND JSON
   ========================================================= */

header("Content-Type: application/json");

echo json_encode(
    $response,
    JSON_PRETTY_PRINT
);


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

mysqli_close($conn);

?>
