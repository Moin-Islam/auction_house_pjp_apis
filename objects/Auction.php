<?php

class Auction
{
    private $conn;
    private $table_name = "auctions";
    public $auction_id;
    public $seller_id;
    public $item_name;
    public $item_description;
    public $item_image;
    public $category;
    public $starting_price;
    public $reserve_price;
    public $end_date;
    public $current_bid;
    public $is_active;
    public $created_at;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function CreateAuction()
    {

        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->seller_id) && !empty($data->item_name) && !empty($data->item_description) && !empty($data->item_image) && !empty($data->category) && !empty($data->starting_price) && !empty($data->reserve_price)
            && !empty($data->end_date) && !empty($data->current_bid) && !empty($data->is_active)
        ) {
            $this->seller_id = $data->seller_id;
            $this->item_name = $data->item_name;
            $this->item_description = $data->item_description;
            $this->item_image = $data->item_image;
            $this->category = $data->category;
            $this->starting_price = $data->starting_price;
            $this->reserve_price = $data->reserve_price;
            $this->end_date = $data->end_date;
            $this->current_bid = $data->current_bid;
            $this->is_active = $data->is_active;
        }

        $query = "INSERT INTO $this->table_name(seller_id,item_name,item_description,item_image,category,starting_price,reserve_price, end_date, current_bid, is_active)
        VALUES (:seller_id, :item_name, :item_description, :item_image, :category, :starting_price, :reserve_price, :end_date, :current_bid, :is_active)";
        $stmt = $this->conn->prepare($query);

        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->item_description = htmlspecialchars(strip_tags($this->item_description));
        $this->item_image = htmlspecialchars(strip_tags($this->item_image));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->starting_price = htmlspecialchars(strip_tags($this->starting_price));
        $this->reserve_price = htmlspecialchars(strip_tags($this->reserve_price));
        $this->end_date = htmlspecialchars(strip_tags($this->end_date));
        $this->current_bid = htmlspecialchars(strip_tags($this->current_bid));
        $this->is_active = htmlspecialchars(strip_tags($this->is_active));


        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":item_description", $this->item_description);
        $stmt->bindParam(":item_image", $this->item_image);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":starting_price", $this->starting_price);
        $stmt->bindParam(":reserve_price", $this->reserve_price);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":current_bid", $this->current_bid);
        $stmt->bindParam(":is_active", $this->is_active);

        try {
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "New Auction Created"
            ]);
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Unable to create new auction"
            ]);
        }
    }

    public function FetchActiveAuctions()
    {
        $query = "SELECT * FROM $this->table_name WHERE is_active = 1";
        $stmt = $this->conn->prepare($query);

        try {
            $stmt->execute();
            $num = $stmt->rowcount();
            if ($num > 0) {
                $auction_arr = [];
                $auction_arr["records"] = array();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);

                    $auction_list = [
                        "auction_id" => $auction_id,
                        "seller_id" => $seller_id,
                        "item_name" => $item_name,
                        "item_description" => $item_description,
                        "item_image" => $item_image,
                        "category" => $category,
                        "starting_price" => $starting_price,
                        "reserve_price" => $reserve_price,
                        "end_date" => $end_date,
                        "current_bid" => $current_bid,
                        "is_active" => $is_active,
                        "created_at" => $created_at
                    ];
                    array_push($auction_arr["records"], $auction_list);
                }
                http_response_code(200);
                /*print_r($auction_arr);
                die;*/
                echo json_encode($auction_arr);
            } else {
                http_response_code(405);
                echo json_encode([
                    "message" => "No Auction is active"
                ]);
            }
        } catch (e) {
            http_response_code(405);
            echo json_encode([
                "message" => "Invalid Connection"
            ]);
        }

    }

    public function FetchItemDetails()
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
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->auction_id = $row["auction_id"];
                $this->seller_id = $row["seller_id"];
                $this->item_name = $row["item_name"];
                $this->item_description = $row["item_description"];
                $this->item_image = $row["item_image"];
                $this->category = $row["category"];
                $this->starting_price = $row["starting_price"];
                $this->reserve_price = $row["reserve_price"];
                $this->end_date = $row["end_date"];
                $this->current_bid = $row["current_bid"];
                $this->is_active = $row["is_active"];
                $this->created_at = $row["created_at"];

                if ($this->seller_id != null) {
                    $auction_arr = [
                        "auction_id" => $this->auction_id,
                        "item_name" => $this->item_name,
                        "item_description" => $this->item_description,
                        "item_image" => $this->item_image,
                        "category" => $this->category,
                        "starting_price" => $this->starting_price,
                        "reserve_price" => $this->reserve_price,
                        "end_date" => $this->end_date,
                        "current_bid" => $this->current_bid,
                        "is_active" => $this->is_active,
                        "created_at" => $this->created_at
                    ];

                    http_response_code(200);
                    echo json_encode($auction_arr);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        "message" => "No such auction exists"
                    ]);
                }
            }
        } catch (e) {
            http_response_code(404);
            echo json_encode([
                "message" => "Invalid connection please try again later"
            ]);
        }
    }

    public function EndAuction()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->auction_id)) {
            $this->auction_id = $data->auction_id;
        }

        $query = "UPDATE $this->table_name SET is_active = 0 WHERE auction_id = :auction_id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":auction_id", $this->auction_id);

        try{
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "Successfully canceled the auction"
            ]);
        } catch (e)
        {
            http_response_code(400);
            echo json_encode([
                "message" => "Unable to cancel the auction check your connection"
            ]);
        }
    }

}