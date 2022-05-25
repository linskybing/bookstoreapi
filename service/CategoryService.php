<?php

namespace Service;

use Model\Category;
use PDO;

class CategoryService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new Category();
    }

    //讀取
    public function read()
    {

        $query = 'SELECT c.*,COUNT(t.CategoryId) AS Count
                    FROM category c
                    LEFT JOIN taglist t	  
                    ON c.CategoryId = t.CategoryId
                    GROUP BY c.CategoryId';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'CategoryId' => $CategoryId,
                    'Tag' => $Tag,
                    'Color' => $Color,
                    'Count' => $Count
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有分類';
        }

        return $response_arr;
    }

    //
    public function read_tag()
    {
        $query = "SELECT c.*,COUNT(t.CategoryId) AS Count
                  FROM category c
                  LEFT JOIN (SELECT tl.*
                             FROM taglist tl,
                                  product pl
                             WHERE tl.ProductId =  pl.ProductId AND
                                   Rent > 0)t	  
                    ON c.CategoryId = t.CategoryId
                    GROUP BY c.CategoryId
        ";
        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'CategoryId' => $CategoryId,
                    'Tag' => $Tag,
                    'Color' => $Color,
                    'Count' => $Count
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有分類';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($CategoryId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE CategoryId = " . $CategoryId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'CategoryId' => $CategoryId,
                'Tag' => $Tag,
                'Color' => $Color,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '種類不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table .
            "(Tag,                          
                           CreatedAt,
                           UpdatedAt) 
                           VALUES ( ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Tag'],
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
    public function update($CategoryId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($CategoryId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($CategoryId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE CategoryId = " . $CategoryId . ";";
        return $query;
    }

    //刪除
    public function delete($CategoryId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE CategoryId = " . $CategoryId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
