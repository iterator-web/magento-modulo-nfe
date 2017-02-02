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

class Iterator_Nfe_Block_Adminhtml_Nfe_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $model = Mage::registry('nfe');
     
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('nfe');
     
        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => utf8_encode('Dados da NF-e'),
            'class'     => 'fieldset',
        ));
        
        if($model->getId()) {
            $fieldset->addField('nfe_id', 'hidden', array(
                'name' => 'nfe_id',
            ));
        }
        
        $fieldset->addField('pedido_increment_id', 'text', array(
            'name'      => 'pedido_increment_id',
            'label'     => utf8_encode('Número do Pedido'),
            'title'     => utf8_encode('Número do Pedido'),
            'required'  => false,
            'disabled'  => ($model->getId() ? true : false),
            'style'     => ($model->getId() ? "background:none" : "background:#fff"),
            'class'     => 'validate-zero-or-greater',
        ));
        
        $fieldset->addField('tp_nf', 'select', array(
            'name'      => 'tp_nf',
            'label'     => utf8_encode('Tipo de Operação'),
            'title'     => utf8_encode('Tipo de Operação'),
            'values'    => array(
               array('value' => 0, 'label' => 'Entrada'),
               array('value' => 1, 'label' => utf8_encode('Saída')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('nat_op', 'select', array(
            'name'      => 'nat_op',
            'label'     => utf8_encode('Natureza da Operação'),
            'title'     => utf8_encode('Natureza da Operação'),
            'values'    => array(
               array('value' => '', 'label' => utf8_encode('Selecione a Natureza da Operação...')),
               array('value' => 'Venda de Mercadoria', 'label' => 'Venda de Mercadoria'),
               array('value' => utf8_encode('Devolução de venda'), 'label' => utf8_encode('Devolução de venda')),
               array('value' => 'Compra de Mercadoria', 'label' => 'Compra de Mercadoria'),
               array('value' => utf8_encode('Devolução de Compra'), 'label' => utf8_encode('Devolução de Compra')),
               array('value' => 'Simples Remessa', 'label' => 'Simples Remessa'),
               array('value' => 'Simples faturamento decorrente de venda para entrega futura', 'label' => 'Simples faturamento decorrente de venda para entrega futura'),
               array('value' => 'Venda originada de encomenda para entrega futura', 'label' => 'Venda originada de encomenda para entrega futura'),
               array('value' => 'Simples remessa de mercadoria para troca/garantia', 'label' => 'Simples remessa de mercadoria para troca/garantia'),
               array('value' => 'Retorno de simples remessa de mercadoria para troca/garantia', 'label' => 'Retorno de simples remessa de mercadoria para troca/garantia'),
               array('value' => utf8_encode('Amostra Grátis'), 'label' => utf8_encode('Amostra Grátis')),
               array('value' => 'Brindes', 'label' => 'Brindes'),
               array('value' => utf8_encode('Bonificação'), 'label' => utf8_encode('Bonificação')),
               array('value' => utf8_encode('Doação'), 'label' => utf8_encode('Doação')),
               array('value' => 'Presente', 'label' => 'Presente'),
               array('value' => utf8_encode('Outras Saídas'), 'label' => utf8_encode('Outras Saídas')),
               array('value' => 'Compra de ativo imobilizado', 'label' => 'Compra de ativo imobilizado'),
               array('value' => 'Compra de material para uso e consumo', 'label' => 'Compra de material para uso e consumo'),
               array('value' => 'Venda de material para uso e consumo', 'label' => 'Venda de material para uso e consumo'),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('ind_pag', 'select', array(
            'name'      => 'ind_pag',
            'label'     => 'Forma de Pagamento',
            'title'     => 'Forma de Pagamento',
            'values'    => array(
               array('value' => 0, 'label' => utf8_encode('Pagamento à vista')),
               array('value' => 1, 'label' => 'Pagamento a prazo'),
               array('value' => 2, 'label' => 'Outros'),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('dh_emi', 'date', array(
            'name'      => 'dh_emi',
            'label'     => utf8_encode('Data e Hora de Emissão'),
            'title'     => utf8_encode('Data e Hora de Emissão'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => true,
            'time' => true
        ));
        
        $fieldset->addField('dh_sai_ent', 'date', array(
            'name'      => 'dh_sai_ent',
            'label'     => utf8_encode('Data e Hora de Saída/Entrada'),
            'title'     => utf8_encode('Data e Hora de Saída/Entrada'),
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'format'    => Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT),
            'value'     => date( Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), strtotime('next weekday')),
            'required'  => true,
            'time' => true
        ));
        
        $fieldset->addField('id_dest', 'select', array(
            'name'      => 'id_dest',
            'label'     => utf8_encode('Destino da Operação'),
            'title'     => utf8_encode('Destino da Operação'),
            'values'    => array(
               array('value' => 1, 'label' => utf8_encode('Operação interna')),
               array('value' => 2, 'label' => utf8_encode('Operação interestadual')),
               array('value' => 3, 'label' => utf8_encode('Operação com exterior')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('fin_nfe', 'select', array(
            'name'      => 'fin_nfe',
            'label'     => utf8_encode('Finalidade de Emissão'),
            'title'     => utf8_encode('Finalidade de Emissão'),
            'values'    => array(
               array('value' => 1, 'label' => 'NF-e normal'),
               array('value' => 2, 'label' => 'NF-e complementar'),
               array('value' => 3, 'label' => 'NF-e de ajuste'),
               array('value' => 4, 'label' => utf8_encode('Devolução de mercadoria')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('ind_final', 'select', array(
            'name'      => 'ind_final',
            'label'     => utf8_encode('Finalidade da Operação'),
            'title'     => utf8_encode('Finalidade da Operação'),
            'values'    => array(
               array('value' => 0, 'label' => 'Normal'),
               array('value' => 1, 'label' => 'Consumidor final'),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('ind_pres', 'select', array(
            'name'      => 'ind_pres',
            'label'     => utf8_encode('Relação da Operação'),
            'title'     => utf8_encode('Relação da Operação'),
            'values'    => array(
               array('value' => 0, 'label' => utf8_encode('Não se aplica')),
               array('value' => 1, 'label' => utf8_encode('Operação presencial')),
               array('value' => 2, 'label' => utf8_encode('Operação não presencial, pela Internet')),
               array('value' => 3, 'label' => utf8_encode('Operação não presencial, Teleatendimento')),
               array('value' => 4, 'label' => utf8_encode('NFC-e em operação com entrega a domicílio')),
               array('value' => 9, 'label' => utf8_encode('Operação não presencial, outros')),
            ),
            'required'  => true,
        ));
        
        $fieldset->addField('tem_retirada', 'checkbox', array(
          'name'      => 'tem_retirada',
          'label'     => 'Ident. do Local de Retirada',
          'title'     => 'Ident. do Local de Retirada',
          'required'  => false,
          'checked'   => ($model->getTemRetirada() ? "checked" : ""),
          'after_element_html' => '<small>Informar somente se diferente do remetente</small>',
        ));
        
        $fieldset->addField('tem_entrega', 'checkbox', array(
          'name'      => 'tem_entrega',
          'label'     => 'Ident. do Local de Entrega',
          'title'     => 'Ident. do Local de Entrega',
          'required'  => false,
          'checked'   => ($model->getTemEntrega() ? "checked" : ""),
          'after_element_html' => utf8_encode('<small>Informar somente se diferente do destinatário</small>'),
        ));
        
        $fieldset->addField('tem_importacao', 'checkbox', array(
          'name'      => 'tem_importacao',
          'label'     => utf8_encode('NF-e Importação'),
          'title'     => utf8_encode('NF-e Importação'),
          'required'  => false,
          'checked'   => ($model->getTemImportacao() ? "checked" : ""),
          'after_element_html' => utf8_encode('<small>Informar somente se for para importação</small>'),
        ));
        
        $fieldset->addField('tem_exportacao', 'checkbox', array(
          'name'      => 'tem_exportacao',
          'label'     => utf8_encode('NF-e Exportação'),
          'title'     => utf8_encode('NF-e Exportação'),
          'required'  => false,
          'checked'   => ($model->getTemExportacao() ? "checked" : ""),
          'after_element_html' => utf8_encode('<small>Informar somente se for para exportação</small>'),
        ));
     
        $form->setValues($model->getData());
        $this->setForm($form);
    }

}
?>