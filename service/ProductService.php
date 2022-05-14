<?php

namespace Service;

use Model\Product;
use PDO;

class ProductService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new Product();
    }

    //讀取
    public function read()
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE DeletedAt IS NULL';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProductId' => $ProductId,
                    'Name' => $Name,
                    'Description' => $Description,
                    'Price' => $Price,
                    'Inventory' => $Inventory,
                    'State' => $State,
                    'Seller' => $Seller,
                    'Watch' => $Watch,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有商品';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($ProductId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE ProductId = " . $ProductId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ProductId' => $ProductId,
                'Name' => $Name,
                'Description' => $Description,
                'Price' => $Price,
                'Inventory' => $Inventory,
                'State' => $State,
                'Seller' => $Seller,
                'Watch' => $Watch,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
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

        $query = "INSERT INTO Product
                              (Name,
                               Description,
                               Price,
                               Inventory,                               
                               Seller,                               
                               CreatedAt,
                               UpdatedAt)
                           VALUES ( ? , ? , ? , ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Name'],
            $data['Description'],
            $data['Price'],
            $data['Inventory'],
            $data['Seller'],
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
    public function update($ProductId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ProductId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ProductId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProductId = " . $ProductId . ";";
        return $query;
    }

    //刪除
    public function delete($ProductId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProductId = " . $ProductId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }


}
