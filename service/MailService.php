<?php

namespace Service;

use auth\Jwt;

class MailService
{
  public function sendmail($to, $message)
  {
    $subject = '二手書交易平台';

    $headers[] = 'Content-Type: text/html;charset=utf8';

    $headers[] = 'From: Lin <linskybing@gmail.com>';

    if (mail($to, $subject, $message, implode("\r\n", $headers))) {

      $response['info'] = '信箱已成功送出';
    } else {

      $response['error'] = '信箱送出發生錯誤';
    }

    return $response;
  }

  //生成驗證碼
  public function generateauthcode()
  {
    $code = ['a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'z', 'x', 'c', 'v', 'b', 'n', 'm', 'Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];
    $authcode = '';
    for ($i = 0; $i < 10; $i++) {
      $authcode .= $code[rand(0, count($code) - 1)];
    }
    return $authcode;
  }
  //取得mailbody

  public function getmailbody($username, $url)
  {

    $body = '
        <html>
          <head>
              <style>
              .mailbox {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
              }

              .mailcontent {
                margin: 25px;
                width: 700px;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2),
                  0 6px 20px 0 rgba(0, 0, 0, 0.19);
              }

              .header {
                text-align:center;
                background-color: #f3864c;
                width: 100%;        
                padding: 10px 0px
              }

              .header span {
                color: white;
                font-size: 20px;
                font-weight: bold;
              }

              .mailbody *{       
                padding:20px auto;
              }

              .mailbody span { 
                margin:10px 25px;               
                font-size: 20px;
                font-weight: bold;
              }

              .mailbody p {
                padding: 10px 0px;
                margin-left: 25px;
                font-size:14px;
                text-indent: 2em;
              }
            

              #authbtn {
                margin-left:40%;        
                font-size: 14px;        
                color: #ffffff;
                font-weight: normal;
                padding:10px;
                vertical-align: middle;
                background-color: #0092ff;
                border-radius: 15px;
                border-top: 0px None #000;
                border-right: 0px None #000;
                border-bottom: 0px None #000;
                border-left: 0px None #000;
                text-decoration: none;
              }
            </style>
          </head>

          <body>
            <div class="mailbox" sytle="display: flex;justify-content: center;align-items: center;width: 100%;">
              <div class="mailcontent">
                <div class="header">
                  <span>會員驗證信</span>
                </div>
                <div class="mailbody">   
                    </br>       
                    <span>{{user}} 您好:</span>            
                    <p>歡迎註冊本二手書交易平台會員，請點擊下方按鈕即可完成註冊</p></br></br>
                    <a id="authbtn" href="{{url}}">會員註冊</a>
                        
                </div>
              </div>
            </div>
          </body>
        </html>
        ';

    $body = str_replace('{{user}}', $username, $body);
    $body = str_replace('{{url}}', $url, $body);

    return $body;
  }

  public function getforgetbody($user)
  {
    $body = '
        <html>
          <head>
              <style>
              .mailbox {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
              }

              .mailcontent {
                margin: 25px;
                width: 700px;
                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2),
                  0 6px 20px 0 rgba(0, 0, 0, 0.19);
              }

              .header {
                text-align:center;
                background-color: #f3864c;
                width: 100%;        
                padding: 10px 0px
              }

              .header span {
                color: white;
                font-size: 20px;
                font-weight: bold;
              }

              .mailbody *{       
                padding:20px auto;
              }

              .mailbody span { 
                margin:10px 25px;               
                font-size: 20px;
                font-weight: bold;
              }

              .mailbody p {
                padding: 10px 0px;
                margin-left: 25px;
                font-size:14px;
                text-indent: 2em;
              }
            

              #authbtn {
                margin-left:40%;        
                font-size: 14px;        
                color: #ffffff;
                font-weight: normal;
                padding:10px;
                vertical-align: middle;
                background-color: #0092ff;
                border-radius: 15px;
                border-top: 0px None #000;
                border-right: 0px None #000;
                border-bottom: 0px None #000;
                border-left: 0px None #000;
                text-decoration: none;
              }
            </style>
          </head>

          <body>
            <div class="mailbox" sytle="display: flex;justify-content: center;align-items: center;width: 100%;">
              <div class="mailcontent">
                <div class="header">
                  <span>忘記密碼</span>
                </div>
                <div class="mailbody">   
                    </br>       
                    <span>{{user}} 您好:</span>            
                    <p>請點擊下方按鈕即可重設密碼</p></br></br>
                    <a id="authbtn" href="{{url}}">重設密碼</a>
                        
                </div>
              </div>
            </div>
          </body>
        </html>
        ';
    $token = Jwt::getToken(array(
      'Account' => $user['Account'],
      'Password' => $user['Password'],
      'Eamil' => $user['Email'],
      'exp' => strtotime(date('Y-m-d H:i:s') . '+ 15minutes')
    ));
    $url = 'http://localhost/view/resetpassword.html?token=' . $token;
    $body = str_replace('{{user}}', $user['Name'], $body);
    $body = str_replace('{{url}}', $url, $body);

    return $body;
  }

  public function getcancelbody($id)
  {
    $body = '
    <html>
      <head>
          <style>
          .mailbox {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
          }

          .mailcontent {
            margin: 25px;
            width: 700px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2),
              0 6px 20px 0 rgba(0, 0, 0, 0.19);
          }

          .header {
            text-align:center;
            background-color: #f3864c;
            width: 100%;        
            padding: 10px 0px
          }

          .header span {
            color: white;
            font-size: 20px;
            font-weight: bold;
          }

          .mailbody *{       
            padding:20px auto;
          }

          .mailbody span { 
            margin:10px 25px;               
            font-size: 20px;
            font-weight: bold;
          }

          .mailbody p {
            padding: 10px 0px;
            margin-left: 25px;
            font-size:14px;
            text-indent: 2em;
          }
        

          #authbtn {
            margin-left:40%;        
            font-size: 14px;        
            color: #ffffff;
            font-weight: normal;
            padding:10px;
            vertical-align: middle;
            background-color: #0092ff;
            border-radius: 15px;
            border-top: 0px None #000;
            border-right: 0px None #000;
            border-bottom: 0px None #000;
            border-left: 0px None #000;
            text-decoration: none;
          }
        </style>
      </head>

      <body>
        <div class="mailbox" sytle="display: flex;justify-content: center;align-items: center;width: 100%;">
          <div class="mailcontent">
            <div class="header">
              <span>商品交易取消請求</span>
            </div>
            <div class="mailbody">   
                </br>
                <p>請點擊下方按鈕前往確認商品取消交易原由</p></br></br>
                <a id="authbtn" href="{{url}}">前往確認</a>
                    
            </div>
          </div>
        </div>
      </body>
    </html>
    ';
    $url = "http://localhost/bookstore/view/transaction_detail_s.html?id=" . $id;
    $body = str_replace('{{url}}', $url, $body);
    return $body;
  }

  public function getcancelbody2($id)
  {
    $body = '
    <html>
      <head>
          <style>
          .mailbox {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
          }

          .mailcontent {
            margin: 25px;
            width: 700px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2),
              0 6px 20px 0 rgba(0, 0, 0, 0.19);
          }

          .header {
            text-align:center;
            background-color: #f3864c;
            width: 100%;        
            padding: 10px 0px
          }

          .header span {
            color: white;
            font-size: 20px;
            font-weight: bold;
          }

          .mailbody *{       
            padding:20px auto;
          }

          .mailbody span { 
            margin:10px 25px;               
            font-size: 20px;
            font-weight: bold;
          }

          .mailbody p {
            padding: 10px 0px;
            margin-left: 25px;
            font-size:14px;
            text-indent: 2em;
          }
        

          #authbtn {
            margin-left:40%;        
            font-size: 14px;        
            color: #ffffff;
            font-weight: normal;
            padding:10px;
            vertical-align: middle;
            background-color: #0092ff;
            border-radius: 15px;
            border-top: 0px None #000;
            border-right: 0px None #000;
            border-bottom: 0px None #000;
            border-left: 0px None #000;
            text-decoration: none;
          }
        </style>
      </head>

      <body>
        <div class="mailbox" sytle="display: flex;justify-content: center;align-items: center;width: 100%;">
          <div class="mailcontent">
            <div class="header">
              <span>商品交易取消請求</span>
            </div>
            <div class="mailbody">   
                </br>
                <p>請點擊下方按鈕前往確認商品取消交易原由</p></br></br>
                <a id="authbtn" href="{{url}}">前往確認</a>
                    
            </div>
          </div>
        </div>
      </body>
    </html>
    ';
    $url = "http://localhost/bookstore/view/transaction_detail.html?id=" . $id;
    $body = str_replace('{{url}}', $url, $body);
    return $body;
  }
}
