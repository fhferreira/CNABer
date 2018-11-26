<?php

namespace App\Http\Controllers\Registro;

use App\Http\Controllers\Registro\RegistroAbstract;
use Exception;
use App\Http\Controllers\Remessa\RemessaAbstract;

abstract class RegistroRemAbstract extends RegistroAbstract {

    protected $entryData;

    /**
     * Método __construct()
     * instancia registro qualquer
     * @$data = array de dados para o registro
     */
    public function __construct($data = NULL) {
        if ($data) { // se o ID for informado
            // carrega o objeto correspondente
            $this->entryData = $data;
            foreach ($this->meta as $key => $value) {
                $this->$key = (isset($data[$key])) ? $data[$key] : $this->meta[$key]['default'];
            }
        }
    }

    /**
     * Método __set()
     * executado sempre que uma propriedade for atribu?da.
     */
    public function __set($prop, $value) {
        // verifica se existe Método set_<propriedade>
        if (method_exists($this, 'set_' . $prop)) {
            // executa o Método set_<propriedade>
            call_user_func(array($this, 'set_' . $prop), $value);
        } else {
            $metaData = (isset($this->meta[$prop])) ? $this->meta[$prop] : null;
            if (($value == "" || $value === NULL) && $metaData[$prop]['default'] != "") {
                $this->data[$prop] = $metaData[$prop]['default'];
            } else {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }

    /**
     * Método __get()
     * executado sempre que uma propriedade for requerida
     */
    public function __get($prop) {
        // verifica se existe Método get_<propriedade>
        if (method_exists($this, 'get_' . $prop)) {
            // executa o Método get_<propriedade>
            return call_user_func(array($this, 'get_' . $prop));
        } else {
            return $this->___get($prop);
        }
    }

    /**
     * Método ___get()
     * metodo auxiliar para ser chamado para dentro de metodo get personalizado
     */
    public function ___get($prop) {
        // retorna o valor da propriedade
        if (isset($this->meta[$prop])) {
            $metaData = (isset($this->meta[$prop])) ? $this->meta[$prop] : null;
            $this->data[$prop] = !isset($this->data[$prop]) || $this->data[$prop] == '' ? $metaData['default'] : $this->data[$prop];
            if ($metaData['required'] == true && ($this->data[$prop] == '' || !isset($this->data[$prop]))) {
                throw new Exception('Campo faltante ou com valor nulo:' . $prop . " Boleto Numero:" . $this->data['nosso_numero']);
            }
            switch ($metaData['tipo']) {
                case 'decimal':
                    $retorno = ($this->data[$prop]) ? number_format($this->data[$prop], $metaData['precision'], '', '') : '';
                    return str_pad($retorno, $metaData['tamanho'] + $metaData['precision'], '0', STR_PAD_LEFT);
                case 'int':
                    $retorno = (isset($this->data[$prop])) ? number_format($this->data[$prop], 0, '', '') : '';
                    return str_pad($retorno, $metaData['tamanho'], '0', STR_PAD_LEFT);
                case 'alfa':
                    $retorno = (isset($this->data[$prop])) ? $this->prepareText($this->data[$prop]) : '';
                    return $this->mb_str_pad(mb_substr($retorno, 0, $metaData['tamanho'], "UTF-8"), $metaData['tamanho'], ' ', STR_PAD_RIGHT);
                case 'alfa2':
                    $retorno = (isset($this->data[$prop])) ? $this->data[$prop] : '';
                    return $this->mb_str_pad(mb_substr($retorno, 0, $metaData['tamanho'], "UTF-8"), $metaData['tamanho'], ' ', STR_PAD_RIGHT);
                case $metaData['tipo'] == 'date' && $metaData['tamanho'] == 6:
                    $retorno = ($this->data[$prop]) ? date("dmy", strtotime($this->data[$prop])) : '';
                    return str_pad($retorno, $metaData['tamanho'], '0', STR_PAD_LEFT);
                case $metaData['tipo'] == 'date' && $metaData['tamanho'] == 8:
                    $retorno = ($this->data[$prop]) ? date("dmY", strtotime($this->data[$prop])) : '';
                    return str_pad($retorno, $metaData['tamanho'], '0', STR_PAD_LEFT);
                case $metaData['tipo'] == 'dateReverse':
                    $retorno = ($this->data[$prop]) ? date("Ymd", strtotime($this->data[$prop])) : '';
                    return str_pad($retorno, $metaData['tamanho'], '0', STR_PAD_LEFT);
                default:
                    return null;
            }
        }
    }

    public function getFileName() {
        return 'R' . RemessaAbstract::$banco  . str_pad($this->entryData['numero_sequencial_arquivo'],4,'0',STR_PAD_LEFT) . '.rem';
    }

}