<?php

namespace Controller;


use Service\Authentication;
use Service\Validator;
use Service\ProductService;

class ProductController
{
    protected $productservice;
    public function __construct($db)
    {
        $this->productservice = new ProductService($db);
    }

    public function Get($request)
    {
        $data = $this->productservice->read();
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->productservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Name' => ['required'],
            'Description' => ['required'],
            'Price' => ['required'],
            'Inventory' => ['required'],
        ), $data);

        if ($validate != '') {
            $result = $validate;
        } else {

            $data['Seller'] = $auth;

            $result = $this->productservice->post($data);
        }

        return $result;
    }

    public function Patch($request, $id)
    {
        $data = $request->getBody();

        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $product = $this->productservice->read_single($id);
        if (isset($product['Seller'])) {
            if (Authentication::isCreator($product['Seller'], $auth)) {

                $result['info'] = $this->productservice->update($id, $data);
                return $result;
            } else {
                return ['error' => '權限不足'];
            }
        } else {
            return ['error' => '商品不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $product = $this->productservice->read_single($id);
        if (isset($product['Seller'])) {
            if (Authentication::isCreator($product['Seller'], $auth)) {

                $data['info'] = $this->productservice->delete($id);
                return $data;
            } else {
                return ['error' => '權限不足'];
            }
        } else {
            return ['error' => '商品不存在'];
        }
    }
}
