<?php
function UserValid() {
    if(session_status() != PHP_SESSION_ACTIVE) session_start();
    if(!isset($_SESSION['user_id'], $_SESSION['session_id']) || $_SESSION['session_id'] != session_id()) 
    exit('auth');
}

/*
    USER AVATAR UPLOAD
    SESSION
*/
if($_GET['a'] == 'userAccountImage') {
    UserValid();
    $url = ImageUpload();
    $id = $_SESSION['user_id'];
    Query("UPDATE user SET img = '$url' WHERE id = '$id'");
    exit('uploaded');
}

/*
    USER ACCOUNT INFO
    SESSION
*/
if($_GET['a'] == 'userAccountInfo') {
    UserValid();
    $id = $_SESSION['user_id'];
    $array = QueryAssoc("SELECT * FROM user WHERE id = '$id'");
    unset($array['id']);
    unset($array['password']);
    // console_log("JSON.parse('".json_encode($array)."')");
    exit(json_encode($array));
}

/*
    USER ACCOUNT EDIT
    POST name, password
*/
if($_GET['a'] == 'userAccountEdit') {
    UserValid();

    $id = $_SESSION['user_id'];
    $array = QueryAssoc("SELECT name, password FROM user WHERE id = '$id'");
    $name = $array['name'];
    $password  = $array['password'];

    $password_new = substr($_POST['password'], 0, 254);
    if($password_new != $password && $password_new != '') $password = $password_new;

    $name_new = substr($_POST['name'], 0, 254);
    if($name_new != $name && $name_new != '') $name = $name_new;

    Query("UPDATE user SET name = '$name', password='$password' WHERE id='$id'");
    exit('updated');
}

/*
    USER LOGIN
    POST login, passowrd
*/
if($_GET['a'] == 'userLogin') {
    $login = $_POST['login'];
    $password = $_POST['password'];

    $array = QueryAssoc("SELECT id FROM user WHERE login='$login' AND password='$password'");
    if($array == null) exit('wrong');

    session_start();
    $_SESSION['session_id'] = session_id();
    $_SESSION['user_id'] = $array['id'];
    exit('loggedin');
}

/*
    USER FORGOT
    POST login
*/
if($_GET['a'] == 'userForgot') {
    $login = $_POST['login'];

    $array = QueryAssoc("SELECT password FROM user WHERE login='$login'");
    if($array != null) exit($array['password']);
    else exit(0);
}

/*
    USER REGISTER
    POST login, password, name, image
*/
if($_GET['a'] == 'userRegister') {
    $login =  substr($_POST['login'], 0, 34);
    $password =  substr($_POST['password'], 0, 254);
    $name = substr($_POST['name'], 0, 254);

    if($login == '' || $password == '') exit('empty');
    if(!preg_match('/^[a-zA-Z0-9]+$/', $login)) exit('illegal');
    $array = QueryAssoc("SELECT id FROM user WHERE login='$login'");
    if($array != null) exit('exist');

    $img = ImageUpload();

    Query("INSERT INTO user (login, password, name, img) 
    VALUES ('$login', '$password', '$name', '$img')");

    session_start();
    $_SESSION['session_id'] = session_id();
    $_SESSION['user_id'] = mysqli_insert_id($connection);
    exit('loggedin');
}

/*
    USER LOGOUT
    SESSION
*/
if($_GET['a'] == 'userLogout') {
    if(session_status() != PHP_SESSION_ACTIVE) session_start();
    session_destroy();
    exit('loggedout');
}

/*
    USER INFO
    POST login
*/
if($_GET['a'] == 'userInfo') {
    UserValid();
    $login = $_POST['login'];
    $array = QueryAssoc("SELECT * FROM user WHERE login = '$login'");
    unset($array['id']);
    unset($array['password']);
    exit(json_encode($array));
}
?>