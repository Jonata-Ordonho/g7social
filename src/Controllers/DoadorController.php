<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Doador;

class DoadorController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    private function getFilterSql($filter){
        $sqlFilter = '';
        if (isset($filter['consulta'])) {
            $sqlFilter .= " AND (nome LIKE :consulta OR email LIKE :consulta";
            if (isset($filter['consulta_numeros'])) {
                $sqlFilter .= " OR documento LIKE :consulta_numeros";
            }
            $sqlFilter .= ") ";
        }
        if (isset($filter['tipo'])) {
            $sqlFilter .= " AND tipo = :tipo ";
        }

        return $sqlFilter;
    }

    private function bindFilterValues($stmt, $filter){
        if (isset($filter['consulta'])) {
            $stmt->bindValue(':consulta', '%' . $filter['consulta'] . '%', \PDO::PARAM_STR);
            if (isset($filter['consulta_numeros'])) {
                $stmt->bindValue(':consulta_numeros', '%' . $filter['consulta_numeros'] . '%', \PDO::PARAM_STR);
            }
        }
        if (isset($filter['tipo'])) {
            $stmt->bindValue(':tipo', $filter['tipo'], \PDO::PARAM_STR);
        }
    }

    public function getDonorsList($filter, $startRow, $maxRows){
        try {
            $query = "SELECT * FROM doadores WHERE id_doador IS NOT NULL" . $this->getFilterSql($filter) . " ORDER BY nome ASC LIMIT :inicio, :fim";
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

    public function getDonorsTotal($filter){
        try {
            $query = "SELECT COUNT(*) FROM doadores WHERE id_doador IS NOT NULL" . $this->getFilterSql($filter);
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function checkDonorDocument($type, $document, $ignoreID = 0){
        try {
            $query = "SELECT id_doador FROM doadores WHERE tipo = :tipo AND documento = :documento AND id_doador <> :ignorar";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':tipo', $type, \PDO::PARAM_STR);
            $stmt->bindValue(':documento', $document, \PDO::PARAM_STR);
            $stmt->bindValue(':ignorar', $ignoreID, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() != 0;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDonorByID($idDonor){
        try {
            $query = "SELECT * FROM doadores WHERE id_doador = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idDonor, \PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() != 0) {
                return $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                return NULL;
            }
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function updateDonor(Doador $obj){
        try {
            $query = "UPDATE doadores SET tipo = :tipo, nome = :nome, documento = :documento, email = :email, telefone = :telefone WHERE id_doador = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $update->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $update->bindValue(':documento', $obj->getDocumento(), \PDO::PARAM_STR);
            $update->bindValue(':email', $obj->getEmail(), $obj->getEmail() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':telefone', $obj->getTelefone(), $obj->getTelefone() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getDoadorID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertDonor(Doador $obj){
        try {
            $query = "INSERT INTO doadores (tipo, nome, documento, email, telefone, id_usuario) VALUES (:tipo, :nome, :documento, :email, :telefone, :id_usuario)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $insert->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $insert->bindValue(':documento', $obj->getDocumento(), \PDO::PARAM_STR);
            $insert->bindValue(':email', $obj->getEmail(), $obj->getEmail() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $insert->bindValue(':telefone', $obj->getTelefone(), $obj->getTelefone() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $insert->bindValue(':id_usuario', $obj->getUsuarioID(), \PDO::PARAM_INT);
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
