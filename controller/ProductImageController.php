<?php

namespace Controller;

use Service\Authentication;
use Service\File;
use Service\Validator;
use Service\ProductImageService;
use Service\ProductService;

class ProductImageController
{
    protected $imageservice;
    protected $productservice;
    public function __construct($db)
    {
        $this->productservice = new ProductService($db);
        $this->imageservice = new ProductImageService($db);
    }

    public function Get($request, $id)
    {
        $data = $this->imageservice->read($id);
        return $data;
    }

    public function Get_Single($request, $id)
    {
        $data = $this->imageservice->read_single($id);
        return $data;
    }

    public function Post($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $data = $request->getBody();

        $product = $this->productservice->read_single($id);
        if (isset($product['Seller'])) {
            if (!Authentication::isCreator($product['Seller'], $auth)) {
                $result['info'] = array();
                $data['ProductId'] = $id;
                $id = $this->imageservice->getlastnum();
                $file = File::store($_FILES, 'Products', $id);
                foreach ($file as $value) {
                    $data['Image'] = $value;
                    array_push($result['info'], $this->imageservice->post($data));
                }
                return ['info' => '新增成功'];
            } else {
                return ['error' => '權限不足'];
            }
        } else {

            return ['error' => '商品不存在'];
        }
    }

    public function Delete($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) return $auth;

        $image = $this->imageservice->read_single($id);

        if (isset($image['Image'])) {
            $file = File::delete('Products', $image['Image']);
            $data = $this->imageservice->delete($id);
            return  $file;
        } else {
            return ['error' => '商品圖片不存在'];
        }
    }
}
