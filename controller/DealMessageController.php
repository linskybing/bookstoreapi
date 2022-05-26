<?php

namespace Controller;

use Service\Authentication;
use Service\DealMessageService;
use Service\Validator;


class DealMessageController
{
    protected $dealmessageservice;
    public function __construct($db)
    {
        $this->dealmessageservice = new DealMessageService($db);
    }

    public function Get($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealmessageservice->read($id);
        return $data;
    }

    public function Read_Single($request, $id, $user)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealmessageservice->read_cancel($id, $user);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealmessageservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RecordId' => ['required'],
            'Content' => ['required'],
        ), $data);
        $data['Creator'] = $auth;
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->dealmessageservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $datarole = $this->dealmessageservice->read_single($id);
        if (isset($datarole['MessageId'])) {

            $result['info'] = $this->dealmessageservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '留言不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->dealmessageservice->read_single($id);
        if (isset($data['MessageId'])) {
            $result['info'] = $this->dealmessageservice->delete($id);
            return $result;
        } else {
            return ['error' => '留言不存在'];
        }
    }
}
