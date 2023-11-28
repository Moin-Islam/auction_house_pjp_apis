<?php
include_once("../../config/Headers.php");
include_once('../../objects/Auction.php');


$auction = new Auction($pdo);

if (isset($_GET["service"])) {

    $serviceName = $_GET["service"];

    switch ($serviceName) {
        case "CreateAuction":
            $auction->CreateAuction();
            break;
        case "ActiveAuctions" :
            $auction -> FetchActiveAuctions();
            break;
        case "FetchItemDetails" :
            $auction -> FetchItemDetails();
            break;
        case "EndAuction" :
            $auction -> EndAuction();
            break;
    }
}