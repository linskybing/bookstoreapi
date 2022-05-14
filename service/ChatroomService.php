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
    public function readseller($user)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . " WHERE Seller = '" . $user . "' AND DeletedAt IS NULL";

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
                    'User' => $User,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚無聊天室列表';
        }

        return $response_arr;
    }

    //讀取
    public function readcustomer($user)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . " WHERE User = '" . $user . "' AND DeletedAt IS NULL";

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
                    'User' => $User,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
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
            $response_arr['info'] = '商品不存在';
            return $response_arr;
        }
    }

    //上傳商品
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
}
