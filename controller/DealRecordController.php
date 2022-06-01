<?php

namespace Controller;

use Service\Authentication;
use Service\DealRecordService;
use Service\MailService;
use Service\ProductService;
use Service\ShoppingListService;
use Service\UserService;
use Service\Validator;


class DealRecordController
{
    protected $dealservice;
    protected $listservice;
    protected $mailservice;
    protected $memberservice;
    protected $productservice;
    public function __construct($db)
    {
        $this->dealservice = new DealRecordService($db);
        $this->listservice = new ShoppingListService($db);
        $this->mailservice = new MailService();
        $this->memberservice = new UserService($db);
        $this->productservice = new ProductService($db);
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
            if (isset($data['State']) && $data['State'] == '未歸還') {
                date_default_timezone_set('Asia/Taipei');
                $data['StartTime'] =  date('Y-m-d H:i:s');
                $data['EndTime'] = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . '+ ' . $datarole['Count'] . 'days'));
            }
            if (isset($data['State']) && $data['State'] == '已歸還') {
                date_default_timezone_set('Asia/Taipei');
                $data['ReturnTime'] = date('Y-m-d H:i:s');
            }

            if (isset($data['Customer_Agree']) && $data['Customer_Agree'] && !isset($data['State'])) {
                $deal = $this->dealservice->read_single($datarole['RecordId']);
                $body = $this->mailservice->getcancelbody($id);
                $userdata = $this->memberservice->read_single($deal['Seller']);
                $this->mailservice->sendmail($userdata['Email'], $body);
            }

            if (isset($data['Seller_Agree']) && $data['Seller_Agree'] && !isset($data['State'])) {
                $deal = $this->dealservice->read_single($datarole['RecordId']);
                $body = $this->mailservice->getcancelbody2($id);
                $userdata = $this->memberservice->read_single($deal['Member']);
                $this->mailservice->sendmail($userdata['Email'], $body);
            }

            if (isset($data['State']) && $data['State'] == '已取消') {
                $product = $this->productservice->read_single($datarole['ProductId']);
                $count  = $product['Inventory'] + $datarole['Count'];
                $update = array(
                    'Inventory' => $count
                );
                $this->productservice->update($datarole['ProductId'], $update);
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
