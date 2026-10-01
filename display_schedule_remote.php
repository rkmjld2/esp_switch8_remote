<?php

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
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");


/* INDIA TIME */

date_default_timezone_set("Asia/Kolkata");

$current_time = date("Y-m-d H:i:s");
$current_display = date("d-m-Y H:i:s");
$today_day = date("l");


/* CONTROLLER */
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


/* GET TODAY'S SCHEDULE */

$sql = "
    SELECT *
    FROM weekly_schedule
    WHERE day_week = '$today_day'
      AND controller_id = '$controller_sql'
    ORDER BY id
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);


/* DISPLAY DATE AND TIME */

function display_datetime($value)
{
    if (
        empty($value) ||
        $value == "0000-00-00 00:00:00"
    ) {
        return "";
    }

    return date(
        "d-m-Y H:i",
        strtotime($value)
    );
}


/* CHECK PERIOD STATUS */

function check_status(
    $period_active,
    $start,
    $end,
    $current_time
)
{
    /* DEACTIVATED HAS PRIORITY */

    if ((int)$period_active == 0) {
        return "DEACTIVATED";
    }

    /* START OR END MISSING */

    if (
        empty($start) ||
        empty($end)
    ) {
        return "INACTIVE";
    }

    $start_timestamp = strtotime($start);
    $end_timestamp = strtotime($end);
    $current_timestamp = strtotime($current_time);

    /* CHECK CURRENT TIME */

    if (
        $current_timestamp >= $start_timestamp &&
        $current_timestamp <= $end_timestamp
    ) {
        return "ACTIVE";
    }

    return "INACTIVE";
}


/* PERIOD DATA */

$periods = array();

if ($row) {

    $periods[1] = array(

        "start" => $row["start_time_1"],

        "end" => $row["end_time_1"],

        "pins" => $row["pins_output_1"],

        "period_active" =>
            (int)$row["period_active_1"],

        "status" =>
            check_status(
                $row["period_active_1"],
                $row["start_time_1"],
                $row["end_time_1"],
                $current_time
            )
    );


    $periods[2] = array(

        "start" => $row["start_time_2"],

        "end" => $row["end_time_2"],

        "pins" => $row["pins_output_2"],

        "period_active" =>
            (int)$row["period_active_2"],

        "status" =>
            check_status(
                $row["period_active_2"],
                $row["start_time_2"],
                $row["end_time_2"],
                $current_time
            )
    );


    $periods[3] = array(

        "start" => $row["start_time_3"],

        "end" => $row["end_time_3"],

        "pins" => $row["pins_output_3"],

        "period_active" =>
            (int)$row["period_active_3"],

        "status" =>
            check_status(
                $row["period_active_3"],
                $row["start_time_3"],
                $row["end_time_3"],
                $current_time
            )
    );
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Display Schedule</title>

<style>

/* PAGE */

body {
    font-family: Arial, sans-serif;
    background-color: white;
    margin: 0;
    padding: 20px;
}


/* HEADING */

h1 {
    text-align: center;
    color: #222;
}


/* CURRENT TIME */

.current-time {
    width: 500px;
    max-width: 90%;

    margin: 20px auto;

    padding: 15px;

    background-color: #222;
    color: white;

    text-align: center;

    font-size: 22px;
    font-weight: bold;

    border-radius: 10px;
}


/* TODAY */

.today {
    text-align: center;

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 20px;
}


/* =================================================
   COMPLETE CLICKABLE PERIOD BOX
   ================================================= */

.period {

    width: 700px;

    max-width: 95%;

    margin: 20px auto;

    padding: 25px;

    box-sizing: border-box;

    border-radius: 15px;

    cursor: pointer;

    color: #111;

    box-shadow: 0 4px 10px rgba(0,0,0,0.20);

    transition: transform 0.15s;
}


/* SMALL MOVEMENT WHEN CLICKED */

.period:active {
    transform: scale(0.98);
}


/* =================================================
   FULL BOX COLORS
   ================================================= */

.period-1 {

    background-color: #4da6ff;

    border: 4px solid #0066cc;
}


.period-2 {

    background-color: #66cc66;

    border: 4px solid #008000;
}


.period-3 {

    background-color: #ffb366;

    border: 4px solid #cc6600;
}


/* PERIOD TITLE */

.period-title {

    font-size: 28px;

    font-weight: bold;

    margin-bottom: 18px;

    color: #000;
}


/* DATE / TIME */

.date-time {

    font-size: 19px;

    line-height: 1.8;
}


/* STATUS */

.active {

    color: #006400;

    font-size: 23px;

    font-weight: bold;

    margin-top: 8px;
}


.inactive {

    color: #990000;

    font-size: 23px;

    font-weight: bold;

    margin-top: 8px;
}


.deactivated {

    color: #990000;

    font-size: 23px;

    font-weight: bold;

    margin-top: 8px;
}


/* OUTPUT */

.output-on {

    color: #006400;

    font-size: 21px;

    font-weight: bold;

    margin-top: 10px;
}


.output-off {

    color: #990000;

    font-size: 21px;

    font-weight: bold;

    margin-top: 10px;
}


/* PIN TITLE */

.pins-title {

    margin-top: 18px;

    font-size: 19px;

    font-weight: bold;
}


/* PIN AREA */

.pins {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 12px;
}


/* PIN BOX */

.pin {

    width: 65px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 7px;

    font-weight: bold;

    font-size: 15px;
}


/* PIN ON */

.pin-selected {

    background-color: #006400;

    color: white;

    border: 2px solid #004d00;
}


/* PIN OFF */

.pin-not-selected {

    background-color: white;

    color: #333;

    border: 2px solid #777;
}


/* NO SCHEDULE */

.no-schedule {

    width: 700px;

    max-width: 95%;

    margin: 20px auto;

    padding: 30px;

    box-sizing: border-box;

    background-color: #eeeeee;

    border: 2px solid #999;

    border-radius: 10px;

    text-align: center;

    font-size: 20px;
}

</style>

<script>

/* REFRESH EVERY 1 SECOND */

setTimeout(function() {

    location.reload();

}, 1000);


/*
   CLICK PERIOD BOX
*/

function periodClicked(period, pins, status)
{

    if (status == "ACTIVE") {

        if (pins == "") {

            alert(
                period +
                "\n\nStatus: ACTIVE" +
                "\n\nNo pins selected."
            );

        }
        else {

            alert(
                period +
                "\n\nStatus: ACTIVE" +
                "\n\nPins ON: " +
                pins
            );

        }

    }
    else {

        alert(
            period +
            "\n\nStatus: " +
            status +
            "\n\nOutput: OFF (0)"
        );

    }

}

</script>

</head>

<body>

<h1>Weekly Schedule</h1>

<div class="current-time">

Current India Time:

<br>

<?php echo $current_display; ?>

</div>

<div class="today">

Today:

<?php echo $today_day; ?>

</div>

<?php

/* NO SCHEDULE */

if (!$row) {

    echo '
    <div class="no-schedule">
        No schedule found for today.
    </div>
    ';
}


/* DISPLAY PERIODS */

if ($row) {

    for ($p = 1; $p <= 3; $p++) {

        $start =
            $periods[$p]["start"];

        $end =
            $periods[$p]["end"];

        $pins =
            $periods[$p]["pins"];

        $period_active =
            $periods[$p]["period_active"];

        $status =
            $periods[$p]["status"];


        /* SKIP EMPTY PERIOD */

        if (
            empty($start) ||
            empty($end)
        ) {
            continue;
        }


        /* STATUS CLASS */

        if ($status == "ACTIVE") {

            $status_class = "active";

        }
        elseif ($status == "DEACTIVATED") {

            $status_class = "deactivated";

        }
        else {

            $status_class = "inactive";
        }


        /* OUTPUT */

        if ($status == "ACTIVE") {

            $output_status = "OUTPUT ACTIVE";

            $output_class = "output-on";

        }
        else {

            $output_status = "OUTPUT OFF (0)";

            $output_class = "output-off";
        }

?>

<div
    class="period period-<?php echo $p; ?>"
    onclick="periodClicked(
        'Period <?php echo $p; ?>',
        '<?php echo htmlspecialchars($pins, ENT_QUOTES); ?>',
        '<?php echo $status; ?>'
    )"
>

<div class="period-title">

Period <?php echo $p; ?>

</div>

<div class="date-time">

<b>Start:</b>

<?php echo display_datetime($start); ?>

</div>

<div class="date-time">

<b>End:</b>

<?php echo display_datetime($end); ?>

</div>

<div class="date-time">

<b>Period Setting:</b>

<?php

if ($period_active == 1) {

    echo "Active";

}
else {

    echo "Deactivated";

}

?>

</div>

<div class="<?php echo $status_class; ?>">

Status:

<?php echo $status; ?>

</div>

<div class="<?php echo $output_class; ?>">

<?php echo $output_status; ?>

</div>

<div class="pins-title">

Scheduled Output Pins:

</div>

<div class="pins">

<?php

/* D1 TO D8 */

for ($i = 1; $i <= 8; $i++) {

    $pin = "D" . $i;


    /*
       PIN ON ONLY WHEN
       PERIOD IS ACTIVE
    */

    if (
        $status == "ACTIVE" &&
        !empty($pins) &&
        strpos(
            strtoupper($pins),
            $pin
        ) !== false
    ) {

        echo '
        <div class="pin pin-selected">
            ' . $pin . ' ON
        </div>
        ';

    }
    else {

        echo '
        <div class="pin pin-not-selected">
            ' . $pin . ' OFF
        </div>
        ';
    }

}

?>

</div>

</div>

<?php

    }

}

?>

</body>

</html>

<?php

mysqli_close($conn);

?>
