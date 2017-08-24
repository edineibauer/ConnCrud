<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 22/08/2017
 * Time: 22:21
 */

namespace ConnCrud;

class JsonEntity
{
    private $file;
    private $data;
    private $title;
    private $erro;
    private $entity;

    /**
     * @param array $entity
     */
    public function setEntityArray(array $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @param string $entity
     */
    public function setEntityJson(string $entity)
    {
        $this->setEntityArray(json_decode($entity, true));
    }

    /**
     * @param string file_json $entity
     */
    public function setEntityFileJson(string $entity)
    {
        $this->setEntityJson(file_get_contents($entity));
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public function loadFile($file)
    {
        if (file_exists(PATH_HOME . "sql/tables_formated/" . $file . '.json')) {
            $this->file = $file;
            $this->loadJson(file_get_contents(PATH_HOME . "sql/tables_formated/" . $file . '.json'));
        } elseif (file_exists(PATH_HOME . "sql/tables/" . $file . '.json')) {
            $this->file = $file;
            $this->loadJson(file_get_contents(PATH_HOME . "sql/tables/" . $file . '.json'), $file);
        } else {
            $this->erro = "os arquivos json para serem carregados devem ficar na pasta 'sql/tables/' caso estas pastas não existão no seu sistema, crie elas";
        }
    }

    public function loadJson($json, $fileName = null)
    {
        if($json) {
            $data = $this->autoLoadInfo(json_decode($json, true));

            if ($fileName) {
                $this->file = $fileName;
                $this->createFileFormated($data);
            }

            $this->data = $data;
        }
    }

    public function createTable($file = null)
    {
        if ($file) {
            $this->loadFile($file);
        }

        if ($this->data) {
            $create = new JsonCreateTable();
            $create->setArrayData($this->data, $this->file);
        }

        return true;
    }

    private function createFileFormated($data)
    {
        $fp = fopen(PATH_HOME . "sql/tables_formated/" . $this->file . '.json', "w");
        fwrite($fp, json_encode($data));
        fclose($fp);
    }

    private function autoLoadInfo($data)
    {
        if ($data && is_array($data)) {
            foreach ($data as $column => $dados) {
                switch ($dados['type']) {
                    case '1-1':
                        $data[$column] = $this->oneToOne($data[$column]);
                        break;
                    case '1-n':
                        $data[$column] = $this->oneToMany($data[$column]);
                        break;
                    case 'n-n':
                        $data[$column] = $this->manyToMany($data[$column]);
                        break;
                    case 'pri':
                        $data[$column] = $this->inputPrimary($data[$column]);
                        break;
                    case 'int':
                        $data[$column] = $this->inputInt($data[$column]);
                        break;
                    case 'tinyint':
                        $data[$column] = $this->inputTinyint($data[$column]);
                        break;
                    case 'title':
                        $data[$column] = $this->inputTitle($column, $data[$column]);
                        break;
                    case 'link':
                        $data[$column] = $this->inputLink($data, $column);
                        break;
                    case 'cover':
                        $data[$column] = $this->inputCover($data[$column]);
                        break;
                    case 'text':
                        $data[$column] = $this->inputText($data[$column]);
                        break;
                    case 'on':
                        $data[$column] = $this->inputOn($data[$column]);
                        break;
                }
            }
        }

        return $data;
    }

    private function oneToOne($field)
    {
        $field['type'] = "int";
        $field['size'] = 11;
        $field['key'] = "fk";
        $field['key_delete'] = "cascade";
        $field['key_update'] = "no action";

        return $field;
    }

    private function oneToMany($field)
    {
        $field['type'] = "int";
        $field['size'] = 11;
        $field['key'] = "fk";
        $field['key_delete'] = "no action";
        $field['key_update'] = "no action";

        return $field;
    }

    private function manyToMany($field)
    {
        $field['type'] = "int";
        $field['size'] = 11;
        $field['key'] = "fk";
        $field['key_delete'] = "no action";
        $field['key_update'] = "no action";

        return $field;
    }

    private function inputPrimary($field)
    {
        $field['type'] = "int";
        $field['size'] = 11;
        $field['null'] = false;
        $field['key'] = "primary";

        return $field;
    }

    private function inputText($field)
    {
        $field['type'] = "varchar";

        return $field;
    }

    private function inputInt($field)
    {
        $field['size'] = $field['size'] ?? 11 ;

        return $field;
    }

    private function inputTinyint($field)
    {
        $field['size'] = $field['size'] ?? 1;

        return $field;
    }

    private function inputTitle($column, $field)
    {
        $field['type'] = "varchar";
        $field['size'] = $field['size'] ?? 127;
        $field['null'] = false;
        $field['key'] = "unique";
        $field['class'] = "font-size20 font-bold";
        $field['tag'] = "title";
        $field['list'] = true;
        $this->title = $column;

        return $field;
    }

    private function inputLink($data, $field)
    {
        $data[$field]['link'] = $data[$field]['link'] ?? $this->title;
        $data[$field]['type'] = $data[$this->title]['type'] ?? "varchar";
        $data[$field]['size'] = $data[$field]['size'] ?? $data[$this->title]['size'];
        $data[$field]['null'] = false;
        $data[$field]['key'] = "unique";
        $data[$field]['class'] = "font-size08";
        $data[$field]['tag'] = "link";

        return $data[$field];
    }

    private function inputCover($field)
    {
        $field['type'] = 'varchar';
        $field['size'] = 255;
        $field['null'] = false;
        $field['key'] = "unique";
        $field['input'] = "image";
        $field['list'] = $field['list'] ?? true;

        return $field;
    }

    private function inputOn($field)
    {
        $field['type'] = 'tinyint';
        $field['size'] = 1;
        $field['null'] = false;
        $field['allow'] = [0, 1];
        $field['input'] = "on";
        $field['default'] = 0;

        return $field;
    }
}