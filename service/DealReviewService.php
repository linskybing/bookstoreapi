<?php

namespace Service;

use Model\DealReview;
use PDO;

class DealReviewService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new DealReview();
    }

    //讀取
    public function readbyproduct($id)
    {

        $query = "SELECT dr.* ,u.Image,u.Name
                    FROM RecordDeal rd,
                        DealReview dr,
                        ShoppingList sl,
                        shoppingcart sc,
                        users u
                    WHERE rd.RecordId = dr.RecordId AND
                        rd.ShoppingId = sl.ShoppingId AND 
                        sc.CartId = sl.CartId AND 
                        sc.Member = u.Account AND
                        sl.ProductId = " . $id;

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ReviewId' => $ReviewId,
                    'RecordId' => $RecordId,
                    'Name' => $Name,
                    'CustomerScore' => $CustomerScore,
                    'CustomerReview' => $CustomerReview,
                    'Image' => $Image,
                    'CustomerTime' => $CustomerTime,
                    'SellerScore' => $SellerScore,
                    'SellerReview' => $SellerReview,
                    'SellerTime' => $SellerTime,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }

    public function readbydeal($id)
    {
        $query = "SELECT * 
        FROM  DealReview          
        WHERE RecordId = " . $id;

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);
            $data_item = array(
                'ReviewId' => $ReviewId,
                'RecordId' => $RecordId,
                'CustomerScore' => $CustomerScore,
                'CustomerReview' => $CustomerReview,
                'CustomerTime' => $CustomerTime,
                'SellerScore' => $SellerScore,
                'SellerReview' => $SellerReview,
                'SellerTime' => $SellerTime,
            );
            $response_arr = $data_item;
        } else {
            $response_arr['info'] = '交易尚未擁有評價';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($ReviewId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE ReviewId = " . $ReviewId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ReviewId' => $ReviewId,
                'RecordId' => $RecordId,
                'CustomerScore' => $CustomerScore,
                'CustomerReview' => $CustomerReview,
                'CustomerTime' => $CustomerTime,
                'SellerScore' => $SellerScore,
                'SellerReview' => $SellerReview,
                'SellerTime' => $SellerTime,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '評價不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (RecordId, 
                           CustomerScore,
                           CustomerReview,
                           CustomerTime) 
                  VALUES ( ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['RecordId'],
            $data['CustomerScore'],
            $data['CustomerReview'],
            $time,
        ));

        if ($result) {

            $id = $this->conn->lastInsertId();
            $response_arr = $this->read_single($id);
        } else {

            $response_arr['error'] = '資料新增失敗';
        }
        return $response_arr;
    }

    //上傳商品
    public function post2($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (RecordId, 
                           SellerScore,
                           SellerReview,
                           SellerTime) 
                  VALUES ( ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['RecordId'],
            $data['SellerScore'],
            $data['SellerReview'],
            $time,
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
    public function update($ReviewId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ReviewId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ReviewId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , SellerTime = '" . date('Y-m-d H:i:s') . "' WHERE ReviewId = " . $ReviewId . ";";
        return $query;
    }

    //刪除
    public function delete($ReviewId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ReviewId = " . $ReviewId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
