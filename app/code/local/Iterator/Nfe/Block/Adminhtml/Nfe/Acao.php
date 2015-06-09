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
 * Contrato: http://www.iterator.com.br/licenca.txt
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
