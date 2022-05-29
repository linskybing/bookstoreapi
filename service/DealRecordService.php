<?php

namespace Service;


use Model\RecordDeal;
use PDO;

class DealRecordService
{
    public function __construct($db)
    {
        $this->conn = $db;
        $this->obj = new RecordDeal();
    }

    //讀取
    public function read($cartid, $state)
    {
        switch ($state) {
            case 's_1':
                $string = '待處理';
                break;
            case 's_2':
                $string = '運送中';
                break;
            case 's_3':
                $string = '商品確認';
                break;
            case 's_4':
                $string = '取消交易';
                break;
            default:
                $string = '完成交易';
                break;
        }

        $query = "SELECT r.* ,
                         p.Seller,
                         sc.Member,
                         p.Name,
                         s.Count,
                         p.Price
                FROM RecordDeal r ,
                    shoppinglist s,
                    product p,
                    shoppingcart sc
                WHERE r.ShoppingId = s.ShoppingId AND
                    s.CartId = sc.CartId AND
                    p.ProductId = s.ProductId AND
                    s.CartId = " . $cartid . " AND
                    r.State = '" .  $string . "'	
                ORDER BY r.CreatedAt DESC";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'RecordId' => $RecordId,
                    'Seller' => $Seller,
                    'Member' => $Member,
                    'ShoppingId' => $ShoppingId,
                    'Name' => $Name,
                    'Count' => $Count,
                    'Price' => $Price,
                    'State' => $State,
                    'DealMethod' => $DealMethod,
                    'SentAddress' => $SentAddress,
                    'DealType' => $DealType,
                    'StartTime' => $StartTime,
                    'EndTime' => $EndTime,
                    'Customer_Agree' => $Customer_Agree,
                    'Seller_Agree' => $Seller_Agree,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有交易紀錄';
        }

        return $response_arr;
    }

    //讀取賣家
    public function read_seller($auth, $state)
    {
        switch ($state) {
            case 's_1':
                $string = '待處理';
                break;
            case 's_2':
                $string = '運送中';
                break;
            case 's_3':
                $string = '商品確認';
                break;
            case 's_4':
                $string = '取消交易';
                break;
            default:
                $string = '完成交易';
                break;
        }

        $query = "SELECT r.*,sc.Member,p.Name, s.Count, p.Price
                    FROM RecordDeal r ,
                        shoppinglist s,
                        product p,
                        users u,
                        shoppingcart sc
                        
                    WHERE r.ShoppingId = s.ShoppingId AND
                            p.ProductId = s.ProductId AND
                            p.Seller = u.Account AND
                            sc.CartId = s.CartId AND
                            u.Account = '" . $auth . "' AND
                            r.State = '" . $string . "'
                    ORDER BY r.CreatedAt DESC";

        $stmt  = $this->conn->prepare($query);

        $result = $stmt->execute();

        $num = $stmt->rowCount();
        if ($num > 0) {
            $response_arr = array();
            $response_arr['data'] = array();
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $data_item = array(
                    'RecordId' => $RecordId,
                    'Member' => $Member,
                    'ShoppingId' => $ShoppingId,
                    'Name' => $Name,
                    'Count' => $Count,
                    'Price' => $Price,
                    'State' => $State,
                    'DealMethod' => $DealMethod,
                    'SentAddress' => $SentAddress,
                    'DealType' => $DealType,
                    'StartTime' => $StartTime,
                    'EndTime' => $EndTime,
                    'Customer_Agree' => $Customer_Agree,
                    'Seller_Agree' => $Seller_Agree,
                    'CreatedAt' => $CreatedAt,
                    'UpdatedAt' => $UpdatedAt,
                );
                array_push($response_arr['data'], $data_item);
            }
        } else {
            $response_arr['info'] = '尚未有交易紀錄';
        }

        return $response_arr;
    }


    //讀取單筆資料
    public function read_single($RecordId)
    {
        $query = "SELECT * FROM " . $this->obj->table . " WHERE RecordId = " . $RecordId . " AND DeletedAt IS NULL;";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();


        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            extract($row);

            $data = array(
                'RecordId' => $RecordId,
                'ShoppingId' => $ShoppingId,
                'Name' => $Name,
                'State' => $State,
                'DealMethod' => $DealMethod,
                'SentAddress' => $SentAddress,
                'DealType' => $DealType,
                'StartTime' => $StartTime,
                'EndTime' => $EndTime,
                'Customer_Agree' => $Customer_Agree,
                'Seller_Agree' => $Seller_Agree,
                'CreatedAt' => $CreatedAt,
                'UpdatedAt' => $UpdatedAt,
            );

            $response_arr = $data;
            return $response_arr;
        } else {
            $response_arr['info'] = '交易紀錄不存在';
            return $response_arr;
        }
    }

    //上傳商品
    public function post($data)
    {

        date_default_timezone_set('Asia/Taipei');

        $query = "INSERT INTO " . $this->obj->table . "
                           (ShoppingId, 
                           State,
                           DealMethod,  
                           Phone,
                           SentAddress,
                           DealType,                                                                 
                           CreatedAt,
                           UpdatedAt) 
                  VALUES ( ? , ? , ? , ? , ? , ? , ? , ?)";

        $stmt = $this->conn->prepare($query);

        $time = date('Y-m-d H:i:s');

        $result = $stmt->execute(array(
            $data['ShoppingId'],
            $data['State'],
            $data['DealMethod'],
            $data['Phone'],
            $data['SentAddress'],
            $data['DealType'],
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
    public function update($RecordId, $data)
    {
        date_default_timezone_set('Asia/Taipei');

        $query = $this->getupdatesql($RecordId, $data);

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料更新成功';
        } else {
            return $response_arr['info'] = '資料更新失敗';
        }
    }

    //取得更新sql 
    public function getupdatesql($RecordId, $data)
    {
        $query = "UPDATE " . $this->obj->table;
        $tempsql =  ' SET ';
        foreach ($data as $key => $value) {
            $tempsql .= $key . " = '" . $value . "', ";
        }
        $tempsql = substr($tempsql, 0, strrpos($tempsql, ','));
        $query .= $tempsql . " , UpdatedAt = '" . date('Y-m-d H:i:s') . "' WHERE RecordId = " . $RecordId . ";";
        return $query;
    }

    //刪除
    public function delete($RecordId)
    {
        date_default_timezone_set('Asia/Taipei');
        $query = 'UPDATE ' . $this->obj->table . " SET DeletedAt = '" . date('Y-m-d H:i:s') . "' WHERE RecordId = " . $RecordId . ";";

        $stmt = $this->conn->prepare($query);

        $result = $stmt->execute();

        if ($result) {
            return $response_arr['info'] = '資料刪除成功';
        } else {
            return $response_arr['info'] = '資料刪除失敗';
        }
    }
}
