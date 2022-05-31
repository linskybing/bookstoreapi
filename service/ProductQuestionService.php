<?php

namespace Service;

use Model\ProductQuestion;
use PDO;

class ProductQuestionService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ProductQuestion();
    }

    //讀取
    public function read($productid)
    {

        $query = "SELECT pq.*,
                        c.Image AS UserImage,
                        c.Name AS UserName,
                        s.Image AS SellerImage,
                        s.Name AS SellerName 
                    FROM productquestion pq,
                        product p,
                        users s,
                        users c
                    WHERE pq.ProductId = " . $productid . " AND
                        p.ProductId = pq.ProductId AND
                        p.Seller = s.Account AND
                        pq.Customer = c.Account
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
                    'QuestionId' => $QuestionId,
                    'ProductId' => $ProductId,
                    'Content' => $Content,
                    'PostTime' => $PostTime,
                    'Customer' => $Customer,
                    'UserName' => $UserName,
                    'UserImage' => $UserImage,
                    'Reply' => $Reply,
                    'ReplyTime' => $ReplyTime,
                    'Seller' => $Seller,
                    'SellerName' => $SellerName,
                    'SellerImage' => $SellerImage,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未擁有任何問題';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($QuestionId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE QuestionId = " . $QuestionId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'QuestionId' => $QuestionId,
                'ProductId' => $ProductId,
                'Content' => $Content,
                'PostTime' => $PostTime,
                'Customer' => $Customer,
                'Reply' => $Reply,
                'ReplyTime' => $ReplyTime,
                'Seller' => $Seller,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );
            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '問題不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (ProductId,
                           Content,
                           PostTime,
                           Customer,
                           CreatedAt,
                           UpdatedAt) 
                           VALUES ( ? , ? , ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['ProductId'],
            $data['Content'],
            $time,
            $data['Customer'],
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
    public function update($QuestionId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($QuestionId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($QuestionId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE QuestionId = " . $QuestionId . ";";
        return $query;
    }

    //刪除
    public function delete($QuestionId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE QuestionId = " . $QuestionId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
