<?php

namespace Controller;

use Service\Authentication;
use Service\ChatRecordService;
use Service\ChatRoomService;
use Service\Validator;


class ChatRecordController
{
    protected $chatservice;
    protected $chatroomservice;
    public function __construct($db)
    {
        $this->chatservice = new ChatRecordService($db);
        $this->chatroomservice = new ChatRoomService($db);
    }

    public function Get($request, $roomid)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->chatservice->read($roomid);

        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;


        $data = $this->chatservice->read_single($id);
        if (!$this->chatroomservice->ischatroomuser($data['RoomId'], $auth)) return ['error' => '權限不足'];        
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'RoomId' => ['required'],
            'Message' => ['required'],
        ), $data);
        $data['Creator'] = $auth;
        if (!$this->chatroomservice->ischatroomuser($data['RoomId'], $auth)) return ['error' => '權限不足'];
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->chatservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $chatdata = $this->chatservice->read_single($id);
        if (!$this->chatroomservice->ischatroomuser($chatdata['RoomId'], $auth)) return ['error' => '權限不足'];
        if (isset($chatdata['ChatId'])) {

            $result['info'] = $this->chatservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '留言不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->chatservice->read_single($id);
        if (!$this->chatroomservice->ischatroomuser($data['RoomId'], $auth)) return ['error' => '權限不足'];
        if (isset($data['ChatId'])) {
            $result['info'] = $this->chatservice->delete($id);
            return $result;
        } else {
            return ['error' => '留言不存在'];
        }
    }
}
