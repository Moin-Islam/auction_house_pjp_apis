<?php
include_once("../../config/Headers.php");
include_once('../../objects/Transaction.php');


$transaction = new Transaction($pdo);

if (isset($_GET["service"])) {

    $serviceName = $_GET["service"];

    switch ($serviceName) {
        case "CompleteTransaction":
            $transaction->CompleteTransaction();
            break;
        case "FetchTransactionDetails":
            $transaction->FetchTransactionDetails();
            break;
    }
}