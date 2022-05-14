<?php

namespace config\http;

class Request implements IRequest
{

    function __construct()
    {
        $this->bootstrapSelf();
    }

    private function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value) {
            $this->{$this->toCamelCase($key)} = $value;
        }
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);

        foreach ($matches[0] as $match) {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    public function getBody()
    {
        if ($this->requestMethod === "GET") {
            return;
        }

        if ($this->requestMethod == "POST") {

            $body = array();
            foreach ($_POST as $key => $value) {
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }

            return $body;
        }

        if ($this->requestMethod == "PATCH") {

            $data = file_get_contents('php://input');
            $data = explode("&", $data);
            $body = array();

            foreach ($data as $key => $value) {
                $temp = explode("=", $value);
                $body[$temp[0]] = urldecode($temp[1]);
            }


            return $body;
        }

        if ($this->requestMethod == "DELETE") {
            return;
        }
    }
}
