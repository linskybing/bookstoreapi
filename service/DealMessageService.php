<?php

namespace Service;

use Model\DealMessage;
use PDO;

class DealMessageService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new DealMessage();
    }

    //讀取
    public function read($id)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE RecordId = ' . $id;

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'MessageId' => $MessageId,
                    'RecordId' => $RecordId,
                    'Content' => $Content,
                    'Creator' => $Creator,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有留言';
        }

        return $response_arr;
    }

    public function read_cancel($id, $user)
    {
        $query = "SELECT dm.*
                    FROM dealmessage dm,
                        recorddeal rd
                    WHERE dm.RecordId = rd.RecordId AND
                          dm.Creator = '" . $user . "' AND 
                          dm.RecordId = " . $id;
        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'MessageId' => $MessageId,
                'RecordId' => $RecordId,
                'Content' => $Content,
                'Creator' => $Creator,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['data'] = null;
            return $response_arr;
        }
    }

    //讀取單筆資料
    public function read_single($MessageId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE MessageId = " . $MessageId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'MessageId' => $MessageId,
                'RecordId' => $RecordId,
                'Content' => $Content,
                'Creator' => $Creator,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '留言不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (RecordId, 
                           Content,
                           Creator,
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['RecordId'],
            $data['Content'],
            $data['Creator'],
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
    public function update($MessageId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($MessageId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($MessageId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE MessageId = " . $MessageId . ";";
        return $query;
    }

    //刪除
    public function delete($MessageId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE MessageId = " . $MessageId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
