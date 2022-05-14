<?php

namespace Controller;

use Service\Authentication;
use Service\CartService;
use Service\Validator;


class CartController
{
    protected $cartservice;
    public function __construct($db)
    {
        $this->cartservice = new CartService($db);
    }

    public function Get($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->cartservice->read($auth);

        return $data;
    }

    public function GetById($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->cartservice->read_single($id);
        if (Authentication::isCreator($data['Member'], $auth)) return ['error' => '權限不足'];
        return $data;
    }


    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->cartservice->read_single($id);
        if (Authentication::isCreator($data['Member'], $auth)) return ['error' => '權限不足'];

        if (isset($data['CartId'])) {
            $data['info'] = $this->cartservice->delete($id);
            return $data;
        } else {
            return ['error' => '購物車不存在'];
        }
    }
}
