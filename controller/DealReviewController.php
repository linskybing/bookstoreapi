<?php

namespace Controller;

use Service\Authentication;
use Service\DealReviewService;
use Service\Validator;


class DealRevieweController
{
    protected $dealreviewservice;
    public function __construct($db)
    {
        $this->dealreviewservice = new DealReviewService($db);
    }

    public function GetByProduct($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealreviewservice->readbyproduct($id);
        return $data;
    }

    public function GetByDeal($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealreviewservice->readbydeal($id);
        return $data;
    }


    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealreviewservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RecordId' => ['required'],
            'CustomerScore' => ['required'],
            'CustomerReview' => ['required'],
        ), $data);
        if ($validate != '') {
            return $validate;
        } else {
            $datareview = $this->dealreviewservice->readbydeal($data['RecordId']);

            if (isset($datareview['RecordId'])) {
                return ['error' => '此交易紀錄已經評價'];
            } else {
                $result = $this->dealreviewservice->post($data);
                return $result;
            }
        }
    }
    public function Post2($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RecordId' => ['required'],
            'SellerScore' => ['required'],
            'SellerReview' => ['required'],
        ), $data);
        if ($validate != '') {
            return $validate;
        } else {
            $datareview = $this->dealreviewservice->readbydeal($data['RecordId']);

            if (isset($datareview['RecordId'])) {
                return ['error' => '此交易紀錄已經評價'];
            } else {
                $result = $this->dealreviewservice->post2($data);
                return $result;
            }
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $datareview = $this->dealreviewservice->read_single($id);
        if (isset($datareview['ReviewId'])) {
            $validate = Validator::check(array(
                'SellerScore' => ['required'],
                'SellerReview' => ['required'],
            ), $data);
            $validate2 = Validator::check(array(
                'CustomerScore' => ['required'],
                'CustomerReview' => ['required'],
            ), $data);
            if ($validate != '' && $validate2 != '') {
                return $validate;
            } else {
                if ($validate2 == '') {
                    date_default_timezone_set('Asia/Taipei');
                    $data['CustomerTime'] =  date('Y-m-d H:i:s');
                } else {
                    date_default_timezone_set('Asia/Taipei');
                    $data['SellerTime'] =  date('Y-m-d H:i:s');
                }
                $result['info'] = $this->dealreviewservice->update($id, $data);
                return $result;
            }
        } else {
            return ['error' => '評價不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealreviewservice->read_single($id);
        if (isset($data['ReviewId'])) {
            $result['info'] = $this->dealreviewservice->delete($id);
            return $result;
        } else {
            return ['error' => '評價不存在'];
        }
    }
}
