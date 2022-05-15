<?php

namespace Controller;

use Service\Authentication;
use Service\ChatRoomService;
use Service\Validator;


class ChatRoomController
{
    protected $roomservice;
    public function __construct($db)
    {
        $this->roomservice = new ChatRoomService($db);
    }

    public function GetCustomer($request,$search)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->roomservice->readcustomer($auth,$search);
        return $data;
    }

    public function GetSeller($request,$search)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->roomservice->readseller($auth,$search);
        return $data;
    }

    public function GetById($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;
        if (!$this->roomservice->ischatroomuser($id, $auth)) return ['error' => '權限不足'];
        $data = $this->roomservice->read_single($id);

        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Seller' => ['required'],
        ), $data);

        $data['User'] = $auth;

        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->roomservice->post($data);
            return $result;
        }
    }


    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->roomservice->read_single($id);
        if (isset($data['RoomId'])) {
            $result['info'] = $this->roomservice->delete($id);
            return $result;
        } else {
            return ['error' => '聊天室不存在'];
        }
    }
}
