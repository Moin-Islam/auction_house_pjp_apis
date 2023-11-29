<?php
include_once("../../config/Headers.php");
include_once('../../objects/Bid.php');


$bid = new Bid($pdo);

if (isset($_GET["service"])) {

    $serviceName = $_GET["service"];

    switch ($serviceName) {
        case "NewBid":
            $bid->NewBid();
            break;
        case "FetchBidHistory":
            $bid->FetchBidHistory();
            break;
        case "FetchHighestBid":
            $bid->FetchHighestBid();
            break;
        case "CancelUserBid":
            $bid->CancelUserBid();
            break;
        case "FetchOngoingBid":
            $bid->FetchOngoingBid();
            break;
        
    }
}