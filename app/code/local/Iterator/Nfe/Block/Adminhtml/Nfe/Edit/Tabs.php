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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('nfe_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('nfe')->__(utf8_encode('Informações da NF-e')));
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
            'label' => Mage::helper('nfe')->__(utf8_encode('Destinatário')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Destinatário')),
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
            'label' => Mage::helper('nfe')->__(utf8_encode('Cobrança')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Cobrança')),
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
            'label' => Mage::helper('nfe')->__(utf8_encode('Informações Adicionais')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Informações Adicionais')),
            'content' => $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_adicionais')->toHtml(),
        ));
        
        $this->addTab('exportacao_section', array(
            'label' => Mage::helper('nfe')->__(utf8_encode('Exportação')),
            'title' => Mage::helper('nfe')->__(utf8_encode('Exportação')),
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
