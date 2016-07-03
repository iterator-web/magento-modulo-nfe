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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Exportacao extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informaчѕes de Comщrcio Exterior'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('exp_uf_saida_pais', 'text', array(
            'name'      => 'exp_uf_saida_pais',
            'label'     => 'Sigla da UF de Embarque',
            'title'     => 'Sigla da UF de Embarque',
            'required'  => false,
        ));
        
        $fieldset->addField('exp_x_loc_exporta', 'text', array(
            'name'      => 'exp_x_loc_exporta',
            'label'     => utf8_encode('Descriчуo do Local de Embarque'),
            'title'     => utf8_encode('Descriчуo do Local de Embarque'),
            'required'  => false,
        ));
        
        $fieldset->addField('exp_x_loc_despacho', 'text', array(
            'name'      => 'exp_x_loc_despacho',
            'label'     => utf8_encode('Descriчуo do Local de Despacho'),
            'title'     => utf8_encode('Descriчуo do Local de Despacho'),
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>