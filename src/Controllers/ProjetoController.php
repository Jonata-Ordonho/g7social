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
        if (isset($filter['captacao']) && $filter['captacao'] == 'empresas') {
            $sqlFilter .= " AND p.captacao_empresas = 's' ";
        } elseif (isset($filter['captacao']) && $filter['captacao'] == 'voluntarios') {
            $sqlFilter .= " AND p.captacao_voluntarios = 's' ";
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
                        COALESCE(a.total_atividades, 0) AS total_atividades, a.atividade_inicio, a.atividade_fim,
                        COALESCE(arr.total_doacoes, 0) AS total_doacoes,
                        COALESCE(arr.total_dinheiro, 0) AS total_dinheiro,
                        CASE WHEN p.data_fim < :hoje THEN 'finalizado' ELSE 'ativo' END AS situacao
                      FROM projetos AS p
                      LEFT JOIN (
                          SELECT id_projeto, COUNT(*) AS total_atividades, MIN(data_inicio) AS atividade_inicio, MAX(data_fim) AS atividade_fim
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

    public function updateProject(Projeto $obj){
        try {
            $query = "UPDATE projetos SET titulo = :titulo, descricao = :descricao, captacao_empresas = :captacao_empresas, captacao_voluntarios = :captacao_voluntarios, data_inicio = :data_inicio, data_fim = :data_fim WHERE id_projeto = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':titulo', $obj->getTitulo(), \PDO::PARAM_STR);
            $update->bindValue(':descricao', $obj->getDescricao(), \PDO::PARAM_STR);
            $update->bindValue(':captacao_empresas', $obj->getCaptacaoEmpresas(), \PDO::PARAM_STR);
            $update->bindValue(':captacao_voluntarios', $obj->getCaptacaoVoluntarios(), \PDO::PARAM_STR);
            $update->bindValue(':data_inicio', $obj->getDataInicio(), \PDO::PARAM_STR);
            $update->bindValue(':data_fim', $obj->getDataFim(), \PDO::PARAM_STR);
            $update->bindValue(':id', $obj->getProjetoID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
