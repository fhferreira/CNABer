<?php

namespace App\Http\Controllers\Registro;

use App\Http\Controllers\Registro\RegistroAbstract;
use Exception;
use App\Http\Controllers\Retorno\RetornoAbstract;

abstract class RegistroRetAbstract extends RegistroAbstract
{
    public $linha;

    /**
     * Método __construct()
     * instancia registro qualquer
     * @$data = array de dados para o registro
     */
    public function __construct($linhaTxt)
    {
        // carrega o objeto correspondente
        $posicao = 0;

        $this->linha = $linhaTxt;

        foreach ($this->meta as $key => $value) {
            $valor = (isset($value['precision'])) ?
                substr($linhaTxt, $posicao, $value['tamanho'] + $value['precision']) :
                substr($linhaTxt, $posicao, $value['tamanho']);

            $this->$key = $valor;

            $posicao += (isset($value['precision'])) ?
                $value['tamanho'] + $value['precision'] :
                $value['tamanho'];
        }
    }

    /**
     * método __set()
     * executado sempre que uma propriedade for atribuÃ­da.
     */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_' . $prop)) {
            // executa o Método set_<propriedade>
            call_user_func(array($this, 'set_' . $prop), $value);
        } else {
            $metaData = (isset($this->meta[$prop])) ? $this->meta[$prop] : null;
            switch ($metaData['tipo']) {
                case 'decimal':
                    $inteiro = abs(substr($value, 0, $metaData['tamanho']));
                    $decimal = abs(substr($value, $metaData['tamanho'], $metaData['precision'])) / 100;
                    $retorno = ($inteiro + $decimal);
                    $this->data[$prop] = $retorno;
                    break;
                case 'int':
                    $retorno = abs($value);
                    $this->data[$prop] = $retorno;
                    break;
                case 'alfa':
                    $retorno = trim($value);
                    $this->data[$prop] = $retorno;
                    break;
                case 'date':
                    if ($metaData['required']) {
                        if ($metaData['tamanho'] == 6) {
                            $data = \DateTime::createFromFormat('dmy', sprintf('%06d', $value));
                        } elseif ($metaData['tamanho'] == 8) {
                            $data = \DateTime::createFromFormat('dmY', sprintf('%08d', $value));
                        } else {
                            throw new \InvalidArgumentException("Tamanho do campo {$prop} inválido");
                        }

                        $this->data[$prop] = $data->format('Y-m-d');
                    } else {
                        $this->data[$prop] = '';
                    }
                    break;
                default:
                    $this->data[$prop] = $value;
                    break;
            }
        }
    }

    /**
     * Método __get()
     * executado sempre que uma propriedade for requerida
     */
    public function __get($prop)
    {
        // verifica se existe Método get_<propriedade>
        if (method_exists($this, 'get_' . $prop)) {
            // executa o Método get_<propriedade>
            return call_user_func(array($this, 'get_' . $prop));
        } else {
            return $this->data[$prop];
        }
    }

    /**
     * Método ___get()
     * metodo auxiliar para ser chamado para dentro de metodo get personalizado
     */
    public function ___get($prop)
    {
        // retorna o valor da propriedade
        if (isset($this->meta[$prop])) {
            $metaData = (isset($this->meta[$prop])) ? $this->meta[$prop] : null;
            switch ($metaData['tipo']) {
                case 'decimal':
                    return ($this->data[$prop]) ? number_format($this->data[$prop], $metaData['precision'], ',', '.') : '';
                case 'int':
                    return (isset($this->data[$prop])) ? abs($this->data[$prop]) : '';
                case 'alfa':
                    return ($this->data[$prop]) ? $this->prepareText($this->data[$prop]) : '';
                case $metaData['tipo'] == 'date' && $metaData['tamanho'] == 6:
                    return ($this->data[$prop]) ? date("d/m/y", strtotime($this->data[$prop])) : '';
                case $metaData['tipo'] == 'date' && $metaData['tamanho'] == 8:
                    return ($this->data[$prop]) ? date("d/m/Y", strtotime($this->data[$prop])) : '';
                default:
                    return null;
            }
        }
    }

    public function get_meta()
    {
        return $this->meta;
    }

    /**
     * Método getChilds()
     * Metodo que retorna todos os filhos
     */
    public function getChilds()
    {
        return $this->children;
    }
    /**
     * Método getChild()
     * Metodo que retorna um filho
     */
    public function getChild($index = 0)
    {
        return $this->children[$index];
    }
}
