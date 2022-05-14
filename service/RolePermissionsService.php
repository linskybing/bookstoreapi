<?php

namespace Service;


use Model\RolePermissions;
use PDO;

class RolePermissionsService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new RolePermissions();
    }

    //讀取
    public function read($roleid)
    {

        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE RoleId = ' . $roleid . ' AND  DeletedAt IS NULL';

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'PermissionId' => $PermissionId,
                    'RoleId' => $RoleId,
                    'FunctionId' => $FunctionId,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未擁有後臺權限';
        }

        return $response_arr;
    }

    //讀取單筆資料
    public function read_single($PermissionId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE PermissionId = " . $PermissionId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'PermissionId' => $PermissionId,
                'RoleId' => $RoleId,
                'FunctionId' => $FunctionId,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '權限不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (RoleId, 
                           FunctionId,                                                                                                               
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['RoleId'],
            $data['FunctionId'],
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
    public function update($PermissionId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($PermissionId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($PermissionId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE PermissionId = " . $PermissionId . ";";
        return $query;
    }

    //刪除
    public function delete($PermissionId)
    {
        $query = 'DELETE FROM ' . $this->obj->table . " WHERE PermissionId = " . $PermissionId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            $response_arr['info'] = '資料刪除成功';
            return $response_arr;
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }

    //檢查重複新增
    public function checkinsert($roleid, $functionid)
    {
        $query = 'SELECT * FROM ' . $this->obj->table . ' WHERE RoleId = ' . $roleid . ' AND FunctionId = ' . $functionid . ' AND  DeletedAt IS NULL';

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        return ($stmt->rowCount() > 0) ? false : true;
    }

    public function hasPermission($functionname, $roleid)
    {
        if ($roleid != null) {
            $query = "SELECT RoleId , FunctionName
                  FROM functionlist fl,
                       rolepermissions p
                  WHERE fl.FunctionId  = p.FunctionId 
                        AND FunctionName = '" . $functionname . "'
                        AND RoleId = " . $roleid;
        } else {
            $query = "SELECT RoleId , FunctionName
                  FROM functionlist fl,
                       rolepermissions p
                  WHERE fl.FunctionId  = p.FunctionId 
                        AND FunctionName = '" . $functionname . "'";
        }


        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        return ($stmt->rowCount() > 0) ? true : false;
    }
}
