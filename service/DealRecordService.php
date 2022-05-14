<?php

namespace Service;


use Model\RecordDeal;
use PDO;

class DealRecordService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new RecordDeal();
    }

    //讀取
    public function read($cartid, $state)
    {

        $query = "SELECT * FROM RecordDeal r , ShoppingList s WHERE r.ShoppingId = s.ShoppingId AND CartId = " . $cartid . " AND r.State = '" . urldecode($state) . "'";
        
        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'RecordId' => $RecordId,
                    'ShoppingId' => $ShoppingId,
                    'State' => $State,
                    'DealMethod' => $DealMethod,
                    'SentAddress' => $SentAddress,
                    'DealType' => $DealType,
                    'StartTime' => $StartTime,
                    'EndTime' => $EndTime,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有交易紀錄';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($RecordId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE RecordId = " . $RecordId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'RecordId' => $RecordId,
                'ShoppingId' => $ShoppingId,
                'State' => $State,
                'DealMethod' => $DealMethod,
                'SentAddress' => $SentAddress,
                'DealType' => $DealType,
                'StartTime' => $StartTime,
                'EndTime' => $EndTime,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '交易紀錄不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (ShoppingId, 
                           State,
                           DealMethod,  
                           SentAddress,
                           DealType,                                                                 
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ? , ? , ?)";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['ShoppingId'],
            $data['State'],
            $data['DealMethod'],
            $data['SentAddress'],
            $data['DealType'],
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
    public function update($RecordId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($RecordId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($RecordId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE RecordId = " . $RecordId . ";";
        return $query;
    }

    //刪除
    public function delete($RecordId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE RecordId = " . $RecordId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
