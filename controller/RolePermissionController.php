<?php

namespace Controller;

use Service\Authentication;
use Service\RolePermissionsService;
use Service\Validator;


class RolePermissionController
{
    protected $permisson;
    public function __construct($db)
    {
        $this->permisson = new RolePermissionsService($db);
    }

    public function Get($request, $roleid)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->permisson->read($roleid);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->permisson->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RoleId' => ['required'],
            'FunctionId' => ['required'],
        ), $data);

        if ($validate != '') {
            return $validate;
        } else {
            if ($this->permisson->checkinsert($data['RoleId'], $data['FunctionId'])) {
                $result = $this->permisson->post($data);
                return $result;
            } else {
                return ['error' => '權限已經存在'];
            }
        }
    }


    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->permisson->read_single($id);
        if (isset($data['PermissionId'])) {
            $result = $this->permisson->delete($id);
            return $result;
        } else {
            return ['error' => '權限不存在'];
        }
    }
}
