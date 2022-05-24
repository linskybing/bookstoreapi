<?php

namespace Service;

use Model\RecordChat;
use PDO;

class ChatRecordService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new RecordChat();
    }

    //讀取
    public function read($roomid, $nowpage, $itemnum)
    {

        $query = 'SELECT c.* , u.Image
                  FROM ' . $this->obj->table . ' c , users u
                  WHERE RoomId = ' . $roomid . ' AND u.Account =  c.Creator
                  ORDER BY CreatedAt DESC 
                  LIMIT ' . (($nowpage - 1) * $itemnum) . ',' . $nowpage * $itemnum . ';';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();

        $response_arr = array();
        $detail = $this->read_single($roomid);
        if (isset($detail['RoomId'])) array_push($response_arr, $detail);

        if ($num > 0) {
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ChatId' => $ChatId,
                    'RoomId' => $RoomId,
                    'Creator' => $Creator,
                    'Message' => $Message,
                    'Image' => $Image,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }

    //取得留言數
    public function GetChatCount($roomid)
    {
        $query = 'SELECT c.* , u.Image
        FROM ' . $this->obj->table . ' c , users u
        WHERE RoomId = ' . $roomid . ' AND u.Account =  c.Creator
        ORDER BY CreatedAt DESC ';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();

        return $num;
    }

    //refresh chat

    public function refresh($roomid, $time)
    {
        $query = "SELECT c.* , u.Image
                  FROM " . $this->obj->table . " c , users u
                  WHERE RoomId = " . $roomid . " AND 
                        u.Account =  c.Creator AND 
                        c.CreatedAt > '" . $time . "'
                  ORDER BY CreatedAt DESC";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ChatId' => $ChatId,
                    'RoomId' => $RoomId,
                    'Creator' => $Creator,
                    'Message' => $Message,
                    'Image' => $Image,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未更新';
        }

        return $response_arr;
    }

    //讀取最後留言時間
    public function read_last_time($roomid)
    {
        $query = "SELECT MAX(CreatedAt) AS CreatedAt
                  FROM recordchat c
                  WHERE RoomId = " . $roomid;

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            return  $CreatedAt;
        } else {

            return null;
        }
    }

    //讀取單筆資料
    public function read_single($Id)
    {
        $query = "SELECT c.RoomId ,
                         a.Name AS Seller , 
                         a.Image AS SellerImage,
                         a.Active AS SellerActive,
                         b.Name AS User,                        
                         b.Image AS UserImage,
                         b.Active AS UserActive
                  FROM chatroom c ,users a , users b 
                  WHERE RoomId = " . $Id . " AND c.Seller = a.Account AND c.User = b.Account";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'RoomId' => $RoomId,
                'Seller' => $Seller,
                'SellerActive' => $SellerActive,
                'SellerImage' => $SellerImage,
                'User' => $User,
                'UserImage' => $UserImage,
                'UserActive' => $UserActive,
                'CreatedAt' => $this->read_last_time($RoomId)
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '資訊不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (RoomId, 
                           Creator,
                           Message,                                                                                             
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['RoomId'],
            $data['Creator'],
            $data['Message'],
            $time,
            $time
        ));

        if ($result) {

            $response_arr['info'] = '新增成功';
        } else {

            $response_arr['error'] = '資料新增失敗';
        }
        return $response_arr;
    }

    //更新商品
    public function update($ChatId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ChatId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ChatId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE ChatId = " . $ChatId . ";";
        return $query;
    }

    //刪除
    public function delete($ChatId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ChatId = " . $ChatId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
