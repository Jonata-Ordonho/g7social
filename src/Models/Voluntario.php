<?php

namespace App\Models;

class Voluntario{

    private $voluntarioID;
    private $atividadeID;
    private $nome;
    private $email;
    private $telefone;

    /**
     * @return mixed
     */
    public function getVoluntarioID()
    {
        return $this->voluntarioID;
    }

    /**
     * @param mixed $voluntarioID
     */
    public function setVoluntarioID($voluntarioID)
    {
        $this->voluntarioID = $voluntarioID;
    }

    /**
     * @return mixed
     */
    public function getAtividadeID()
    {
        return $this->atividadeID;
    }

    /**
     * @param mixed $atividadeID
     */
    public function setAtividadeID($atividadeID)
    {
        $this->atividadeID = $atividadeID;
    }

    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * @param mixed $telefone
     */
    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }
}
