<?php

namespace Controller;

use Service\Authentication;
use Service\DealRecordService;
use Service\Validator;


class DealRecordController
{
    protected $dealservice;
    public function __construct($db)
    {
        $this->dealservice = new DealRecordService($db);
    }

    public function Get($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (isset($auth['CartId'])) {
            $data = $this->dealservice->read($auth['CartId']);
        } else {
            $data = null;
        }

        return $data;
    }

    public function Get_Seller($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealservice->read_seller($auth);
        return $data;
    }


    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'ShoppingId' => ['required'],
            'State' => ['required'],
            'Phone' => ['required'],
            'DealMethod' => ['required'],
            'SentAddress' => ['required'],
            'DealType' => ['required'],
        ), $data);
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->dealservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $datarole = $this->dealservice->read_single($id);
        if (isset($datarole['RecordId'])) {

            $result['info'] = $this->dealservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '交易不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealservice->read_single($id);
        if (isset($data['RecordId'])) {
            $result['info'] = $this->dealservice->delete($id);
            return $result;
        } else {
            return ['error' => '交易不存在'];
        }
    }
}
