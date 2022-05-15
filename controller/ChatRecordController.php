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

    public function Get($request, $roomid, $nowpage, $itemnum)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;
        if (!$this->chatroomservice->ischatroomuser($roomid, $auth)) return ['error' => '權限不足'];

        $data = $this->chatservice->read($roomid, $nowpage, $itemnum);

        return $data;
    }

    public function Refresh($request, $roomid, $time)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;
        if (!$this->chatroomservice->ischatroomuser($roomid, $auth)) return ['error' => '權限不足'];

        $time = str_replace('_', ' ', $time);

        $data = $this->chatservice->refresh($roomid, $time);

        return $data;
    }

    //取得留言數
    public function GetChatCount($request, $roomid)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;
        if (!$this->chatroomservice->ischatroomuser($roomid, $auth)) return ['error' => '權限不足'];

        $count  = $this->chatservice->GetChatCount($roomid);

        return $count;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;


        $data = $this->chatservice->read_single($id);
        if (isset($data['RoomId'])) {
            if (!$this->chatroomservice->ischatroomuser($data['RoomId'], $auth)) return ['error' => '權限不足'];
        }
        else{
            return ['error' => '權限不足'];
        }

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
