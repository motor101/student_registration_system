<?php
require_once("db.php");

function get_zodiac_info($str)
{
    global $zodiac_name_str;
    global $zodiac_sign_str;

    $zodiac_names = ["козирог", "водолей", "риби", "овен", "телец", "близнаци", "рак", "лъв", "дева", "везни",
        "скорпион", "стрелец"];

    $zodiac_signs = ["&#2651;", "&#2652;", "&#2653;", "&#2648;", "&#2649;", "&#264a;", "&#2648;", "&#264c;", "&#264d;",
        "&#264e;", "&#264f;", "&#2650;"
    ];

    for ($i = 0; $i < count($zodiac_signs); ++$i) {
        $zodiac_signs[$i] = html_entity_decode($zodiac_signs[$i]);
    }

    $last_day = [19, 19, 20, 20, 21, 21, 22, 23, 23, 22, 22, 21];

    $date = strtotime($str);

    $day = (int)date("d", $date);
    $month = (int)date("m", $date);

    // we want the first month to be number 0
    $month -= 1;

    $index = $month;
    if ($day > $last_day[$month]) {
        $index = ($month + 1) % 12;
    }

    return [$zodiac_name_str => $zodiac_names[$index], $zodiac_sign_str => $zodiac_signs[$index]];
}

function send_error_response()
{
    global $error_message;
    global $script_response;

    $script_response["success"] = false;
    $script_response["error_message"] = $error_message;

    // discard all unexpected messages and turn off output buffering
    ob_end_clean();

    echo json_encode($script_response);

    exit(1);
}

function contains_digit($str)
{
    return preg_match("/[0-9]/", $str);
}

function check_required_arguments()
{
    global $data_is_valid;
    global $required_parameters;
    global $error_message;

    foreach ($required_parameters as $parameter_name) {
        if (!isset($_REQUEST["$parameter_name"]) || $_REQUEST["$parameter_name"] == "") {
            $data_is_valid = false;
            $error_message["$parameter_name"] = "Това поле е празно";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // start output buffering. This is required, because the client expects a response in JSON format.
    // We don't want an unexpected error message to break the client's parser.
    ob_start();

    $first_name_str = "first_name";
    $last_name_str = "last_name";
    $course_str = "course";
    $major_str = "major";
    $faculty_number_str = "faculty_number";
    $group_str = "group";
    $birth_date_str = "birth_date";
    $url_str = "url";
    $motivational_letter_str = "motivational_letter";
    $zodiac_name_str = "zodiac_name";
    $zodiac_sign_str = "zodiac_sign";

    $required_parameters = [$first_name_str, $last_name_str, $course_str, $major_str, $faculty_number_str,
        $group_str, $birth_date_str, $url_str, $motivational_letter_str, $zodiac_name_str, $zodiac_sign_str];

    $photo_str = "photo";
    $signature_str = "signature";

    $data_is_valid = true;
    $error_message = [];
    $script_response = [];

    check_required_arguments();

    if ($data_is_valid == false) {
        send_error_response();
    }

    $first_name = trim($_REQUEST[$first_name_str]);
    if (contains_digit($first_name)) {
        $error_message[$first_name_str] = "Не трябва да съдържа цифри";
        $data_is_valid = false;
    }

    $last_name = $_REQUEST[$last_name_str];
    if (contains_digit($last_name)) {
        $error_message[$last_name_str] = "Не трябва да съдържа цифри";
        $data_is_valid = false;
    }

    $course = trim($_REQUEST[$course_str]);
    if (!preg_match("/^[1-6]$/", $course)) {
        $error_message[$course_str] = "Трябва да е число от 1 до 6";
        $data_is_valid = false;
    }

    $major = trim($_REQUEST[$major_str]);
    if (contains_digit($major)) {
        $error_message[$major_str] = "Не трябва да съдържа цифри";
        $data_is_valid = false;
    }

    $faculty_number = trim($_REQUEST[$faculty_number_str]);

    if (!is_numeric($faculty_number)) {
        $error_message[$faculty_number_str] = "Трябва да е число";
        $data_is_valid = false;
    } else {
        try {
            if (faculty_number_exists_in_db($faculty_number) == true) {
                $error_message[$faculty_number_str] = "Студентът с този факултетен номер вече е регистриран";
                $data_is_valid = false;
            }
        } catch (PDOException $e) {
            $error_message["connection_to_database"] = "Проблем при свъзването с базата данни";
        }
    }

    $group = trim($_REQUEST[$group_str]);
    if (!preg_match("/^[1-9]$/", $group)) {
        $error_message["$group_str"] = "Трябва да е число от 1 до 9";
        $data_is_valid = false;
    }

    $motivational_letter = trim($_REQUEST[$motivational_letter_str]);

    $birth_date = trim($_REQUEST[$birth_date_str]);
    if ($birth_date == false || !preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $birth_date)
        || $birth_date < "1900-01-01" || $birth_date > "2005-01-01") {
        $error_message[$birth_date_str] = "Не е валидна дата";
        $data_is_valid = false;
    }

    $zodiac_name = trim($_REQUEST[$zodiac_name_str]);

    $zodiac_sign = trim($_REQUEST[$zodiac_sign_str]);

    $server_evaluated_zodiac_info = get_zodiac_info($birth_date);

    if ($server_evaluated_zodiac_info[$zodiac_name_str] != $zodiac_name) {
        $error_message[$zodiac_name_str] = "Името на зодията не съответства на рожденната дата";
        $data_is_valid = false;
    }

    $url = trim($_REQUEST[$url_str]);

    if ($_FILES[$signature_str]["tmp_name"] == "") {
        $error_message[$signature_str] = "Не е качен подпис";
        $data_is_valid = false;
    } elseif (exif_imagetype($_FILES[$signature_str]["tmp_name"]) != false) {
        $signature = file_get_contents($_FILES[$signature_str]["tmp_name"]);
    } else {
        $error_message[$signature_str] = "Файлът не е изображение";
        $data_is_valid = false;
    }

    if ($_FILES[$photo_str]["tmp_name"] == "") {
        $error_message[$photo_str] = "He e качена снимка";
        $data_is_valid = false;
    } elseif (exif_imagetype($_FILES[$photo_str]["tmp_name"]) != false) {
        $photo = file_get_contents($_FILES[$photo_str]["tmp_name"]);
    } else {
        $error_message[$photo_str] = "Файлът не е изображение";
        $data_is_valid = false;
    }

    if ($data_is_valid) {
        try {
            save($first_name, $last_name, $course, $major, $faculty_number, $group, $birth_date, $url,
                $motivational_letter, $zodiac_name, $zodiac_sign, $photo, $signature);

            $script_response["success"] = true;
            $script_response["new_url"] = "./after_registration.html";

            // discard all unexpected messages and turn off output buffering
            ob_end_clean();

            echo json_encode($script_response);

        } catch (PDOException $e) {
            $error_message["connection_to_database"] = "Проблем при свъзването с базата данни";

            send_error_response();
        }
    } else {
        send_error_response();
    }
}
?>
