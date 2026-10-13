<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Atividade;

class AtividadeController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    public function getActivitiesByProject($idProject){
        try {
            $query = "SELECT a.*, (SELECT COUNT(*) FROM inscricoes AS ins WHERE ins.id_atividade = a.id_atividade) AS total_voluntarios
                      FROM atividades AS a
                      WHERE a.id_projeto = :id_projeto
                      ORDER BY a.data_inicio ASC, a.id_atividade ASC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_projeto', $idProject, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Atividades com inscrição aberta na página pública do projeto: ativas e ainda não encerradas
    public function getOpenActivitiesByProject($idProject, $today){
        try {
            $query = "SELECT a.*, (SELECT COUNT(*) FROM inscricoes AS ins WHERE ins.id_atividade = a.id_atividade) AS total_voluntarios
                      FROM atividades AS a
                      WHERE a.id_projeto = :id_projeto AND a.status = 'on' AND a.data_fim >= :hoje
                      ORDER BY a.data_inicio ASC, a.id_atividade ASC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_projeto', $idProject, \PDO::PARAM_INT);
            $stmt->bindValue(':hoje', $today, \PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getActivityByID($idActivity){
        try {
            $query = "SELECT * FROM atividades WHERE id_atividade = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idActivity, \PDO::PARAM_INT);
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

    public function getActivityByCode($code){
        try {
            $query = "SELECT a.*, p.titulo AS projeto_titulo, p.descricao AS projeto_descricao, p.codigo AS projeto_codigo, p.data_fim AS projeto_data_fim,
                        (SELECT COUNT(*) FROM inscricoes AS ins WHERE ins.id_atividade = a.id_atividade) AS total_voluntarios
                      FROM atividades AS a
                      INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                      WHERE a.codigo = :codigo";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':codigo', $code, \PDO::PARAM_STR);
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

    public function updateActivityStatus($idActivity, $status){
        try {
            $query = "UPDATE atividades SET status = :status WHERE id_atividade = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':status', $status, \PDO::PARAM_STR);
            $update->bindValue(':id', $idActivity, \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertActivity(Atividade $obj){
        try {
            $query = "INSERT INTO atividades (id_projeto, titulo, descricao, local, data_inicio, data_fim, horario, vagas, codigo, status, id_usuario) VALUES (:id_projeto, :titulo, :descricao, :local, :data_inicio, :data_fim, :horario, :vagas, :codigo, :status, :id_usuario)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':id_projeto', $obj->getProjetoID(), \PDO::PARAM_INT);
            $insert->bindValue(':titulo', $obj->getTitulo(), \PDO::PARAM_STR);
            $insert->bindValue(':descricao', $obj->getDescricao(), \PDO::PARAM_STR);
            $insert->bindValue(':local', $obj->getLocal(), \PDO::PARAM_STR);
            $insert->bindValue(':data_inicio', $obj->getDataInicio(), \PDO::PARAM_STR);
            $insert->bindValue(':data_fim', $obj->getDataFim(), \PDO::PARAM_STR);
            $insert->bindValue(':horario', $obj->getHorario(), \PDO::PARAM_STR);
            $insert->bindValue(':vagas', $obj->getVagas(), $obj->getVagas() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
            // Código aleatório usado no link público de inscrição
            $insert->bindValue(':codigo', bin2hex(random_bytes(16)), \PDO::PARAM_STR);
            $insert->bindValue(':status', $obj->getStatus(), \PDO::PARAM_STR);
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

    public function updateActivity(Atividade $obj){
        try {
            $query = "UPDATE atividades SET titulo = :titulo, descricao = :descricao, local = :local, data_inicio = :data_inicio, data_fim = :data_fim, horario = :horario, vagas = :vagas, status = :status WHERE id_atividade = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':titulo', $obj->getTitulo(), \PDO::PARAM_STR);
            $update->bindValue(':descricao', $obj->getDescricao(), \PDO::PARAM_STR);
            $update->bindValue(':local', $obj->getLocal(), \PDO::PARAM_STR);
            $update->bindValue(':data_inicio', $obj->getDataInicio(), \PDO::PARAM_STR);
            $update->bindValue(':data_fim', $obj->getDataFim(), \PDO::PARAM_STR);
            $update->bindValue(':horario', $obj->getHorario(), \PDO::PARAM_STR);
            $update->bindValue(':vagas', $obj->getVagas(), $obj->getVagas() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_INT);
            $update->bindValue(':status', $obj->getStatus(), \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getAtividadeID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
