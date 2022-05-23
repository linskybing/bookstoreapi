<?php

namespace Service;

use auth\Jwt;

class File
{

    public static function store($file, $dir, $id)
    {
        $count = 0;
        $filelist = array();
        foreach ($file['file']['tmp_name'] as $value) {
            $target =  'C:\\xampp\\htdocs\\Restful_Api_0511\\public\\images\\' . $dir;
            $filename = $file['file']['name'][$count];
            $filetype = substr($filename, strpos($filename, '.'));
            $filename = rtrim(strtr(base64_encode($id), '+/', '-_'), '=') . $filetype;
            $target .= '\\' . $filename;
            move_uploaded_file($value, $target);
            array_push($filelist, $filename);
            $id++;
            $count++;
        }
        return $filelist;
    }

    public static function delete($dir, $filename)
    {
        $path = 'C:\\xampp\\htdocs\\Restful_Api_0511\\public\\images\\' . $dir . '\\' . $filename;

        if (!unlink($path)) {
            return false;
        } else {
            return true;
        }
    }
}
