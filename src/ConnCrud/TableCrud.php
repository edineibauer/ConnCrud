<?php

/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 03/12/2016
 * Time: 19:59
 *
 * Banco
 *
 * Manipulador CRUD de uma tabela
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace ConnCrud;

use ConnCrud\Read;
use ConnCrud\Create;
use ConnCrud\Update;
use ConnCrud\Delete;
use ConnCrud\InfoTable;

class TableCrud
{
    private $table;
    private $colunas;
    private $_data;

    public function __construct($table)
    {
        if (defined('PRE')):
            $this->table = (preg_match('/^' . PRE . '/', $table) ? $table : PRE . $table);
        endif;
    }

    public function __set($property, $value)
    {
        if (is_array($this->getColunas()) && in_array($property, $this->getColunas())):
            $value = (is_float($value) ? (float)$value : ($value == "0" || (is_numeric($value) && !preg_match('/^0\d+/i', $value)) ? (int)$value : (empty($value) ? NULL : (string)$value)));
            $this->_data[$property] = $value;
        endif;
    }

    public function __get($property)
    {
        if (is_array($this->getColunas()) && in_array($property, $this->getColunas())):
            return array_key_exists($property, $this->_data) ? $this->_data[$property] : null;
        endif;
    }

    public function exist()
    {
        return isset($this->_data['id']) && $this->_data['id'] > 0;
    }

    public function getDados()
    {
        $dados = array();
        if ($this->_data):
            foreach ($this->_data as $key => $value):
                $dados[$key] = $value;
            endforeach;
        endif;

        return $dados;
    }

    public function save()
    {
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

    public function delete()
    {
        $dados = $this->getDados();

        if (isset($dados['id']) && !empty($dados['id'])):
            $del = new Delete();
            $del->ExeDelete($this->table, "WHERE id = :id", "id={$dados['id']}");
            return $del->getResult();
        else:
            return null;
        endif;
    }

    public function load($attr, $value = null)
    {
        $attrTemp = $value ? $attr : "id";
        $value = $value ? $value : $attr;
        $attr = $attrTemp;
        unset($attrTemp);

        $read = new Read();
        $read->ExeRead($this->table, "WHERE {$attr} = '" . str_replace("'", "''", $value) . "'");
        if ($read->getResult()):
            foreach ($read->getResult()[0] as $key => $value):
                $this->colunas[] = $key;
                $this->{$key} = $value;
            endforeach;
        else:
            $this->readNewTableColumns();
            $this->{$attr} = $value;
        endif;
    }

    public function loadArray($array)
    {
        $attr = "";

        foreach ($array as $k => $v):
            $attr = (!empty($attr) ? $attr . " && " : "") . "{$k} = '" . str_replace("'", "''", $v) . "'";
        endforeach;

        $this->loadSql($attr, $array);
    }

    public function loadSql($sql, $arr = null)
    {
        $read = new Read();
        $read->ExeRead($this->table, "WHERE {$sql}");
        if ($read->getResult()):
            foreach ($read->getResult()[0] as $key => $value):
                $this->colunas[] = $key;
                $this->{$key} = $value;
            endforeach;

        elseif ($arr):

            $this->readNewTableColumns();
            foreach ($arr as $key => $value):
                $this->{$key} = $value;
            endforeach;
        endif;
    }

    public function setDados($dados)
    {
        foreach ($dados as $key => $value):
            $this->{$key} = $value;
        endforeach;
    }

    private function readNewTableColumns()
    {
        if (!$this->colunas):
            $db = DATABASE;
            $read = new InfoTable();
            $read->exeRead("COLUMNS", "WHERE TABLE_SCHEMA = :nb && TABLE_NAME = :nt", "nb={$db}&nt={$this->table}");
            if ($read->getResult()):
                foreach ($read->getResult() as $gg):
                    $this->colunas[] = $gg['COLUMN_NAME'];
                endforeach;
            endif;
        endif;

        $this->id = 0;
    }

    private function getColunas()
    {
        if(!$this->colunas):
            $this->readNewTableColumns();
        endif;

        return $this->colunas;
    }

}