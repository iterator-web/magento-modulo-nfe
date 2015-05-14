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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Destinatario extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe_destinatario');
     
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('destinatario_');
        $form->setFieldNameSuffix('destinatario');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informações do Destinatário'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tipo_identificacao', 'hidden', array(
            'name' => 'tipo_identificacao',
        ));
        
        $fieldset->addField('tipo_pessoa', 'select', array(
            'name'      => 'tipo_pessoa',
            'label'     => utf8_encode('Tipo Pessoa'),
            'title'     => utf8_encode('Tipo Pessoa'),
            'values'    => array(
               array('value' => 1, 'label' => utf8_encode('Pessoa Física')),
               array('value' => 2, 'label' => utf8_encode('Pessoa Jurídica')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('cnpj', 'text', array(
            'name'      => 'cnpj',
            'label'     => 'CNPJ',
            'title'     => 'CNPJ',
            'required'  => true,
            'class'     => 'validar_cnpj',
        ));
        
        $fieldset->addField('ind_ie_dest', 'select', array(
            'name'      => 'ind_ie_dest',
            'label'     => utf8_encode('Indicador da IE do Destinatário'),
            'title'     => utf8_encode('Indicador da IE do Destinatário'),
            'values'    => array(
               array('value' => 1, 'label' => utf8_encode('Contribuinte ICMS (informar a IE do destinatário)')),
               array('value' => 2, 'label' => utf8_encode('Contribuinte isento de Inscrição no cadastro de Contribuintes do ICMS')),
               array('value' => 9, 'label' => utf8_encode('Não Contribuinte, que pode ou não possuir Inscrição Estadual no Cadastro de Contribuintes do ICMS')),
            ),
            'required'  => false,
        ));
        
        $fieldset->addField('ie', 'text', array(
            'name'      => 'ie',
            'label'     => utf8_encode('Inscrição Estadual'),
            'title'     => utf8_encode('Inscrição Estadual'),
            'required'  => false,
        ));
        
        $fieldset->addField('isuf', 'text', array(
            'name'      => 'isuf',
            'label'     => utf8_encode('Inscrição na SUFRAMA'),
            'title'     => utf8_encode('Inscrição na SUFRAMA'),
            'required'  => false,
        ));
        
        $fieldset->addField('cpf', 'text', array(
            'name'      => 'cpf',
            'label'     => 'CPF',
            'title'     => 'CPF',
            'required'  => true,
            'class'     => 'validar_cpf',
        ));
        
        $fieldset->addField('x_nome', 'text', array(
            'name'      => 'x_nome',
            'label'     => utf8_encode('Nome ou Razão Social'),
            'title'     => utf8_encode('Nome ou Razão Social'),
            'required'  => true,
        ));
        
        $fieldsetEndereco = $form->addFieldset('base_fieldset_endereco', array(
            'legend'    => utf8_encode('Endereço do Emitente'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetEndereco->addField('x_lgr', 'text', array(
            'name'      => 'x_lgr',
            'label'     => 'Logradouro',
            'title'     => 'Logradouro',
            'required'  => true,
        ));
        
        $fieldsetEndereco->addField('nro', 'text', array(
            'name'      => 'nro',
            'label'     => utf8_encode('Número'),
            'title'     => utf8_encode('Número'),
            'required'  => true,
        ));
        
        $fieldsetEndereco->addField('x_cpl', 'text', array(
            'name'      => 'x_cpl',
            'label'     => 'Complemento',
            'title'     => 'Complemento',
            'required'  => false,
        ));
        
        $fieldsetEndereco->addField('x_bairro', 'text', array(
            'name'      => 'x_bairro',
            'label'     => 'Bairro',
            'title'     => 'Bairro',
            'required'  => true,
        ));
        
        $fieldsetEndereco->addField('x_mun', 'text', array(
            'name'      => 'x_mun',
            'label'     => utf8_encode('Município'),
            'required'  => true,
        ));
        $municipio = $form->getElement('x_mun');
        $municipio->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_municipiodestinatario')
        );
        
        $fieldsetEndereco->addField('region_id', 'select', array(
            'label'     => 'Estado',
            'title'     => 'Estado',
            'name'      => 'region_id',
            'values'    => Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('BR')->load()->toOptionArray(),
            'required'  => true,
        ));
        
        $fieldsetEndereco->addField('cep', 'text', array(
            'name'      => 'cep',
            'label'     => 'CEP',
            'title'     => 'CEP',
            'required'  => false,
        ));
        
        $fieldsetEndereco->addField('fone', 'text', array(
            'name'      => 'fone',
            'label'     => 'Telefone',
            'title'     => 'Telefone',
            'required'  => false,
        ));
        
        $fieldsetEndereco->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => 'Email',
            'title'     => 'Email',
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>