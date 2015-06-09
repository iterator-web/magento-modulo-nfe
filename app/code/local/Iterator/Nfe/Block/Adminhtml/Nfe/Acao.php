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

class Iterator_Nfe_Block_Adminhtml_Nfe_Acao extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
   
    public function render(Varien_Object $row) {
        $nfeId =  $row->getData('nfe_id');
        $status =  $row->getData('status');
        $order = Mage::getModel('sales/order')->loadByIncrementId($row->getData('pedido_increment_id'));
        if($status == '0' || $status == '4') {
            $acao = '<a href="javascript:window.location.replace(\''.Mage::helper('adminhtml')->getUrl('*/nfe/edit/')."nfe_id/".$nfeId.'\');">Editar e Aprovar</a>';
        } else if($status == '1' || $status == '2' || $status == '5' || $status == '6' || $status == '8' || $status == '9' || $status == '7' && $order->getStatus() == 'complete') {
            $nNf =  $row->getData('n_nf');
            $adminUrl = Mage::helper('adminhtml')->getUrl('*/nfe/consultarNfe');
            $skinUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
            $acao = '<a href="javascript:carregarNfe(\''.$adminUrl.'\', \''.$skinUrl.'\', \''.$nfeId.'\', \''.$nNf.'\')">Visualizar Detalhes</a>';
        } else if($status == '3') {
            $acao = '<a href="javascript:if(confirm(\'Confirma que deseja cancelar esta NF-e?\'))window.location.replace(\''.Mage::helper('adminhtml')->getUrl('*/nfe/cancel/')."nfe_id/".$nfeId.'\');">Cancelar</a>';
        } else if($status == '7' && $status == '7' && $order->getStatus() != 'complete') {
            $acao = '<a href="javascript:window.location.replace(\''.Mage::helper('adminhtml')->getUrl('*/nfe/').'\');window.open(\''.Mage::helper('adminhtml')->getUrl('*/nfe/imprimir/')."nfe_id/".$nfeId.'\', \'_blank\');">Imprimir</a> | <a href="javascript:if(confirm(\'Confirma que deseja cancelar esta NF-e?\'))window.location.replace(\''.Mage::helper('adminhtml')->getUrl('*/nfe/cancel/')."nfe_id/".$nfeId.'\');">Cancelar</a>';
        } else if($status == '7' && $status == '7' && $order->getStatus() == 'complete') {
            $acao = '<a href="javascript:window.location.replace(\''.Mage::helper('adminhtml')->getUrl('*/nfe/').'\');window.open(\''.Mage::helper('adminhtml')->getUrl('*/nfe/imprimir/')."nfe_id/".$nfeId.'\', \'_blank\');">Imprimir</a>';
        }
        return $acao;
    }
}
?>
