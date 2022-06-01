<?php

namespace Controller;

use Service\Authentication;
use Service\ProductService;
use Service\RoleService;
use Service\ShoppingListService;
use Service\Validator;


class ShoppingListController
{
    protected $listservice;
    protected $productservice;
    public function __construct($db)
    {
        $this->listservice = new ShoppingListService($db);
        $this->productservice = new ProductService($db);
    }
    public function Get($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        $data = $this->listservice->read($auth['CartId']);

        return $data;
    }

    public function Get_Single($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $this->listservice->read_single($auth['CartId']);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'ProductId' => ['required'],
            'Count' => ['required'],
            'Type' => ['required']
        ), $data);
        $data['CartId'] = $auth['CartId'];

        if ($validate != '') {
            return $validate;
        } else {

            $product = $this->productservice->read_single($data['ProductId']);

            $count = $product['Inventory'];
            $this->productservice->update($data['ProductId'], array('Inventory' => ($count - $data['Count'])));
            $result = $this->listservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();


        $list = $this->listservice->read_single($id);
        if (Authentication::isCreator($list["CartId"], $auth['CartId'])) return ['error' => '權限不足'];
        if (isset($list['ShoppingId'])) {
            $result['info'] = $this->listservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '清單不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $this->productservice->read_cartid($id);
        if (isset($CartId)) {
            if (Authentication::isCreator($data["CartId"], $auth['CartId'])) return ['error' => '權限不足'];
        }

        if (isset($data['ProductId'])) {
            $product = $this->productservice->read_single($data['ProductId']);
            $count = $product['Inventory'];
            $this->productservice->update($data['ProductId'], array('Inventory' => ($count + $data['Count'])));
            $result = $this->listservice->delete($id);
            return $result;
        } else {
            return ['error' => '清單不存在'];
        }
    }
}
