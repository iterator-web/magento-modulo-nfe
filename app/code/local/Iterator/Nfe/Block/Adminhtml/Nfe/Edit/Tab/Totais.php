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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Totais extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informa��es dos Totais (ICMS)'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('v_bc', 'text', array(
            'name'      => 'v_bc',
            'label'     => utf8_encode('Base de C�lculo do ICMS'),
            'title'     => utf8_encode('Base de C�lculo do ICMS'),
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_icms', 'text', array(
            'name'      => 'v_icms',
            'label'     => 'Valor Total do ICMS',
            'title'     => 'Valor Total do ICMS',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_icms_deson', 'text', array(
            'name'      => 'v_icms_deson',
            'label'     => 'Valor Total do ICMS desonerado',
            'title'     => 'Valor Total do ICMS desonerado',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_bc_st', 'text', array(
            'name'      => 'v_bc_st',
            'label'     => utf8_encode('Base de C�lculo do ICMS ST'),
            'title'     => utf8_encode('Base de C�lculo do ICMS ST'),
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_st', 'text', array(
            'name'      => 'v_st',
            'label'     => 'Valor Total do ICMS ST',
            'title'     => 'Valor Total do ICMS ST',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_prod', 'text', array(
            'name'      => 'v_prod',
            'label'     => utf8_encode('Valor Total dos Produtos e Servi�os'),
            'title'     => utf8_encode('Valor Total dos Produtos e Servi�os'),
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_frete', 'text', array(
            'name'      => 'v_frete',
            'label'     => 'Valor Total do Frete',
            'title'     => 'Valor Total do Frete',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_seg', 'text', array(
            'name'      => 'v_seg',
            'label'     => 'Valor Total do Seguro',
            'title'     => 'Valor Total do Seguro',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_desc', 'text', array(
            'name'      => 'v_desc',
            'label'     => 'Valor Total do Desconto',
            'title'     => 'Valor Total do Desconto',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_ll', 'text', array(
            'name'      => 'v_ll',
            'label'     => 'Valor Total do II',
            'title'     => 'Valor Total do II',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_ipi', 'text', array(
            'name'      => 'v_ipi',
            'label'     => 'Valor Total do IPI',
            'title'     => 'Valor Total do IPI',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_pis', 'text', array(
            'name'      => 'v_pis',
            'label'     => 'Valor do PIS',
            'title'     => 'Valor do PIS',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_cofins', 'text', array(
            'name'      => 'v_cofins',
            'label'     => 'Valor da COFINS',
            'title'     => 'Valor da COFINS',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_outro', 'text', array(
            'name'      => 'v_outro',
            'label'     => utf8_encode('Outras Despesas Acess�rias'),
            'title'     => utf8_encode('Outras Despesas Acess�rias'),
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_nf', 'text', array(
            'name'      => 'v_nf',
            'label'     => 'Valor Total da NF-e',
            'title'     => 'Valor Total da NF-e',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('v_tot_trib', 'text', array(
            'name'      => 'v_tot_trib',
            'label'     => 'Valor Aproximado de Tributos',
            'title'     => 'Valor Aproximado de Tributos',
            'required'  => true,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn = $form->addFieldset('base_fieldset_issqn', array(
            'legend'    => utf8_encode('Informa��es dos Totais (ISSQN)'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetIssqn->addField('v_serv', 'text', array(
            'name'      => 'v_serv',
            'label'     => utf8_encode('Valor Total dos Servi�os'),
            'title'     => utf8_encode('Valor Total dos Servi�os'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
            'after_element_html' => utf8_encode('<p class="note">Valor total dos Servi�os sob n�o-incid�ncia ou n�o tributados pelo ICMS</p>')
        ));
        
        $fieldsetIssqn->addField('v_bc_iss', 'text', array(
            'name'      => 'v_bc_iss',
            'label'     => utf8_encode('Valor Total Base de C�lculo do ISS'),
            'title'     => utf8_encode('Valor Total Base de C�lculo do ISS'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('v_iss', 'text', array(
            'name'      => 'v_iss',
            'label'     => 'Valor Total do ISS',
            'title'     => 'Valor Total do ISS',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('v_pis_iss', 'text', array(
            'name'      => 'v_pis_iss',
            'label'     => utf8_encode('Valor Total do PIS Servi�os'),
            'title'     => utf8_encode('Valor Total do PIS Servi�os'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('v_cofins_iss', 'text', array(
            'name'      => 'v_cofins_iss',
            'label'     => utf8_encode('Valor Total da COFINS Servi�os'),
            'title'     => utf8_encode('Valor Total da COFINS Servi�os'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('d_compet', 'date', array(
            'name'      => 'd_compet',
            'label'     => utf8_encode('Data da Presta��o do Servi�o'),
            'title'     => utf8_encode('Data da Presta��o do Servi�o'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => false,
        ));
        
        $fieldsetIssqn->addField('v_deducao', 'text', array(
            'name'      => 'v_deducao',
            'label'     => utf8_encode('Valor Total Dedu��o'),
            'title'     => utf8_encode('Valor Total Dedu��o'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
            'after_element_html' => utf8_encode('<p class="note">Valor total dedu��o para redu��o da Base de C�lculo</p>')
        ));
        
        $fieldsetIssqn->addField('v_desc_incond', 'text', array(
            'name'      => 'v_desc_incond',
            'label'     => 'Valor Total Desconto Incondicionado',
            'title'     => 'Valor Total Desconto Incondicionado',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('v_desc_cond', 'text', array(
            'name'      => 'v_desc_cond',
            'label'     => 'Valor Total Desconto Condicionado',
            'title'     => 'Valor Total Desconto Condicionado',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('v_iss_ret', 'text', array(
            'name'      => 'v_iss_ret',
            'label'     => utf8_encode('Valor Total Reten��o ISS'),
            'title'     => utf8_encode('Valor Total Reten��o ISS'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetIssqn->addField('c_reg_trib', 'select', array(
            'name'      => 'c_reg_trib',
            'label'     => utf8_encode('C�digo do Regime Especial de Tributa��o'),
            'title'     => utf8_encode('C�digo do Regime Especial de Tributa��o'),
            'values'    => array(
               array('value' => 0, 'label' => 'Selecione...'),
               array('value' => 1, 'label' => 'Microempresa Municipal'),
               array('value' => 2, 'label' => 'Estimativa'),
               array('value' => 3, 'label' => 'Sociedade de Profissionais'),
               array('value' => 4, 'label' => 'Cooperativa'),
               array('value' => 5, 'label' => utf8_encode('Microempres�rio Individual (MEI)')),
               array('value' => 6, 'label' => utf8_encode('Microempres�rio e Empresa de Pequeno Porte (ME/EPP)')),
            ),
            'required'  => false,
        ));
        
        $fieldsetRetencao = $form->addFieldset('base_fieldset_retencao', array(
            'legend'    => utf8_encode('Reten��es de Tributos'),
            'class'     => 'fieldset',
        ));
        
        $fieldsetRetencao->addField('v_ret_pis', 'text', array(
            'name'      => 'v_ret_pis',
            'label'     => 'Valor Retido de PIS',
            'title'     => 'Valor Retido de PIS',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_ret_cofins', 'text', array(
            'name'      => 'v_ret_cofins',
            'label'     => 'Valor Retido de COFINS',
            'title'     => 'Valor Retido de COFINS',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_ret_csll', 'text', array(
            'name'      => 'v_ret_csll',
            'label'     => 'Valor Retido de CSLL',
            'title'     => 'Valor Retido de CSLL',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_bc_irrf', 'text', array(
            'name'      => 'v_bc_irrf',
            'label'     => utf8_encode('Base de C�lculo do IRRF'),
            'title'     => utf8_encode('Base de C�lculo do IRRF'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_irrf', 'text', array(
            'name'      => 'v_irrf',
            'label'     => 'Valor Retido do IRRF',
            'title'     => 'Valor Retido do IRRF',
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_bc_ret_prev', 'text', array(
            'name'      => 'v_bc_ret_prev',
            'label'     => utf8_encode('Base de C�lculo da Reten��o da Previd�ncia Social'),
            'title'     => utf8_encode('Base de C�lculo da Reten��o da Previd�ncia Social'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldsetRetencao->addField('v_ret_prev', 'text', array(
            'name'      => 'v_ret_prev',
            'label'     => utf8_encode('Valor da Reten��o da Previd�ncia Social'),
            'title'     => utf8_encode('Valor da Reten��o da Previd�ncia Social'),
            'required'  => false,
            'class'     => 'validate-zero-or-greater',
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>