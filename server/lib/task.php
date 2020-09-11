<?php
function TaskTeamCheck($task_id) {
    $array = QueryAssoc("SELECT team_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist'); else return $array['team_id'];
}

/*
    TASK INFO
    POST task_id
*/
if($_GET['a'] == 'taskInfo') {
    UserValid();
    $task_id = $_POST['task_id'];
    $array = QueryAssoc("SELECT * FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');

    $supervisor_id = $array['supervisor_id'];
    $q = QueryAssoc("SELECT login FROM user WHERE id='$supervisor_id'");
    $supervisor_login = $q;
    $array['supervisor_login'] = $supervisor_login;

    $executor_id = $array['executor_id'];
    $q = QueryAssoc("SELECT login FROM user WHERE id='$executor_id'");
    $executor_login = $q;
    $array['executor_login'] = $executor_login;

    unset($array['supervisor_id']);
    unset($array['executor_id']);
    exit(json_encode($array));
}

/*
    TASK CREATE
    POST team_id, supervisor_login, executor_login, name, desc, important, expired_date, tasks
*/
if($_GET['a'] == 'taskCreate') {
    UserValid();
    $team_id = $_POST['team_id'];
    if(TeamCheck($team_id) < 1) exit('rank');

    $supervisor_login = $_POST['supervisor_login'];
    $array = QueryAssoc("SELECT id FROM user WHERE login='$supervisor_login'");
    if($array == null) $supervisor_id = ''; else $supervisor_id = $array;

    $executor_login = $_POST['executor_login'];
    $array = QueryAssoc("SELECT id FROM user WHERE login='$executor_login'");
    if($array == null) $executor_id = ''; else $executor_id = $array;

    $name = $_POST['name'];
    $desc = $_POST['desc'];
    if($_POST['important'] != '') $important = 1; else $important = 0;
    $tasks = $_POST['tasks'];
    if($_POST['expired_date'] != '') $expired_date = $_POST['expired_date']; else $expired_date = NULL;

    Query("INSERT INTO task (team_id, supervisor_id, executor_id, name, description, important, expired_date, tasks) 
    VALUES ('$team_id', '$supervisor_id', '$executor_id', '$name', '$desc', '$important', '$expired_date', '$tasks')");
    exit('created');
}

/*
    TASK EDIT
    POST task_id, supervisor_login, executor_login, name, desc, important, expired_date, tasks, status, closed_date, closed_commentary
*/
if($_GET['a'] == 'taskEdit') {
    UserValid();
    $task_id = $_POST['task_id'];
    $team_id = TaskTeamCheck($task_id);
    if(TeamCheck($team_id) < 1) exit('rank');

    $supervisor_login = $_POST['supervisor_login'];
    $array = QueryAssoc("SELECT id FROM user WHERE login='$supervisor_login'");
    if($array == null) $supervisor_id = ''; else $supervisor_id = $array;

    $executor_login = $_POST['executor_login'];
    $array = QueryAssoc("SELECT id FROM user WHERE login='$executor_login'");
    if($array == null) $executor_id = ''; else $executor_id = $array;

    $name = $_POST['name'];
    $desc = $_POST['desc'];
    if($_POST['important'] != '') $important = 1; else $important = 0;
    $tasks = $_POST['tasks'];
    $status = $_POST['status'];
    if($_POST['expired_date'] != '') $expired_date = $_POST['expired_date']; else $expired_date = NULL;
    $closed_date = $_POST['closed_date'];
    $closed_commentary = $_POST['closed_commentary'];

    Query("UPDATE `task` SET 
    `supervisor_id`='$supervisor_id',
    `executor_id`='$executor_id',
    `name`='$name',
    `description`='$desc',
    `important`='$important',
    `expired_date`='$expired_date',
    `tasks`='$tasks',
    `status`='$status',
    `closed_date`='$closed_date',
    `closed_commentary`='$closed_commentary' 
    WHERE id='$task_id'");
    exit('updated');
}

/*
    TASK DELETE
    POST task_id
*/
if($_GET['a'] == 'taskDelete') {
    UserValid();
    $task_id = $_POST['task_id'];
    $team_id = TaskTeamCheck($task_id);
    if(TeamCheck($team_id) < 1) exit('rank');

    Query("DELETE FROM task WHERE id='$task_id'");
    exit('deteled');
}

/*
    TASK EXECUTOR ASSIGN
    POST task_id
*/
if($_GET['a'] == 'taskExectuorAssign') {
    UserValid();
    $task_id = $_POST['task_id'];

    $array = QueryAssoc("SELECT executor_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');
    if($array['executor_id'] == $_SESSION['user_id']) exit('already');
    if($array['executor_id'] != '0') exit('occupied');

    $user_id = $_SESSION['user_id'];
    Query("UPDATE task SET executor_id='$user_id' WHERE id='$task_id'");
    exit('assigned');
}

/*
    TASK EXECUTOR LEAVE
    POST task_id
*/
if($_GET['a'] == 'taskExectorLeave') {
    UserValid();
    $task_id = $_POST['task_id'];

    $array = QueryAssoc("SELECT executor_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');
    if($array['executor_id'] != $_SESSION['user_id']) exit('notyou');

    $user_id = $_SESSION['user_id'];
    Query("UPDATE task SET executor_id='0' WHERE id='$task_id'");
    exit('left');
}

/*
    TASK STATUS
    POST task_id, status
*/
if($_GET['a'] == 'taskExectorStatus') {
    UserValid();
    $task_id = $_POST['task_id'];

    $array = QueryAssoc("SELECT supervisor_id, executor_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');
    if($array['supervisor_id'] != $_SESSION['user_id'] && $array['executor_id'] != $_SESSION['user_id']) exit('cant');

    $status = $_POST['status'];
    Query("UPDATE task SET status='$status' WHERE id='$task_id'");
    exit('updated');
}

/*
    TASK SUPERVISOR CLOSE
    POST task_id, closed_commentary
*/
if($_GET['a'] == 'taskSupervisorClose') {
    UserValid();
    $task_id = $_POST['task_id'];

    $array = QueryAssoc("SELECT supervisor_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');
    if($array['supervisor_id'] != $_SESSION['user_id']) exit('cant');

    $closed_commentary = $_POST['closed_commentary'];
    $date = date("Y-m-d H:i:s");

    Query("UPDATE task SET closed_date='$date', closed_commentary='$closed_commentary' WHERE id='$task_id'");
    exit('closed');
}

/*
    TASK SUPERVISOR REOPEN
    POST task_id
*/
if($_GET['a'] == 'taskSupervisorOpen') {
    UserValid();
    $task_id = $_POST['task_id'];

    $array = QueryAssoc("SELECT supervisor_id FROM task WHERE id='$task_id'");
    if($array == null) exit('notexist');
    if($array['supervisor_id'] != $_SESSION['user_id']) exit('cant');
    
    Query("UPDATE task SET closed_date=NULL, closed_commentary='' WHERE id='$task_id'");
    exit('open'); 
}
?>