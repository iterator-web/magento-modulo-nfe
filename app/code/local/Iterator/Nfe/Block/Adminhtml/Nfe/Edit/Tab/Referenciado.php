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
               array('value' => 'refNFe', 'label' => utf8_encode('Nota Fiscal Eletrônica')),
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
            'label'     => utf8_encode('Ano e Mês de Emissão da NF'),
            'title'     => utf8_encode('Ano e Mês de Emissão da NF'),
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
            'label'     => utf8_encode('Série'),
            'title'     => utf8_encode('Série'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_nf', 'text', array(
            'name'      => 'n_nf',
            'label'     => utf8_encode('Número'),
            'title'     => utf8_encode('Número'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_ecf', 'text', array(
            'name'      => 'n_ecf',
            'label'     => utf8_encode('Número do ECF'),
            'title'     => utf8_encode('Número do ECF'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
        
        $fieldset->addField('n_coo', 'text', array(
            'name'      => 'n_coo',
            'label'     => utf8_encode('Número do COO'),
            'title'     => utf8_encode('Número do COO'),
            'disabled'  => true,
            'style'     => ("background:none"),
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>