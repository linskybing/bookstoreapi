<?php

namespace Service;

use Model\User;
use PDO;


class UserService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new User();
    }
    //讀取資料
    public function read()
    {
        //建立query
        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE DeletedAt IS NULL';
        //prepate statement
        $stmt  = $this->conn->prepare($query);
        //執行query
        $result = $stmt->execute();
        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'Account' => $Account,
                    'Password' => $Password,
                    'Name' => $Name,
                    'Email' => $Email,
                    'EmailVerifiedAt' => $EmailVerifiedAt,
                    'AuthCode' => $AuthCode,
                    'Money' => $Money,
                    'Balance' => $Balance,
                    'Address' => $Address,
                    'Image' => $Image,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                    'DeletedAt' => $DeletedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有會員';
        }

        return $response_arr;
    }
    //讀取當筆資料
    public function read_single($Account)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE Account = '" . $Account . "' AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'Account' => $Account,
                'Password' => $Password,
                'Name' => $Name,
                'Email' => $Email,
                'EmailVerifiedAt' => $EmailVerifiedAt,
                'AuthCode' => $AuthCode,
                'Money' => $Money,
                'Balance' => $Balance,
                'Address' => $Address,
                'Image' => $Image,
                'Active' => $Active,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
                'DeletedAt' => $DeletedAt,
            );
            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '資料不存在';
            return $response_arr;
        }
    }

    //上傳資料
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');
        $data['Password'] = $this->formatpassword($data['Password']);

        $query = $this->getpostsql($data);

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Account'],
            $data['Password'],
            $data['Name'],
            $data['Email'],
            $data['AuthCode'],
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

    //取得上傳data sql 
    public function getpostsql($data)
    {
        $query = "INSERT INTO Users
                              (Account,
                               Password,
                               Name,
                               Email,
                               AuthCode,
                               CreatedAt,
                               UpdatedAt)
                  VALUES ( ? , ? , ? , ? , ? , ? , ?)";
        return $query;
    }

    //hash密碼
    public function formatpassword($password)
    {
        $secret = "ASSADOPIZASNFEWA";
        $password = hash('sha256', $password . $secret);
        return $password;
    }


    //更新資料
    public function update($Account, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($Account, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($Account, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE Account = '" . $Account . "';";
        return $query;
    }

    //刪除
    public function delete($Account)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE Account = '" . $Account . "'";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }

    //檢查密碼是否相同
    public function passwordcheck($account, $password)
    {
        $data = $this->read_single($account);

        $password = $this->formatpassword($password);
        if (isset($data['Password'])) {
            if ($data['Password'] == $password) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    //檢查驗證碼
    public function checkauthcode($account, $auth)
    {
        $data  = $this->read_single($account);
        if (!is_null($data)) {
            if ($data['AuthCode'] == $auth) {
                $result = $this->update($account, array('AuthCode' => ''));
                $response['info'] = '驗證成功';
            } else {

                $response['error'] = '驗證碼錯誤';
            }
        } else {

            $response['error'] = '帳號不存在';
        }
        return $response;
    }


    //檢查帳號
    public function accountcheck($account)
    {
        $data = $this->read_single($account);
        if (isset($data['info'])) {
            return true;
        } else {
            return false;
        }
    }
}
