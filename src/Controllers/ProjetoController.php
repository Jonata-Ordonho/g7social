<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Projeto;

class ProjetoController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    private function getFilterSql($filter){
        $sqlFilter = '';
        // Ativo enquanto a data fim não passou; depois disso, finalizado
        if (isset($filter['situacao']) && $filter['situacao'] == 'ativos') {
            $sqlFilter .= " AND p.data_fim >= :hoje ";
        } elseif (isset($filter['situacao']) && $filter['situacao'] == 'finalizados') {
            $sqlFilter .= " AND p.data_fim < :hoje ";
        }
        if (isset($filter['consulta'])) {
            $sqlFilter .= " AND p.titulo LIKE :consulta ";
        }

        return $sqlFilter;
    }

    private function bindFilterValues($stmt, $filter, $sql){
        if (strpos($sql, ':hoje') !== false) {
            $stmt->bindValue(':hoje', $filter['hoje'], \PDO::PARAM_STR);
        }
        if (isset($filter['consulta'])) {
            $stmt->bindValue(':consulta', '%' . $filter['consulta'] . '%', \PDO::PARAM_STR);
        }
    }

    public function getProjectsList($filter, $startRow, $maxRows){
        try {
            // Ordem: ativos (que terminam primeiro) e depois finalizados (mais recentes primeiro)
            $query = "SELECT p.*,
                        COALESCE(a.total_atividades, 0) AS total_atividades,
                        COALESCE(arr.total_doacoes, 0) AS total_doacoes,
                        COALESCE(arr.total_dinheiro, 0) AS total_dinheiro,
                        CASE WHEN p.data_fim < :hoje THEN 'finalizado' ELSE 'ativo' END AS situacao
                      FROM projetos AS p
                      LEFT JOIN (
                          SELECT id_projeto, COUNT(*) AS total_atividades
                          FROM atividades
                          GROUP BY id_projeto
                      ) AS a ON a.id_projeto = p.id_projeto
                      LEFT JOIN (
                          SELECT id_projeto, COUNT(*) AS total_doacoes, SUM(valor) AS total_dinheiro
                          FROM arrecadacoes
                          GROUP BY id_projeto
                      ) AS arr ON arr.id_projeto = p.id_projeto
                      WHERE p.id_projeto IS NOT NULL" . $this->getFilterSql($filter) . "
                      ORDER BY FIELD(situacao, 'ativo', 'finalizado'),
                        CASE WHEN p.data_fim < :hoje THEN NULL ELSE p.data_fim END ASC,
                        p.data_fim DESC
                      LIMIT :inicio, :fim";
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter, $query);
            $stmt->bindValue(':inicio', $startRow, \PDO::PARAM_INT);
            $stmt->bindValue(':fim', $maxRows, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getProjectsTotal($filter){
        try {
            $query = "SELECT COUNT(*) FROM projetos AS p WHERE p.id_projeto IS NOT NULL" . $this->getFilterSql($filter);
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter, $query);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getProjectByID($idProject){
        try {
            $query = "SELECT * FROM projetos WHERE id_projeto = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idProject, \PDO::PARAM_INT);
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

    public function getProjectByCode($code){
        try {
            $query = "SELECT * FROM projetos WHERE codigo = :codigo";
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

    // Retorna o código do link público do projeto, gerando um se ainda não existir
    public function getProjectCode($idProject, $currentCode = NULL){
        if ($currentCode != NULL) {
            return $currentCode;
        }
        try {
            $code = bin2hex(random_bytes(16));
            $query = "UPDATE projetos SET codigo = :codigo WHERE id_projeto = :id AND codigo IS NULL";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':codigo', $code, \PDO::PARAM_STR);
            $update->bindValue(':id', $idProject, \PDO::PARAM_INT);
            $update->execute();

            // Lê de novo: outra requisição pode ter gerado o código ao mesmo tempo
            $projeto = $this->getProjectByID($idProject);
            return $projeto != NULL ? $projeto['codigo'] : NULL;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertProject(Projeto $obj){
        try {
            $query = "INSERT INTO projetos (titulo, descricao, data_inicio, data_fim, arrecadacao_estimada, codigo, id_usuario) VALUES (:titulo, :descricao, :data_inicio, :data_fim, :arrecadacao_estimada, :codigo, :id_usuario)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':titulo', $obj->getTitulo(), \PDO::PARAM_STR);
            $insert->bindValue(':descricao', $obj->getDescricao(), \PDO::PARAM_STR);
            $insert->bindValue(':data_inicio', $obj->getDataInicio(), \PDO::PARAM_STR);
            $insert->bindValue(':data_fim', $obj->getDataFim(), \PDO::PARAM_STR);
            $insert->bindValue(':arrecadacao_estimada', $obj->getArrecadacaoEstimada(), $obj->getArrecadacaoEstimada() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            // Código aleatório usado no link público de inscrição do projeto
            $insert->bindValue(':codigo', bin2hex(random_bytes(16)), \PDO::PARAM_STR);
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

    public function updateProject(Projeto $obj){
        try {
            $query = "UPDATE projetos SET titulo = :titulo, descricao = :descricao, data_inicio = :data_inicio, data_fim = :data_fim, arrecadacao_estimada = :arrecadacao_estimada WHERE id_projeto = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':titulo', $obj->getTitulo(), \PDO::PARAM_STR);
            $update->bindValue(':descricao', $obj->getDescricao(), \PDO::PARAM_STR);
            $update->bindValue(':data_inicio', $obj->getDataInicio(), \PDO::PARAM_STR);
            $update->bindValue(':data_fim', $obj->getDataFim(), \PDO::PARAM_STR);
            $update->bindValue(':arrecadacao_estimada', $obj->getArrecadacaoEstimada(), $obj->getArrecadacaoEstimada() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getProjetoID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
