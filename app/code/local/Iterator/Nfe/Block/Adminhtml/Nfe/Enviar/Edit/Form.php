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

class Iterator_Nfe_Block_Adminhtml_Nfe_Enviar_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_enviar_form');
        $this->setTitle($this->__(utf8_encode('Enviar NF-e Mês')));
    }  
    
    protected function _prepareForm() {
        $model = Mage::registry('nfe_enviar');
        
        $data = array();
        if(Mage::getSingleton('adminhtml/session')->getNfeEnviar()){
            $data = Mage::getSingleton('adminhtml/session')->getNfeEnviar();
            Mage::getSingleton('adminhtml/session')->setNfeEnviar(null);
        } elseif ( Mage::registry('nfe_enviar')) {
            $data =  Mage::registry('nfe_enviar');
        }
        $obj = new Varien_Object($data->getData());
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/saveEnviar'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informações do Envio'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('mes', 'select', array(
            'name'      => 'mes',
            'label'     => utf8_encode('Mês'),
            'title'     => utf8_encode('Mês'),
            'values'    => array(
               array('value' => '', 'label' => utf8_encode('Selecione o Mês...')),
               array('value' => '01', 'label' => utf8_encode('Janeiro')),
               array('value' => '02', 'label' => utf8_encode('Fevereiro')),
               array('value' => '03', 'label' => utf8_encode('Março')),
               array('value' => '04', 'label' => utf8_encode('Abril')),
               array('value' => '05', 'label' => utf8_encode('Maio')),
               array('value' => '06', 'label' => utf8_encode('Junho')),
               array('value' => '07', 'label' => utf8_encode('Julho')),
               array('value' => '08', 'label' => utf8_encode('Agosto')),
               array('value' => '09', 'label' => utf8_encode('Setembro')),
               array('value' => '10', 'label' => utf8_encode('Outubro')),
               array('value' => '11', 'label' => utf8_encode('Novembro')),
               array('value' => '12', 'label' => utf8_encode('Dezembro')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('ano', 'select', array(
            'name'      => 'ano',
            'label'     => 'Ano',
            'title'     => 'Ano',
            'values'    => array(
               array('value' => date('Y'), 'label' => date('Y')),
               array('value' => date('Y')-1, 'label' => date('Y')-1),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => utf8_encode('E-Mail Destinatário'),
            'title'     => utf8_encode('E-Mail Destinatário'),
            'required'  => true,
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }  
}
?>