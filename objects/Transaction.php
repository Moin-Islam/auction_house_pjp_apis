<?php

class Transaction
{
    private $conn;
    private $table_name = "transactions";
    public $transaction_id;
    public $auction_id;
    public $buyer_id;
    public $seller_id;
    public $transaction_amount;
    public $payment_method;
    public $transaction_date;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function CompleteTransaction()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->auction_id) && !empty($data->buyer_id) && !empty($data->seller_id) && !empty($data->transaction_amount) && !empty($data->payment_method) && !empty($data->transaction_date)) {
            $this->auction_id = $data->auction_id;
            $this->buyer_id = $data->buyer_id;
            $this->seller_id = $data->seller_id;
            $this->transaction_amount = $data->transaction_amount;
            $this->payment_method = $data->payment_method;
            $this->transaction_date = $data->transaction_date;
        }

        $query = "INSERT INTO $this->table_name(auction_id,buyer_id,seller_id,transaction_amount,payment_method,transaction_date)
        VALUES (:auction_id, :buyer_id, :seller_id, :transaction_amount, :payment_method, :transaction_date)";
        $stmt = $this->conn->prepare($query);

        $this->auction_id = htmlspecialchars(strip_tags($this->auction_id));
        $this->buyer_id = htmlspecialchars(strip_tags($this->buyer_id));
        $this->seller_id = htmlspecialchars(strip_tags($this->seller_id));
        $this->transaction_amount = htmlspecialchars(strip_tags($this->transaction_amount));
        $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
        $this->transaction_date = htmlspecialchars(strip_tags($this->transaction_date));

        $stmt->bindParam(":auction_id", $this->auction_id);
        $stmt->bindParam(":buyer_id", $this->buyer_id);
        $stmt->bindParam(":seller_id", $this->seller_id);
        $stmt->bindParam(":transaction_amount", $this->transaction_amount);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":transaction_date", $this->transaction_date);

        try {
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "Transaction Completed"
            ]);
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Unable to complete transaction"
            ]);
        }
    }

    public function FetchTransactionDetails()
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
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->transaction_id  = $row["transaction_id"];
            $this->buyer_id  = $row["buyer_id"];
            $this->seller_id  = $row["seller_id"];
            $this->transaction_amount = $row["transaction_amount"];
            $this->payment_method = $row["payment_method"];
            $this->transaction_date = $row["transaction_date"];

            if ($this->transaction_id != null) {
                $transaction_arr = [
                    "transaction_id" => $this->transaction_id,
                    "buyer_id" => $this->buyer_id,
                    "seller_id" => $this->seller_id,
                    "transaction_amount" => $this->transaction_amount,
                    "payment_method" => $this->payment_method,
                    "transaction_date" => $this->transaction_date,
                ];

                http_response_code(200);
                echo json_encode($transaction_arr);
            } else {
                http_response_code(400);
                echo json_encode([
                    "message" => "Unable to Fetch Transaction Details"
                ]);
            }
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Invalid Transaction for that specific auction"
            ]);
        }
    }
}