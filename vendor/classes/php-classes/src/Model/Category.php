<?php

namespace Hcode\Model;
use \Hcode\Db\Sql;
use \Hcode\Model;
use \Hcode\Model\Product;
use \Hcode\Mailer;
class Category extends Model {

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory ");
    }

    public function save()
    {
        $sql = new Sql();
        
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ':idcategory'=>$this->getidcategory(),
            ':descategory'=>$this->getdescategory()
        ));

        $this->setData($results[0]);
        Category::updateFile();
    }

    // Este método é responsável por pegar um usuário utilizando somente o id que é passado por parâmetro
    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", array(
            ':idcategory'=>$idcategory
        ));

        $this->setData($results[0]);
    }

    // Esse método é responsavel por pegar os dados que foram passados no setData e atualizalos no DB
    public function update()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ':idcategory'=>$this->getidcategory(),
            ':descategory'=>$this->getdescategory()
        ));

        $this->setData($results[0]);

    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", array (
            ':idcategory'=>$this->getidcategory()
        ));
    
        Category::updateFile();

    }

    public static function updateFile()
    {
        $category = Category::listAll();

        $html = array();

        foreach ($category as $row) {
            array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
    
        file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."category-menu.html", implode('', $html));
    }

    public function getProducts($related = true)
    {
        $sql = new Sql();

        if($related === true)
        {
        return $sql->select(
           "SELECT * FROM tb_products WHERE idproduct IN (
            SELECT a.idproduct
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
            );
         ", array(
            ':idcategory'=>$this->getidcategory()
         ));
        }
        else
        {
        return $sql->select(
           "SELECT * FROM tb_products WHERE idproduct NOT IN (
            SELECT a.idproduct
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
           );	    
         ", array(
            ':idcategory'=>$this->getidcategory()
         ));
        }
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES (:idcategory, :idproduct)",array(
            ':idcategory'=>$this->getidcategory(),
            'idproduct'=>$product->getidproduct()
        ));
    }

    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("DELETE FROM  tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",array(
            ':idcategory'=>$this->getidcategory(),
            'idproduct'=>$product->getidproduct()
        ));
    }

    public function getProductsPage($page = 1, $itensPerPage = 8)
    {

        $start = ($page-1)*$itensPerPage;
        $sql = new Sql();
        $results = $sql->select(
           "SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itensPerPage;
        ", array(
            ':idcategory'=>$this->getidcategory()
        ));

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return array(
            'data'=>Product::checkList($results),
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage)
        );
    }

}    