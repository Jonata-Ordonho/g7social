<?php

namespace App\Models;

class Doador{

    private $doadorID;
    private $tipo;
    private $nome;
    private $documento;
    private $email;
    private $telefone;
    private $usuarioID;

    /**
     * @return mixed
     */
    public function getDoadorID()
    {
        return $this->doadorID;
    }

    /**
     * @param mixed $doadorID
     */
    public function setDoadorID($doadorID)
    {
        $this->doadorID = $doadorID;
    }

    /**
     * @return mixed
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * @param mixed $tipo
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
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
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param mixed $documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
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

    /**
     * @return mixed
     */
    public function getUsuarioID()
    {
        return $this->usuarioID;
    }

    /**
     * @param mixed $usuarioID
     */
    public function setUsuarioID($usuarioID)
    {
        $this->usuarioID = $usuarioID;
    }
}
