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
