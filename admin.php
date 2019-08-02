<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

