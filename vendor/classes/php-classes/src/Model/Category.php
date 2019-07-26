<?php

namespace Hcode\Model;
use \Hcode\Db\Sql;
use \Hcode\Model;
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

        
    }
}    