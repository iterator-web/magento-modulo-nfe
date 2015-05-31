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
 * Contrato: http://www.iterator.com.br/licenca.txt
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

class Iterator_Nfe_Block_Adminhtml_Nfe_AcaoStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
   
    public function render(Varien_Object $row) {
        $status =  $row->getData('status');
        $descricao = null;
        switch($status) {
            case 0:
                $descricao = utf8_encode('<strong style="color:#77a97c;">AGUARDANDO APROVA��O</strong>');
                break;
            case 1:
                $descricao = '<strong style="color:#e6a800;">AGUARDANDO ENVIO</strong>';
                break;
            case 2:
                $descricao = '<strong style="color:#737373;">AGUARDANDO RETORNO</strong>';
                break;
            case 3:
                $descricao = '<strong style="color:#000000;">AUTORIZADO</strong>';
                break;
            case 4:
                $descricao = utf8_encode('<strong style="color:#7b3c20;">AGUARDANDO CORRE��O</strong>');
                break;
            case 5:
                $descricao = '<strong style="color:#c1a0a0;">AGUARDANDO CANCELAMENTO</strong>';
                break;
            case 6:
                $descricao = '<strong style="color:#be3030;">CANCELADO</strong>';
                break;
            case 7:
                $descricao = '<strong style="color:#0aa219;">COMPLETO</strong>';
                break;
            case 8:
                $descricao = '<strong style="color:#995015;">DENEGADO</strong>';
                break;
            default:
                break;
        }
        return $descricao;
    }
}
?>
