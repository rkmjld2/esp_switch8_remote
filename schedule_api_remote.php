<?php

/* =========================================================
   ESP-SWITCH8
   schedule_api_remote.php

   DATABASE:
       esp_switch8

   TABLE:
       weekly_schedule

   IMPORTANT:

       period_active_1 = 1  -> Period 1 enabled
       period_active_1 = 0  -> Period 1 DEACTIVATED

       period_active_2 = 1  -> Period 2 enabled
       period_active_2 = 0  -> Period 2 DEACTIVATED

       period_active_3 = 1  -> Period 3 enabled
       period_active_3 = 0  -> Period 3 DEACTIVATED

   DEACTIVATED PERIODS:
       Their saved date/time and pins remain in database,
       but their pins are NEVER included in active_pins.

   Therefore ESP receives only pins from currently active
   periods.

   If no period is active:
       active_pins = ""

   ESP then turns ALL D1-D8 OFF.
   ========================================================= */


/* =========================================================
   DATABASE CONNECTION
   Render + TiDB Cloud
   ========================================================= */

$host =
    getenv("DB_HOST");

$user =
    getenv("DB_USER");

$password =
    getenv("DB_PASSWORD");

$database =
    getenv("DB_NAME");

$port =
    intval(
        getenv("DB_PORT")
    );


/* =========================================================
   JSON HEADER
   ========================================================= */

header(
    "Content-Type: application/json"
);


/* =========================================================
   CHECK DATABASE SETTINGS
   ========================================================= */

if (
    empty($host) ||
    empty($user) ||
    empty($database) ||
    $port <= 0
) {

    echo json_encode(
        [
            "status" =>
                "error",

            "message" =>
                "Database environment variables are not configured"
        ],
        JSON_PRETTY_PRINT
    );

    exit;

}


/* =========================================================
   CONNECT TO DATABASE
   ========================================================= */

$conn =
    mysqli_init();


/*
   TiDB Cloud uses SSL.
*/

mysqli_ssl_set(
    $conn,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL
);


if (
    !mysqli_real_connect(
        $conn,
        $host,
        $user,
        $password,
        $database,
        $port,
        NULL,
        MYSQLI_CLIENT_SSL
    )
) {

    echo json_encode(
        [
            "status" =>
                "error",

            "message" =>
                "Database connection failed"
        ],
        JSON_PRETTY_PRINT
    );

    exit;

}


/* =========================================================
   CHARACTER SET
   ========================================================= */

mysqli_set_charset(
    $conn,
    "utf8mb4"
);


/* =========================================================
   INDIA TIME
   ========================================================= */

date_default_timezone_set(
    "Asia/Kolkata"
);


/* =========================================================
   CONTROLLER ID
   ========================================================= */

$controller_id =
    isset($_GET["controller_id"])
    ? trim($_GET["controller_id"])
    : "ESP0001";


if (
    $controller_id === ""
) {

    $controller_id =
        "ESP0001";

}


/*
   Escape controller ID
   for SQL.
*/

$controller_sql =
    mysqli_real_escape_string(
        $conn,
        $controller_id
    );


/* =========================================================
   CURRENT DATE AND TIME
   ========================================================= */

$current_datetime =
    date(
        "Y-m-d H:i:s"
    );


/* =========================================================
   CURRENT DAY
   ========================================================= */

$current_day =
    date("l");


/* =========================================================
   FUNCTION
   CHECK WHETHER PERIOD IS CURRENTLY ACTIVE
   ========================================================= */

function isPeriodActive(
    $period_active,
    $start,
    $end,
    $current
) {

    /*
       FIRST CHECK:

       period_active MUST be 1.

       If it is 0:

           DEACTIVATED

       and the function immediately returns false.
    */

    if (
        intval($period_active) != 1
    ) {

        return false;

    }


    /*
       Start or end missing.
    */

    if (
        empty($start) ||
        empty($end)
    ) {

        return false;

    }


    /*
       Convert date/time
       into timestamps.
    */

    $current_timestamp =
        strtotime($current);

    $start_timestamp =
        strtotime($start);

    $end_timestamp =
        strtotime($end);


    /*
       Invalid date/time.
    */

    if (
        $current_timestamp === false ||
        $start_timestamp === false ||
        $end_timestamp === false
    ) {

        return false;

    }


    /*
       Current time must be
       inside the period.
    */

    if (
        $current_timestamp >=
            $start_timestamp

        &&

        $current_timestamp <=
            $end_timestamp
    ) {

        return true;

    }


    return false;

}


/* =========================================================
   READ TODAY'S SCHEDULE
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

WHERE
    day_week = '$current_day'

AND

    controller_id = '$controller_sql'

ORDER BY id

";


$result =
    mysqli_query(
        $conn,
        $sql
    );


/* =========================================================
   DATABASE ERROR
   ========================================================= */

if (
    !$result
) {

    echo json_encode(
        [
            "status" =>
                "error",

            "message" =>
                mysqli_error($conn)
        ],
        JSON_PRETTY_PRINT
    );

    mysqli_close($conn);

    exit;

}


/* =========================================================
   ARRAYS
   ========================================================= */

$periods =
    [];

$active_periods =
    [];

$active_pins =
    [];


/* =========================================================
   READ TODAY'S ROW
   ========================================================= */

while (
    $row =
        mysqli_fetch_assoc(
            $result
        )
) {


    /* =====================================================
       PERIOD 1
       ===================================================== */

    $period1_active =
        intval(
            $row["period_active_1"]
        );


    $active1 =
        isPeriodActive(
            $period1_active,
            $row["start_time_1"],
            $row["end_time_1"],
            $current_datetime
        );


    /*
       Determine status.

       IMPORTANT:

       period_active = 0
       means DEACTIVATED.

       It must not simply be called INACTIVE.
    */

    if (
        $period1_active != 1
    ) {

        $status1 =
            "DEACTIVATED";

    }
    else if (
        $active1
    ) {

        $status1 =
            "ACTIVE";

    }
    else {

        $status1 =
            "INACTIVE";

    }


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
            $period1_active,

        "active" =>
            $active1,

        "status" =>
            $status1

    ];


    $periods[] =
        $period1;


    /* =====================================================
       ADD PERIOD 1 PINS ONLY IF:

       1. period_active = 1
       2. Current date/time is inside period
       ===================================================== */

    if (
        $active1 === true
    ) {

        $active_periods[] =
            $period1;


        if (
            !empty(
                $row["pins_output_1"]
            )
        ) {

            $pins =
                explode(
                    ",",
                    $row["pins_output_1"]
                );


            foreach (
                $pins as $pin
            ) {

                $pin =
                    trim($pin);


                if (
                    $pin != ""

                    &&

                    !in_array(
                        $pin,
                        $active_pins
                    )
                ) {

                    $active_pins[] =
                        $pin;

                }

            }

        }

    }



    /* =====================================================
       PERIOD 2
       ===================================================== */

    $period2_active =
        intval(
            $row["period_active_2"]
        );


    $active2 =
        isPeriodActive(
            $period2_active,
            $row["start_time_2"],
            $row["end_time_2"],
            $current_datetime
        );


    /*
       Determine status.

       period_active = 0
       means DEACTIVATED.
    */

    if (
        $period2_active != 1
    ) {

        $status2 =
            "DEACTIVATED";

    }
    else if (
        $active2
    ) {

        $status2 =
            "ACTIVE";

    }
    else {

        $status2 =
            "INACTIVE";

    }


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
            $period2_active,

        "active" =>
            $active2,

        "status" =>
            $status2

    ];


    $periods[] =
        $period2;


    /* =====================================================
       ADD PERIOD 2 PINS ONLY IF ACTIVE
       ===================================================== */

    if (
        $active2 === true
    ) {

        $active_periods[] =
            $period2;


        if (
            !empty(
                $row["pins_output_2"]
            )
        ) {

            $pins =
                explode(
                    ",",
                    $row["pins_output_2"]
                );


            foreach (
                $pins as $pin
            ) {

                $pin =
                    trim($pin);


                if (
                    $pin != ""

                    &&

                    !in_array(
                        $pin,
                        $active_pins
                    )
                ) {

                    $active_pins[] =
                        $pin;

                }

            }

        }

    }



    /* =====================================================
       PERIOD 3
       ===================================================== */

    $period3_active =
        intval(
            $row["period_active_3"]
        );


    $active3 =
        isPeriodActive(
            $period3_active,
            $row["start_time_3"],
            $row["end_time_3"],
            $current_datetime
        );


    /*
       Determine status.

       period_active = 0
       means DEACTIVATED.
    */

    if (
        $period3_active != 1
    ) {

        $status3 =
            "DEACTIVATED";

    }
    else if (
        $active3
    ) {

        $status3 =
            "ACTIVE";

    }
    else {

        $status3 =
            "INACTIVE";

    }


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
            $period3_active,

        "active" =>
            $active3,

        "status" =>
            $status3

    ];


    $periods[] =
        $period3;


    /* =====================================================
       ADD PERIOD 3 PINS ONLY IF ACTIVE
       ===================================================== */

    if (
        $active3 === true
    ) {

        $active_periods[] =
            $period3;


        if (
            !empty(
                $row["pins_output_3"]
            )
        ) {

            $pins =
                explode(
                    ",",
                    $row["pins_output_3"]
                );


            foreach (
                $pins as $pin
            ) {

                $pin =
                    trim($pin);


                if (
                    $pin != ""

                    &&

                    !in_array(
                        $pin,
                        $active_pins
                    )
                ) {

                    $active_pins[] =
                        $pin;

                }

            }

        }

    }

}


/* =========================================================
   CREATE active_pins STRING
   ========================================================= */

/*
   If no currently active period exists:

       implode() produces:

       ""

   This is exactly what the ESP needs.

   ESP then turns all D1-D8 OFF.
*/

$active_pins_string =
    implode(
        ",",
        $active_pins
    );


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
       ONLY currently active period pins
       are included here.
    */

    "active_pins" =>
        $active_pins_string

];


/* =========================================================
   SEND JSON
   ========================================================= */

echo json_encode(
    $response,
    JSON_PRETTY_PRINT
);


/* =========================================================
   CLOSE DATABASE
   ========================================================= */

mysqli_close(
    $conn
);

?>

