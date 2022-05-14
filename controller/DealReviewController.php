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
            if ($validate != '') {
                return $validate;
            } else {

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
