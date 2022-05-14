<?php

namespace auth;

class Jwt
{
    //header
    private static $header = array(
        'alg' => 'HS256',
        'typ' => 'JWT'
    );

    //HMAC生成訊息摘要所使用密要
    private static $key = '123456';

    public static function getToken(array $payload)
    {
        if (is_array($payload)) {
            $base64header = self::base64UrlEncode(json_encode(self::$header, JSON_UNESCAPED_UNICODE));
            $base64payload = self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_UNICODE));
            $token = $base64header . '.' . $base64payload . '.' . self::signature($base64header . '.' . $base64payload, self::$key, self::$header['alg']);
            return $token;
        } else {
            return false;
        }
    }

    public static function verifyToken(string $token)
    {
        $tokens = explode('.', $token);

        if (count($tokens) != 3) {
            return false;
        }

        list($base64header, $base64payload, $sign) = $tokens;

        $base64decodeheader = json_decode(self::base64UrlDecode($base64header), JSON_OBJECT_AS_ARRAY);
        if (empty($base64decodeheader['alg']))
            return false;

        if (self::signature($base64header . '.' . $base64payload, self::$key, $base64decodeheader['alg']) !== $sign)
            return false;

        $payload = json_decode(self::base64UrlDecode($base64payload), JSON_OBJECT_AS_ARRAY);

        //簽發時間驗證
        if (isset($payload['iat']) && $payload['iat'] > time())
            return false;

        //過期時間驗證
        if (isset($payload['exp']) && $payload['exp'] < time())
            return false;
        //nbf時間之前不處理token
        if (isset($payload['nbf']) && $payload['nbf'] > time())
            return false;

        return $payload;
    }

    private static function base64UrlEncode(String $input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

    private static function base64UrlDecode(string $input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $addlen = 4 - $remainder;
            $input .= str_repeat('=', $addlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    private static function signature(string $input, string $key, string $alg = 'HS256')
    {
        $alg_config = array(
            'HS256' => 'sha256'
        );
        return self::base64UrlEncode(hash_hmac($alg_config[$alg], $input, $key, true));
    }
}

/*
$payload = array('name' => 'John Doe', 'iat' => 1516239022 , 'Cart_Id' => 'asdfasdf');
$jwt = new Jwt;
$token = $jwt->getToken($payload);
echo "<pre>";
echo $token;

$getPayload = $jwt->verifyToken($token);
echo "<br><br>";
var_dump($getPayload);
echo "<br><br>";*/
