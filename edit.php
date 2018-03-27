<?php

include_once 'connect.php';
session_start();
if(!empty($_SESSION['id'])) {
    if(!empty($_POST)) {
        $id = $_POST['edit_id'];
        $sth =$pdo->prepare("UPDATE `task` SET description=? WHERE id='$id'");
        $sth->execute([$_POST['edit']]);
        header("Location: /user_data/aabramov/less4.3/index.php");
    }
} else {
    header("Location: /user_data/aabramov/less4.3/index.php");    
}
