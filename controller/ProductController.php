<?php

namespace Controller;

use Exception;
use Service\Authentication;
use Service\ProductImageService;
use Service\Validator;
use Service\ProductService;
use Service\TagListService;

class ProductController
{
    protected $productservice;
    protected $imageservice;
    protected $producttag;
    public function __construct($db)
    {
        $this->productservice = new ProductService($db);
        $this->imageservice = new ProductImageService($db);
        $this->producttag = new TagListService($db);
    }

    public function Get($request, $category = null, $state, $search = null, $nowpage = 1, $itemnum = 10)
    {

        $data = $this->productservice->read($category, $state, $search, $nowpage, $itemnum);
        return $data;
    }

    public function Get_Seller($request, $state, $search, $nowpage = 1, $itemnum = 10)
    {

        try {

            $auth = Authentication::isAuth();
            if (isset($auth['error'])) return $auth;
            $data = $this->productservice->read_seller($state, $search, $nowpage, $itemnum, $auth);
            return $data;
        } catch (Exception $e) {

            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }

    public function Get_Single($request, $id)
    {
        try {
            $data = $this->productservice->read_single($id);
            if (isset($data['ProductId'])) {
                $img = $this->imageservice->read($data['ProductId']);
                $category = $this->producttag->read($data['ProductId']);
            }

            if (isset($img['data'])) $data['Image'] = $img['data'];
            if (isset($category['data'])) $data['Category'] = $category['data'];
            return $data;
        } catch (Exception $e) {
            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }

    public function Post($request)
    {
        try {
            $auth = Authentication::isAuth();
            if (isset($auth['error'])) return $auth;

            $data = $request->getBody();

            $validate = Validator::check(array(
                'Name' => ['required'],
                'Description' => ['required'],
                'Price' => ['required'],
                'Inventory' => ['required'],
            ), $data);

            if ($validate != '') {
                $result['error'] = '資料欄位不可為空';
            } else {

                $data['Seller'] = $auth;

                $this->productservice->post($data);
                $result['info'] = '新增成功';
            }

            return $result;
        } catch (Exception $e) {
            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }

    public function Patch($request, $id)
    {
        try {
            $data = $request->getBody();

            $auth = Authentication::isAuth();
            if (isset($auth['error'])) return $auth;

            $product = $this->productservice->read_single($id);
            if (isset($product['Seller'])) {
                if (!Authentication::isCreator($product['Seller'], $auth)) {

                    $result['info'] = $this->productservice->update($id, $data);
                    return $result;
                } else {
                    return ['error' => '權限不足'];
                }
            } else {
                return ['error' => $auth];
            }
        } catch (Exception $e) {
            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }

    public function Delete($request, $id)
    {
        try {
            $auth = Authentication::isAuth();
            if (isset($auth['error'])) return $auth;

            $product = $this->productservice->read_single($id);
            if (isset($product['Seller'])) {
                if (Authentication::isCreator($product['Seller'], $auth)) {

                    $data['info'] = $this->productservice->delete($id);
                    return $data;
                } else {
                    return ['error' => '權限不足'];
                }
            } else {
                return ['error' => '商品不存在'];
            }
        } catch (Exception $e) {
            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }
}
