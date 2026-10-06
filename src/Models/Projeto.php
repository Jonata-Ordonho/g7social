<?php

namespace App\Models;

class Projeto{

    private $projetoID;
    private $titulo;
    private $descricao;
    private $captacaoEmpresas;
    private $captacaoVoluntarios;
    private $dataInicio;
    private $dataFim;
    private $usuarioID;

    /**
     * @return mixed
     */
    public function getProjetoID()
    {
        return $this->projetoID;
    }

    /**
     * @param mixed $projetoID
     */
    public function setProjetoID($projetoID)
    {
        $this->projetoID = $projetoID;
    }

    /**
     * @return mixed
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * @param mixed $titulo
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * @return mixed
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @param mixed $descricao
     */
    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * @return mixed
     */
    public function getCaptacaoEmpresas()
    {
        return $this->captacaoEmpresas;
    }

    /**
     * @param mixed $captacaoEmpresas
     */
    public function setCaptacaoEmpresas($captacaoEmpresas)
    {
        $this->captacaoEmpresas = $captacaoEmpresas;
    }

    /**
     * @return mixed
     */
    public function getCaptacaoVoluntarios()
    {
        return $this->captacaoVoluntarios;
    }

    /**
     * @param mixed $captacaoVoluntarios
     */
    public function setCaptacaoVoluntarios($captacaoVoluntarios)
    {
        $this->captacaoVoluntarios = $captacaoVoluntarios;
    }

    /**
     * @return mixed
     */
    public function getDataInicio()
    {
        return $this->dataInicio;
    }

    /**
     * @param mixed $dataInicio
     */
    public function setDataInicio($dataInicio)
    {
        $this->dataInicio = $dataInicio;
    }

    /**
     * @return mixed
     */
    public function getDataFim()
    {
        return $this->dataFim;
    }

    /**
     * @param mixed $dataFim
     */
    public function setDataFim($dataFim)
    {
        $this->dataFim = $dataFim;
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
