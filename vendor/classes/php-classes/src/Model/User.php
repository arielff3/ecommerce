<?php

namespace Hcode\Model;
use \Hcode\Db\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp7_secret";
    const SECRET_IV = "HcodePhp7_secret";

    public static function getFromSession()
    {
        $user = new User();
        
        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
        {
            $user->setData($_SESSION[User::SESSION]);
        }   

        return $user;
    }

    public static function checkLogin($inadmin = truel)
    {
        if (
            !isset($_SESSION[User::SESSION]) 
            || 
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0 
        ) {
            // Não está logado
            return false;
        }
        else
        {
            if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true ) 
            {
                return true;
            }
            else if ($inadmin === false )
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    // Método responsavel por validar o login
    public static function login($login, $password)
    {

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ':LOGIN'=>$login
        ));

        if(count($results) === 0 )
        {
            throw new \Exception("Usuário ou senha inválida!");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true)
        {
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
            return $user; 
            

        } else {
            throw new \Exception("Usuário ou senha inválida!");
        }


    }
    // Método que verifica se existe uma sessão, se não existir redireciona para página de loguin pôis só poderar
    // prosseguir com o acesso se estiver logado ( Com um sessão iniciada ) 
    public static function verifyLogin($inadmin = true)
    {
        if (User::checkLogin($inadmin)) 
        {
            header("location: /admin/login");
            exit;
        }
    }
    
    // Este método é responsável por Deslogar o usuário
    public static function logout()
    {

        $_SESSION[User::SESSION] = NULL;

    }

    // Este metodo retorna todos os usuários da tabela tb_users e da tb_persons 
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
        
    }

    // Este método é responsável por salvar dados atualizados de um usuario 
    public function save()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ':desperson'=>$this->getdesperson(),
            ':deslogin'=>$this->getdeslogin(),
            ':despassword'=>$this->getdespassword(),
            ':desemail'=>$this->getdesemail(),
            ':nrphone'=>$this->getnrphone(),
            ':inadmin'=>$this->getinadmin()
        ));

        $this->setData($result[0]);

    }
    
    // Este método é responsável por pegar um usuário utilizando somente o id que é passado por parâmetro
    public function get($iduser)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
            ':iduser'=>$iduser
        ));

        $this->setData($results[0]);
    }

    // Esse método é responsavel por pegar os dados que foram passados no setData e atualizalos no DB
    public function update()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ':iduser'=>$this->getiduser(),
            ':desperson'=>$this->getdesperson(),
            ':deslogin'=>$this->getdeslogin(),
            ':despassword'=>$this->getdespassword(),
            ':desemail'=>$this->getdesemail(),
            ':nrphone'=>$this->getnrphone(),
            ':inadmin'=>$this->getinadmin()
        ));

        $this->setData($result[0]);

    }

    // Deletar um usuário!
    public function delete()
    {
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ':iduser'=>$this->getiduser()
        ));
    }

    // Este método é responsável por selecionar um usuário pelo email que foi passado pelo parâmetro
    // e enviar o e-mail de recuperação de senha 
    public static function getForgot($email, $inadmin = true)
    {

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email", array(
            ':email'=>$email
        ));

        if (count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha");
        }
        else
        {

            $data = $results[0];

            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create( :iduser, :desip)", array(
                ':iduser'=>$data["iduser"],
                'desip'=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($results2) === 0 )
            {
                throw new \Exception("Não foi possível recuperar a senha");
            }
            else
            {
                $datarecovery = $results2[0];

                $code = openssl_encrypt($datarecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

                $link =  "http://www.ecommerce.com.br/admin/forgot/reset?code=$code";

                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
					"name"=>$data['desperson'],
					"link"=>$link
                ));

                $mailer->send();

                return $data;

            }
            
        }
    }

    // Esse método é responsável foi descriptografar o hash enviado pelo $_GET
    public static function validForgotDecrypt($code)
    {
        $code = base64_decode($code);
        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

        $sql = new Sql();
        $results = $sql->select("
            SELECT *
			FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
				AND
				a.dtrecovery IS NULL
				AND
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
		", array(
			':idrecovery'=>$idrecovery
        ));
        
        if(count($results) === 0)
        {
            throw new \Exception("Não foi possivel recuperar a senha");
        }
        else
        {

            return $results[0];

        }
    }
    
    public static function setForgotUsed($idrecovery)
    {

        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ':idrecovery'=>$idrecovery
        ));

    }

    // Inserir a senha nova no banco de dados
    public function setPassword($password)
    {

        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ':password'=>$password,
            ':iduser'=>$this->getiduser()
        ));
    }

    // Transformar a nova senha num hash antes de inseri-lá no banco de dados
    public static function getPasswordHash($password)
   {

    return password_hash($password, PASSWORD_DEFAULT, [
        // Nivel de segurança! 12 entá ótimo
        'cost'=>12
    ]);
    
   }

}




