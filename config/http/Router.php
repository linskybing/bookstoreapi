<?php

namespace config\http;



class Router
{
  private $request;
  private $supportedHttpMethods = array(
    "GET",
    "POST",
    "PATCH",
    "DELETE"
  );

  function __construct(IRequest $request)
  {
    $this->request = $request;
  }

  function __call($name, $args)
  {
    list($route, $method, $controller) = $args;

    if (!in_array(strtoupper($name), $this->supportedHttpMethods)) {
      $this->invalidMethodHandler();
    }

    $this->{strtolower($name)}[$this->formatRoute($route) . '+' . $this->argCount($name, $route)][0] = $method;
    $this->{strtolower($name)}[$this->formatRoute($route) . '+' . $this->argCount($name, $route)][1] = $controller;
  }

  /**
   * Removes trailing forward slashes from the right of the route.
   * @param route (string)
   */
  private function formatRoute($route)
  {
    if (substr($route, 0, strpos($route, "{")) != null) {
      $route = substr($route, 0, strpos($route, "{"));
    }
    $result = rtrim($route, '/');
    if ($result === '') {
      return '/';
    }
    return $result;
  }

  private function formatUri($uri)
  {
    $tempuri = $uri;
    $tempuri = rtrim($uri);
    $methodDictionary = $this->{strtolower($this->request->requestMethod)};
    $count = 0;

    while (!array_key_exists($tempuri . '+' . (string)$count, $methodDictionary) && $tempuri != '') {

      $count++;
      $tempuri = substr($tempuri, 0, strrpos($tempuri, '/'));
    }
    if ($tempuri != '') {

      return array($tempuri . '+' . (string)$count, $count);
    } else {

      return array($uri . '+' . (string)0, 0);
    }
  }

  private function getUridata($count, $uri)
  {
    $uriarr = explode("/", $uri);
    $data = array();
    for ($i = $count; $i > 0; $i--) {
      array_push($data, $uriarr[count($uriarr) - $i]);
    }
    return $data;
  }

  private function argCount($name, $route)
  {
    $route = rtrim($route, '/');
    $tempstr = $route;
    $count = 0;
    while (str_contains($tempstr, "{") && str_contains($tempstr, "}")) {
      $right = strpos($tempstr, "}");
      $tempstr = substr($tempstr, $right + 1);
      $count++;
    }
    return (string)$count;
  }

  private function invalidMethodHandler()
  {
    header("{$this->request->serverProtocol} 405 Method Not Allowed");
  }

  private function defaultRequestHandler()
  {
    header("{$this->request->serverProtocol} 404 Not Found");
  }

  /**
   * Resolves a route
   */
  function resolve()
  {
    if (isset($this->{strtolower($this->request->requestMethod)})) {

      $methodDictionary = $this->{strtolower($this->request->requestMethod)};

      list($formatedRoute, $argCount) = $this->formatUri($this->request->requestUri);

      if (isset($methodDictionary[$formatedRoute][0])) {

        $method = $methodDictionary[$formatedRoute][0];
        $varlist = array($this->request, $methodDictionary[$formatedRoute][1]);

        if ($argCount != 0) {

          $temp = $this->getUridata($argCount, $this->request->requestUri);

          foreach ($temp as $i) {
            array_push($varlist, $i);
          }
        }        
        echo call_user_func_array($method, $varlist);
      } else {

        echo json_encode(array(['Error' => '404 Not Found']));
        $this->defaultRequestHandler();
        return;
      }
    } else {

      echo json_encode(array(['Error' => '請求方法不支援']));
      return;
    }
  }

  function __destruct()
  {
    $this->resolve();
  }
}
