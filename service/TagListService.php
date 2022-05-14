<?php

namespace Service;


use Model\TagList;
use PDO;

class TagListService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new TagList();
    }

    //讀取
    public function read($product)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE ProductId = ' . $product . ' AND DeletedAt IS NULL';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'Id' => $Id,
                    'CategoryId' => $CategoryId,
                    'ProductId' => $ProductId,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有商品標籤';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($Id)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE Id = " . $Id . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'Id' => $Id,
                'CategoryId' => $CategoryId,
                'ProductId' => $ProductId,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '標籤不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (CategoryId, 
                           ProductId,                                                                                                               
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);
        
        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['CategoryId'],
            $data['ProductId'],
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
    public function update($Id, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($Id, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($Id, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE Id = " . $Id . ";";
        return $query;
    }

    //刪除
    public function delete($Id)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'DELETE FROM ' . $this->obj->table . " WHERE Id = " . $Id . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}