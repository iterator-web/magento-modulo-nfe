<?php
 /**
 * Iterator Sistemas Web
 *
 * NOTAS SOBRE LICENÇA
 *
 * Este arquivo de código-fonte está em vigência dentro dos termos da EULA.
 * Ao fazer uso deste arquivo em seu produto, automaticamente você está 
 * concordando com os termos do Contrato de Licença de Usuário Final(EULA)
 * propostos pela empresa Iterator Sistemas Web.
 *
 * =================================================================
 *                     MÓDULO DE INTEGRAÇÃO NF-E                          
 * =================================================================
 * Este produto foi desenvolvido para integrar o Ecommerce Magento
 * ao Sistema da SEFAZ para geração de Nota Fiscal Eletrônica(NF-e).
 * Através deste módulo a loja virtual do contratante do serviço
 * passará a gerar o XML da NF-e, validar e assinar digitalmente em
 * ambiente da própria loja virtual. Também terá a possibilidade de 
 * fazer outros processos diretos com o SEFAZ como cancelamentos de
 * NF-e, consultas e inutilizações de numeração. O módulo faz ainda
 * o processo de geração da DANFE e envio automático de e-mail ao
 * cliente com as informações e arquivos relacionados a sua NF-e.
 * Por fim o módulo disponibiliza também a NF-e de entrada que será
 * gerada no momento da devolução de pedidos por parte dos clientes.
 * =================================================================
 *
 * @category   Iterator
 * @package    Iterator_Nfe
 * @author     Ricardo Auler Barrientos <contato@iterator.com.br>
 * @copyright  Copyright (c) Iterator Sistemas Web - CNPJ: 19.717.703/0001-63
 * @license    O Produto é protegido por leis de direitos autorais, bem como outras leis de propriedade intelectual.
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
            case 'AC':
                $ufIbge = '12';
                break;
            case 'AL':
                $ufIbge = '27';
                break;
            case 'AP':
                $ufIbge = '16';
                break;
            case 'AM':
                $ufIbge = '13';
                break;
            case 'BA':
                $ufIbge = '29';
                break;
            case 'CE':
                $ufIbge = '23';
                break;
            case 'DF':
                $ufIbge = '53';
                break;
            case 'ES':
                $ufIbge = '32';
                break;
            case 'GO':
                $ufIbge = '52';
                break;
            case 'MA':
                $ufIbge = '21';
                break;
            case 'MT':
                $ufIbge = '51';
                break;
            case 'MS':
                $ufIbge = '50';
                break;
            case 'MG':
                $ufIbge = '31';
                break;
            case 'PA':
                $ufIbge = '15';
                break;
            case 'PB':
                $ufIbge = '25';
                break;
            case 'PR':
                $ufIbge = '41';
                break;
            case 'PE':
                $ufIbge = '26';
                break;
            case 'PI':
                $ufIbge = '22';
                break;
            case 'RJ':
                $ufIbge = '33';
                break;
            case 'RN':
                $ufIbge = '24';
                break;
            case 'RS':
                $ufIbge = '43';
                break;
            case 'RO':
                $ufIbge = '11';
                break;
            case 'RR':
                $ufIbge = '14';
                break;
            case 'SC':
                $ufIbge = '42';
                break;
            case 'SP':
                $ufIbge = '35';
                break;
            case 'SE':
                $ufIbge = '28';
                break;
            case 'TO':
                $ufIbge = '17';
                break;
            default:
                break;
        }
        
        return $ufIbge;
    }
    
    public function getMunicipio($municipio, $uf) {
        $nfeMunicipio = Mage::getModel('nfe/nfemunicipio')->getCollection()->addfieldToFilter('nome', array('like' => $municipio))->getFirstItem();
        if(utf8_decode($municipio) != utf8_decode($nfeMunicipio['nome']) || $uf != 'n' && $uf != $nfeMunicipio['ibge_uf']) {
            $nfeMunicipios = Mage::getModel('nfe/nfemunicipio')->getCollection()->addfieldToFilter('nome', array('like' => $municipio));
            foreach($nfeMunicipios as $nfeMunicipio) {
                $nfeMunicipio = $nfeMunicipio;
            }
        }
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
