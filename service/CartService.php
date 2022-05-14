<?php

namespace Service;


use Model\ShoppingCart;
use PDO;

class CartService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ShoppingCart();
    }

    //讀取
    public function read($user)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . " WHERE Member = '" . $user . "' AND DeletedAt IS NULL";
        
        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'CartId' => $CartId,
                    'Member' => $Member,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt
                );
                $response_arr = $data_item;
            }
        } else {
            $response_arr = $this->post(array('Member' => $user));
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($CartId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE CartId = " . $CartId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'CartId' => $CartId,
                'Member' => $Member,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt
            );
            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '購物車不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (Member,                          
                           CreatedAt,
                           UpdatedAt) 
                           VALUES ( ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Member'],
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
    public function update($CartId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($CartId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($CartId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE CartId = " . $CartId . ";";
        return $query;
    }

    //刪除
    public function delete($CartId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE CartId = " . $CartId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
