<?php

namespace Service;

use Model\ProblemReply;
use PDO;

class ProblemReplyService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new ProblemReply();
    }

    //讀取
    public function read($ProblemId)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE ProblemId = ' . $ProblemId . ' AND DeletedAt IS NULL';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProblemReply' => $ProblemReply,
                    'ProblemId' => $ProblemId,
                    'Reply' => $Reply,
                    'ReplyUser' => $ReplyUser,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未擁有回覆內容';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($ProblemReply)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE ProblemReply = " . $ProblemReply . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ProblemReply' => $ProblemReply,
                'ProblemId' => $ProblemId,
                'Reply' => $Reply,
                'ReplyUser' => $ReplyUser,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '回覆內容不存在';
            return $response_arr;
        }
    }

    //上傳
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (ProblemId, 
                           Reply,
                           ReplyUser,                                                  
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ?)";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['ProblemId'],
            $data['Reply'],
            $data['ReplyUser'],
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
    public function update($ProblemReply, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ProblemReply, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ProblemReply, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProblemReply = " . $ProblemReply . ";";
        return $query;
    }

    //刪除
    public function delete($ProblemReply)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProblemReply = " . $ProblemReply . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
