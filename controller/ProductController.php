<?php

namespace Controller;

use Exception;
use Service\Authentication;
use Service\DealRecordService;
use Service\DealReviewService;
use Service\ProductImageService;
use Service\Validator;
use Service\ProductService;
use Service\TagListService;
use Service\UserService;

class ProductController
{
    protected $productservice;
    protected $imageservice;
    protected $producttag;
    protected $dealservice;
    protected $dealreviewservice;
    protected $userservice;
    public function __construct($db)
    {
        $this->productservice = new ProductService($db);
        $this->imageservice = new ProductImageService($db);
        $this->producttag = new TagListService($db);
        $this->dealservice = new DealRecordService($db);
        $this->dealreviewservice = new DealReviewService($db);
        $this->userservice = new UserService($db);
    }

    public function Get($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) {
            $data = $this->productservice->read(null);
        } else {
            $data = $this->productservice->read($auth);
        }

        return $data;
    }

    public function Get_Rent($request)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) {
            $data = $this->productservice->read_rent(null);
        } else {
            $data = $this->productservice->read_rent($auth);
        }

        return $data;
    }

    public function Get_Seller($request, $state)
    {

        try {

            $auth = Authentication::isAuth();
            if (isset($auth['error'])) return $auth;
            $data = $this->productservice->read_seller($state, $auth);
            return $data;
        } catch (Exception $e) {

            return ['error' => '發生錯誤，請查看參數是否正確'];
        }
    }

    public function Get_Single($request, $id)
    {
        try {
            $auth = Authentication::isAuth();
            if (isset($auth['error'])) {
                $data = $this->productservice->read_single($id, null);
            } else {
                $data = $this->productservice->read_single($id, $auth);
            }
            if (isset($data['ProductId'])) {
                $img = $this->imageservice->read($data['ProductId']);
                $category = $this->producttag->read($data['ProductId']);
                $reivew = $this->dealreviewservice->readbyproduct($data['ProductId']);
                $user = $this->userservice->read_single($data['Seller']);
            }
            $data['SellerImg'] = $user['Image'];
            $data['SellerName'] = $user['Name'];
            $data['SellerActive'] = $user['Active'];
            if (isset($img['data'])) $data['Image'] = $img['data'];
            if (isset($category['data'])) $data['Category'] = $category['data'];
            if (isset($reivew['data'])) $data['Review'] = $reivew['data'];
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
                if (!Authentication::isCreator($product['Seller'], $auth)) {

                    $data = $this->productservice->delete($id);
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

    public function InCart($request, $id)
    {
        $auth = Authentication::isAuth();
        if (isset($auth['error'])) {
            $data = $this->productservice->incart($id, null);
        } else {
            $data = $this->productservice->incart($id, $auth);
        }

        return $data;
    }
}
