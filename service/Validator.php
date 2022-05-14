<?php

namespace Service;

class Validator
{
    public static $validate = array(
        'required' => '{{column}} 不可為空',
        'min' => '長度不可小於{{len}}字元',
        'max' => '長度不可大於{{len}}字元'
    );

    public static function check($column, $data)
    {
        $error = array();
        $error['error'] = array();
        foreach ($column as $key => $value) {
            $columnerror = array();
            foreach ($value as $item) {
                if (strpos($item, ':') != '') {
                    $test = self::explodelen($item);
                    $errorstr = self::validate($data, $key, $test[0], $test[1]);
                } else {
                    $errorstr = self::validate($data, $key, $item, null);
                }
                if ($errorstr != '') {
                    array_push($columnerror, $errorstr);
                }
            }
            if (count($columnerror) != 0) {
                $error['error'][$key]= $columnerror;
            }
        }

        return (count($error['error']) > 0) ?  $error : '';
    }

    public static function validate($data, $key, $value, $len)
    {
        switch ($value) {
            case 'required': {

                    $validatestr = self::$validate[$value];                    
                    if (!isset($data[$key]) || strlen(trim($data[$key]) <= 0)) {
                        $validatestr = str_replace('{{column}}', $key, $validatestr);

                        return $validatestr;
                    }
                    return '';
                }
            case 'min': {
                    if (isset($data[$key])) {
                        if (strlen($data[$key]) < (int)$len) {

                            $validatestr = self::$validate[$value];
                            $validatestr = str_replace('{{len}}', $len, $validatestr);

                            return $validatestr;
                        }
                    }
                    return '';
                }
            case 'max': {
                    if (isset($data[$key])) {
                        if (strlen($data[$key]) > (int)$len) {

                            $validatestr = self::$validate[$value];
                            $validatestr = str_replace('{{len}}', $len, $validatestr);

                            return $validatestr;
                        }
                    }

                    return '';
                }
        }
        return '';
    }

    public static function explodelen($str)
    {
        $data = explode(':', $str);

        return $data;
    }
}
