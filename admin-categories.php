<?php

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app->get('/admin/categories', function(){
	
	User::verifyLogin();
	$categories = Category::listAll();

	$page = new PageAdmin();
	$page->setTpl('categories', array(
		'categories'=>$categories
	));

});

$app->get('/admin/categories/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl('categories-create');

});

$app->post('/admin/categories/create', function(){

	User::verifyLogin();

	$category = new Category();
	$category->setData($_POST);//set
	$category->save();//get

	header('location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);

	$page = new PageAdmin();
	$page->setTpl('categories-update', array(
		"category"=>$category->getValues()
	));
	
});

$app->post('/admin/categories/:idcategory', function($idcategory){

	User::verifyLogin();
	$category = new Category();
	$category->get((int)$idcategory);
	$category->setData($_POST);
	$category->update();

	header('location: /admin/categories');
	exit;
});

$app->get('/admin/categories/:idcategory/delete', function($idcategory){

	User::verifyLogin();	
	$category = new Category();
	$category->get((int)$idcategory);
	$category->delete();


	header('location: /admin/categories');
	exit;
});
