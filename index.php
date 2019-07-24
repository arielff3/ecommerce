<?php 
session_start();
require_once('vendor/autoload.php');

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();
	$page->setTpl('index');

});
// Admin Get
$app->get('/admin',function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('index');

});
// Admin Login
$app->get('/admin/login', function(){

	if(isset($_SESSION[User::SESSION]))
	{
		header('location: /admin');
		exit;
	} 
	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl('login');
});
// Post login admin
$app->post('/admin/login', function(){
	
	User::login($_POST['login'], $_POST['password']);
	header('location: /admin');
	exit;

});
// Logout admin
$app->get('/admin/logout', function(){

	User::logout();
	header('location: /admin');
	exit;

});
// Listar usuarios
$app->get('/admin/users', function(){

	User::verifyLogin();
	$users = User::listAll();
	$page = new PageAdmin();
	$page->setTpl('users', array(
		'users'=>$users
	));

});
// Criar usuarios
$app->get('/admin/users/create', function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl('users-create');

});
// Deletar usuário
$app->get('/admin/users/:iduser/delete', function($iduser){

	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$user->delete($iduser);
	header("location: /admin/users");
	exit;
	
});
// Atualizar usuario (GET)
$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl('users-update', array(
		"user"=>$user->getValues()
	));

});
// Criar usuarios (Verificar se está logado)
$app->post('/admin/users/create', function(){
	
	User::verifyLogin();

	$user = new User();

 	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;

 	$_POST['despassword'] = password_hash($_POST["despassword"], PASSWORD_DEFAULT, [

 		"cost"=>12

 	]);

 	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
 	exit;

});

$app->post('/admin/users/:iduser', function($iduser){

	User::verifyLogin();
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"])) ? 1 : 0;
	$user->get((int)$iduser);
	$user->setData($_POST);
	$user->update();
	header("location: /admin/users");
	exit;

});

$app->run();

 ?>