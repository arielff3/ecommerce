<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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
	$user->delete();
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