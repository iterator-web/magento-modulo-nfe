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

class Iterator_Nfe_Adminhtml_NfeController extends Mage_Adminhtml_Controller_Action {
    
    public function _construct() {
        $helper = Mage::helper('nfe');
        if(!method_exists($helper, 'checkValidationNfe')) {
            exit();
        } else {
            if(md5($_SERVER['HTTP_HOST'].'i_|*12*|_T'.$_SERVER['SERVER_ADDR']) != $helper->checkValidationNfe()) {
                exit();
            }
        }
    }
    
    public function gerarNfeAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            try {
                $order = Mage::getModel('sales/order')->load($orderId);
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $retorno = $nfeRN->montarNfe($order);
                if($retorno['status'] == 'sucesso') {
                    $this->_getSession()->addSuccess($this->__($retorno['msg']));
                } else if($retorno['status'] == 'erro') {
                    $this->_getSession()->addError($this->__($retorno['msg']));
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Pedido com NF-e n&atilde;o gerada.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
    }
    
    public function massGerarNfeAction(){
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countNfeOrder = 0;
        $countNonNfeOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getStatus() == 'processing' || $order->getStatus() == 'nfe_cancelada') {
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $retorno = $nfeRN->montarNfe($order);
                if($retorno['status'] == 'sucesso') {
                    $countNfeOrder++;
                } else if($retorno['status'] == 'erro') {
                    $this->_getSession()->addError($this->__($retorno['msg']));
                    $countNonNfeOrder++;
                }
            } else {
                $countNonNfeOrder++;
            }
        }
        if ($countNonNfeOrder) {
            if ($countNfeOrder) {
                $this->_getSession()->addError($this->__('%s solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e n&atilde;o gerada(s).', $countNonNfeOrder));
            } else {
                $this->_getSession()->addError($this->__('Solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e n&atilde;o gerada(s).'));
            }
        }
        if ($countNfeOrder) {
            $this->_getSession()->addSuccess($this->__('%s solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e gerada(s) com sucesso.', $countNfeOrder));
        }
        $this->_redirect('*/sales_order/');
    }
}

?>
