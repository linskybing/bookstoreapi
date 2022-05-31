<?php

namespace Controller;

use Service\Authentication;
use Service\DealRecordService;
use Service\ShoppingListService;
use Service\Validator;


class DealRecordController
{
    protected $dealservice;
    protected $listservice;
    public function __construct($db)
    {
        $this->dealservice = new DealRecordService($db);
        $this->listservice = new ShoppingListService($db);
    }

    public function Get($request, $state)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (isset($auth['CartId'])) {
            $data = $this->dealservice->read($auth['CartId'], $state);
        } else {
            $data = null;
        }

        return $data;
    }

    public function Get_Seller($request, $state)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealservice->read_seller($auth, $state);
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
            if ($data['State'] == '未歸還') {
                date_default_timezone_set('Asia/Taipei');
                $data['StartTime'] =  date('Y-m-d H:i:s');
                $data['EndTime'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '+ ' . $datarole['Count'] . 'days'));
            }
            if ($data['State'] == '已歸還') {
                date_default_timezone_set('Asia/Taipei');
                $data['ReturnTime'] = date('Y-m-d H:i:s');
            }

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
