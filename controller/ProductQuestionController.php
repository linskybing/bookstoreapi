<?php

namespace Controller;


use Service\Authentication;
use Service\ProductQuestionService;
use Service\ProductService;
use Service\Validator;

class ProductQuestionController
{
    protected $questionservice;
    protected $productservice;
    public function __construct($db)
    {
        $this->questionservice = new ProductQuestionService($db);
        $this->productservice = new ProductService($db);
    }

    public function Get($request, $productid)
    {
        $data = $this->questionservice->read($productid);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->questionservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'ProductId' => ['required'],
            'Content' => ['required'],
        ), $data);

        if ($validate != '') {
            $result = $validate;
        } else {

            $data['Customer'] = $auth;

            $result = $this->questionservice->post($data);
        }

        return $result;
    }

    public function Patch($request, $id)
    {
        $data = $request->getBody();

        $auth = Authentication::isAuth();

        if (isset($auth['error'])) return $auth;

        $validate = Validator::check(array(
            'Reply' => ['required'],
        ), $data);

        $data['Seller'] = $auth;

        if ($validate != '') return $validate;

        $question = $this->questionservice->read_single($id);
        $product = $this->productservice->read_single($question['ProductId']);
        if (isset($product['ProductId'])) {
            if (!Authentication::isCreator($product['Seller'], $auth)) {

                $result['info'] = $this->questionservice->update($id, $data);
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

        $product = $this->questionservice->read_single($id);
        if (isset($product['ProductId'])) {
            if (Authentication::isCreator($product['Seller'], $auth)) {

                $data['info'] = $this->questionservice->delete($id);
                return $data;
            } else {
                return ['error' => '權限不足'];
            }
        } else {
            return ['error' => '商品不存在'];
        }
    }
}
