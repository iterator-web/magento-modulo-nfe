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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Emitente extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe_emitente');
     
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('emitente_');
        $form->setFieldNameSuffix('emitente');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es do Emitente'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tipo_identificacao', 'hidden', array(
            'name' => 'tipo_identificacao',
        ));
        
        $fieldset->addField('crt', 'select', array(
            'name'      => 'crt',
            'label'     => utf8_encode('C�digo de Regime Tribut�rio'),
            'title'     => utf8_encode('C�digo de Regime Tribut�rio'),
            'values'    => array(
               array('value' => 1, 'label' => 'Simples Nacional'),
               array('value' => 2, 'label' => 'Simples Nacional, excesso sublimite de receita bruta'),
               array('value' => 3, 'label' => 'Regime Normal'),
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
        
        $fieldset->addField('ie', 'text', array(
            'name'      => 'ie',
            'label'     => utf8_encode('Inscri��o Estadual'),
            'title'     => utf8_encode('Inscri��o Estadual'),
            'required'  => false,
        ));
        
        $fieldset->addField('iest', 'text', array(
            'name'      => 'iest',
            'label'     => utf8_encode('Inscri��o Estadual do ST'),
            'title'     => utf8_encode('Inscri��o Estadual do ST'),
            'required'  => false,
        ));
        
        $fieldset->addField('x_nome', 'text', array(
            'name'      => 'x_nome',
            'label'     => utf8_encode('Nome ou Raz�o Social'),
            'title'     => utf8_encode('Nome ou Raz�o Social'),
            'required'  => true,
        ));
        
        $fieldset->addField('x_fant', 'text', array(
            'name'      => 'x_fant',
            'label'     => 'Nome Fantasia',
            'title'     => 'Nome Fantasia',
            'required'  => false,
        ));
        
        $fieldsetEndereco = $form->addFieldset('base_fieldset_endereco', array(
            'legend'    => utf8_encode('Endere�o do Emitente'),
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
            'label'     => utf8_encode('N�mero'),
            'title'     => utf8_encode('N�mero'),
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
            'label'     => utf8_encode('Munic�pio'),
            'required'  => true,
        ));
        $municipio = $form->getElement('x_mun');
        $municipio->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_municipioemitente')
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
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>