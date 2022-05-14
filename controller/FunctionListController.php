<?php

namespace Controller;

use Service\Authentication;
use Service\FunctionListService;
use Service\Validator;


class FunctionListController
{
    protected $functionservice;
    public function __construct($db)
    {
        $this->functionservice = new FunctionListService($db);
    }

    public function Get($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->functionservice->read();
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->functionservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];        

        $data = $request->getBody();

        $validate = Validator::check(array(
            'FunctionName' => ['required'],
        ), $data);

        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->functionservice->post($data);
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
            'FunctionName' => ['required'],
        ), $data);

        $data = $this->functionservice->read_single($id);
        if (isset($data['FunctionName'])) {

            if ($validate != '') {
                return $validate;
            } else {
                $result['info'] = $this->functionservice->update($id, $data);
                return $result;
            }
        } else {
            return ['error' => '功能不存在'];
        }
    }

    public function Delete($request, $id)
    {
       $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('權限管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->functionservice->read_single($id);
        if (isset($data['FunctionName'])) {
            $data = $this->functionservice->delete($id);
            return $data;
        } else {
            return ['error' => '功能不存在'];
        }
    }
}
