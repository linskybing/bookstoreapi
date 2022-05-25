<?php

namespace Controller;

use Service\Authentication;
use Service\ProductService;
use Service\TagListService;
use Service\Validator;


class TagListController
{
    protected $tagservice;
    protected $productservice;
    public function __construct($db)
    {
        $this->tagservice = new TagListService($db);
        $this->productservice = new ProductService($db);
    }

    public function Get($request, $product)
    {
        $data = $this->tagservice->read($product);
        return $data;
    }
    public function Get_Single($request, $id)
    {
        $data = $this->tagservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'CategoryId' => ['required'],
            'ProductId' => ['required'],
        ), $data);
        $product = $this->productservice->read_single($data['ProductId']);
        if (Authentication::isCreator($product['Seller'], $auth)) return ['error' => '權限不足'];

        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->tagservice->post($data);
            return $result;
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->tagservice->read_single($id);
        $product = $this->productservice->read_single($data['ProductId']);
        if (Authentication::isCreator($product['Seller'], $auth)) return ['error' => '權限不足'];

        if (isset($data['Id'])) {
            $result['info'] = $this->tagservice->delete($id);
            return $result;
        } else {
            return ['error' => '種類不存在'];
        }
    }
}
