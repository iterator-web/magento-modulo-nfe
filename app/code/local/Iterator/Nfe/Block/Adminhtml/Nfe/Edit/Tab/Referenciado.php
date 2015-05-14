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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Referenciado extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe_referenciado');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('referenciado');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => 'Documento Fiscal Referenciado',
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('tem_referencia', 'hidden', array(
            'name' => 'tem_referencia',
        ));
        
        $fieldset->addField('tipo_documento', 'select', array(
            'name'      => 'tipo_documento',
            'label'     => 'Tipo do Documento',
            'title'     => 'Tipo do Documento',
            'values'    => array(
               array('value' => 'refNFe', 'label' => utf8_encode('Nota Fiscal Eletr�nica')),
               array('value' => 'refNF', 'label' => 'Nota Fiscal Modelo 1/1A'),
               array('value' => 'refNFP', 'label' => 'Nota Fiscal Produto Rural'),
               array('value' => 'refECF', 'label' => 'Cupom Fiscal'),
            ),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('ref_nfe', 'text', array(
            'name'      => 'ref_nfe',
            'label'     => utf8_encode('Chave de Acesso da NF-e'),
            'title'     => utf8_encode('Chave de Acesso da NF-e'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('ref_cte', 'text', array(
            'name'      => 'ref_cte',
            'label'     => utf8_encode('Chave de Acesso do CT-e'),
            'title'     => utf8_encode('Chave de Acesso do CT-e'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('region_id', 'select', array(
            'label'     => 'UF do Emitente',
            'title'     => 'UF do Emitente',
            'name'      => 'region_id',
            'values'    => Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter('BR')->load()->toOptionArray(),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('aamm', 'text', array(
            'name'      => 'aamm',
            'label'     => utf8_encode('Ano e M�s de Emiss�o da NF'),
            'title'     => utf8_encode('Ano e M�s de Emiss�o da NF'),
            'style'     => ("background:none;"),
            'required'  => false,
        ));
        
        $fieldset->addField('cpf', 'text', array(
            'name'      => 'cpf',
            'label'     => 'CPF',
            'title'     => 'CPF',
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
            'class'     => 'validar_cpf',
        ));
        
        $fieldset->addField('cnpj', 'text', array(
            'name'      => 'cnpj',
            'label'     => 'CNPJ',
            'title'     => 'CNPJ',
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
            'class'     => 'validar_cnpj',
        ));
        
        $fieldset->addField('ie', 'text', array(
            'name'      => 'ie',
            'label'     => 'IE do Emitente',
            'title'     => 'IE do Emitente',
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('mod', 'text', array(
            'name'      => 'mod',
            'label'     => 'Modelo',
            'title'     => 'Modelo',
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('serie', 'text', array(
            'name'      => 'serie',
            'label'     => utf8_encode('S�rie'),
            'title'     => utf8_encode('S�rie'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_nf', 'text', array(
            'name'      => 'n_nf',
            'label'     => utf8_encode('N�mero'),
            'title'     => utf8_encode('N�mero'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_ecf', 'text', array(
            'name'      => 'n_ecf',
            'label'     => utf8_encode('N�mero do ECF'),
            'title'     => utf8_encode('N�mero do ECF'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_coo', 'text', array(
            'name'      => 'n_coo',
            'label'     => utf8_encode('N�mero do COO'),
            'title'     => utf8_encode('N�mero do COO'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>