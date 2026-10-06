<?php

namespace App\Models;

class Arrecadacao{

    private $arrecadacaoID;
    private $projetoID;
    private $doadorID;
    private $tipo;
    private $valor;
    private $descricao;
    private $dataArrecadacao;

    /**
     * @return mixed
     */
    public function getArrecadacaoID()
    {
        return $this->arrecadacaoID;
    }

    /**
     * @param mixed $arrecadacaoID
     */
    public function setArrecadacaoID($arrecadacaoID)
    {
        $this->arrecadacaoID = $arrecadacaoID;
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
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
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
    public function getDataArrecadacao()
    {
        return $this->dataArrecadacao;
    }

    /**
     * @param mixed $dataArrecadacao
     */
    public function setDataArrecadacao($dataArrecadacao)
    {
        $this->dataArrecadacao = $dataArrecadacao;
    }
}
