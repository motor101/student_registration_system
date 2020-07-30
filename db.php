<?php

$server_name = "localhost";
$user_name = "root";
$password = "password";
$database_name = "my_db";
$table_name = "users";

function save($first_name, $last_name, $course, $major, $faculty_number, $group, $birth_date, $url,
              $motivational_letter, $zodiac_name, $zodiac_sign, $photo, $signature)
{
    global $server_name;
    global $user_name;
    global $password;
    global $database_name;
    global $table_name;

    $conn = new PDO("mysql:host=$server_name;dbname=$database_name;", $user_name, $password,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $statement = $conn->prepare("INSERT INTO $table_name VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $statement->execute([$first_name, $last_name, $course, $major, $faculty_number, $group, $birth_date,
        $zodiac_name, $zodiac_sign, $url, $motivational_letter, $photo, $signature]);
}

function faculty_number_exists_in_db($faculty_number)
{
    global $server_name;
    global $user_name;
    global $password;
    global $database_name;
    global $table_name;

    $faculty_number_db = "faculty_number";

    $conn = new PDO("mysql:host=$server_name;dbname=$database_name;", $user_name, $password,
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $statement = $conn->prepare("SELECT * FROM $table_name WHERE $faculty_number_db = ?");
    $statement->bindParam(1, $faculty_number);
    $statement->execute();

    $query_result = $statement->fetchAll();
    if ($query_result == []) {
        return false;
    } else {
        return true;
    }
}

?>

