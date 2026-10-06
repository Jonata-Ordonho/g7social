<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Voluntario;

class VoluntarioController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    public function getVolunteersByActivity($idActivity){
        try {
            $query = "SELECT * FROM voluntarios WHERE id_atividade = :id_atividade ORDER BY nome ASC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getVolunteersTotalByActivity($idActivity){
        try {
            $query = "SELECT COUNT(*) FROM voluntarios WHERE id_atividade = :id_atividade";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function checkVolunteerEmail($idActivity, $email){
        try {
            $query = "SELECT id_voluntario FROM voluntarios WHERE id_atividade = :id_atividade AND email = :email";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->bindValue(':email', $email, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() != 0;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertVolunteer(Voluntario $obj){
        try {
            $query = "INSERT INTO voluntarios (id_atividade, nome, email, telefone) VALUES (:id_atividade, :nome, :email, :telefone)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':id_atividade', $obj->getAtividadeID(), \PDO::PARAM_INT);
            $insert->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $insert->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            $insert->bindValue(':telefone', $obj->getTelefone(), \PDO::PARAM_STR);
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
}
