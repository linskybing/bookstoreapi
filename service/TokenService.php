<?php

namespace Service;

use auth\Jwt;
use Model\UserToken;
use PDO;



class TokenService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->jwt = new Jwt;
        $this->obj = new UserToken();
    }

    public function generatetoken($data)
    {
        date_default_timezone_set('Asia/Taipei');
        $token = $this->jwt->gettoken($data);

        $query = "INSERT INTO " . $this->obj->table . "
                            (Account ,
                             Token ,
                             LastAccessAt ,
                             CreatedAt ,
                             UpdatedAt,
                             ExpiredAt)                              
                  VALUES( ? , ? , ? , ? , ? , ? );";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute(array(
            $data['Account'],
            $token,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', $data['exp'])
        ));

        return $token;
    }

    //更新token
    public function refreshtoken($data)
    {
        date_default_timezone_set('Asia/Taipei');
        $token = $this->jwt->gettoken($data);

        $query = "UPDATE " . $this->obj->table . "
                  SET Token = ? ,
                      UpdatedAt = ? ,
                      ExpiredAt = ?
                  WHERE Account = ?;";
        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute(array(
            $data['Account'],
            $token,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', $data['exp'])
        ));

        return $token;
    }

    public function gettoken($Account, $isVerify)
    {
        $query = "SELECT Token FROM " . $this->obj->table . " WHERE Account = ? ;";
        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute(array(
            $Account
        ));

        if ($stmt->rowCount() > 0) {
            $this->updateaccess($Account);
            if ($isVerify) {

                $response_arr = array();
                $response_arr['data'] = array();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                extract($row);

                $data = array(
                    'token' => $Token,
                );
            } else {
                $userdata = $this->getuserdata($Account);

                $token = $this->refreshtoken($userdata);

                $data  = array(
                    'token' => $token
                );
            }

            $response_arr['data'] = $data;
        } else {
            $data = $this->getuserdata($Account);

            $token = $this->generatetoken($data);

            return $response_arr['data'] = array(
                'token' => $token
            );
        }
        return $response_arr['data'];
    }

    //取得使用者資訊
    public function getuserdata($Account)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = "SELECT users.Account , users.Name , UserRoleId , CartId ,users.Image
                  FROM users
                  LEFT JOIN userrole 
                  ON users.Account = userrole.User
                  LEFT JOIN shoppingcart 
                  ON users.Account = shoppingcart.Member
                  WHERE users.Account = '" . $Account . "';";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);

        $data = array(
            'Account' => $Account,
            'Name' => $Name,
            'RoleId' => $UserRoleId,
            'CartId' => $CartId,
            'Image' => $Image,
            'iat' => time(),
            'exp' => strtotime(date('Y-m-d H:i:s') . '+ 1days'),
        );

        return $data;
    }

    //更新存取時間
    public function updateaccess($Account)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = "UPDATE usertoken
                  SET LastAccessAT = :LastAccessAt,
                      UpdatedAt = :UpdateAt 
                  WHERE Account = :Account;";

        $stmt  = $this->conn->prepare($query);

        $stmt->bindValue(":LastAccessAt", date('Y-m-d H:i:s'));
        $stmt->bindValue(":UpdateAt", date('Y-m-d H:i:s'));
        $stmt->bindValue(":Account", $Account);

        $result = $stmt->execute();

        return $result;
    }
}
