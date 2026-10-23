<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Arrecadacao;

class ArrecadacaoController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    public function getDonationsByProject($idProject, $filter = array()){
        try {
            $sqlFilter = '';
            if (isset($filter['consulta'])) {
                $sqlFilter .= " AND (d.nome LIKE :consulta OR a.descricao LIKE :consulta) ";
            }
            if (isset($filter['tipo'])) {
                $sqlFilter .= " AND a.tipo = :tipo ";
            }

            $query = "SELECT a.*, d.nome AS doador_nome, d.tipo AS doador_tipo, d.documento AS doador_documento
                      FROM arrecadacoes AS a
                      INNER JOIN doadores AS d ON d.id_doador = a.id_doador
                      WHERE a.id_projeto = :id_projeto" . $sqlFilter . "
                      ORDER BY a.data_arrecadacao DESC, a.id_arrecadacao DESC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_projeto', $idProject, \PDO::PARAM_INT);
            if (isset($filter['consulta'])) {
                $stmt->bindValue(':consulta', '%' . $filter['consulta'] . '%', \PDO::PARAM_STR);
            }
            if (isset($filter['tipo'])) {
                $stmt->bindValue(':tipo', $filter['tipo'], \PDO::PARAM_STR);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDonationsByDonor($idDonor){
        try {
            $query = "SELECT a.*, p.titulo AS projeto_titulo
                      FROM arrecadacoes AS a
                      INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                      WHERE a.id_doador = :id_doador
                      ORDER BY a.data_arrecadacao DESC, a.id_arrecadacao DESC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_doador', $idDonor, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDonationByID($idDonation){
        try {
            $query = "SELECT * FROM arrecadacoes WHERE id_arrecadacao = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idDonation, \PDO::PARAM_INT);
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

    public function updateDonation(Arrecadacao $obj){
        try {
            $query = "UPDATE arrecadacoes SET id_doador = :id_doador, tipo = :tipo, valor = :valor, descricao = :descricao, data_arrecadacao = :data_arrecadacao WHERE id_arrecadacao = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':id_doador', $obj->getDoadorID(), \PDO::PARAM_INT);
            $update->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $update->bindValue(':valor', $obj->getValor(), $obj->getValor() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':descricao', $obj->getDescricao(), $obj->getDescricao() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':data_arrecadacao', $obj->getDataArrecadacao(), \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getArrecadacaoID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertDonation(Arrecadacao $obj){
        try {
            $query = "INSERT INTO arrecadacoes (id_projeto, id_doador, tipo, valor, descricao, data_arrecadacao) VALUES (:id_projeto, :id_doador, :tipo, :valor, :descricao, :data_arrecadacao)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':id_projeto', $obj->getProjetoID(), \PDO::PARAM_INT);
            $insert->bindValue(':id_doador', $obj->getDoadorID(), \PDO::PARAM_INT);
            $insert->bindValue(':tipo', $obj->getTipo(), \PDO::PARAM_STR);
            $insert->bindValue(':valor', $obj->getValor(), $obj->getValor() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $insert->bindValue(':descricao', $obj->getDescricao(), $obj->getDescricao() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $insert->bindValue(':data_arrecadacao', $obj->getDataArrecadacao(), \PDO::PARAM_STR);
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

    public function deleteDonation($idDonation){
        try {
            $query = "DELETE FROM arrecadacoes WHERE id_arrecadacao = :id";
            $delete = $this->conn->connectBd()->prepare($query);
            $delete->bindValue(':id', $idDonation, \PDO::PARAM_INT);
            $delete->execute();

            return $delete->rowCount() == 1;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
