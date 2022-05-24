<?php

namespace Service;

use Model\ChatRoom;
use PDO;

class ChatRoomService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ChatRoom();
    }

    //讀取
    public function readseller($user, $search)
    {
        if ($search != "null") {
            $query = "
            SELECT RoomId ,
                seller.Name AS Seller,                
                seller.Active AS SellerActive,
                u.Name AS User,
                u.Image AS UserImage,
                u.Active AS UserActive,
                Message ,
                chat.CreatedAt     
            FROM (SELECT c.RoomId,
                c.Seller,
                c.`User`,
                rc.Message ,
                rc.CreatedAt
                FROM chatroom c
                LEFT JOIN (SELECT * 
                        FROM recordchat r
                        WHERE r.CreatedAt >=
                        ALL(SELECT CreatedAt
                            FROM recordchat rc2
                            WHERE r.RoomId = rc2.RoomId))rc
                ON c.RoomId = rc.RoomId) chat ,
                users seller ,
                users u
            WHERE chat.Seller = seller.Account AND
                chat.`User` = u.Account AND
                chat.`Seller` = '" . $user . "' AND
                u.Name = '" . $search . "'
            ORDER BY CreatedAt DESC , 
                User
            ";
        } else {
            $query = "
            SELECT RoomId ,
                   seller.Name AS Seller,                   
                   seller.Active AS SellerActive,
                   u.Name AS User,
                   u.Image AS UserImage,
                   u.Active AS UserActive,
                   Message ,
                  chat.CreatedAt     
            FROM (SELECT c.RoomId,
                c.Seller,
                c.`User`,
                rc.Message ,
                rc.CreatedAt
                FROM chatroom c
                LEFT JOIN (SELECT * 
                        FROM recordchat r
                        WHERE r.CreatedAt >=
                        ALL(SELECT CreatedAt
                            FROM recordchat rc2
                            WHERE r.RoomId = rc2.RoomId))rc
                ON c.RoomId = rc.RoomId) chat ,
                users seller ,
                users u
            WHERE chat.Seller = seller.Account AND
                  chat.`User` = u.Account AND
                  chat.`Seller` = '" . $user . "'                  
            ORDER BY CreatedAt DESC , 
                  User
            ";
        }

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'RoomId' => $RoomId,
                    'Seller' => $Seller,
                    'SellerActive' => $SellerActive,
                    'User' => $User,
                    'UserImage' => $UserImage,
                    'UserActive' => $SellerActive,
                    'Message' => $Message,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚無聊天室列表';
        }

        return $response_arr;
    }

    //讀取
    public function readcustomer($user, $search)
    {
        if ($search != "null") {
            $query = "
            SELECT RoomId ,
                   seller.Name AS Seller,
                    seller.Image AS SellerImage,
                   seller.Active AS SellerActive,
                   u.Name AS User,
                   u.Active AS UserActive,
                   Message ,
                  chat.CreatedAt     
            FROM (SELECT c.RoomId,
                    c.Seller,
                    c.`User`,
                    rc.Message ,
                    rc.CreatedAt
                    FROM chatroom c
                    LEFT JOIN (SELECT * 
                            FROM recordchat r
                            WHERE r.CreatedAt >=
                            ALL(SELECT CreatedAt
                                FROM recordchat rc2
                                WHERE r.RoomId = rc2.RoomId))rc
                    ON c.RoomId = rc.RoomId) chat ,
                    users seller ,
                    users u
            WHERE chat.Seller = seller.Account AND
                  chat.`User` = u.Account AND
                  chat.`User` = '" . $user . "' AND
                  seller.Name = '" . $search . "'
            ORDER BY CreatedAt DESC , 
                  Seller
            ";
        } else {
            $query = "
            SELECT RoomId ,
                seller.Name AS Seller,
                seller.Image AS SellerImage,
                seller.Active AS SellerActive,
                u.Name AS User,
                u.Active AS UserActive,
                Message ,
                chat.CreatedAt     
            FROM (SELECT c.RoomId,
                c.Seller,
                c.`User`,
                rc.Message ,
                rc.CreatedAt
                FROM chatroom c
                LEFT JOIN (SELECT * 
                        FROM recordchat r
                        WHERE r.CreatedAt >=
                        ALL(SELECT CreatedAt
                            FROM recordchat rc2
                            WHERE r.RoomId = rc2.RoomId))rc
                ON c.RoomId = rc.RoomId) chat ,
                users seller ,
                users u             
            WHERE chat.Seller = seller.Account AND
                chat.`User` = u.Account AND
                chat.`User` = '" . $user . "'
            ORDER BY CreatedAt DESC , 
                Seller
            ";
        }


        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'RoomId' => $RoomId,
                    'Seller' => $Seller,
                    'SellerImage' => $SellerImage,
                    'SellerActive' => $SellerActive,
                    'User' => $User,
                    'SellerActive' => $SellerActive,
                    'Message' => $Message
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚無聊天室列表';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($RoomId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE RoomId = " . $RoomId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'RoomId' => $RoomId,
                'Seller' => $Seller,
                'User' => $User,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '聊天室不存在';
            return $response_arr;
        }
    }

    //讀取單筆資料
    public function read_single_user($seller, $user)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE Seller = '" . $seller . "' AND User = '" . $user . "' AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'RoomId' => $RoomId,
                'Seller' => $Seller,
                'User' => $User,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '聊天室不存在';
            return $response_arr;
        }
    }

    //上傳
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table .
            "(Seller, 
                           User,
                           CreatedAt,
                           UpdatedAt) 
                           VALUES ( ? , ? , ? , ?)";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        if ($this->isCreateChatRoom($data['Seller'], $data['User'])) {
            $result = $stmt->execute(array(
                $data['Seller'],
                $data['User'],
                $time,
                $time
            ));

            if ($result) {

                $id = $this->conn->lastInsertId();
                $response_arr = $this->read_single($id);
            } else {

                $response_arr['error'] = '資料新增失敗';
            }
        } else {

            $response_arr['data'] = $this->read_single_user($data['Seller'], $data['User']);
        }

        return $response_arr;
    }

    //更新商品
    public function update($RoomId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($RoomId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($RoomId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE RoomId = " . $RoomId . ";";
        return $query;
    }

    //刪除
    public function delete($RoomId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE RoomId = " . $RoomId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }

    //判斷是否為聊天室人員
    public function ischatroomuser($id, $user)
    {

        $data = $this->read_single($id);

        if (isset($data['RoomId'])) {
            if ($user == $data['Seller'] || $user == $data['User']) return true;
            return false;
        }
        return false;
    }

    //檢查是否已經建立聊天室
    public function isCreateChatRoom($seller, $user)
    {
        $query = "
        SELECT * FROM 
        chatroom
        WHERE Seller = '" . $seller . "' AND
        User = '" . $user . "'
        LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();
        if ($stmt->rowCount() > 0) {
            return false;
        } else {
            return true;
        }
    }
}
