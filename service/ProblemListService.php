<?php

namespace Service;

use Model\ProblemList;
use PDO;

class ProblemListService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ProblemList();
    }

    //讀取
    public function readbyuser($user, $state)
    {
        switch ($state) {
            case 'p_1':
                $string = '未解決';
                break;
            default:
                $string = '已解決';
                break;
        }

        $query = 'SELECT * FROM ' . $this->obj->table . " WHERE PostUser = '" . $user . "' AND State = '" . $string . "'AND DeletedAt IS NULL";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProblemId' => $ProblemId,
                    'Title' => $Title,
                    'Content' => $Content,
                    'PostUser' => $PostUser,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未詢問任何問題';
        }

        return $response_arr;
    }

    //讀取
    public function readbyadmin($state)
    {
        switch ($state) {
            case 'p_1':
                $string = '未解決';
                break;
            default:
                $string = '已解決';
                break;
        }
        $query = 'SELECT * FROM ' . $this->obj->table . " WHERE State = '" . $string . "' AND DeletedAt IS NULL";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProblemId' => $ProblemId,
                    'Title' => $Title,
                    'Content' => $Content,
                    'PostUser' => $PostUser,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未詢問任何問題';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($ProblemId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE ProblemId = " . $ProblemId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ProblemId' => $ProblemId,
                'Title' => $Title,
                'Content' => $Content,
                'PostUser' => $PostUser,
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

        $query = "INSERT INTO " . $this->obj->table . "
                           (Title, 
                           Content,
                           PostUser,                                                  
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ?)";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Title'],
            $data['Content'],
            $data['PostUser'],
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
    public function update($ProblemId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ProblemId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ProblemId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProblemId = " . $ProblemId . ";";
        return $query;
    }

    //刪除
    public function delete($ProblemId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProblemId = " . $ProblemId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
