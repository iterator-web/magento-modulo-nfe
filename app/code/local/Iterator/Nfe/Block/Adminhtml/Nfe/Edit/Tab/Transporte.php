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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Transporte extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es do Transporte'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('trans_mod_frete', 'select', array(
            'name'      => 'trans_mod_frete',
            'label'     => 'Modalidade do Frete',
            'title'     => 'Modalidade do Frete',
            'values'    => array(
               array('value' => 0, 'label' => 'Por conta do emitente'),
               array('value' => 1, 'label' => utf8_encode('Por conta do destinat�rio/remetente')),
               array('value' => 2, 'label' => 'Por conta de terceiros'),
               array('value' => 9, 'label' => 'Sem frete'),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('trans_volume', 'text', array(
            'name'      => 'trans_volume',
            'label'     => 'Volume(s)',
            'required'  => false,
        ));
        $volume = $form->getElement('trans_volume');
        $volume->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_volume')
        );
        
        $fieldset->addField('trans_lacre', 'text', array(
            'name'      => 'trans_lacre',
            'label'     => 'Lacre(s)',
            'required'  => false,
        ));
        $lacre = $form->getElement('trans_lacre');
        $lacre->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_lacre')
        );
        
        $fieldsetTransportador = $form->addFieldset('base_fieldset_transportados', array(
            'legend'    => utf8_encode('Informa��es do Transportador'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetTransportador->addField('trans_tipo_pessoa', 'select', array(
            'name'      => 'trans_tipo_pessoa',
            'label'     => utf8_encode('Tipo Pessoa'),
            'title'     => utf8_encode('Tipo Pessoa'),
            'values'    => array(
               array('value' => 1, 'label' => utf8_encode('Pessoa F�sica')),
               array('value' => 2, 'label' => utf8_encode('Pessoa Jur�dica')),
            ),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_cnpj', 'text', array(
            'name'      => 'trans_cnpj',
            'label'     => 'CNPJ',
            'title'     => 'CNPJ',
            'required'  => false,
            'class'     => 'validar_cnpj',
        ));
        
        $fieldsetTransportador->addField('trans_ie', 'text', array(
            'name'      => 'trans_ie',
            'label'     => utf8_encode('Inscri��o Estadual'),
            'title'     => utf8_encode('Inscri��o Estadual'),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_cpf', 'text', array(
            'name'      => 'trans_cpf',
            'label'     => 'CPF',
            'title'     => 'CPF',
            'required'  => false,
            'class'     => 'validar_cpf',
        ));
        
        $fieldsetTransportador->addField('trans_x_nome', 'text', array(
            'name'      => 'trans_x_nome',
            'label'     => utf8_encode('Nome ou Raz�o Social'),
            'title'     => utf8_encode('Nome ou Raz�o Social'),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_x_ender', 'text', array(
            'name'      => 'trans_x_ender',
            'label'     => utf8_encode('Endere�o Completo'),
            'title'     => utf8_encode('Endere�o Completo'),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_x_mun', 'text', array(
            'name'      => 'trans_x_mun',
            'label'     => utf8_encode('Munic�pio'),
            'required'  => false,
        ));
        $municipio = $form->getElement('trans_x_mun');
        $municipio->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_municipiotransporte')
        );
        
        $fieldsetTransportador->addField('trans_region_id', 'select', array(
            'label'     => 'Estado',
            'title'     => 'Estado',
            'name'      => 'trans_region_id',
            'values'    => Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('BR')->load()->toOptionArray(),
            'required'  => false,
        ));
        
        $fieldsetIcms = $form->addFieldset('base_fieldset_icms', array(
            'legend'    => utf8_encode('Reten��o ICMS'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetIcms->addField('trans_v_serv', 'text', array(
            'name'      => 'trans_v_serv',
            'label'     => utf8_encode('Valor do Servi�o'),
            'title'     => utf8_encode('Valor do Servi�o'),
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_v_bc_ret', 'text', array(
            'name'      => 'trans_v_bc_ret',
            'label'     => utf8_encode('BC da Reten��o do ICMS'),
            'title'     => utf8_encode('BC da Reten��o do ICMS'),
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_p_icms_ret', 'text', array(
            'name'      => 'trans_p_icms_ret',
            'label'     => utf8_encode('Al�quota da Reten��o'),
            'title'     => utf8_encode('Al�quota da Reten��o'),
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_v_icms_ret', 'text', array(
            'name'      => 'trans_v_icms_ret',
            'label'     => 'Valor do ICMS Retido',
            'title'     => 'Valor do ICMS Retido',
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_cfop', 'text', array(
            'name'      => 'trans_cfop',
            'label'     => 'CFOP',
            'title'     => 'CFOP',
            'required'  => false,
        ));
        
        $fieldsetVeiculo = $form->addFieldset('base_fieldset_veiculo', array(
            'legend'    => utf8_encode('Ve�culo do Transporte'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetVeiculo->addField('trans_placa', 'text', array(
            'name'      => 'trans_placa',
            'label'     => utf8_encode('Placa do Ve�culo'),
            'title'     => utf8_encode('Placa do Ve�culo'),
            'required'  => false,
        ));
        
        $fieldsetVeiculo->addField('trans_veic_region_id', 'select', array(
            'label'     => 'Estado',
            'title'     => 'Estado',
            'name'      => 'trans_veic_region_id',
            'values'    => Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('BR')->load()->toOptionArray(),
            'required'  => false,
        ));
        
        $fieldsetVeiculo->addField('trans_rntc', 'text', array(
            'name'      => 'trans_rntc',
            'label'     => 'Registro Nacional de Transportador de Carga',
            'title'     => 'Registro Nacional de Transportador de Carga',
            'required'  => false,
        ));
        
        $fieldsetVeiculo->addField('trans_reboque', 'text', array(
            'name'      => 'trans_reboque',
            'label'     => 'Reboque(s)',
            'required'  => false,
        ));
        $reboque = $form->getElement('trans_reboque');
        $reboque->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_reboque')
        );
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>