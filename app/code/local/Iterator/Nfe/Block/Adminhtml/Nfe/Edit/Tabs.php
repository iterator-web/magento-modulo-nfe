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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('nfe_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('nfe')->__(utf8_encode('Informa��es da NF-e')));
    }

    protected function _beforeToHtml() {
        $model = Mage::registry('nfe');
        $this->addTab('form_section', array(
            'label' => Mage::helper('nfe')->__('Dados da NF-e'),
            'title' => Mage::helper('nfe')->__('Dados da NF-e'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_form')->toHtml(),
        ));
        
        $this->addTab('referenciado_section', array(
            'label' => Mage::helper('nfe')->__('Documento Referenciado'),
            'title' => Mage::helper('nfe')->__('Documento Referenciado'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_referenciado')->toHtml(),
        ));
        
        $this->addTab('emitente_section', array(
            'label' => Mage::helper('nfe')->__('Emitente'),
            'title' => Mage::helper('nfe')->__('Emitente'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_emitente')->toHtml(),
        ));
        
        $this->addTab('retirada_section', array(
            'label' => Mage::helper('nfe')->__('Local de Retirada'),
            'title' => Mage::helper('nfe')->__('Local de Retirada'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_retirada')->toHtml(),
        ));
        
        $this->addTab('destinatario_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Destinat�rio')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Destinat�rio')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_destinatario')->toHtml(),
        ));
        
        $this->addTab('entrega_section', array(
            'label' => Mage::helper('nfe')->__('Local de Entrega'),
            'title' => Mage::helper('nfe')->__('Local de Entrega'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_entrega')->toHtml(),
        ));
        
        $this->addTab('itens_section', array(
            'label' => Mage::helper('nfe')->__('Itens'),
            'title' => Mage::helper('nfe')->__('Itens'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_itens')->toHtml(),
        ));
        
        $this->addTab('cobranca_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Cobran�a')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Cobran�a')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_cobranca')->toHtml(),
        ));
        
        $this->addTab('transporte_section', array(
            'label' => Mage::helper('nfe')->__('Transporte'),
            'title' => Mage::helper('nfe')->__('Transporte'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_transporte')->toHtml(),
        ));
        
        $this->addTab('totais_section', array(
            'label' => Mage::helper('nfe')->__('Totais'),
            'title' => Mage::helper('nfe')->__('Totais'),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_totais')->toHtml(),
        ));
        
        $this->addTab('adicionais_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Informa��es Adicionais')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Informa��es Adicionais')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_adicionais')->toHtml(),
        ));
        
        $this->addTab('exportacao_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Exporta��o')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Exporta��o')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_exportacao')->toHtml(),
        ));
        
        $this->addTab('compra_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Compra')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Compra')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_compra')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
