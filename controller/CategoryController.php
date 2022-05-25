<?php

namespace Controller;

use Service\Authentication;
use Service\CategoryService;
use Service\Validator;


class CategoryController
{
    protected $categoryservice;
    public function __construct($db)
    {
        $this->categoryservice = new CategoryService($db);
    }

    public function Get($request)
    {
        $data = $this->categoryservice->read();
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->categoryservice->read_single($id);
        return $data;
    }

    public function Get_Rent($request)
    {
        $data = $this->categoryservice->read_tag();
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('商品種類管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Tag' => ['required'],
        ), $data);

        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->categoryservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        if (!Authentication::hasPermission('商品種類管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Tag' => ['required'],
        ), $data);

        $category = $this->categoryservice->read_single($id);
        if (isset($category['CategoryId'])) {

            if ($validate != '') {
                return $validate;
            } else {
                $result['info'] = $this->categoryservice->update($id, $data);
                return $result;
            }
        } else {
            return ['error' => '種類不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!Authentication::hasPermission('商品種類管理', $auth['RoleId'])) return ['error' => '權限不足'];


        $data = $this->categoryservice->read_single($id);
        if (isset($data['CategoryId'])) {
            $result['info'] = $this->categoryservice->delete($id);
            return $result;
        } else {
            return ['error' => '種類不存在'];
        }
    }
}
