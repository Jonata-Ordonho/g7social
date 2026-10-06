<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Usuario;

class UsuarioController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    public function authenticationUserLogin($userEmail, $userPassword){
        try {
            $query_login = "SELECT * FROM usuarios WHERE email = :email AND senha = :password";
            $stmt_login = $this->conn->connectBd()->prepare($query_login);
            $stmt_login->bindValue(':email', $userEmail, \PDO::PARAM_STR);
            $stmt_login->bindValue(':password', sha1($userPassword), \PDO::PARAM_STR);
            $stmt_login->execute();

            if ($stmt_login->rowCount() != 0) {
                return $stmt_login->fetch(\PDO::FETCH_ASSOC);
            } else {
                return NULL;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function checkUserLogin($userEmail){
        try {
            $query_login = "SELECT * FROM usuarios WHERE email = :email";
            $stmt_login = $this->conn->connectBd()->prepare($query_login);
            $stmt_login->bindValue(':email', $userEmail, \PDO::PARAM_STR);
            $stmt_login->execute();

            if ($stmt_login->rowCount() != 0) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    private function getFilterSql($filter){
        $sqlFilter = '';
        if (isset($filter['consulta'])) {
            $sqlFilter .= " AND (nome LIKE :consulta OR email LIKE :consulta) ";
        }
        if (isset($filter['tipo'])) {
            $sqlFilter .= " AND tipo = :tipo ";
        }
        if (isset($filter['status'])) {
            $sqlFilter .= " AND status = :status ";
        }

        return $sqlFilter;
    }

    private function bindFilterValues($stmt, $filter){
        if (isset($filter['consulta'])) {
            $stmt->bindValue(':consulta', '%' . $filter['consulta'] . '%', \PDO::PARAM_STR);
        }
        if (isset($filter['tipo'])) {
            $stmt->bindValue(':tipo', $filter['tipo'], \PDO::PARAM_STR);
        }
        if (isset($filter['status'])) {
            $stmt->bindValue(':status', $filter['status'], \PDO::PARAM_STR);
        }
    }

    public function getUsersList($filter, $startRow, $maxRows){
        try {
            $query = "SELECT id_usuario, nome, email, tipo, status, data_registro FROM usuarios WHERE id_usuario IS NOT NULL" . $this->getFilterSql($filter) . " ORDER BY status ASC, nome ASC LIMIT :inicio, :fim";
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter);
            $stmt->bindValue(':inicio', $startRow, \PDO::PARAM_INT);
            $stmt->bindValue(':fim', $maxRows, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getUsersTotal($filter){
        try {
            $query = "SELECT COUNT(*) FROM usuarios WHERE id_usuario IS NOT NULL" . $this->getFilterSql($filter);
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertNewUser(Usuario $obj){
        try {
            $query_insert = "INSERT INTO usuarios (nome, email, senha, tipo, status) VALUES (:nome, :email, :senha, :tipo, :status)";
            $insert = $this->conn->connectBd()->prepare($query_insert);
            $insert->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $insert->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            $insert->bindValue(':senha', sha1($obj->getSenha()), \PDO::PARAM_STR);
            $insert->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $insert->bindValue(':status', $obj->getStatus() == 'off' ? 'off' : 'on', \PDO::PARAM_STR);
            $result = $insert->execute();

            if ($result) {
                return $this->conn->connectBd()->lastInsertId();
            } else {
                return 0;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getUserByID($idUser){
        try {
            $query_user = "SELECT * FROM usuarios WHERE id_usuario = :id";
            $stmt_user = $this->conn->connectBd()->prepare($query_user);
            $stmt_user->bindValue(':id', $idUser, \PDO::PARAM_INT);
            $stmt_user->execute();

            if ($stmt_user->rowCount() != 0) {
                return $stmt_user->fetch(\PDO::FETCH_ASSOC);
            } else {
                return NULL;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function updateUser(Usuario $obj){
        try {
            $update_senha = '';
            if ($obj->getSenha() != NULL) {
                $update_senha = " senha = :senha,";
            }

            $query_update = "UPDATE usuarios SET nome = :nome, email = :email," . $update_senha . " status = :status, tipo = :tipo WHERE id_usuario = :id";
            $update = $this->conn->connectBd()->prepare($query_update);
            $update->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $update->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            if ($update_senha != '') {
                $update->bindValue(':senha', sha1($obj->getSenha()), \PDO::PARAM_STR);
            }
            $update->bindValue(':status', $obj->getStatus(), \PDO::PARAM_STR);
            $update->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getUsuarioID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function updateUserPersonalData(Usuario $obj){
        try {
            $update_senha = '';
            if ($obj->getSenha() != NULL) {
                $update_senha = ", senha = :senha";
            }

            $query_update = "UPDATE usuarios SET nome = :nome, email = :email" . $update_senha . " WHERE id_usuario = :id";
            $update = $this->conn->connectBd()->prepare($query_update);
            $update->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $update->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            if ($update_senha != '') {
                $update->bindValue(':senha', sha1($obj->getSenha()), \PDO::PARAM_STR);
            }
            $update->bindValue(':id', $obj->getUsuarioID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function updateUserStatus($idUser, $status){
        try {
            $query_update = "UPDATE usuarios SET status = :status WHERE id_usuario = :id";
            $update = $this->conn->connectBd()->prepare($query_update);
            $update->bindValue(':status', $status, \PDO::PARAM_STR);
            $update->bindValue(':id', $idUser, \PDO::PARAM_INT);
            $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
