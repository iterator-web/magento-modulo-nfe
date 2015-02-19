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
    
    public function indexAction() {
        $this->_initAction()->renderLayout();
    }
    
    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('sales/nfe/nfe')
            ->_title($this->__('Sales'))->_title($this->__('NF-e'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__(utf8_encode('Nota Fiscal Eletrônica')), $this->__('NF-e'));
         
        return $this;
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
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/controleestoque');
    }
    
    public function exportCsvAction() {
        $fileName   = 'entrada.csv';
        $content    = $this->getLayout()->createBlock('controleestoque/adminhtml_controleestoqueentrada_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction() {
        $fileName   = 'entrada.xml';
        $content    = $this->getLayout()->createBlock('controleestoque/adminhtml_controleestoqueentrada_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }
    
    public function consultarNfeAction() {
        $nfeId = $this->getRequest()->getParam('nfe_id');
        $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
        $nfeProdutosCollection = Mage::getModel('nfe/nfeproduto')->getCollection()->addFieldToFilter('nfe_id', array('eq' => $nfeId));
        $html .= '<ul style="border:1px solid #333; width:793px; margin:0 auto; padding:0 0 10px;">';
        $html .= '<li style="background:#ccc; text-align:center; margin-bottom:5px;"><strong>Chave de Acesso: </strong>#'.$nfe->getIdTag().'</li>';
        $html .= utf8_encode('
            <li>
                <strong style="margin:0 50px 0 15px;">Pedido</strong>
                <strong style="margin-right:68px;">Data da Emissão</strong>
                <strong style="margin-right:68px;">Data de Saída/Entrada</strong>
                <strong style="margin-right:68px;">Modelo NF</strong> 
                <strong style="margin-right:68px;">Série NF</strong> 
                <strong>Número NF</strong>
            </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:65px; margin-right:25px; margin-left:15px; text-align:left;">'.$nfe->getPedidoIncrementId().'</div>
                    <div style="float:left; width:100px; margin-right:80px; text-align:center;">'.Mage::helper('core')->formatDate($nfe->getDhEmi(), 'short').'</div>
                    <div style="float:left; width:100px; margin-right:35px; text-align:center;">'.Mage::helper('core')->formatDate($nfe->getDhSaiEnt(), 'short').'</div>
                    <div style="float:left; width:85px; margin-right:25px; text-align:right;">'.$nfe->getMod().'</div>
                    <div style="float:left; width:95px; margin-right:25px; text-align:right;">'.$nfe->getSerie().'</div>
                    <div style="float:left; width:125px; text-align:right;">'.$nfe->getNNf().'</div>
                  </li>';
        $html .= utf8_encode('
                <li style="margin-top:5px;">
                    <strong style="margin:0 75px 0 15px;">Base ICMS</strong>
                    <strong style="margin-right:80px;">Valor ICMS</strong> 
                    <strong style="margin-right:80px;">Base ICMS Subst.</strong>
                    <strong style="margin-right:81px;">Valor ICMS Subst.</strong>
                    <strong>Valor Total Produtos</strong> 
                </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:60px; margin-right:20px; margin-left:15px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVBc(), true, false).'</div>
                    <div style="float:left; width:118px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVIcms(), true, false).'</div>
                    <div style="float:left; width:162px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getBcSt(), true, false).'</div>
                    <div style="float:left; width:163px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVSt(), true, false).'</div>
                    <div style="float:left; width:177px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVProd(), true, false).'</div>
                  </li>';
        $html .= utf8_encode('
                <li style="margin-top:5px;">
                    <strong style="margin:0 70px 0 15px;">Valor Frete</strong> 
                    <strong style="margin-right:70px;">Valor Seguro</strong>
                    <strong style="margin-right:71px;">Desconto</strong> 
                    <strong style="margin-right:71px;">Outras Desp.</strong> 
                    <strong style="margin-right:71px;">Valor IPI</strong>
                    <strong>Valor Total Nota</strong>
                </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:60px; margin-right:20px; margin-left:15px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVFrete(), true, false).'</div>
                    <div style="float:left; width:126px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVSeg(), true, false).'</div>
                    <div style="float:left; width:107px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVDesc(), true, false).'</div>
                    <div style="float:left; width:125px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVOutro(), true, false).'</div>
                    <div style="float:left; width:102px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVIpi(), true, false).'</div>
                    <div style="float:left; width:140px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVNf(), true, false).'</div>
                  </li>';
        $html .= '</ul>';
        $html .= '<div style="padding:30px 0; min-height:280px;">';
        $html .= '<ul>';
        $html .= '<li><strong>Itens da Entrada:</strong></li>';
        $html .= utf8_encode('
                <li style="background:#ccc; border:1px solid #333;">
                    <strong style="margin:0 180px 0 70px;">Produto</strong> 
                    <strong style="margin-right:50px;">Quantidade</strong> 
                    <strong style="margin-right:73px;">Valor Unitário</strong> 
                    <strong style="margin-right:67px;">Desconto</strong> 
                    <strong style="margin-right:67px;">Valor Total</strong> 
                    <strong style="margin-right:67px;">Valor ICMS</strong> 
                    <strong>Valor IPI</strong>
                </li>');
        foreach($nfeProdutosCollection as $nfeProduto) {
            $valorIcms = 0;
            $valorIpi = 0;
            if($nfeProduto->getTemIcms()) {
                $produtoImpostoIcms = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                    ->addFieldToFilter('produto_id', $nfeProduto->getProdutoId())->addFieldToFilter('tipo_imposto', 'icms')->getFirstItem();
                $valorIcms = $produtoImpostoIcms->getVIcms();
            }
            if($nfeProduto->getTemIpi()) {
                $produtoImpostoIpi = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                    ->addFieldToFilter('produto_id', $nfeProduto->getProdutoId())->addFieldToFilter('tipo_imposto', 'ipi')->getFirstItem();
                $valorIpi = $produtoImpostoIpi->getVIpi();
            }
            $html .= '<li style="border:1px solid #333; margin:0; overflow:hidden;">
                        <div style="float:left; width:280px; margin-right:10px; text-align:left;">'.$nfeProduto->getXProd().'</div>
                        <div style="float:left; width:45px; margin-right:55px; text-align:right;">'.$nfeProduto->getQTrib().'</div>
                        <div style="float:left; width:100px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVUnTrib(), true, false).'</div>
                        <div style="float:left; width:120px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVDesc(), true, false).'</div>
                        <div style="float:left; width:120px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVProd(), true, false).'</div>
                        <div style="float:left; width:120px; text-align:right;">'.Mage::helper('core')->currency($valorIcms, true, false).'</div>
                        <div style="float:left; width:118px; text-align:right;">'.Mage::helper('core')->currency($valorIpi, true, false).'</div>
                      </li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        
        $this->getResponse()->setBody($html);
    }
}

?>
