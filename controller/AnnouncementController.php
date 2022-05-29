<?php

namespace Controller;

use Service\Authentication;
use Service\AnnouncementService;
use Service\Validator;


class AnnouncementController
{
    protected $announcementservice;
    public function __construct($db)
    {
        $this->announcementservice = new AnnouncementService($db);
    }

    public function Get($request)
    {
        $data = $this->announcementservice->read();
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->announcementservice->read_single($id);
        return $data;
    }

    public function Post($request)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;
        if (!$auth) return ['error' => '權限不足'];
        if (!Authentication::hasPermission('公告管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Title' => ['required'],
            'Content' => ['required'],
        ), $data);
        $data['Admin'] = $auth['Account'];
        if ($validate != '') {
            return $validate;
        } else {
            $result = $this->announcementservice->post($data);
            return $result;
        }
    }

    public function Patch($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        if (!Authentication::hasPermission('公告管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $request->getBody();

        $datarole = $this->announcementservice->read_single($id);
        if (isset($datarole['AnnouncementId'])) {

            $result['info'] = $this->announcementservice->update($id, $data);
            return $result;
        } else {
            return ['error' => '公告不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::getPayload();
        if (isset($auth['error'])) return $auth;

        if (!Authentication::hasPermission('公告管理', $auth['RoleId'])) return ['error' => '權限不足'];

        $data = $this->announcementservice->read_single($id);
        if (isset($data['AnnouncementId'])) {
            $result['info'] = $this->announcementservice->delete($id);
            return $result;
        } else {
            return ['error' => '公告不存在'];
        }
    }
}
