<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Voluntario;

// Inscrições de voluntários em atividades
class InscricaoController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    public function getInscriptionsByActivity($idActivity){
        try {
            $query = "SELECT i.id_inscricao, i.data_inscricao, v.id_voluntario, v.nome, v.cpf, v.email, v.telefone
                      FROM inscricoes AS i
                      INNER JOIN voluntarios AS v ON v.id_voluntario = i.id_voluntario
                      WHERE i.id_atividade = :id_atividade
                      ORDER BY v.nome ASC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getInscriptionsTotalByActivity($idActivity){
        try {
            $query = "SELECT COUNT(*) FROM inscricoes WHERE id_atividade = :id_atividade";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getInscriptionByID($idInscription){
        try {
            $query = "SELECT i.*, v.nome, v.cpf, v.email
                      FROM inscricoes AS i
                      INNER JOIN voluntarios AS v ON v.id_voluntario = i.id_voluntario
                      WHERE i.id_inscricao = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idInscription, \PDO::PARAM_INT);
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

    // TRUE se o voluntário já está inscrito na atividade
    public function checkInscription($idVolunteer, $idActivity){
        try {
            $query = "SELECT id_inscricao FROM inscricoes WHERE id_voluntario = :id_voluntario AND id_atividade = :id_atividade";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id_voluntario', $idVolunteer, \PDO::PARAM_INT);
            $stmt->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() != 0;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertInscription($idVolunteer, $idActivity){
        try {
            $query = "INSERT INTO inscricoes (id_voluntario, id_atividade) VALUES (:id_voluntario, :id_atividade)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':id_voluntario', $idVolunteer, \PDO::PARAM_INT);
            $insert->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $result = $insert->execute();

            return $result ? $this->conn->connectBd()->lastInsertId() : 0;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Inscreve um voluntário numa atividade, usado no formulário público e no gestor.
     * Procura o voluntário pelo CPF; se não existir, cadastra. Os dados de um voluntário
     * já cadastrado não são alterados aqui (só pela tela de alteração do gestor).
     * Espera dados já validados (CPF e telefone só com números).
     * Retorna array('erro' => mensagem ou '', 'id_inscricao' => id, 'novo_voluntario' => bool).
     */
    // $atualizarDados: quando o CPF já está no banco, grava o nome, e-mail e telefone informados (página pública)
    public function registerInscription($dados, $atividade, $atualizarDados = false){
        $controllerVoluntario = new VoluntarioController;
        $voluntario = $controllerVoluntario->getVolunteerByCPF($dados['cpf']);

        if ($voluntario != NULL && $this->checkInscription($voluntario['id_voluntario'], $atividade['id_atividade'])) {
            return array('erro' => 'Este CPF já está inscrito nesta atividade.', 'id_inscricao' => 0, 'novo_voluntario' => false);
        }
        if ($atividade['vagas'] != NULL && $this->getInscriptionsTotalByActivity($atividade['id_atividade']) >= $atividade['vagas']) {
            return array('erro' => 'Todas as vagas desta atividade já foram preenchidas.', 'id_inscricao' => 0, 'novo_voluntario' => false);
        }

        $novo = false;
        if ($voluntario == NULL) {
            $obj = new Voluntario;
            $obj->setNome($dados['nome']);
            $obj->setCpf($dados['cpf']);
            $obj->setEmail($dados['email']);
            $obj->setTelefone($dados['telefone'] != '' ? $dados['telefone'] : NULL);
            $idVoluntario = $controllerVoluntario->insertVolunteer($obj);
            $novo = true;
        } else {
            $idVoluntario = $voluntario['id_voluntario'];

            if ($atualizarDados) {
                $obj = new Voluntario;
                $obj->setVoluntarioID($idVoluntario);
                $obj->setNome($dados['nome']);
                $obj->setCpf($voluntario['cpf']);
                $obj->setEmail($dados['email']);
                $obj->setTelefone($dados['telefone'] != '' ? $dados['telefone'] : NULL);
                if (!$controllerVoluntario->updateVolunteer($obj)) {
                    return array('erro' => 'Não foi possível atualizar os seus dados. Tente novamente.', 'id_inscricao' => 0, 'novo_voluntario' => false);
                }
            }
        }

        $idInscricao = $idVoluntario ? $this->insertInscription($idVoluntario, $atividade['id_atividade']) : 0;
        if (!$idInscricao) {
            return array('erro' => 'Não foi possível realizar a inscrição. Tente novamente.', 'id_inscricao' => 0, 'novo_voluntario' => false);
        }
        return array('erro' => '', 'id_inscricao' => $idInscricao, 'novo_voluntario' => $novo);
    }

    public function updateInscriptionActivity($idInscription, $idActivity){
        try {
            $query = "UPDATE inscricoes SET id_atividade = :id_atividade WHERE id_inscricao = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':id_atividade', $idActivity, \PDO::PARAM_INT);
            $update->bindValue(':id', $idInscription, \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function deleteInscription($idInscription){
        try {
            $query = "DELETE FROM inscricoes WHERE id_inscricao = :id";
            $delete = $this->conn->connectBd()->prepare($query);
            $delete->bindValue(':id', $idInscription, \PDO::PARAM_INT);
            $delete->execute();

            return $delete->rowCount() == 1;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
