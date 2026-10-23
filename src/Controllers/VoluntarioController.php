<?php
namespace App\Controllers;

require_once __DIR__ . '/../../vendor/autoload.php';

use DataBase\Connections;
use App\Models\Voluntario;

// Banco de voluntários (uma linha por pessoa). As inscrições em atividades ficam no InscricaoController.
class VoluntarioController{
    private $conn;

    public function __construct(){
        $this->conn = new Connections;
    }

    private function getFilterSql($filter){
        $sqlFilter = '';
        if (isset($filter['consulta'])) {
            $sqlFilter .= " AND (v.nome LIKE :consulta OR v.email LIKE :consulta";
            if (isset($filter['consulta_numeros'])) {
                $sqlFilter .= " OR v.cpf LIKE :consulta_numeros";
            }
            $sqlFilter .= ") ";
        }
        if (isset($filter['grupo_sete'])) {
            $sqlFilter .= " AND v.grupo_sete = :grupo_sete ";
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
        if (isset($filter['grupo_sete'])) {
            $stmt->bindValue(':grupo_sete', $filter['grupo_sete'], \PDO::PARAM_INT);
        }
    }

    public function getVolunteersList($filter, $startRow, $maxRows){
        try {
            $query = "SELECT v.*, (SELECT COUNT(*) FROM inscricoes AS i WHERE i.id_voluntario = v.id_voluntario) AS total_inscricoes
                      FROM voluntarios AS v
                      WHERE v.id_voluntario IS NOT NULL" . $this->getFilterSql($filter) . "
                      ORDER BY v.nome ASC
                      LIMIT :inicio, :fim";
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

    public function getVolunteersTotal($filter){
        try {
            $query = "SELECT COUNT(*) FROM voluntarios AS v WHERE v.id_voluntario IS NOT NULL" . $this->getFilterSql($filter);
            $stmt = $this->conn->connectBd()->prepare($query);
            $this->bindFilterValues($stmt, $filter);
            $stmt->execute();

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function getVolunteerByID($idVolunteer){
        try {
            $query = "SELECT * FROM voluntarios WHERE id_voluntario = :id";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idVolunteer, \PDO::PARAM_INT);
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

    public function getVolunteerByCPF($cpf){
        try {
            $query = "SELECT * FROM voluntarios WHERE cpf = :cpf";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':cpf', $cpf, \PDO::PARAM_STR);
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

    // TRUE se o CPF já pertence a outro voluntário
    public function checkVolunteerCPF($cpf, $ignoreID = 0){
        try {
            $query = "SELECT id_voluntario FROM voluntarios WHERE cpf = :cpf AND id_voluntario <> :ignorar";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':cpf', $cpf, \PDO::PARAM_STR);
            $stmt->bindValue(':ignorar', $ignoreID, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->rowCount() != 0;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    public function insertVolunteer(Voluntario $obj){
        try {
            $query = "INSERT INTO voluntarios (nome, cpf, email, telefone, grupo_sete) VALUES (:nome, :cpf, :email, :telefone, :grupo_sete)";
            $insert = $this->conn->connectBd()->prepare($query);
            $insert->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $insert->bindValue(':cpf', $obj->getCpf(), \PDO::PARAM_STR);
            $insert->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            $insert->bindValue(':telefone', $obj->getTelefone(), $obj->getTelefone() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $insert->bindValue(':grupo_sete', $obj->getGrupoSete() ? 1 : 0, \PDO::PARAM_INT);
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

    public function updateVolunteer(Voluntario $obj){
        try {
            $query = "UPDATE voluntarios SET nome = :nome, cpf = :cpf, email = :email, telefone = :telefone, grupo_sete = :grupo_sete WHERE id_voluntario = :id";
            $update = $this->conn->connectBd()->prepare($query);
            $update->bindValue(':nome', $obj->getNome(), \PDO::PARAM_STR);
            $update->bindValue(':cpf', $obj->getCpf(), \PDO::PARAM_STR);
            $update->bindValue(':email', $obj->getEmail(), \PDO::PARAM_STR);
            $update->bindValue(':telefone', $obj->getTelefone(), $obj->getTelefone() === NULL ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
            $update->bindValue(':grupo_sete', $obj->getGrupoSete() ? 1 : 0, \PDO::PARAM_INT);
            $update->bindValue(':id', $obj->getVoluntarioID(), \PDO::PARAM_INT);

            return $update->execute();
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Projetos e atividades em que o voluntário se inscreveu
    public function getParticipations($idVolunteer){
        try {
            $query = "SELECT i.id_inscricao, i.data_inscricao, a.id_atividade, a.titulo AS atividade_titulo, a.data_inicio, a.data_fim,
                             p.id_projeto, p.titulo AS projeto_titulo
                      FROM inscricoes AS i
                      INNER JOIN atividades AS a ON a.id_atividade = i.id_atividade
                      INNER JOIN projetos AS p ON p.id_projeto = a.id_projeto
                      WHERE i.id_voluntario = :id
                      ORDER BY a.data_inicio DESC, i.data_inscricao DESC";
            $stmt = $this->conn->connectBd()->prepare($query);
            $stmt->bindValue(':id', $idVolunteer, \PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }
}
