<?php
/**
 * Created by PhpStorm.
 * User: nenab
 * Date: 21/07/2017
 * Time: 16:50
 *
 * <b>RestoreSql:</b>
 * Classe responsável por restaurar arquivos sql
 *
 * @copyright (c) 2017, Edinei J. Bauer
 */

namespace ConnCrud;

use ConnCrud\SqlCommand;

class SqlRestore
{

    private $maxRuntime;

    /**
     * $filename = caminho do arquivo sql para importar
     */
    public function __construct($filename = null, $maxRuntime = 30)
    {
        $this->maxRuntime = $maxRuntime + time();

        if ($filename) {

            $this->restore($filename);

        } else {

            $this->folderSql("sql/up");

            if ($handle = opendir('sql/up')) {
                while ($entry = readdir($handle)) {
                    if (strtolower(pathinfo($entry, PATHINFO_EXTENSION)) === "sql") {
                        $this->restore("sql/up/{$entry}");
                    }

                }
                closedir($handle);
            }

        }
    }

    private function restore($filename)
    {
        $progressFilename = $filename . '_filepointer'; // tmp file for progress
        ($fp = fopen($filename, 'r')) OR die('failed to open file:' . $filename);

        if (file_exists($progressFilename)) {
            $valor = (int)file_get_contents($progressFilename);
            fseek($fp, $valor);
        }

        $queryCount = 0;
        $query = '';
        $sql = new SqlCommand();
        while ($this->maxRuntime > time() AND ($line = fgets($fp, 1024000))) {
            if (substr($line, 0, 2) == '--' OR trim($line) == '') {
                continue;
            }

            $query .= $line;
            if (substr(trim($query), -1) == ';') {
                if (preg_match('/^(ALTER |CREATE |INSERT )/i', $query)) {
                    $sql->exeCommand($query);
                    if ($sql->getResult()) {
                        die('<div style="width:100%;float:left;clear: both;">Erro ao executar o comando \'<strong>' . $query . '</strong></div>');
                    }
                }
                file_put_contents($progressFilename, ftell($fp)); // save the current file position for
                $queryCount++;
                $query = '';
            }
        }

        if (feof($fp)) {
            echo 'restaurado com sucesso!';
            unlink($progressFilename);
        } else {
            echo '<html><head> <meta http-equiv="refresh" content="1"><pre>';
            echo ftell($fp) . '/' . filesize($filename) . ' ' . (round(ftell($fp) / filesize($filename), 2) * 100) . '%' . "\n";
            echo $queryCount . ' comandos processados! Espere refresh automático!';
        }
    }

    private function createFolder($Folder)
    {
        if (!file_exists($Folder) && !is_dir($Folder)):
            mkdir($Folder, 0755);
        endif;
    }

    private function folderSql($folder)
    {
        if (preg_match('/\//i', $folder)) {
            $folderTmp = "";
            foreach (explode('/', $folder) as $fold) {
                $folderTmp = (empty($folderTmp) ? "" : $folderTmp . "/") . $fold;
                $this->createFolder($folderTmp);
            }
        } else {
            $this->createFolder($folder);
        }
        return "{$folder}";
    }
}