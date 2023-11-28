<?php
include_once("../../config/Headers.php");
include_once('../../objects/User.php');


$user = new User($pdo);

if (isset($_GET["service"])) {

    $serviceName = $_GET["service"];

    switch ($serviceName) {
        case "CreateUser":
            $user->CreateUser();
            break;
        case "authentication":
            $user->UserAuthentication();
            break;
        case "UserInfo":
            $user->FetchUserInfo();
            break;
    }
}