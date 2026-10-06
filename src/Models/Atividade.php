<?php

namespace App\Models;

class Atividade{

    private $atividadeID;
    private $projetoID;
    private $titulo;
    private $descricao;
    private $local;
    private $dataInicio;
    private $dataFim;
    private $horario;
    private $vagas;
    private $codigo;
    private $status;
    private $usuarioID;

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
    public function getLocal()
    {
        return $this->local;
    }

    /**
     * @param mixed $local
     */
    public function setLocal($local)
    {
        $this->local = $local;
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
    public function getHorario()
    {
        return $this->horario;
    }

    /**
     * @param mixed $horario
     */
    public function setHorario($horario)
    {
        $this->horario = $horario;
    }

    /**
     * @return mixed
     */
    public function getVagas()
    {
        return $this->vagas;
    }

    /**
     * @param mixed $vagas
     */
    public function setVagas($vagas)
    {
        $this->vagas = $vagas;
    }

    /**
     * @return mixed
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
