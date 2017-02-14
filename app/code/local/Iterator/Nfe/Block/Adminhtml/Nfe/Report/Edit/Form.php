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

class Iterator_Nfe_Block_Adminhtml_Nfe_Report_Edit_Form extends Mage_Adminhtml_Block_Widget_Form { 
    
    public function __construct() {  
        parent::__construct();
     
        $this->setId('iterator_nfe_report_form');
        $this->setTitle($this->__(utf8_encode('Informa��es do Relat�rio')));
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
            'legend'    => utf8_encode('Ordenar Relat�rio'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetOrder->addField('ordenar', 'select', array(
            'name'      => 'ordenar',
            'label'     => 'Ordenar NFe Por',
            'title'     => 'Ordenar NFe Por',
            'values'    => array(
               array('value' => 'nfe_id', 'label' => utf8_encode('C�digo')),
               array('value' => 'pedido_increment_id', 'label' => utf8_encode('N�mero do Pedido')),
               array('value' => 'n_nf', 'label' => utf8_encode('N�mero da Nota Fiscal')), 
               array('value' => 'tp_nf', 'label' => 'Tipo da Nota Fiscal'),
               array('value' => 'v_nf', 'label' => 'Valor da Nota Fiscal'),
        )));
        
        $fieldsetOrder->addField('posicao', 'select', array(
            'name'      => 'posicao',
            'label'     => utf8_encode('Posi��o'),
            'title'     => utf8_encode('Posi��o'),
            'values'    => array(
               array('value' => 'ASC', 'label' => 'Ascentente'),
               array('value' => 'DESC', 'label' => 'Descendente'),
            )
        ));
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Filtrar Relat�rio'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tp_nf', 'select', array(
            'name'      => 'tp_nf',
            'label'     => utf8_encode('Tipo de Opera��o'),
            'title'     => utf8_encode('Tipo de Opera��o'),
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 0, 'label' => 'Entrada'),
               array('value' => 1, 'label' => utf8_encode('Sa�da')),
            ),
        ));
        
        $fieldset->addField('nat_op', 'select', array(
            'name'      => 'nat_op',
            'label'     => utf8_encode('Natureza da Opera��o'),
            'title'     => utf8_encode('Natureza da Opera��o'),
            'values'    => array(
               array('value' => '', 'label' => 'Todos'),
               array('value' => 'Venda de Mercadoria', 'label' => 'Venda de Mercadoria'),
               array('value' => utf8_encode('Devolu��o de venda'), 'label' => utf8_encode('Devolu��o de venda')),
               array('value' => 'Compra de Mercadoria', 'label' => 'Compra de Mercadoria'),
               array('value' => utf8_encode('Devolu��o de Compra'), 'label' => utf8_encode('Devolu��o de Compra')),
               array('value' => 'Simples Remessa', 'label' => 'Simples Remessa'),
               array('value' => 'Simples faturamento decorrente de venda para entrega futura', 'label' => 'Simples faturamento decorrente de venda para entrega futura'),
               array('value' => 'Venda originada de encomenda para entrega futura', 'label' => 'Venda originada de encomenda para entrega futura'),
               array('value' => 'Simples remessa de mercadoria para troca/garantia', 'label' => 'Simples remessa de mercadoria para troca/garantia'),
               array('value' => 'Retorno de simples remessa de mercadoria para troca/garantia', 'label' => 'Retorno de simples remessa de mercadoria para troca/garantia'),
               array('value' => utf8_encode('Amostra Gr�tis'), 'label' => utf8_encode('Amostra Gr�tis')),
               array('value' => 'Brindes', 'label' => 'Brindes'),
               array('value' => utf8_encode('Bonifica��o'), 'label' => utf8_encode('Bonifica��o')),
               array('value' => utf8_encode('Doa��o'), 'label' => utf8_encode('Doa��o')),
               array('value' => 'Presente', 'label' => 'Presente'),
               array('value' => utf8_encode('Outras Sa�das'), 'label' => utf8_encode('Outras Sa�das')),
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
               array('value' => 0, 'label' => utf8_encode('Aguardando Aprova��o')),
               array('value' => 1, 'label' => 'Aguardando Envio'),
               array('value' => 2, 'label' => 'Aguardando Retorno'),
               array('value' => 3, 'label' => 'Autorizado'),
               array('value' => 4, 'label' => utf8_encode('Aguardando Corre��o')),
               array('value' => 5, 'label' => 'Aguardando Cancelamento'),
               array('value' => 6, 'label' => 'Cancelado'),
               array('value' => 7, 'label' => 'Completo'),
               array('value' => 8, 'label' => 'Denegado'),
               array('value' => 9, 'label' => 'Inutilizado'),
            ),
        ));
        
        $fieldset->addField('dh_recbto_desde', 'date', array(
            'name'      => 'dh_recbto_desde',
            'label'     => utf8_encode('Data de Autoriza��o (Desde)'),
            'title'     => utf8_encode('Data de Autoriza��o (Desde)'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => false,
        ));
        
        $fieldset->addField('dh_recbto_ate', 'date', array(
            'name'      => 'dh_recbto_ate',
            'label'     => utf8_encode('Data de Autoriza��o (At�)'),
            'title'     => utf8_encode('Data de Autoriza��o (At�)'),
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