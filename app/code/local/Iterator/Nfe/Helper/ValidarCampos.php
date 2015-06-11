<?php
 /**
 * Iterator Sistemas Web
 *
 * NOTAS SOBRE LICEN�A
 *
 * Este arquivo de c�digo-fonte est� em vig�ncia dentro dos termos da EULA.
 * Ao fazer uso deste arquivo em seu produto, automaticamente voc� est� 
 * concordando com os termos do Contrato de Licen�a de Usu�rio Final(EULA)
 * propostos pela empresa Iterator Sistemas Web.
 *
 * =================================================================
 *                     M�DULO DE INTEGRA��O NF-E                          
 * =================================================================
 * Este produto foi desenvolvido para integrar o Ecommerce Magento
 * ao Sistema da SEFAZ para gera��o de Nota Fiscal Eletr�nica(NF-e).
 * Atrav�s deste m�dulo a loja virtual do contratante do servi�o
 * passar� a gerar o XML da NF-e, validar e assinar digitalmente em
 * ambiente da pr�pria loja virtual. Tamb�m ter� a possibilidade de 
 * fazer outros processos diretos com o SEFAZ como cancelamentos de
 * NF-e, consultas e inutiliza��es de numera��o. O m�dulo faz ainda
 * o processo de gera��o da DANFE e envio autom�tico de e-mail ao
 * cliente com as informa��es e arquivos relacionados a sua NF-e.
 * Por fim o m�dulo disponibiliza tamb�m a NF-e de entrada que ser�
 * gerada no momento da devolu��o de pedidos por parte dos clientes.
 * =================================================================
 *
 * @category   Iterator
 * @package    Iterator_Nfe
 * @author     Ricardo Auler Barrientos <contato@iterator.com.br>
 * @copyright  Copyright (c) Iterator Sistemas Web - CNPJ: 19.717.703/0001-63
 * @license    O Produto � protegido por leis de direitos autorais, bem como outras leis de propriedade intelectual.
 */

class Iterator_Nfe_Helper_ValidarCampos extends Mage_Core_Helper_Abstract {
    
    public function validarCnpj($cnpj) {
        if (strlen($cnpj) <> 14) {
            return false; 
        }
        $soma = 0;

        $soma += ($cnpj[0] * 5);
        $soma += ($cnpj[1] * 4);
        $soma += ($cnpj[2] * 3);
        $soma += ($cnpj[3] * 2);
        $soma += ($cnpj[4] * 9); 
        $soma += ($cnpj[5] * 8);
        $soma += ($cnpj[6] * 7);
        $soma += ($cnpj[7] * 6);
        $soma += ($cnpj[8] * 5);
        $soma += ($cnpj[9] * 4);
        $soma += ($cnpj[10] * 3);
        $soma += ($cnpj[11] * 2); 

        $d1 = $soma % 11; 
        $d1 = $d1 < 2 ? 0 : 11 - $d1; 

        $soma = 0;
        $soma += ($cnpj[0] * 6); 
        $soma += ($cnpj[1] * 5);
        $soma += ($cnpj[2] * 4);
        $soma += ($cnpj[3] * 3);
        $soma += ($cnpj[4] * 2);
        $soma += ($cnpj[5] * 9);
        $soma += ($cnpj[6] * 8);
        $soma += ($cnpj[7] * 7);
        $soma += ($cnpj[8] * 6);
        $soma += ($cnpj[9] * 5);
        $soma += ($cnpj[10] * 4);
        $soma += ($cnpj[11] * 3);
        $soma += ($cnpj[12] * 2); 

        $d2 = $soma % 11; 
        $d2 = $d2 < 2 ? 0 : 11 - $d2; 

        if ($cnpj[12] == $d1 && $cnpj[13] == $d2) {
            return true;
        } else {
            return false;
        }
    }
    
    function validarCpf($cpf) {
        $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);
        if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {
            return false;
        } else {
            for ($t = 9; $t < 11; $t++) {
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }

                $d = ((10 * $d) % 11) % 10;

                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            return true;
        }
    }
    
    public function getUfEquivalente($ufMagento) {
        $ufIbge = null;
        switch ($ufMagento) {
            case '485':
                $ufIbge = '12';
                break;
            case '486':
                $ufIbge = '27';
                break;
            case '487':
                $ufIbge = '16';
                break;
            case '488':
                $ufIbge = '13';
                break;
            case '489':
                $ufIbge = '29';
                break;
            case '490':
                $ufIbge = '23';
                break;
            case '491':
                $ufIbge = '53';
                break;
            case '492':
                $ufIbge = '32';
                break;
            case '493':
                $ufIbge = '52';
                break;
            case '494':
                $ufIbge = '21';
                break;
            case '495':
                $ufIbge = '51';
                break;
            case '496':
                $ufIbge = '50';
                break;
            case '497':
                $ufIbge = '31';
                break;
            case '498':
                $ufIbge = '15';
                break;
            case '499':
                $ufIbge = '25';
                break;
            case '500':
                $ufIbge = '41';
                break;
            case '501':
                $ufIbge = '26';
                break;
            case '502':
                $ufIbge = '22';
                break;
            case '503':
                $ufIbge = '33';
                break;
            case '504':
                $ufIbge = '24';
                break;
            case '505':
                $ufIbge = '43';
                break;
            case '506':
                $ufIbge = '11';
                break;
            case '507':
                $ufIbge = '14';
                break;
            case '508':
                $ufIbge = '42';
                break;
            case '509':
                $ufIbge = '35';
                break;
            case '510':
                $ufIbge = '28';
                break;
            case '511':
                $ufIbge = '17';
                break;
            default:
                break;
        }
        
        return $ufIbge;
    }
    
    public function getMunicipio($municipio) {
        $nfeMunicipio = Mage::getModel('nfe/nfemunicipio')->getCollection()->addfieldToFilter('nome', array('like' => $municipio))->getFirstItem();
        return $nfeMunicipio;
    }
    
    public function validateDate( $date, $format='YYYY-MM-DD') {
        switch( $format ) {
            case 'YYYY/MM/DD':
            case 'YYYY-MM-DD':
            list( $y, $m, $d ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'YYYY/DD/MM':
            case 'YYYY-DD-MM':
            list( $y, $d, $m ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'DD-MM-YYYY':
            case 'DD/MM/YYYY':
            list( $d, $m, $y ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'MM-DD-YYYY':
            case 'MM/DD/YYYY':
            list( $m, $d, $y ) = preg_split( '/[-\.\/ ]/', $date );
            break;

            case 'YYYYMMDD':
            $y = substr( $date, 0, 4 );
            $m = substr( $date, 4, 2 );
            $d = substr( $date, 6, 2 );
            break;

            case 'YYYYDDMM':
            $y = substr( $date, 0, 4 );
            $d = substr( $date, 4, 2 );
            $m = substr( $date, 6, 2 );
            break;
        
            case 'YYYYDDMM':
            $y = substr( $date, 0, 4 );
            $d = substr( $date, 4, 2 );
            $m = substr( $date, 6, 2 );
            break;
        
            case 'YYYYMM':
            $y = substr( $date, 0, 4 );
            $d = substr( $date, 4, 2 );
            $m = substr( $date, 6, 2 );
            break;

            default:
            throw new Exception( "Invalid Date Format" );
        }
        return checkdate( $m, $d, $y );
    }
    
    public function validaMinimoMaximo($valor, $min, $max){
        if (strlen($valor) > $max) {
            return false;
        } else if (strlen($valor) < $min) {
            return false;
        } else {
            return true;
        }
    }

    public function validaEMail($mail) { 
        if($mail !== "") {
            if (ereg("^[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[@]{1}[-A-Za-z0-9_]+[-A-Za-z0-9_.]*[.]{1}[A-Za-z]{2,5}$", $mail)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getHoraCerta($dataParam) {
        $dataReturn = date_create($dataParam, timezone_open('GMT'));
        date_timezone_set($dataReturn, timezone_open('America/Sao_Paulo'));
        $dataReturn = (date_format($dataReturn, 'Y-m-d H:i:s'));
        return $dataReturn;
    }
    
    public function getHoraServidor($dataParam) {
        $dataReturn = date_create($dataParam, timezone_open('America/Sao_Paulo'));
        date_timezone_set($dataReturn, timezone_open('GMT'));
        $dataReturn = (date_format($dataReturn, 'Y-m-d H:i:s'));
        return $dataReturn;
    }
}
