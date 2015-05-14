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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Transporte extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informações do Transporte'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('trans_mod_frete', 'select', array(
            'name'      => 'trans_mod_frete',
            'label'     => 'Modalidade do Frete',
            'title'     => 'Modalidade do Frete',
            'values'    => array(
               array('value' => 0, 'label' => 'Por conta do emitente'),
               array('value' => 1, 'label' => utf8_encode('Por conta do destinatário/remetente')),
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
            'legend'    => utf8_encode('Informações do Transportador'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetTransportador->addField('trans_tipo_pessoa', 'select', array(
            'name'      => 'trans_tipo_pessoa',
            'label'     => utf8_encode('Tipo Pessoa'),
            'title'     => utf8_encode('Tipo Pessoa'),
            'values'    => array(
               array('value' => 1, 'label' => utf8_encode('Pessoa Física')),
               array('value' => 2, 'label' => utf8_encode('Pessoa Jurídica')),
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
            'label'     => utf8_encode('Inscrição Estadual'),
            'title'     => utf8_encode('Inscrição Estadual'),
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
            'label'     => utf8_encode('Nome ou Razão Social'),
            'title'     => utf8_encode('Nome ou Razão Social'),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_x_ender', 'text', array(
            'name'      => 'trans_x_ender',
            'label'     => utf8_encode('Endereço Completo'),
            'title'     => utf8_encode('Endereço Completo'),
            'required'  => false,
        ));
        
        $fieldsetTransportador->addField('trans_x_mun', 'text', array(
            'name'      => 'trans_x_mun',
            'label'     => utf8_encode('Município'),
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
            'legend'    => utf8_encode('Retenção ICMS'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetIcms->addField('trans_v_serv', 'text', array(
            'name'      => 'trans_v_serv',
            'label'     => utf8_encode('Valor do Serviço'),
            'title'     => utf8_encode('Valor do Serviço'),
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_v_bc_ret', 'text', array(
            'name'      => 'trans_v_bc_ret',
            'label'     => utf8_encode('BC da Retenção do ICMS'),
            'title'     => utf8_encode('BC da Retenção do ICMS'),
            'required'  => false,
        ));
        
        $fieldsetIcms->addField('trans_p_icms_ret', 'text', array(
            'name'      => 'trans_p_icms_ret',
            'label'     => utf8_encode('Alíquota da Retenção'),
            'title'     => utf8_encode('Alíquota da Retenção'),
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
            'legend'    => utf8_encode('Veículo do Transporte'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetVeiculo->addField('trans_placa', 'text', array(
            'name'      => 'trans_placa',
            'label'     => utf8_encode('Placa do Veículo'),
            'title'     => utf8_encode('Placa do Veículo'),
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