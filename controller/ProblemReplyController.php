<?php

namespace Controller;

use Service\Authentication;
use Service\ProblemReplyService;
use Service\Validator;


class ProblemReplyController
{
    protected $replyservice;
    public function __construct($db)
    {
        $this->replyservice = new ProblemReplyService($db);
    }

    public function Get($request, $ProblemId)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->replyservice->read($ProblemId);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->replyservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'ProblemId' => ['required'],
            'Reply' => ['required'],
        ), $data);
        $data['ReplyUser'] = $auth;
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->replyservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $reply = $this->replyservice->read_single($id);
        if (Authentication::isCreator($reply['ReplyUser'], $auth)) return ['error' => '權限不足'];
        if (isset($reply['ProblemReply'])) {
            $validate = Validator::check(array(
                'Reply' => ['required'],
            ), $data);
            if ($validate != '') return $validate;
            $result['info'] = $this->replyservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '回覆不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->replyservice->read_single($id);
        if (Authentication::isCreator($data['ReplyUser'], $auth)) return ['error' => '權限不足'];
        if (isset($data['ProblemReply'])) {
            $result['info'] = $this->replyservice->delete($id);
            return $result;
        } else {
            return ['error' => '回覆不存在'];
        }
    }
}
