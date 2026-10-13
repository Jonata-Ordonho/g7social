<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;

// Dados dos relatórios: geral (projetos de um período) e de um projeto específico
class RelatorioController{
    private $conn;

    // Projetos cujo período (data início/fim) cruza o intervalo do filtro
    const FILTRO_PROJETOS = "p.data_inicio <= :fim AND p.data_fim >= :inicio";

    public function __construct(){
        $this->conn = new Connections;
    }

    private function lista($query, $params = array()){
        $stmt = $this->conn->connectBd()->prepare($query);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, is_int($valor) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function linha($query, $params = array()){
        $resultado = $this->lista($query, $params);
        return count($resultado) > 0 ? $resultado[0] : NULL;
    }

    /***** RELATÓRIO GERAL *****/

    public function getResumoGeral($inicio, $fim){
        try {
            $params = array(':inicio' => $inicio, ':fim' => $fim);
            $projetos = "SELECT p.id_projeto FROM projetos AS p WHERE " . self::FILTRO_PROJETOS;

            $resumo = $this->linha(
                "SELECT COUNT(*) AS projetos, COALESCE(SUM(p.arrecadacao_estimada), 0) AS estimado
                 FROM projetos AS p WHERE " . self::FILTRO_PROJETOS, $params);

            $resumo += $this->linha(
                "SELECT COUNT(DISTINCT a.id_atividade) AS atividades, COUNT(i.id_inscricao) AS inscricoes,
                        COUNT(DISTINCT i.id_voluntario) AS voluntarios
                 FROM atividades AS a
                 LEFT JOIN inscricoes AS i ON i.id_atividade = a.id_atividade
                 WHERE a.id_projeto IN ($projetos)", $params);

            $resumo += $this->linha(
                "SELECT COALESCE(SUM(CASE WHEN ar.tipo = 'dinheiro' THEN ar.valor END), 0) AS arrecadado,
                        COALESCE(SUM(ar.tipo = 'dinheiro'), 0) AS doacoes_dinheiro,
                        COALESCE(SUM(ar.tipo = 'itens'), 0) AS doacoes_itens,
                        COUNT(DISTINCT ar.id_doador) AS doadores
                 FROM arrecadacoes AS ar
                 WHERE ar.id_projeto IN ($projetos)", $params);

            return $resumo;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getProjetosGeral($inicio, $fim){
        try {
            return $this->lista(
                "SELECT p.id_projeto, p.titulo, p.data_inicio, p.data_fim, p.arrecadacao_estimada,
                        (SELECT COUNT(*) FROM atividades AS a WHERE a.id_projeto = p.id_projeto) AS atividades,
                        (SELECT COUNT(*) FROM inscricoes AS i INNER JOIN atividades AS a ON a.id_atividade = i.id_atividade
                         WHERE a.id_projeto = p.id_projeto) AS inscricoes,
                        (SELECT COALESCE(SUM(ar.valor), 0) FROM arrecadacoes AS ar WHERE ar.id_projeto = p.id_projeto AND ar.tipo = 'dinheiro') AS arrecadado,
                        (SELECT COUNT(*) FROM arrecadacoes AS ar WHERE ar.id_projeto = p.id_projeto AND ar.tipo = 'dinheiro') AS doacoes_dinheiro,
                        (SELECT COUNT(*) FROM arrecadacoes AS ar WHERE ar.id_projeto = p.id_projeto AND ar.tipo = 'itens') AS doacoes_itens
                 FROM projetos AS p
                 WHERE " . self::FILTRO_PROJETOS . "
                 ORDER BY p.data_inicio ASC, p.titulo ASC",
                array(':inicio' => $inicio, ':fim' => $fim)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Doações em dinheiro dos projetos do período, por mês do intervalo (meses sem doação entram com zero)
    public function getArrecadacaoPorMes($inicio, $fim){
        try {
            $dados = $this->lista(
                "SELECT DATE_FORMAT(ar.data_arrecadacao, '%Y-%m') AS mes, SUM(ar.valor) AS total, COUNT(*) AS doacoes
                 FROM arrecadacoes AS ar
                 INNER JOIN projetos AS p ON p.id_projeto = ar.id_projeto
                 WHERE ar.tipo = 'dinheiro' AND ar.data_arrecadacao BETWEEN :inicio2 AND :fim2 AND " . self::FILTRO_PROJETOS . "
                 GROUP BY mes",
                array(':inicio' => $inicio, ':fim' => $fim, ':inicio2' => $inicio, ':fim2' => $fim)
            );
            $porMes = array();
            foreach ($dados as $d) { $porMes[$d['mes']] = $d; }

            $resultado = array();
            $mes = date('Y-m', strtotime($inicio));
            $ultimo = date('Y-m', strtotime($fim));
            while ($mes <= $ultimo) {
                $resultado[] = array(
                    'mes' => $mes,
                    'total' => isset($porMes[$mes]) ? (float) $porMes[$mes]['total'] : 0.0,
                    'doacoes' => isset($porMes[$mes]) ? (int) $porMes[$mes]['doacoes'] : 0,
                );
                $mes = date('Y-m', strtotime($mes . '-01 +1 month'));
            }
            return $resultado;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getTopDoadores($inicio, $fim, $limite = 10){
        try {
            return $this->lista(
                "SELECT d.id_doador, d.nome, d.tipo, COUNT(*) AS doacoes,
                        COALESCE(SUM(CASE WHEN ar.tipo = 'dinheiro' THEN ar.valor END), 0) AS total,
                        COUNT(DISTINCT ar.id_projeto) AS projetos
                 FROM arrecadacoes AS ar
                 INNER JOIN doadores AS d ON d.id_doador = ar.id_doador
                 INNER JOIN projetos AS p ON p.id_projeto = ar.id_projeto
                 WHERE " . self::FILTRO_PROJETOS . "
                 GROUP BY d.id_doador, d.nome, d.tipo
                 ORDER BY total DESC, doacoes DESC, d.nome ASC
                 LIMIT :limite",
                array(':inicio' => $inicio, ':fim' => $fim, ':limite' => (int) $limite)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getTopVoluntarios($inicio, $fim, $limite = 10){
        try {
            return $this->lista(
                "SELECT v.id_voluntario, v.nome, COUNT(*) AS inscricoes, COUNT(DISTINCT p.id_projeto) AS projetos,
                        MAX(a.data_inicio) AS ultima_atividade
                 FROM inscricoes AS i
                 INNER JOIN voluntarios AS v ON v.id_voluntario = i.id_voluntario
                 INNER JOIN atividades AS a ON a.id_atividade = i.id_atividade
                 INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                 WHERE " . self::FILTRO_PROJETOS . "
                 GROUP BY v.id_voluntario, v.nome
                 ORDER BY inscricoes DESC, v.nome ASC
                 LIMIT :limite",
                array(':inicio' => $inicio, ':fim' => $fim, ':limite' => (int) $limite)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /***** RELATÓRIO POR PROJETO *****/

    // Projetos para o seletor do relatório (mais recentes primeiro)
    public function getProjetosSelect(){
        try {
            return $this->lista("SELECT id_projeto, titulo, data_inicio, data_fim FROM projetos ORDER BY data_inicio DESC, titulo ASC");
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getResumoProjeto($idProject){
        try {
            $params = array(':id' => (int) $idProject);

            $resumo = $this->linha(
                "SELECT COUNT(DISTINCT a.id_atividade) AS atividades, COUNT(i.id_inscricao) AS inscricoes,
                        COUNT(DISTINCT i.id_voluntario) AS voluntarios
                 FROM atividades AS a
                 LEFT JOIN inscricoes AS i ON i.id_atividade = a.id_atividade
                 WHERE a.id_projeto = :id", $params);

            $resumo += $this->linha(
                "SELECT COALESCE(SUM(CASE WHEN tipo = 'dinheiro' THEN valor END), 0) AS arrecadado,
                        COALESCE(SUM(tipo = 'dinheiro'), 0) AS doacoes_dinheiro,
                        COALESCE(SUM(tipo = 'itens'), 0) AS doacoes_itens,
                        COUNT(DISTINCT id_doador) AS doadores
                 FROM arrecadacoes WHERE id_projeto = :id", $params);

            return $resumo;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getAtividadesProjeto($idProject){
        try {
            return $this->lista(
                "SELECT a.id_atividade, a.titulo, a.local, a.data_inicio, a.data_fim, a.horario, a.vagas, a.status,
                        (SELECT COUNT(*) FROM inscricoes AS i WHERE i.id_atividade = a.id_atividade) AS inscritos
                 FROM atividades AS a
                 WHERE a.id_projeto = :id
                 ORDER BY a.data_inicio ASC, a.titulo ASC",
                array(':id' => (int) $idProject)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getVoluntariosProjeto($idProject){
        try {
            return $this->lista(
                "SELECT a.id_atividade, a.titulo AS atividade_titulo, a.data_inicio,
                        v.id_voluntario, v.nome, v.cpf, v.telefone, v.email, i.data_inscricao
                 FROM inscricoes AS i
                 INNER JOIN voluntarios AS v ON v.id_voluntario = i.id_voluntario
                 INNER JOIN atividades AS a ON a.id_atividade = i.id_atividade
                 WHERE a.id_projeto = :id
                 ORDER BY a.data_inicio ASC, a.titulo ASC, v.nome ASC",
                array(':id' => (int) $idProject)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getArrecadacoesProjeto($idProject){
        try {
            return $this->lista(
                "SELECT ar.id_arrecadacao, ar.data_arrecadacao, ar.tipo, ar.valor, ar.descricao,
                        d.nome AS doador_nome, d.tipo AS doador_tipo
                 FROM arrecadacoes AS ar
                 INNER JOIN doadores AS d ON d.id_doador = ar.id_doador
                 WHERE ar.id_projeto = :id
                 ORDER BY ar.data_arrecadacao ASC, ar.id_arrecadacao ASC",
                array(':id' => (int) $idProject)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getDoadoresProjeto($idProject){
        try {
            return $this->lista(
                "SELECT d.id_doador, d.nome, d.tipo, COUNT(*) AS doacoes,
                        COALESCE(SUM(CASE WHEN ar.tipo = 'dinheiro' THEN ar.valor END), 0) AS total,
                        SUM(ar.tipo = 'itens') AS doacoes_itens
                 FROM arrecadacoes AS ar
                 INNER JOIN doadores AS d ON d.id_doador = ar.id_doador
                 WHERE ar.id_projeto = :id
                 GROUP BY d.id_doador, d.nome, d.tipo
                 ORDER BY total DESC, doacoes DESC, d.nome ASC",
                array(':id' => (int) $idProject)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
