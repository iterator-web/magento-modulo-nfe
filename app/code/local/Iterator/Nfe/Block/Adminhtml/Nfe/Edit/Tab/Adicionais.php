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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Adicionais extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Informações Adicionais'),
            'class'     => 'fieldset',
        ));
        
        $fieldset->addField('inf_inf_ad_fisco', 'textarea', array(
            'name'      => 'inf_inf_ad_fisco',
            'label'     => utf8_encode('Informações Adicionais de Interesse do Fisco'),
            'title'     => utf8_encode('Informações Adicionais de Interesse do Fisco'),
            'required'  => false,
        ));
        
        $fieldset->addField('inf_inf_cpl', 'textarea', array(
            'name'      => 'inf_inf_cpl',
            'label'     => utf8_encode('Informações Complementares de interesse do Contribuinte'),
            'title'     => utf8_encode('Informações Complementares de interesse do Contribuinte'),
            'required'  => false,
        ));
        
        $fieldsetLivre = $form->addFieldset('base_fieldset_livre', array(
            'legend'    => 'Campo de Uso Livre',
            'class'     => 'fieldset',
        ));
        
        $fieldsetLivre->addField('inf_x_campo', 'text', array(
            'name'      => 'inf_x_campo',
            'label'     => utf8_encode('Identificação do campo'),
            'title'     => utf8_encode('Identificação do campo'),
            'required'  => false,
        ));
        
        $fieldsetLivre->addField('inf_x_texto', 'text', array(
            'name'      => 'inf_x_texto',
            'label'     => utf8_encode('Conteúdo do campo'),
            'title'     => utf8_encode('Conteúdo do campo'),
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