<?php

namespace Controller;


use Service\Authentication;
use Service\DepositsService;
use Service\Validator;

class DepositController
{
    protected $depositservice;
    public function __construct($db)
    {
        $this->depositservice = new DepositsService($db);
    }

    public function Get($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->depositservice->read($auth);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        
        $data = $this->depositservice->read_single($id);
        if (Authentication::isCreator($data['Account'], $auth['Account'])) return ['error' => '權限不足'];
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'BankId' => ['required'],
            'DepositAccount' => ['required'],
        ), $data);
        $data['User'] = $auth;


        if ($validate != '') return $validate;

        $result = $this->depositservice->post($data);


        return $result;
    }

    public function Patch($request, $id)
    {
        $data = $request->getBody();

        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $deposit = $this->depositservice->read_single($id);
        if (isset($deposit['DepositId'])) {
            if (Authentication::isCreator($deposit['User'], $auth)) {

                $result['info'] = $this->depositservice->update($id, $data);
                return $result;
            } else {
                return ['error' => '權限不足'];
            }
        } else {
            return ['error' => '存款帳號不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $deposit = $this->depositservice->read_single($id);
        if (isset($deposit['DepositId'])) {
            if (!Authentication::isCreator($deposit['User'], $auth)) {

                $data['info'] = $this->depositservice->delete($id);
                return $data;
            } else {
                return ['error' => '權限不足'];
            }
        } else {
            return ['error' => '存款帳號不存在'];
        }
    }
}
