<?php
 /**
 * Iterator Sistemas Web
 *
 * NOTAS SOBRE LICENЧA
 *
 * Este arquivo de cѓdigo-fonte estс em vigъncia dentro dos termos da EULA.
 * Ao fazer uso deste arquivo em seu produto, automaticamente vocъ estс 
 * concordando com os termos do Contrato de Licenчa de Usuсrio Final(EULA)
 * propostos pela empresa Iterator Sistemas Web.
 * Contrato: http://www.iterator.com.br/licenca.txt
 *
 * =================================================================
 *                     MгDULO DE INTEGRAЧУO NF-E                          
 * =================================================================
 * Este produto foi desenvolvido para integrar o Ecommerce Magento
 * ao Sistema da SEFAZ para geraчуo de Nota Fiscal Eletrєnica(NF-e).
 * Atravщs deste mѓdulo a loja virtual do contratante do serviчo
 * passarс a gerar o XML da NF-e, validar e assinar digitalmente em
 * ambiente da prѓpria loja virtual. Tambщm terс a possibilidade de 
 * fazer outros processos diretos com o SEFAZ como cancelamentos de
 * NF-e, consultas e inutilizaчѕes de numeraчуo. O mѓdulo faz ainda
 * o processo de geraчуo da DANFE e envio automсtico de e-mail ao
 * cliente com as informaчѕes e arquivos relacionados a sua NF-e.
 * Por fim o mѓdulo disponibiliza tambщm a NF-e de entrada que serс
 * gerada no momento da devoluчуo de pedidos por parte dos clientes.
 * =================================================================
 *
 * @category   Iterator
 * @package    Iterator_Nfe
 * @author     Ricardo Auler Barrientos <contato@iterator.com.br>
 * @copyright  Copyright (c) Iterator Sistemas Web - CNPJ: 19.717.703/0001-63
 * @license    O Produto щ protegido por leis de direitos autorais, bem como outras leis de propriedade intelectual.
 */

class Iterator_Nfe_Block_Adminhtml_Nfe_Report_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_report_form');
        $this->setTitle($this->__(utf8_encode('Informaчѕes do Relatѓrio')));
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
            'legend'    => utf8_encode('Ordenar Relatѓrio'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetOrder->addField('ordenar', 'select', array(
            'name'      => 'ordenar',
            'label'     => 'Ordenar NFe Por',
            'title'     => 'Ordenar NFe Por',
            'values'    => array(
               array('value' => 'nfe_id', 'label' => utf8_encode('Cѓdigo')),
               array('value' => 'pedido_increment_id', 'label' => utf8_encode('Nњmero do Pedido')),
               array('value' => 'n_nf', 'label' => utf8_encode('Nњmero da Nota Fiscal')), 
               array('value' => 'tp_nf', 'label' => 'Tipo da Nota Fiscal'),
               array('value' => 'v_nf', 'label' => 'Valor da Nota Fiscal'),
        )));
        
        $fieldsetOrder->addField('posicao', 'select', array(
            'name'      => 'posicao',
            'label'     => utf8_encode('Posiчуo'),
            'title'     => utf8_encode('Posiчуo'),
            'values'    => array(
               array('value' => 'ASC', 'label' => 'Ascentente'),
               array('value' => 'DESC', 'label' => 'Descendente'),
            )
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Filtrar Relatѓrio'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tp_nf', 'select', array(
            'name'      => 'tp_nf',
            'label'     => utf8_encode('Tipo de Operaчуo'),
            'title'     => utf8_encode('Tipo de Operaчуo'),
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 0, 'label' => 'Entrada'),
               array('value' => 1, 'label' => utf8_encode('Saэda')),
            ),
        ));
        
        $fieldset->addField('nat_op', 'select', array(
            'name'      => 'nat_op',
            'label'     => utf8_encode('Natureza da Operaчуo'),
            'title'     => utf8_encode('Natureza da Operaчуo'),
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 'Venda de Mercadoria', 'label' => 'Venda de Mercadoria'),
               array('value' => utf8_encode('Devoluчуo de venda'), 'label' => utf8_encode('Devoluчуo de venda')),
               array('value' => 'Compra de Mercadoria', 'label' => 'Compra de Mercadoria'),
               array('value' => utf8_encode('Devoluчуo de Compra'), 'label' => utf8_encode('Devoluчуo de Compra')),
               array('value' => 'Simples Remessa', 'label' => 'Simples Remessa'),
               array('value' => 'Simples faturamento decorrente de venda para entrega futura', 'label' => 'Simples faturamento decorrente de venda para entrega futura'),
               array('value' => 'Venda originada de encomenda para entrega futura', 'label' => 'Venda originada de encomenda para entrega futura'),
               array('value' => 'Simples remessa de mercadoria para troca/garantia', 'label' => 'Simples remessa de mercadoria para troca/garantia'),
               array('value' => 'Retorno de simples remessa de mercadoria para troca/garantia', 'label' => 'Retorno de simples remessa de mercadoria para troca/garantia'),
               array('value' => utf8_encode('Amostra Grсtis'), 'label' => utf8_encode('Amostra Grсtis')),
               array('value' => 'Brindes', 'label' => 'Brindes'),
               array('value' => utf8_encode('Bonificaчуo'), 'label' => utf8_encode('Bonificaчуo')),
               array('value' => utf8_encode('Doaчуo'), 'label' => utf8_encode('Doaчуo')),
               array('value' => 'Presente', 'label' => 'Presente'),
               array('value' => utf8_encode('Outras Saэdas'), 'label' => utf8_encode('Outras Saэdas')),
               array('value' => 'Compra de ativo imobilizado', 'label' => 'Compra de ativo imobilizado'),
               array('value' => 'Compra de material para uso e consumo', 'label' => 'Compra de material para uso e consumo'),
               array('value' => 'Venda de material para uso e consumo', 'label' => 'Venda de material para uso e consumo'),
               array('value' => 'Venda de imobilizado', 'label' => 'Venda de imobilizado'),
            ),
        ));
        
        $fieldset->addField('status', 'select', array(
            'name'      => 'status',
            'label'     => 'Status',
            'title'     => 'Status',
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 0, 'label' => utf8_encode('Aguardando Aprovaчуo')),
               array('value' => 1, 'label' => 'Aguardando Envio'),
               array('value' => 2, 'label' => 'Aguardando Retorno'),
               array('value' => 3, 'label' => 'Autorizado'),
               array('value' => 4, 'label' => utf8_encode('Aguardando Correчуo')),
               array('value' => 5, 'label' => 'Aguardando Cancelamento'),
               array('value' => 6, 'label' => 'Cancelado'),
               array('value' => 7, 'label' => 'Completo'),
               array('value' => 8, 'label' => 'Denegado'),
               array('value' => 9, 'label' => 'Inutilizado'),
            ),
        ));
        
        $fieldset->addField('dh_recbto_desde', 'date', array(
            'name'      => 'dh_recbto_desde',
            'label'     => utf8_encode('Data de Autorizaчуo (Desde)'),
            'title'     => utf8_encode('Data de Autorizaчуo (Desde)'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => false,
        ));
        
        $fieldset->addField('dh_recbto_ate', 'date', array(
            'name'      => 'dh_recbto_ate',
            'label'     => utf8_encode('Data de Autorizaчуo (Atщ)'),
            'title'     => utf8_encode('Data de Autorizaчуo (Atщ)'),
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