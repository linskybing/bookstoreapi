<?php
include_once '../config/database/config.php';
require '../vendor/autoload.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Headers: Origin, Methods, Content-Type, Authorization");

use Controller\UserController;
use config\http\Request;
use config\http\Router as Router;
use Controller\AnnouncementController;
use Controller\CartController;
use Controller\CategoryController;
use Controller\ChatRecordController;
use Controller\ChatRoomController;
use Controller\DealMessageController;
use Controller\DealRecordController;
use Controller\DealRevieweController;
use Controller\DepositController;
use Controller\FunctionListController;
use Controller\ProblemListController;
use Controller\ProblemReplyController;
use Controller\ProductController;
use Controller\ProductImageController;
use Controller\ProductQuestionController;
use Controller\RoleController;
use Controller\RolePermissionController;
use Controller\ShoppingListController;
use Controller\TagListController;
use Controller\UserRoleController;

/*include_once 'config/http/Request.php';
include_once 'config/http/Router.php';*/
//require_once 'controller/UserController.php';


$router = new Router(new Request);
$usercontroller = new UserController($db);
$productcontroller = new ProductController($db);
$productimagecontroller = new ProductImageController($db);
$functionlistcontroller = new FunctionListController($db);
$rolecontroller = new RoleController($db);
$permissioncontroller = new RolePermissionController($db);
$userrolecontroller = new UserRoleController($db);
$categorycontroller = new CategoryController($db);
$cartcontroller = new CartController($db);
$shoppingcontroller = new ShoppingListController($db);
$tagcontroller = new TagListController($db);
$announcement = new AnnouncementController($db);
$chatroomcontroller = new ChatRoomController($db);
$chatcontroller = new ChatRecordController($db);
$dealcontroller = new DealRecordController($db);
$dealmessagecontroller = new DealMessageController($db);
$dealreviewcontroller = new DealRevieweController($db);
$problemlistcontroller = new ProblemListController($db);
$replycontroller = new ProblemReplyController($db);
$questioncontroller = new ProductQuestionController($db);
$depositcontroller = new DepositController($db);
//---------------------------------------------------------------------------------------------------------------------
//User
//取得所有使用者
$router->get('/user', function ($request, $controller) {

    $data = $controller->GetAllUser($request);

    return json_encode($data);
}, $usercontroller);

//取得單一使用者
$router->get('/users', function ($request, $controller) {

    $data = $controller->GetUserSingle($request);

    return json_encode($data);
}, $usercontroller);

//取得帳號權限
$router->get('/user/{user}/{auth}', function ($request, $controller, $user, $auth) {

    $result = $controller->AuthCode($request, $user, $auth);

    return json_encode($result);
}, $usercontroller);

//檢查帳號是否重複
$router->get('/user/check/{account}', function ($request, $controller, $account) {

    $result = $controller->AccountCheck($request, $account);

    return json_encode($result);
}, $usercontroller);

//註冊會員
$router->post('/user', function ($request, $controller) {

    $result = $controller->Register($request);

    return json_encode($result);
}, $usercontroller);

//會員登入
$router->post('/user/login', function ($request, $controller) {

    $result = $controller->Login($request, $request->getBody());

    return json_encode($result);
}, $usercontroller);

//修改圖片
$router->post('/user/img', function ($request, $controller) {
    $result = $controller->ChangeImage($request);
    return json_encode($result);
}, $usercontroller);

//修改地址
$router->patch('/user/address', function ($request, $controller) {

    $result  = $controller->ChangeAddress($request);
    return json_encode($result);
}, $usercontroller);

$router->patch('/user/password', function ($request, $controller) {

    $result  = $controller->ChangePassword($request);
    return json_encode($result);
}, $usercontroller);

//忘記密碼
$router->post('/user/forget', function ($request, $controller) {

    $result = $controller->ForgetPassword($request);
    return json_encode($result);
}, $usercontroller);

//重設密碼
$router->post('/user/forgetv', function ($request, $controller) {

    $result = $controller->UpdatePassword($request);
    return json_encode($result);
}, $usercontroller);

//取得使用者大頭貼
$router->get('/user/image', function ($request, $controller) {

    $result = $controller->GetUserImage($request);
    return json_encode($result);
}, $usercontroller);

//登出
$router->get('/user/logout', function ($request, $controller) {

    $result = $controller->Logout($request);
    return json_encode($result);
}, $usercontroller);

//---------------------------------------------------------------------------------------------------------------------
//Product
$router->get('/product/{state}/{search}/{nowpage}/{itemnum}', function ($request, $controller, $state, $search, $nowpage, $itemnum) {

    $result = $controller->Get($request, $state, $search, $nowpage, $itemnum);
    return json_encode($result);
}, $productcontroller);

$router->get('/products/{state}/{search}/{nowpage}/{itemnum}', function ($request, $controller, $state, $search, $nowpage, $itemnum) {

    $result = $controller->Get_Seller($request, $state, $search, $nowpage, $itemnum);
    return json_encode($result);
}, $productcontroller);

$router->get('/product/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $productcontroller);

$router->post('/product', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $productcontroller);

$router->patch('/product/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $productcontroller);

$router->delete('/product/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $productcontroller);

//---------------------------------------------------------------------------------------------------------------------
//ProductImage
$router->get('/productimage/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $productimagecontroller);

$router->get('/productimages/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $productimagecontroller);

$router->post('/productimage/{id}', function ($request, $controller, $id) {

    $result = $controller->Post($request, $id);
    return json_encode($result);
}, $productimagecontroller);

$router->patch('/productimage/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $productimagecontroller);

$router->delete('/productimage/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $productimagecontroller);

//---------------------------------------------------------------------------------------------------------------------
//FunctionList

$router->get('/function', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $functionlistcontroller);

$router->post('/function', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $functionlistcontroller);

$router->patch('/function/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $functionlistcontroller);

$router->delete('/function/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $functionlistcontroller);

//---------------------------------------------------------------------------------------------------------------------
//Role

$router->get('/role', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $rolecontroller);

$router->post('/role', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $rolecontroller);

$router->patch('/role/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $rolecontroller);

$router->delete('/role/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $rolecontroller);

//---------------------------------------------------------------------------------------------------------------------
//Permisson

$router->get('/permisson/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $permissioncontroller);

$router->post('/permisson/{id}', function ($request, $controller, $id) {

    $result = $controller->Post($request, $id);
    return json_encode($result);
}, $permissioncontroller);

$router->delete('/permisson/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $permissioncontroller);

//---------------------------------------------------------------------------------------------------------------------
//UserRole

$router->get('/userrole', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $userrolecontroller);

$router->post('/userrole', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $userrolecontroller);

$router->delete('/userrole/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $userrolecontroller);

//---------------------------------------------------------------------------------------------------------------------
//Category

$router->get('/category', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $categorycontroller);

$router->post('/category', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $categorycontroller);

$router->patch('/category/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $categorycontroller);

$router->delete('/category/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $categorycontroller);

//---------------------------------------------------------------------------------------------------------------------
//ShoppingList

$router->get('/tag/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $tagcontroller);

$router->post('/tag', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $tagcontroller);

$router->delete('/tag/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $tagcontroller);

//---------------------------------------------------------------------------------------------------------------------
//Cart

$router->get('/cart', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $cartcontroller);

$router->get('/cart/{id}', function ($request, $controller, $id) {

    $result = $controller->GetById($request, $id);
    return json_encode($result);
}, $cartcontroller);

//---------------------------------------------------------------------------------------------------------------------
//ShoppingList

$router->get('/list', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $shoppingcontroller);

$router->post('/list', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $shoppingcontroller);

$router->patch('/list/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $shoppingcontroller);

$router->delete('/list/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $shoppingcontroller);

//---------------------------------------------------------------------------------------------------------------------
//Annoucement
$router->get('/announcement', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $announcement);

$router->get('/announcement/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $announcement);

$router->post('/announcement', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $announcement);

$router->patch('/announcement/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $announcement);

$router->delete('/announcement/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $announcement);


//---------------------------------------------------------------------------------------------------------------------
//Chatroom

$router->get('/chatroomc/{search}', function ($request, $controller, $search) {

    $result = $controller->GetCustomer($request, $search);
    return json_encode($result);
}, $chatroomcontroller);

$router->get('/chatrooms/{search}', function ($request, $controller, $search) {

    $result = $controller->GetSeller($request, $search);
    return json_encode($result);
}, $chatroomcontroller);

$router->get('/chatroom/{id}', function ($request, $controller, $id) {

    $result = $controller->GetById($request, $id);
    return json_encode($result);
}, $chatroomcontroller);

$router->post('/chatroom', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $chatroomcontroller);

$router->delete('/chatroom/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $chatroomcontroller);

//---------------------------------------------------------------------------------------------------------------------
//Chat

$router->get('/chatroomr/{id}/{nowpage}/{itemnum}', function ($request, $controller, $id, $nowpage, $itemnum) {

    $result = $controller->Get($request, $id, $nowpage, $itemnum);
    return json_encode($result);
}, $chatcontroller);

$router->get('/chatroomrr/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $chatcontroller);

$router->get('/chatroomrrefresh/{id}/{time}', function ($request, $controller, $id, $time) {

    $result = $controller->Refresh($request, $id, $time);
    return json_encode($result);
}, $chatcontroller);

$router->get('/chatroomrcount/{roomid}', function ($request, $controller, $id) {

    $result = $controller->GetChatCount($request, $id);
    return json_encode($result);
}, $chatcontroller);

$router->post('/chatroomr', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $chatcontroller);


$router->patch('/chatroomr/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $chatcontroller);

$router->delete('/chatroomr/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $chatcontroller);

//---------------------------------------------------------------------------------------------------------------------
//DealRecord

$router->get('/dealr/{state}', function ($request, $controller, $state) {

    $result = $controller->Get($request, $state);
    return json_encode($result);
}, $dealcontroller);

$router->get('/dealrs/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $dealcontroller);

$router->post('/dealr', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $dealcontroller);


$router->patch('/dealr/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $dealcontroller);

$router->delete('/dealr/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $dealcontroller);

//---------------------------------------------------------------------------------------------------------------------
//DealMessage

$router->get('/dealm/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $dealmessagecontroller);

$router->post('/dealm', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $dealmessagecontroller);


$router->patch('/dealm/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $dealmessagecontroller);

$router->delete('/dealm/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $dealmessagecontroller);

//---------------------------------------------------------------------------------------------------------------------
//DealReview

$router->get('/dealreviewp/{id}', function ($request, $controller, $id) {

    $result = $controller->GetByProduct($request, $id);
    return json_encode($result);
}, $dealreviewcontroller);

$router->get('/dealreviewd/{id}', function ($request, $controller, $id) {

    $result = $controller->GetByDeal($request, $id);
    return json_encode($result);
}, $dealreviewcontroller);

$router->post('/dealreview', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $dealreviewcontroller);


$router->patch('/dealreview/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $dealreviewcontroller);

$router->delete('/dealreview/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $dealreviewcontroller);

//---------------------------------------------------------------------------------------------------------------------
//ProblemList

$router->get('/problemlistu/{state}', function ($request, $controller, $state) {

    $result = $controller->GetByUser($request, $state);
    return json_encode($result);
}, $problemlistcontroller);

$router->get('/problemlista/{state}', function ($request, $controller, $state) {

    $result = $controller->GetByAdmin($request, $state);
    return json_encode($result);
}, $problemlistcontroller);

$router->get('/problemlistsingle/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $problemlistcontroller);


$router->post('/problemlist', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $problemlistcontroller);


$router->patch('/problemlist/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $problemlistcontroller);

$router->delete('/problemlist/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $problemlistcontroller);

//---------------------------------------------------------------------------------------------------------------------
//Reply

$router->get('/problemreply/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $replycontroller);

$router->get('/problemreplysingle/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $replycontroller);

$router->post('/problemreply', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $replycontroller);


$router->patch('/problemreply/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $replycontroller);

$router->delete('/problemreply/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $replycontroller);

//---------------------------------------------------------------------------------------------------------------------
//Question

$router->get('/productquestion/{id}', function ($request, $controller, $id) {

    $result = $controller->Get($request, $id);
    return json_encode($result);
}, $questioncontroller);

$router->get('/productquestionsingle/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $questioncontroller);

$router->post('/productquestion', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $questioncontroller);


$router->patch('/productquestion/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $questioncontroller);

$router->delete('/productquestion/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $questioncontroller);

//---------------------------------------------------------------------------------------------------------------------
//Deposit

$router->get('/deposit', function ($request, $controller) {

    $result = $controller->Get($request);
    return json_encode($result);
}, $depositcontroller);

$router->get('/deposit/{id}', function ($request, $controller, $id) {

    $result = $controller->Get_Single($request, $id);
    return json_encode($result);
}, $depositcontroller);

$router->post('/deposit', function ($request, $controller) {

    $result = $controller->Post($request);
    return json_encode($result);
}, $depositcontroller);


$router->patch('/deposit/{id}', function ($request, $controller, $id) {

    $result = $controller->Patch($request, $id);
    return json_encode($result);
}, $depositcontroller);

$router->delete('/deposit/{id}', function ($request, $controller, $id) {

    $result = $controller->Delete($request, $id);
    return json_encode($result);
}, $depositcontroller);
