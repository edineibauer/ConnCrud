<?php

/**
 * <b>Delete.class:</b>
 * Classe responsável por deletar genéricamente no banco de dados!
 *
 * @copyright (c) 2017, Edinei J.  Bauer
 */

namespace ConnCrud;

class Delete extends Conn
{
    private $tabela;
    private $termos;
    private $places;
    private $result;

    /** @var PDOStatement */
    private $delete;

    /** @var PDO */
    private $conn;

    public function exeDelete($Tabela, $Termos, $ParseString)
    {
        $this->tabela = (string)$Tabela;
        $this->termos = (string)$Termos;

        parse_str($ParseString, $this->places);
        $this->getSyntax();
        $this->execute();
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getRowCount()
    {
        return $this->delete->rowCount();
    }

    public function setPlaces($ParseString)
    {
        parse_str($ParseString, $this->places);
        $this->getSyntax();
        $this->execute();
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    //Obtém o PDO e Prepara a query
    private function Connect()
    {
        $this->conn = parent::getConn();
        $this->delete = $this->conn->prepare($this->delete);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax()
    {
        $this->delete = "DELETE FROM {$this->tabela} {$this->termos}";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function execute()
    {
        $this->Connect();
        try {
            $this->delete->execute($this->places);
            $this->result = true;
        } catch (PDOException $e) {
            $this->result = null;
            parent::error("<b>Erro ao Deletar:</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
