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

class Iterator_Nfe_Block_Adminhtml_Nfe_Report_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_report_form');
        $this->setTitle($this->__(utf8_encode('Informações do Relatório')));
    }  
    
    protected function _prepareForm() {
        $model = Mage::registry('nfe_data');
        
        $data = array();
        if(Mage::getSingleton('adminhtml/session')->getNfeData()){
            $data = Mage::getSingleton('adminhtml/session')->getNfeData();
            Mage::getSingleton('adminhtml/session')->setNfeData(null);
        } elseif ( Mage::registry('nfe_data')) {
            $data =  Mage::registry('nfe_data');
        }
        $obj = new Varien_Object($data->getData());
     
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/gerarRelatorio'),
            'method'    => 'post',
            'enctype'   => 'multipart/form-data'
        ));
        
        $fieldsetOrder = $form->addFieldset('base_sort_fieldset', array(
            'legend'    => utf8_encode('Ordenar Relatório'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetOrder->addField('ordenar', 'select', array(
            'name'      => 'ordenar',
            'label'     => 'Ordenar NFe Por',
            'title'     => 'Ordenar NFe Por',
            'values'    => array(
               array('value' => 'nfe_id', 'label' => utf8_encode('Código')),
               array('value' => 'pedido_increment_id', 'label' => utf8_encode('Número do Pedido')),
               array('value' => 'n_nf', 'label' => utf8_encode('Número da Nota Fiscal')), 
               array('value' => 'tp_nf', 'label' => 'Tipo da Nota Fiscal'),
               array('value' => 'v_nf', 'label' => 'Valor da Nota Fiscal'),
        )));
        
        $fieldsetOrder->addField('posicao', 'select', array(
            'name'      => 'posicao',
            'label'     => utf8_encode('Posição'),
            'title'     => utf8_encode('Posição'),
            'values'    => array(
               array('value' => 'ASC', 'label' => 'Ascentente'),
               array('value' => 'DESC', 'label' => 'Descendente'),
            )
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Filtrar Relatório'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tp_nf', 'select', array(
            'name'      => 'tp_nf',
            'label'     => utf8_encode('Tipo de Operação'),
            'title'     => utf8_encode('Tipo de Operação'),
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 0, 'label' => 'Entrada'),
               array('value' => 1, 'label' => utf8_encode('Saída')),
            ),
        ));
        
        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => 'Status',
            'title'     => 'Status',
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 0, 'label' => utf8_encode('Aguardando Aprovação')),
               array('value' => 1, 'label' => 'Aguardando Envio'),
               array('value' => 2, 'label' => 'Aguardando Retorno'),
               array('value' => 3, 'label' => 'Autorizado'),
               array('value' => 4, 'label' => utf8_encode('Aguardando Correção')),
               array('value' => 5, 'label' => 'Aguardando Cancelamento'),
               array('value' => 6, 'label' => 'Cancelado'),
               array('value' => 7, 'label' => 'Completo'),
               array('value' => 8, 'label' => 'Denegado'),
               array('value' => 9, 'label' => 'Inutilizado'),
            ),
        ));
        
        $fieldset->addField('dh_recbto_desde', 'date', array(
            'name'      => 'dh_recbto_desde',
            'label'     => utf8_encode('Data de Autorização (Desde)'),
            'title'     => utf8_encode('Data de Autorização (Desde)'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => false,
        ));
        
        $fieldset->addField('dh_recbto_ate', 'date', array(
            'name'      => 'dh_recbto_ate',
            'label'     => utf8_encode('Data de Autorização (Até)'),
            'title'     => utf8_encode('Data de Autorização (Até)'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);
     
        return parent::_prepareForm();
    }  
}
?>