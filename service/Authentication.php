<?php

namespace Service;

use auth\Jwt;
use PDO;

class Authentication
{
    public static function isAuth()
    {
        $headers = apache_request_headers();

        if (isset($headers['Authorization']) && $data = Jwt::verifyToken($headers['Authorization'])) {

            return $data['Account'];
        } else {
            $error['error'] = '未通過權限驗證';
            return $error;
        }
    }

    public static function getPayload()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {

            $data = Jwt::verifyToken($headers['Authorization']);
            return $data;
        } else {
            $error['error'] = '未通過權限驗證';
            return $error;
        }
    }

    public static function hasPermission($function, $roleid)
    {
        $db_user = 'root';
        $db_password = '';
        $db_name = 'secondhandmarket';

        $db = new PDO('mysql:host=127.0.0.1;dbname=' . $db_name . ';charset=utf8', $db_user, $db_password);
        $permission = new RolePermissionsService($db);

        return $permission->hasPermission($function, $roleid);
    }

    public static function isCreator($craetor, $user)
    {
        if ($craetor == $user) return false;
        return true;
    }
}
