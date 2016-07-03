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

class Iterator_Nfe_Block_Adminhtml_Nfe_Range_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_range_form');
        $this->setTitle($this->__(utf8_encode('Gerenciar Range da NF-e')));
    }  
    
    protected function _prepareForm() {
        $model = Mage::registry('nfe_range');
        
        $data = array();
        if(Mage::getSingleton('adminhtml/session')->getNfeRange()){
            $data = Mage::getSingleton('adminhtml/session')->getNfeRange();
            Mage::getSingleton('adminhtml/session')->setNfeRange(null);
        } elseif ( Mage::registry('nfe_range')) {
            $data =  Mage::registry('nfe_range');
        }
        $obj = new Varien_Object($data->getData());
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/saveRange'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informações do Range'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('serie', 'text', array(
            'name'      => 'serie',
            'label'     => utf8_encode('Série'),
            'title'     => utf8_encode('Série'),
            'required'  => true,
            'disabled'  => ($model->getValorInicio() == '1' ? true : false),
            'style'     => ($model->getValorInicio() == '1' ? "background:none" : "background:#fff")
        ));
        
        $fieldset->addField('numero', 'text', array(
            'name'      => 'numero',
            'label'     => utf8_encode('Número'),
            'title'     => utf8_encode('Número'),
            'required'  => true,
            'disabled'  => ($model->getValorInicio() == '1' ? true : false),
            'style'     => ($model->getValorInicio() == '1' ? "background:none" : "background:#fff")
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }  
}
?>