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

class Iterator_Nfe_Block_Adminhtml_Nfe_Cce_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_cce_form');
        $this->setTitle($this->__(utf8_encode('Informa��es da CC-e')));
    }  
    
    protected function _prepareForm() {
        $model = Mage::registry('nfe/nfecce');
        
        $data = array();
        if(Mage::getSingleton('adminhtml/session')->getCceData()){
            $data = Mage::getSingleton('adminhtml/session')->getCceData();
            Mage::getSingleton('adminhtml/session')->setCceData(null);
        } elseif ( Mage::registry('nfe/nfecce')) {
            $data =  Mage::registry('nfe/nfecce');
        }
        $obj = new Varien_Object($data->getData());
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/saveCorrigir'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es da Carta de Corre��o'),
            'class'     => 'fieldset',
        ));
        
        if($model->getId()) {
            $fieldset->addField('cce_id', 'hidden', array(
                'name' => 'cce_id',
            ));
        }
        $fieldset->addField('nfe_id', 'hidden', array(
            'name' => 'nfe_id',
        ));
        
        $fieldset->addField('n_seq_evento', 'text', array(
            'name'      => 'n_seq_evento',
            'label'     => utf8_encode('N�mero Sequencial'),
            'title'     => utf8_encode('N�mero Sequencial'),
            'required'  => true,
            'readonly'  => true,
            'style'     => "background:none"
        ));
        
        $fieldset->addField('x_correcao', 'textarea', array(
            'name'      => 'x_correcao',
            'label'     => utf8_encode('Texto da Corre��o'),
            'title'     => utf8_encode('Texto da Corre��o'),
            'required'  => true
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }  
}
?>