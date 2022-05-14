<?php

namespace Controller;

use Service\Authentication;
use Service\ProblemListService;
use Service\Validator;


class ProblemListController
{
    protected $problemlistservice;
    public function __construct($db)
    {
        $this->problemlistservice = new ProblemListService($db);
    }

    public function GetByUser($request, $state)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->problemlistservice->readbyuser($auth, $state);
        return $data;
    }

    public function GetByAdmin($request, $state)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('問題回報', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->problemlistservice->readbyadmin($state);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->problemlistservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;
        

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Title' => ['required'],
            'Content' => ['required'],
        ), $data);
        $data['PostUser'] = $auth;
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->problemlistservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('問題回報', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $problem = $this->problemlistservice->read_single($id);
        if (isset($problem['ProblemId'])) {

            $result['info'] = $this->problemlistservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '問題不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('問題回報', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->problemlistservice->read_single($id);
        if (isset($data['ProblemId'])) {
            $result['info'] = $this->problemlistservice->delete($id);
            return $result;
        } else {
            return ['error' => '問題不存在'];
        }
    }
}
