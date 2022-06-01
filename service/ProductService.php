<?php

namespace Service;

use Model\Product;
use PDO;

class ProductService
{
    protected $imageservice;
    protected $producttag;
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new Product();
        $this->imageservice = new ProductImageService($db);
        $this->producttag = new TagListService($db);
    }

    //讀取
    public function read($auth = null)
    {
        $query = "SELECT p.ProductId,
                            Name,
                            Description,
                            Price,
                            Inventory,
                            Image,
                            State,
                            Seller,
                            Watch,
                            p.CreatedAt,
                            Rent,
                            MaxRent,
                            RentPrice,
                            p.ProductId IN (SELECT ProductId
                                            FROM shoppingcart sc,
                                                 shoppinglist sl
                                            WHERE sc.CartId = sl.CartId AND
                                                  State = '未結帳' AND
                                                  Member = '" . $auth . "') AS InCart                        
                        FROM product p
                        LEFT JOIN productimage img
                        ON p.ProductId = img.ProductId
                        WHERE p.DeletedAt IS NULL AND
                            State = 'on'
                        GROUP BY ProductId
                        ORDER BY CreatedAt";


        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProductId' => $ProductId,
                    'Name' => $Name,
                    'InCart' => $InCart,
                    'Description' => $Description,
                    'Price' => $Price,
                    'Inventory' => $Inventory,
                    'Image' => $Image,
                    'State' => $State,
                    'Rent' => $Rent,
                    'MaxRent' => $MaxRent,
                    'RentPrice' => $RentPrice,
                    'Seller' => $Seller,
                    'Watch' => $Watch,
                    'CreatedAt' => $CreatedAt,
                );

                $data_item['Image'] = $this->imageservice->read($data_item['ProductId'])['data'];
                $data_item['Category'] = $this->producttag->read($data_item['ProductId'])['data'];
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }

    //讀取
    public function read_rent($auth = null)
    {
        $query = "SELECT p.ProductId,
                            Name,
                            Description,
                            Price,
                            Inventory,
                            Image,
                            State,
                            Seller,
                            Watch,
                            p.CreatedAt,
                            Rent,
                            MaxRent,
                            RentPrice,
                            p.ProductId IN (SELECT ProductId
                                            FROM shoppingcart sc,
                                                 shoppinglist sl
                                            WHERE sc.CartId = sl.CartId AND
                                                  State = '未結帳' AND
                                                  Member = '" . $auth . "') AS InCart                        
                        FROM product p
                        LEFT JOIN productimage img
                        ON p.ProductId = img.ProductId
                        WHERE p.DeletedAt IS NULL AND
                            State = 'on'
                        GROUP BY ProductId
                        HAVING Rent = 1
                        ORDER BY CreatedAt";


        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProductId' => $ProductId,
                    'Name' => $Name,
                    'InCart' => $InCart,
                    'Description' => $Description,
                    'Price' => $Price,
                    'Inventory' => $Inventory,
                    'Image' => $Image,
                    'State' => $State,
                    'Rent' => $Rent,
                    'MaxRent' => $MaxRent,
                    'RentPrice' => $RentPrice,
                    'Seller' => $Seller,
                    'Watch' => $Watch,
                    'CreatedAt' => $CreatedAt,
                );

                $data_item['Image'] = $this->imageservice->read($data_item['ProductId'])['data'];
                $data_item['Category'] = $this->producttag->read($data_item['ProductId'])['data'];
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }

    public function read_seller($state,  $auth)
    {


        $query = "SELECT p.ProductId,
                            Name,
                            Description,
                            Price,
                            Inventory,
                            Image,
                            State,
                            Seller,
                            Watch,
                            p.CreatedAt,
                            Rent,
                            MaxRent,
                            RentPrice
                    FROM product p
                    LEFT JOIN productimage img
                    ON p.ProductId = img.ProductId
                    WHERE p.DeletedAt IS NULL AND
                        State = '" . $state . "' AND                        
                        Seller = '" . $auth . "'
                    GROUP BY ProductId
                    ORDER BY CreatedAt	
                    ";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'ProductId' => $ProductId,
                    'Name' => $Name,
                    'Description' => $Description,
                    'Price' => $Price,
                    'Inventory' => $Inventory,
                    'Image' => $Image,
                    'State' => $State,
                    'Rent' => $Rent,
                    'MaxRent' => $MaxRent,
                    'RentPrice' => $RentPrice,
                    'Seller' => $Seller,
                    'Watch' => $Watch,
                    'CreatedAt' => $CreatedAt,
                );
                $data_item['Image'] = $this->imageservice->read($data_item['ProductId'])['data'];
                $data_item['Category'] = $this->producttag->read($data_item['ProductId'])['data'];

                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['data'] = null;
        }

        return $response_arr;
    }
    public function incart($ProductId, $auth = null)
    {
        $query  = "
        SELECT p.ProductId IN (SELECT ProductId
								FROM shoppingcart sc,
                        	  shoppinglist sl
                        WHERE sc.CartId = sl.CartId AND
                              State = '未結帳' AND
                              Member = '" . $auth . "') AS InCart 
        FROM product p
        WHERE p.ProductId = " . $ProductId . "
        LIMIT 1;
        ";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);
        }

        return ($InCart > 0);
    }

    public function read_cartid($ProductId)
    {
        $query = "SELECT ProductId,CartId,sl.Count FROM ShoppingList sl WHERE sl.ProductId =  " . $ProductId . " AND State = '未結帳'";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);
            return array('CartId' => $CartId, 'ProductId' => $ProductId, 'Count' => $Count);
        }

        return null;
    }
    //讀取單筆資料
    public function read_single($ProductId, $auth = null)
    {
        $query = "
                        SELECT p.ProductId,
                            Name,
                            Description,
                            Price,
                            Inventory,
                            Image,
                            State,
                            Seller,
                            Watch,
                            p.CreatedAt,
                            Rent,
                            MaxRent,
                            RentPrice,
                            p.ProductId IN (SELECT ProductId
                                            FROM shoppingcart sc,
                                                 shoppinglist sl
                                            WHERE sc.CartId = sl.CartId AND
                                                  State = '未結帳' AND
                                                  Member = '" . $auth . "') AS InCart                        
                        FROM product p
                        LEFT JOIN productimage img
                        ON p.ProductId = img.ProductId
                        WHERE p.DeletedAt IS NULL AND
                            State = 'on' AND 
                            p.ProductId = " . $ProductId . "
                        GROUP BY ProductId
                        ORDER BY CreatedAt
        ";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'ProductId' => $ProductId,
                'Name' => $Name,
                'Description' => $Description,
                'Price' => $Price,
                'Inventory' => $Inventory,
                'State' => $State,
                'InCart' => $InCart,
                'Rent' => $Rent,
                'MaxRent' => $MaxRent,
                'RentPrice' => $RentPrice,
                'Seller' => $Seller,
                'Watch' => $Watch,
                'CreatedAt' => $CreatedAt,
            );
            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['data'] = null;
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO Product
                              (Name,
                               Description,
                               Price,
                               Inventory,                               
                               Seller,                               
                               CreatedAt,
                               UpdatedAt)
                           VALUES ( ? , ? , ? , ? , ? , ? , ? )";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['Name'],
            $data['Description'],
            $data['Price'],
            $data['Inventory'],
            $data['Seller'],
            $time,
            $time
        ));

        if ($result) {
            $id = $this->conn->lastInsertId();
            $response_arr = $this->read_single($id);
        } else {

            $response_arr['error'] = '資料新增失敗';
        }
        return $response_arr;
    }

    //更新商品
    public function update($ProductId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($ProductId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($ProductId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProductId = " . $ProductId . ";";
        return $query;
    }

    //刪除
    public function delete($ProductId)
    {
        date_default_timezone_set('Asia/Taipei');
        if (!$this->deletecheck($ProductId)) {
            $response_arr['error'] = '該商品已有交易紀錄，不可刪除';
            return $response_arr;
        }
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE ProductId = " . $ProductId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            $response_arr['info'] = '資料刪除成功';
        } else {
            $response_arr['info'] = '資料刪除失敗';
        }
        return $response_arr;
    }

    public function deletecheck($ProductId)
    {
        $query = "SELECT *
                FROM product p,
                    shoppinglist sl,
                    recorddeal rd
                WHERE p.ProductId = sl.ProductId AND
                    sl.ShoppingId = rd.ShoppingId AND
                    p.ProductId = " . $ProductId . "
        ";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($stmt->rowCount() > 0) return false;
        return true;
    }
}
