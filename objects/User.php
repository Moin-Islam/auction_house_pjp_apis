<?php

class User
{
    private $conn;
    private $table_name = "users";
    public $user_id;
    public $username;
    public $email;
    public $password;
    public $first_name;
    public $last_name;
    public $phone_number;
    public $address;
    public $created_at;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function CreateUser()
    {

        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->username) && !empty($data->email) && !empty($data->password) && !empty($data->first_name) && !empty($data->last_name) && !empty($data->phone_number) && !empty($data->address)) {
            $this->username = $data->username;
            $this->first_name = $data->first_name;
            $this->last_name = $data->last_name;
            $this->email = $data->email;
            $this->password = $data->password;
            $this->phone_number = $data->phone_number;
            $this->address = $data->address;
        }

        $query = "INSERT INTO $this->table_name(username,email,password,first_name,last_name,phone_number,address)
        VALUES (:username, :email, :password, :first_name, :last_name, :phone_number, :address)";
        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->address = htmlspecialchars(strip_tags($this->address));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":address", $this->address);

        try {
            $stmt->execute();
            http_response_code(200);
            echo json_encode([
                "message" => "New User Added"
            ]);
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Unable to create new user"
            ]);
        }
    }

    public function UserAuthentication()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->email) && !empty($data->password)) {
            $this->email = $data->email;
            $this->password = $data->password;
        }

        $query = "SELECT * FROM $this->table_name WHERE email = :email";
        $stmt = $this->conn->prepare($query);

        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));

        $stmt->bindParam(":email", $this->email);

        

        if ($stmt->execute()) {
            $row = $stmt->rowCount();
            if ($row > 0) {
                $fetchQuery = $stmt->fetch(PDO::FETCH_ASSOC);
                $storedPassword = $fetchQuery["password"];
                $userId = $fetchQuery["user_id"];
                if ($this->password == $storedPassword) {
                    http_response_code(200);
                    echo json_encode([
                        "message" => "Successful Login",
                        "code" => 200,
                        "user_id" => $userId
                    ]);
                } else {
                    http_response_code(400);
                    echo json_encode([
                        "message" => "Invalid Password",
                        "code" => 400,
                    ]);
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    "message" => "Invalid Email",
                    "code" => 401,
                ]);
            }
        } else {
            http_response_code(402);
            echo json_encode([
                "message" => "Unsuccessful Login",
                "code" => 402,
            ]);
        }

    }

    public function FetchUserInfo()
    {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->user_id)) {
            $this->user_id = $data->user_id;
        }

        $query = "SELECT * FROM $this->table_name WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $stmt->bindParam(":user_id", $this->user_id);

        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->username = $row["username"];
            $this->email = $row["email"];
            $this->password = $row["password"];
            $this->first_name = $row["first_name"];
            $this->last_name = $row["last_name"];
            $this->phone_number = $row["phone_number"];
            $this->address = $row["address"];

            if ($this->username != null) {
                $customer_arr = [
                    "user_id" => $this->user_id,
                    "username" => $this->username,
                    "email" => $this->email,
                    "password" => $this->password,
                    "first_name" => $this->first_name,
                    "last_name" => $this->last_name,
                    "phone_number" => $this->phone_number,
                    "address" => $this->address
                ];

                http_response_code(200);
                echo json_encode($customer_arr);
            } else {
                http_response_code(400);
                echo json_encode([
                    "message" => "Unable to Fetch User"
                ]);
            }
        } catch (e) {
            http_response_code(401);
            echo json_encode([
                "message" => "Invalid User"
            ]);
        }
    }
}