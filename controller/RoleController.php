<?php

namespace Controller;

use Service\Authentication;
use Service\RoleService;
use Service\Validator;


class RoleController
{
    protected $roleservice;
    public function __construct($db)
    {
        $this->roleservice = new RoleService($db);
    }

    public function Get($request)
    {        
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->roleservice->read();
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->roleservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RoleName' => ['required'],
        ), $data);

        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->roleservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RoleName' => ['required'],
        ), $data);

        $datarole = $this->roleservice->read_single($id);
        if (isset($datarole['RoleName'])) {

            if ($validate != '') {
                return $validate;
            } else {
                $result['info'] = $this->roleservice->update($id, $data);
                return $result;
            }
        } else {
            return ['error' => '角色不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->roleservice->read_single($id);
        if (isset($data['RoleName'])) {
            $data['info'] = $this->roleservice->delete($id);
            return $data;
        } else {
            return ['error' => '角色不存在'];
        }
    }
}
