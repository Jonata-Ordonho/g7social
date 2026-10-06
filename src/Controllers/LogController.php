<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;

class LogController{
    private $conn;

    public function __construct()
    {
        $this->conn = new Connections;
    }

    public function insertActionUserLog($idAction, $idUser, $description){
        try {
            $query = "INSERT INTO logs(id_acao, id_usuario, descricao) VALUES(:id_acao, :id_usuario, :descricao)";
            $pst = $this->conn->connectBd()->prepare($query);
            $pst->bindValue(':id_acao', $idAction, \PDO::PARAM_INT);
            $pst->bindValue(':id_usuario', $idUser, \PDO::PARAM_INT);
            $pst->bindValue(':descricao', $description, \PDO::PARAM_STR);
            $pst->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getLastActionUser($idAction){
        try {
            $query = "SELECT l.id_log, l.data_registro, u.nome FROM logs AS l INNER JOIN usuarios AS u ON l.id_usuario = u.id_usuario WHERE l.id_acao = :id_acao ORDER BY l.data_registro DESC LIMIT 1";
            $pst = $this->conn->connectBd()->prepare($query);
            $pst->bindValue(':id_acao', $idAction, \PDO::PARAM_INT);
            $pst->execute();

            if($pst->rowCount()){
                return $pst->fetch(\PDO::FETCH_ASSOC);
            }else{
                return null;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
