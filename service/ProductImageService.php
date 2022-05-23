<?php

namespace Service;

use Model\ProductImage;
use PDO;

class ProductImageService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ProductImage();
    }

    //讀取
    public function read($id)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE ProductId = ' . $id;

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ImageId' => $ImageId,
                    'Image' => $Image,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($ImageId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE ImageId = " . $ImageId . " ;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ImageId' => $ImageId,
                'Image' => $Image,
            );
            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '圖片不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table .
            "(ProductId,
                           Image,
                           CreatedAt) 
                           VALUES ( ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['ProductId'],
            $data['Image'],
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

    //刪除
    public function delete($ImageId)
    {

        $query = 'DELETE FROM ' . $this->obj->table . " WHERE ImageId = " . $ImageId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            $response_arr['info'] = '圖片刪除成功';
            return $response_arr;
        } else {
            $response_arr['error'] = '資料刪除失敗';
            return $response_arr;
        }
    }

    //取得最後編號
    public function getlastnum()
    {
        $query = 'SELECT ImageId FROM ' . $this->obj->table . ' ORDER BY ImageId DESC';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            return ($ImageId + 1);
        } else {
            return (1);
        }
    }
}
