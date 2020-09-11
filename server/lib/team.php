<?php
function TeamCheck($team_id) {
    $user_id = $_SESSION['user_id'];
    $array = QueryAssoc("SELECT rank FROM joined WHERE team_id = '$team_id' AND user_id = '$user_id'");
    if($array == null) exit('notinteam'); 
    else return $array['rank'];
}

/*
    TEAM JOIN
    POST team_id
*/
if($_GET['a'] == 'teamJoin') {
    UserValid();
    $team_id = $_POST['team_id'];
    $user_id = $_SESSION['user_id'];
    if(QueryAssoc("SELECT rank FROM joined WHERE team_id='$team_id' AND user_id='$user_id'") != null) exit('alreadyin');
    Query("INSERT INTO joined (team_id, user_id, rank) VALUES ('$team_id', '$user_id', '0')");
    exit('joined');
}

/*
    TEAM LEAVE
    POST team_id
*/
if($_GET['a'] == 'teamLeave') {
    UserValid();
    $team_id = $_POST['team_id'];
    TeamCheck($team_id);

    $user_id = $_SESSION['user_id'];
    Query("UPDATE task SET supervisor_id='' WHERE supervisor_id='$user_id'");
    Query("UPDATE task SET executor_id='' WHERE executor_id='$user_id'");
    Query("DELETE FROM joined WHERE user_id='$user_id' AND team_id='$team_id'");
    exit('left');
}

/*
    TEAM CREATE
    POST name, private, private_code, img
*/
if($_GET['a'] == 'teamCreate') {
    UserValid();

    $name = $_POST['name'];
    if($name == '') exit('emptyname');

    $private = $_POST['private'];
    if($private == '') $private_code = '';
    else {
        $private = 1;
        $private_code = $_POST['private_code'];
        if($private_code == '') exit('nocode');
        if(QueryAssoc("SELECT id FROM team WHERE private_code = '$private_code'") != null) exit('codeoccupied');
    }

    if (empty($_FILES['image'])) $img = '';
    else $img = ImageUpload();

    Query("INSERT INTO team (name, img, private, private_code) 
    VALUES ('$name', '$img', '$private', '$private_code')");

    $user_id = $_SESSION['user_id'];
    $team_id = mysqli_insert_id($connection);

    Query("INSERT INTO joined (user_id, team_id, rank) VALUES ('$user_id', '$team_id', '2')");
    console_log(mysqli_insert_id($connection));
}

/*
    TEAM DELETE
    POST team_id
*/
if($_GET['a'] == 'teamDelete') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 2) exit('rank');

    Query("DELETE FROM task WHERE team_id = '$team_id'");
    Query("DELETE FROM joined WHERE team_id = '$team_id'");
    Query("DELETE FROM team WHERE id = '$team_id'");
}

/*
    TEAM EDIT
    POST team_id, name, private, private_code
*/
if($_GET['a'] == 'teamEdit') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 2) exit('rank');

    $array = QueryAssoc("SELECT name FROM team WHERE id = '$team_id'");
    if($_POST['name'] == '') $name = $array['name']; else $name = $_POST['name'];
    if($_POST['private'] == '') {
        $private = 0;
        $private_code = '';
    }else {
        $private = 1;
        $private_code = $_POST['private_code'];
        if($private_code == '') exit('nocode');

        $array = QueryAssoc("SELECT id FROM team WHERE private_code = '$private_code'");
        if($array != null) {
            if($array['id'] != $team_id) exit('codeoccupied'); else exit('samecode');
        } 
    } 

    Query("UPDATE team SET name='$name', private='$private', private_code='$private_code' WHERE id='$team_id'");
    exit('updated');
}

/*
    TEAM AVATAR UPLOAD
    POST team_id
*/
if($_GET['a'] == 'teamImage') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 2) exit('rank');
    $url = ImageUpload();
    $id = $_SESSION['user_id'];
    Query("UPDATE team SET img = '$url' WHERE id = '$team_id'");
    exit('uploaded');
}

/*
    TEAM ASSIGN USER RANK
    POST team_id, login, rank
*/
if($_GET['a'] == 'teamAssign') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 2) exit('rank');

    $login = $_POST['login'];
    $user_id = QueryAssoc("SELECT id FROM user WHERE login = '$login'")['id'];
    if(QueryAssoc("SELECT rank FROM joined WHERE team_id='$team_id' AND user_id='$user_id'") == null) exit('notinteam');

    $rank = $_POST['rank'];
    Query("UPDATE joined SET rank = '$rank' WHERE team_id='$team_id' AND user_id='$user_id'");
    exit('assigned');
}

/*
    TEAM KICK
    POST team_id, login
*/
if($_GET['a'] == 'teamKick') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 2) exit('rank');

    $login = $_POST['login'];
    $array = QueryAssoc("SELECT id FROM user WHERE login = '$login'");
    if($array == null) exit('usernotexist');
    $user_id = $array['id'];
    if(QueryAssoc("SELECT rank FROM joined WHERE team_id='$team_id' AND user_id='$user_id'") == null) exit('notinteam');

    Query("UPDATE task SET supervisor_id='' WHERE supervisor_id='$user_id'");
    Query("UPDATE task SET executor_id='' WHERE executor_id='$user_id'");
    Query("DELETE FROM joined WHERE user_id='$user_id' AND team_id='$team_id'");
    exit('kicked');
}
?>