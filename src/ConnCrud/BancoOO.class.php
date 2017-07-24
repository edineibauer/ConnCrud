<?php

/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 03/12/2016
 * Time: 16:18
 */

namespace ConnCrud;

abstract class BancoOO {

    private $table;
    private $colunas;

    /**
     * Banco constructor.
     * @param $table
     */
    public function __construct($table) {
        $this->table = (string) (preg_match("/^". PRE ."/i", $table) ? "" : PRE) . $table;
    }

    public function save() {
        $dados = $this->getDados();

        if (isset($dados['id']) && !empty($dados['id']) && $dados['id'] > 0):
            $up = new Update();
            $up->ExeUpdate($this->table, $dados, "WHERE id = :id", "id={$dados['id']}");
            return $dados['id'];
        else:
            unset($dados['id']);
            $create = new Create();
            $create->ExeCreate($this->table, $dados);
            return $create->getResult();
        endif;
    }

    public function delete() {
        $dados = $this->getDados();

        if (isset($dados['id']) && !empty($dados['id'])):
            $del = new Delete();
            $del->ExeDelete($this->table, "WHERE id = :id", "id={$dados['id']}");
            return $del->getResult();
        else:
            return null;
        endif;
    }

    public function load($attr, $value) {
        $read = new Read();
        $read->ExeRead($this->table, "WHERE {$attr} = '" . str_replace("'", "''", $value) . "'");
        if ($read->getResult()):
            foreach ($read->getResult()[0] as $key => $value):
                $this->colunas[] = $key;
                $this->{$key} = $value;
            endforeach;

        else:

            $this->read();
            $this->id = 0;
            $this->{$attr} = $value;
        endif;
    }

    public function loadArray($array) {
        foreach ($array as $k => $v):
            $attr = (isset($attr) ? $attr . " && " : "") . "{$k} = '" . str_replace("'", "''", $v) . "'";
        endforeach;

        $this->loadSql($attr, $array);
    }

    public function loadSql($sql, $arr = null) {
        $read = new Read();
        $read->ExeRead($this->table, "WHERE {$sql}");
        if ($read->getResult()):
            foreach ($read->getResult()[0] as $key => $value):
                $this->colunas[] = $key;
                $this->{$key} = $value;
            endforeach;

        elseif ($arr):

            $this->read();
            $this->id = 0;
            foreach ($arr as $key => $value):
                $this->{$key} = $value;
            endforeach;
        endif;
    }

    public function setDados($dados) {
        foreach ($dados as $key => $value):
            $this->{$key} = $value;
        endforeach;
    }

    protected function read() {
        if (!$this->colunas):
            $db = DBSA;
            $read = new ReadInfo();
            $read->ExeRead("COLUMNS", "WHERE TABLE_SCHEMA = :nb && TABLE_NAME = :nt", "nb={$db}&nt={$this->table}");
            if ($read->getResult()):
                foreach ($read->getResult() as $gg):
                    $this->colunas[] = $gg['COLUMN_NAME'];
                endforeach;
            endif;
        endif;
    }

    protected function getColunas() {
        return $this->colunas;
    }
}