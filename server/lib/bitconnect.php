<?php
if (!isset($_GET['a'])) $_GET['a'] = '';

define("DB_SERVER", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "taskete");

// define("DB_SERVER", "allah.xyecoc.club");
// define("DB_USER", "Desks");
// define("DB_PASS", "YV0QXuCo8pGRFtbCGrcX");
// define("DB_NAME", "desks");

$connection = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
} 

mysqli_set_charset($connection, 'utf8');
mb_internal_encoding("UTF-8");

function Query($query) {
    global $connection;
    return mysqli_query($connection, $query);
}

function QueryAssoc($query) {
    return mysqli_fetch_assoc(Query($query));
}
?>