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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Cobranca extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es da Cobran�a'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('cob_n_fat', 'text', array(
            'name'      => 'cob_n_fat',
            'label'     => utf8_encode('N�mero da Fatura'),
            'title'     => utf8_encode('N�mero da Fatura'),
            'required'  => false,
        ));
        
        $fieldset->addField('cob_v_orig', 'text', array(
            'name'      => 'cob_v_orig',
            'label'     => 'Valor Original',
            'title'     => 'Valor Original',
            'required'  => false,
        ));
        
        $fieldset->addField('cob_v_desc', 'text', array(
            'name'      => 'cob_v_desc',
            'label'     => 'Valor Desconto',
            'title'     => 'Valor Desconto',
            'required'  => false,
        ));
        
        $fieldset->addField('cob_v_liq', 'text', array(
            'name'      => 'cob_v_liq',
            'label'     => utf8_encode('Valor L�quido'),
            'title'     => utf8_encode('Valor L�quido'),
            'required'  => false,
        ));
        
        $fieldset->addField('cob_duplicata', 'text', array(
            'name'      => 'cob_duplicata',
            'label'     => 'Duplicata(s)',
            'required'  => false,
        ));
        $cobranca = $form->getElement('cob_duplicata');
        $cobranca->setRenderer(
            $this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tab_renderer_cobranca')
        );
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>