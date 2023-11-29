<?php

class Bid
{
    private $conn;
    private $table_name = "bids";
    public $bid_id;
    public $auction_id;
    public $bidder_id;
    public $bid_amount;
    public $bid_timestamp;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function NewBid()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id) && !empty($data->bidder_id) && !empty($data->bid_amount)) {
            $this->auction_id = $data->auction_id;
            $this->bidder_id = $data->bidder_id;
            $this->bid_amount = $data->bid_amount;
        }

        $query = "INSERT INTO $this->table_name(auction_id,bidder_id,bid_amount)
        VALUES (:auction_id, :bidder_id, :bid_amount)";
        $stmt = $this->conn->prepare($query);

        $this->auction_id = htmlspecialchars(strip_tags($this->auction_id));
        $this->bidder_id = htmlspecialchars(strip_tags($this->bidder_id));
        $this->bid_amount = htmlspecialchars(strip_tags($this->bid_amount));

        $stmt->bindParam(":auction_id", $this->auction_id);
        $stmt->bindParam(":bidder_id", $this->bidder_id);
        $stmt->bindParam(":bid_amount", $this->bid_amount);

        try {
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "New Bid Placed"
            ]);
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Unable to place new bid"
            ]);
        }
    }

    public function FetchBidHistory()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id)) {
            $this->auction_id = $data->auction_id;
        }

        $query = "SELECT * FROM $this->table_name WHERE auction_id = :auction_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":auction_id", $this->auction_id);

        try {
            $stmt->execute();
            $num = $stmt->rowCount();

            if ($num > 0) {
                $bid_arr = [];
                $bid_arr["records"] = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $bidder_specific_id = $bidder_id;
                    $bidder_name_query = "SELECT username FROM users where user_id = :bidder_id";
                    $bidder_name_stmt = $this->conn->prepare($bidder_name_query);
                    $bidder_name_stmt->bindParam(":bidder_id", $bidder_specific_id);
                    $bidder_name_stmt->execute();
                    $bidder_name_row = $bidder_name_stmt->fetch(PDO::FETCH_ASSOC);
                    $bidder_name = $bidder_name_row["username"];

                    $bid_list = [
                        "bid_id" => $bid_id,
                        "bidder_name" => $bidder_name,
                        "auction_id" => $auction_id,
                        "bidder_id" => $bidder_id,
                        "bid_amount" => $bid_amount,
                        "bid_timestamp" => $bid_timestamp,
                        "Total Bid Placed" => $num
                    ];
                    array_push($bid_arr["records"], $bid_list);
                }
                http_response_code(200);
                echo json_encode([
                    $bid_arr,
                    "total_bid_placed" => $num
                ]);
            } else {
                http_response_code(405);
                echo json_encode([
                    "message" => "No bidding history found for this auction"
                ]);
            }
        } catch (e) {
            http_response_code(405);
            echo json_encode([
                "message" => "Invalid Connection"
            ]);
        }
    }

    public function FetchHighestBid()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id)) {
            $this->auction_id = $data->auction_id;
        }

        $query = "SELECT MAX(bid_amount) FROM $this->table_name WHERE auction_id = :auction_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":auction_id", $this->auction_id);

        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            /*print_r ($row);
            die;*/
            $this->bid_amount = $row["MAX(bid_amount)"];

            http_response_code(200);
            echo json_encode([
                "message" => "Succesfully fetched the Highest bid",
                "highest_bid" => $this->bid_amount
            ]);
        } catch (e) {
            http_response_code(200);
            echo json_encode([
                "message" => "Unable to fetch the highest bid"
            ]);
        }

    }

    public function CancelUserBid()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id) && !empty($data->bidder_id)) {
            $this->auction_id = $data->auction_id;
            $this->bidder_id = $data->bidder_id;

        }

        $query = "DELETE FROM $this->table_name WHERE auction_id = :auction_id AND bidder_id = :bidder_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":auction_id", $this->auction_id);
        $stmt->bindParam(":bidder_id", $this->bidder_id);

        try {
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "Succesfully deleted the bid for the user",
            ]);
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Unable to delete the bid for the user, check connection",
            ]);
        }
    }

    public function FetchOngoingBid()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id)) {
            $this->auction_id = $data->auction_id;
        }

        $query = "SELECT * FROM $this->table_name WHERE auction_id=:auction_id ORDER BY bid_timestamp DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":auction_id", $this->auction_id);

        try {
            $stmt->execute();
            $num = $stmt->rowCount();
            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $this->bid_id = $row["bid_id"];
                $this->bidder_id = $row["bidder_id"];
                $this->bid_amount = $row["bid_amount"];
                $this->bid_timestamp = $row["bid_timestamp"];

                if ($this->bid_id != null) {
                    $bid_arr = [
                        "bid_id" => $this->bid_id,
                        "bidder_id" => $this->bidder_id,
                        "bid_amount" => $this->bid_amount,
                        "bid_timestamp" => $this->bid_timestamp,
                    ];

                    http_response_code(200);
                    echo json_encode($bid_arr);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        "message" => "Unable to Fetch the latest bid"
                    ]);
                }
            } else {
                http_response_code(402);
                echo json_encode([
                    "message" => "Still No Bid Placed"
                ]);
            }
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Invalid Auction, Unable to fetch any bid"
            ]);
        }
    }

}