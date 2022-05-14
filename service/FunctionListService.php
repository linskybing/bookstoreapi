<?php

namespace Service;

use Model\FunctionList;
use PDO;

class FunctionListService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new FunctionList();
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
                    'FunctionId' => $FunctionId,
                    'FunctionName' => $FunctionName,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有功能';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($FunctionId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE FunctionId = " . $FunctionId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'FunctionId' => $FunctionId,
                'FunctionName' => $FunctionName,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '功能不存在';
            return $response_arr;
        }
    }

    //上傳
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (FunctionName,                                                      
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['FunctionName'],
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

    //更新
    public function update($FunctionId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($FunctionId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($FunctionId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE FunctionId = " . $FunctionId . ";";
        return $query;
    }

    //刪除
    public function delete($FunctionId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE FunctionId = " . $FunctionId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            $response_arr['info'] = '資料刪除成功';
            return $response_arr;
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
