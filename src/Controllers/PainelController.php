<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;

// Números e listas exibidos no painel inicial do gestor
class PainelController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    private function valor($query, $params = array()){
        $stmt = $this->conn->connectBd()->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    private function lista($query, $params = array()){
        $stmt = $this->conn->connectBd()->prepare($query);
        foreach ($params as $chave => $valor) {
            $stmt->bindValue($chave, $valor, is_int($valor) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getIndicadores($today){
        try {
            $trintaDias = date('Y-m-d', strtotime($today.' -30 days'));

            return array(
                'projetos_total'      => (int) $this->valor("SELECT COUNT(*) FROM projetos"),
                'projetos_ativos'     => (int) $this->valor("SELECT COUNT(*) FROM projetos WHERE data_fim >= ?", array($today)),
                'atividades_abertas'  => (int) $this->valor("SELECT COUNT(*) FROM atividades AS a INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                                                                 WHERE a.status = 'on' AND a.data_fim >= ? AND p.data_fim >= ?", array($today, $today)),
                'atividades_total'    => (int) $this->valor("SELECT COUNT(*) FROM atividades"),
                'voluntarios_total'   => (int) $this->valor("SELECT COUNT(*) FROM voluntarios"),
                'voluntarios_30_dias' => (int) $this->valor("SELECT COUNT(*) FROM voluntarios WHERE data_registro >= ?", array($trintaDias)),
                'inscricoes_total'    => (int) $this->valor("SELECT COUNT(*) FROM inscricoes"),
                'arrecadado_dinheiro' => (float) $this->valor("SELECT COALESCE(SUM(valor), 0) FROM arrecadacoes WHERE tipo = 'dinheiro'"),
                'doacoes_dinheiro'    => (int) $this->valor("SELECT COUNT(*) FROM arrecadacoes WHERE tipo = 'dinheiro'"),
                'doacoes_itens'       => (int) $this->valor("SELECT COUNT(*) FROM arrecadacoes WHERE tipo = 'itens'"),
                'doadores_empresas'   => (int) $this->valor("SELECT COUNT(*) FROM doadores WHERE tipo = 'empresa'"),
                'doadores_pessoas'    => (int) $this->valor("SELECT COUNT(*) FROM doadores WHERE tipo = 'pessoa'"),
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Soma das doações em dinheiro por mês, dos últimos $meses meses (inclui meses sem doação, com zero)
    public function getArrecadacaoPorMes($today, $meses = 6){
        try {
            $inicio = date('Y-m-01', strtotime(date('Y-m-01', strtotime($today)).' -'.($meses - 1).' months'));
            $dados = $this->lista(
                "SELECT DATE_FORMAT(data_arrecadacao, '%Y-%m') AS mes, SUM(valor) AS total, COUNT(*) AS doacoes
                 FROM arrecadacoes
                 WHERE tipo = 'dinheiro' AND data_arrecadacao >= :inicio AND data_arrecadacao <= :hoje
                 GROUP BY mes",
                array(':inicio' => $inicio, ':hoje' => $today)
            );
            $porMes = array();
            foreach ($dados as $d) { $porMes[$d['mes']] = $d; }

            $resultado = array();
            for ($i = 0; $i < $meses; $i++) {
                $mes = date('Y-m', strtotime($inicio.' +'.$i.' months'));
                $resultado[] = array(
                    'mes' => $mes,
                    'total' => isset($porMes[$mes]) ? (float) $porMes[$mes]['total'] : 0.0,
                    'doacoes' => isset($porMes[$mes]) ? (int) $porMes[$mes]['doacoes'] : 0,
                );
            }
            return $resultado;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getProximasAtividades($today, $limite = 5){
        try {
            return $this->lista(
                "SELECT a.id_atividade, a.titulo, a.data_inicio, a.data_fim, a.vagas, p.titulo AS projeto_titulo,
                        (SELECT COUNT(*) FROM inscricoes AS ins WHERE ins.id_atividade = a.id_atividade) AS total_voluntarios
                 FROM atividades AS a
                 INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                 WHERE a.status = 'on' AND a.data_fim >= :hoje
                 ORDER BY a.data_inicio ASC, a.id_atividade ASC
                 LIMIT :limite",
                array(':hoje' => $today, ':limite' => (int) $limite)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getUltimasArrecadacoes($limite = 5){
        try {
            return $this->lista(
                "SELECT a.id_arrecadacao, a.id_projeto, a.tipo, a.valor, a.descricao, a.data_arrecadacao,
                        d.nome AS doador_nome, p.titulo AS projeto_titulo
                 FROM arrecadacoes AS a
                 INNER JOIN doadores AS d ON d.id_doador = a.id_doador
                 INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                 ORDER BY a.data_arrecadacao DESC, a.id_arrecadacao DESC
                 LIMIT :limite",
                array(':limite' => (int) $limite)
            );
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
