<?php

/**
 * <b>Create:</b>
 * Classe responsável por cadastros genéricos no banco de dados!
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace ConnCrud;

class Create extends Conn
{
    //teste
    private $tabela;
    private $dados;
    private $result;

    /** @var PDOStatement */
    private $create;

    /** @var PDO */
    private $conn;

    /**
     * <b>ExeCreate:</b> Executa um cadastro simplificado no banco de dados utilizando prepared statements.
     * Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor!
     *
     * @param STRING $Tabela = Informe o nome da tabela no banco!
     * @param ARRAY $Dados = Informe um array atribuitivo. ( Nome Da Coluna => Valor ).
     */
    public function exeCreate($Tabela, array $Dados)
    {
        $this->tabela = (string)$Tabela;
        $this->dados = $Dados;

        $this->getSyntax();
        $this->execute();
    }

    /**
     * <b>Obter resultado:</b> Retorna o ID do registro inserido ou FALSE caso nem um registro seja inserido!
     * @return INT $Variavel = lastInsertId OR FALSE
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    //Obtém o PDO e Prepara a query
    private function connect()
    {
        $this->conn = parent::getConn();
        $this->create = $this->conn->prepare($this->create);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax()
    {
        $Fileds = implode(', ', array_keys($this->dados));
        $Places = ':' . implode(', :', array_keys($this->dados));
        $this->create = "INSERT INTO {$this->tabela} ({$Fileds}) VALUES ({$Places})";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function execute()
    {
        $this->connect();
        try {
            $this->create->execute($this->dados);
            $this->result = $this->conn->lastInsertId();
        } catch (\PDOException $e) {
            $this->result = null;
            parent::error("<b>Erro ao cadastrar: ({$this->tabela})</b> {$e->getMessage()}", $e->getCode());
        }
    }

}
