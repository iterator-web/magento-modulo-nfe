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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Adicionais extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es Adicionais'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('inf_inf_ad_fisco', 'textarea', array(
            'name'      => 'inf_inf_ad_fisco',
            'label'     => utf8_encode('Informa��es Adicionais de Interesse do Fisco'),
            'title'     => utf8_encode('Informa��es Adicionais de Interesse do Fisco'),
            'required'  => false,
        ));
        
        $fieldset->addField('inf_inf_cpl', 'textarea', array(
            'name'      => 'inf_inf_cpl',
            'label'     => utf8_encode('Informa��es Complementares de interesse do Contribuinte'),
            'title'     => utf8_encode('Informa��es Complementares de interesse do Contribuinte'),
            'required'  => false,
        ));
        
        $fieldsetLivre = $form->addFieldset('base_fieldset_livre', array(
            'legend'    => 'Campo de Uso Livre',
            'class'     => 'fieldset',
        ));
        
        $fieldsetLivre->addField('inf_x_campo', 'text', array(
            'name'      => 'inf_x_campo',
            'label'     => utf8_encode('Identifica��o do campo'),
            'title'     => utf8_encode('Identifica��o do campo'),
            'required'  => false,
        ));
        
        $fieldsetLivre->addField('inf_x_texto', 'text', array(
            'name'      => 'inf_x_texto',
            'label'     => utf8_encode('Conte�do do campo'),
            'title'     => utf8_encode('Conte�do do campo'),
            'required'  => false,
        ));
        
        $fieldsetReferencia = $form->addFieldset('base_fieldset_referencia', array(
            'legend'    => 'Processo Referenciado',
            'class'     => 'fieldset',
        ));
        
        $fieldsetReferencia->addField('inf_n_proc', 'text', array(
            'name'      => 'inf_n_proc',
            'label'     => 'Identificador',
            'title'     => 'Identificador',
            'required'  => false,
        ));
        
        $fieldsetReferencia->addField('inf_ind_proc', 'text', array(
            'name'      => 'inf_ind_proc',
            'label'     => 'Indicador da Origem',
            'title'     => 'Indicador da Origem',
            'required'  => false,
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>