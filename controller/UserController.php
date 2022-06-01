<?php

namespace Controller;

use auth\Jwt;
use Service\Authentication;
use Service\CartService;
use Service\MailService;
use Service\TokenService;
use Service\UserService;
use Service\Validator;
use Service\File;

class UserController
{
    protected $userservice;
    protected $mailservice;
    protected $tokenservice;
    protected $cartservice;
    public function __construct($db)
    {
        $this->userservice = new UserService($db);
        $this->mailservice = new MailService();
        $this->tokenservice = new TokenService($db);
        $this->cartservice = new CartService($db);
    }

    //會員查詢
    public function GetAllUser($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->userservice->read();
        return $data;
    }

    //取得單一使用者帳號
    public function GetUserSingle($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->userservice->read_single($auth);

        $return = array(
            'Account' => $data['Account'],
            'Address' => $data['Address'],
            'Email' => $data['Email'],
            'Imgae' => $data['Image'],
            'Name' => $data['Name'],
            'Address' => $data['Address']
        );
        return $return;
    }

    //登入
    public function Login($request, $data)
    {
        $validate = Validator::check(array(
            'Account' => ['required'],
            'Password' => ['required'],
        ), $data);

        if ($validate != '') {

            $result =  $validate;
        } else {
            if ($this->userservice->PasswordCheck($data['Account'], $data['Password'])) {

                $headers = apache_request_headers();

                $data = $this->tokenservice->getuserdata($data['Account']);

                $token = $this->tokenservice->gettoken($data['Account'], false);
                $payload = Jwt::verifyToken($token['token']);
                foreach ($payload as $key => $value) {
                    $token[$key] = $value;
                }

                $result = $this->userservice->update($data['Account'], array('Active' => true));

                return $token;
            } else {
                $result =  ['error' => '帳號或密碼錯誤'];
            }
        }



        return $result;
    }

    //註冊
    public function Register($request)
    {
        $data = $request->getBody();

        $validate = Validator::check(array(
            'Account' => ['required'],
            'Password' => ['required'],
            'Name' => ['required'],
            'Email' => ['required']
        ), $data);

        $datauser = $this->userservice->read_single($data['Account']);
        if (isset($datauser['Account'])) return ['error' => '此帳號已被註冊'];

        if ($validate != '') {

            $result =  $validate;
        } else {
            $authcode = $this->mailservice->generateauthcode();

            $data['AuthCode'] = $authcode;

            $result = $this->userservice->post($data);

            $this->cartservice->read($data['Account']);

            $body = $this->mailservice->getmailbody($data['Name'], 'http://localhost:8080/user/' . $data['Account'] . '/' . $data['AuthCode']);

            $result = $this->mailservice->sendmail($data['Email'], $body);
        }


        return  $result;
    }

    //帳號驗證是否存在
    public function AccountCheck($request, $account)
    {

        return $this->userservice->accountcheck($account);
    }

    //更新大頭貼
    public function ChangeImage($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;


        $data = $this->userservice->read_single($auth);
        if (Authentication::isCreator($data['Account'], $auth)) return ['error' => '權限不足'];

        if (isset($data)) {
            if (!is_null($_FILES)) {
                $file = File::store($_FILES, 'Members', $data['Name']);

                $this->userservice->update($auth, array('Image' => $file[0]));
                $data = $this->userservice->read_single($auth);
                $result['data'] = $data['Image'];
            } else {
                return ['error' => '檔案不可為空'];
            }
        } else {

            $result['error'] = '更新大頭貼失敗';
        }


        return $result;
    }

    //更新使用者資訊
    public function UpdateUserDate($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Name' => ['required'],
            'Address' => ['required']
        ), $data);

        if ($validate != '') {

            $result =  $validate;
        } else {
            $result['info'] = $this->userservice->update($auth, array('Name' => $data['Name'], 'Address' => $data['Address']));
        }

        return $result;
    }

    //更新地址
    public function ChangeAddress($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Address' => ['required']
        ), $data);


        if ($validate != '') {

            $result =  $validate;
        } else {
            $result['info'] = $this->userservice->update($auth, array('Address' => $data['Address']));
        }

        return $result;
    }

    //更新密碼
    public function ChangePassword($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $validate = Validator::check(array(
            'oldPassword' => ['required'],
            'newPassword' => ['required', 'min:8']
        ), $data);
        $data['Account'] = $auth;

        if ($validate != '') {

            $result =  $validate;
        } else {
            if ($this->userservice->passwordcheck($auth, $data['oldPassword'])) {
                $data['newPassword'] = $this->userservice->formatpassword($data['newPassword']);
                $result['info'] = $this->userservice->update($data['Account'], array('Password' => $data['newPassword']));
            } else {
                return ['error' => '密碼錯誤'];
            }
        }
        return $result;
    }

    //驗證信箱
    public function AuthCode($request, $user, $auth)
    {
        $result = $this->userservice->checkauthcode($user, $auth);

        header('Location: http://localhost/bookstore/view/validate_result.html');
    }

    //忘記密碼
    public function ForgetPassword($request)
    {
        $data = $request->getBody();

        $validate = Validator::check(array(
            'Account' => ['required'],
            'Email' => ['required'],
        ), $data);

        if ($validate != '') {

            $result =  $validate;
        } else {
            $userdata = $this->userservice->read_single($data['Account']);
            if (isset($userdata['Account'])) {

                if ($userdata['Email'] == $data['Email']) {

                    $body = $this->mailservice->getforgetbody($userdata);
                    //
                    $result = $this->mailservice->sendmail($data['Email'], $body);

                    return $result;
                } else {
                    return [
                        'error' => '電子信箱錯誤'
                    ];
                }
            } else {
                return [
                    'error' => '帳號不存在'
                ];
            }
        }
        return $result;
    }

    //修改密碼(For忘記密碼)
    public function UpdatePassword($request)
    {

        $data = $request->getBody();

        $validate = Validator::check(array(
            'Password' => ['required', 'min:8'],
            'PasswordCheck' => ['required', 'min:8'],
            'Token' => ['required']
        ), $data);

        $payload  = Jwt::verifyToken($data['Token']);
        if ($validate != '') {
            $result =  $validate;
            return $result;
        } else if ($payload) {

            if ($data['Password'] != $data['PasswordCheck']) return ['error' => '兩次密碼輸入不一致'];

            $result = $this->userservice->update($payload['Account'],  array(
                'Password' => $this->userservice->formatpassword($data['Password'])
            ));

            return ['info' => $result];
        } else {
            return ['error' => '重設密碼已經失效，請至忘記密碼重新寄送重設郵件'];
        }
    }

    public function Logout($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;


        $result = $this->userservice->update($auth, array('Active' => false));
        return $result;
    }

    public function GetUserImage($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $this->userservice->read_single($auth);

        return array('Image' => $data['Image']);
    }
}
