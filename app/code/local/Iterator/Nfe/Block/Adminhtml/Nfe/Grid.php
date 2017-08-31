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
 * Contrato: http://www.iterator.com.br/licenca.txt
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

class Iterator_Nfe_Block_Adminhtml_Nfe_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        
        $this->setDefaultSort('nfe_id');
        $this->setId('iterator_nfe_nfe_grid');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
    }
    
    public function getMainButtonsHtml() {
        $html = parent::getMainButtonsHtml();
        $addButton = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('adminhtml')->__(utf8_encode('Gerenciar Range')),
                'onclick'   => "setLocation('".$this->getUrl('*/*/editRange')."')",
                'class'   => 'task'
            ))->toHtml();
        $addButtonEnviarMes = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('adminhtml')->__(utf8_encode('Enviar NF-e do Mês')),
                'onclick'   => "setLocation('".$this->getUrl('*/*/editEnviar')."')",
                'class'   => 'task'
            ))->toHtml();
        
        return $addButtonEnviarMes.$addButton.$html;
    }
     
    protected function _getCollectionClass() {
        return 'nfe/nfe_collection';
    }
     
    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()->joinLeft(array('identificacao' => $collection->getTable('nfe/nfeidentificacao')), 'main_table.nfe_id = identificacao.nfe_id and identificacao.tipo_identificacao = "dest"', array('identificacao_dest_uf' => 'identificacao.uf'));
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        $this->addColumn('nfe_id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'center',
                'width' => '50px',
                'index' => 'nfe_id',
                'filter_index' => 'main_table.nfe_id'
            )
        );
         
        $this->addColumn('pedido_increment_id',
            array(
                'header'=> 'Pedido',
                'index' => 'pedido_increment_id',
                'width' => '100px',
                'filter_index' => 'pedido_increment_id'
            )
        );
        
        $this->addColumn('identificacao_dest_uf',
            array(
                'header'=> 'UF Destino',
                'index' => 'identificacao_dest_uf',
                'width' => '50px',
                'align'     =>'center',
                'filter_index' => 'identificacao.uf'
            )
        );
        
        $this->addColumn('tp_nf',
            array(
                'header'=> $this->__('Tipo'),
                'index' => 'tp_nf',
                'align'     =>'center',
                'filter_index' => 'tp_nf',
                'type'      => 'options',
                'options'   => array(
                    0 => 'Entrada',
                    1 => utf8_encode('Saída')
                ),
                'renderer'  => 'Iterator_Nfe_Block_Adminhtml_Nfe_AcaoTipo',
            )
        );
        
        $this->addColumn('serie',
            array(
                'header'=> $this->__(utf8_encode('Série')),
                'index' => 'serie',
                'width' => '50px',
                'filter_index' => 'serie'
            )
        );
        
        $this->addColumn('n_nf',
            array(
                'header'=> $this->__(utf8_encode('Número')),
                'index' => 'n_nf',
                'width' => '100px',
                'filter_index' => 'n_nf',
                'type'      => 'action',
                'renderer'  => 'Iterator_Nfe_Block_Adminhtml_Nfe_AcaoNnf',
            )
        );
        
        $this->addColumn('id_tag',
            array(
                'header'=> $this->__('Chave de Acesso'),
                'index' => 'id_tag',
                'filter_index' => 'id_tag'
            )
        );
        
        $this->addColumn('status',
            array(
                'header'=> $this->__(utf8_encode('Status')),
                'index' => 'status',
                'align'     =>'center',
                'width' => '200px',
                'filter_index' => 'status',
                'type'      => 'options',
                'options'   => array(
                    0 => utf8_encode('Aguardando Aprovação'),
                    1 => 'Aguardando Envio',
                    2 => 'Aguardando Retorno',
                    3 => 'Autorizado',
                    4 => utf8_encode('Aguardando Correção'),
                    5 => 'Aguardando Cancelamento',
                    6 => 'Cancelado',
                    7 => 'Completo',
                    8 => 'Denegado',
                    9 => 'Inutilizado'
                ),
                'renderer'  => 'Iterator_Nfe_Block_Adminhtml_Nfe_AcaoStatus',
            )
        );
        
        $this->addColumn('mensagem',
            array(
                'header'=> $this->__(utf8_encode('Mensagem')),
                'index' => 'mensagem',
                'filter_index' => 'mensagem'
            )
        );
        
        $this->addColumn('dh_emi',
            array(
                'header'=> $this->__(utf8_encode('Data Emissão')),
                'width'     => '150px',
                'index' => 'dh_emi',
                'filter_index' => 'dh_emi',
                'type' => 'datetime'
            )
        );
        
        $this->addColumn('dh_recbto',
            array(
                'header'=> $this->__(utf8_encode('Data Autorização')),
                'width'     => '150px',
                'index' => 'dh_recbto',
                'filter_index' => 'dh_recbto',
                'type' => 'datetime'
            )
        );
        
        $this->addColumn('action',
            array(
                'header'    =>  utf8_encode('Ações'),
                'align'     =>'center',
                'width'     => '180px',
                'type'      => 'action',
                'index'     => 'stores',
                'filter'    => false,
                'sortable'  => false,
                'renderer'  => 'Iterator_Nfe_Block_Adminhtml_Nfe_Acao',
            )
        );
        
        $this->addExportType('*/*/exportCsv', Mage::helper('nfe')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('nfe')->__('XML'));
         
        return parent::_prepareColumns();
    }
    
    protected function _prepareMassaction() {
        $this->setMassactionIdField('nfe_id');
        $this->getMassactionBlock()->setFormFieldName('nfe_id');
        $this->getMassactionBlock()->addItem('imprimir', array(
            'label'=> Mage::helper('nfe')->__('Imprimir'),
            'url'  => $this->getUrl('*/*/massImprimir', array('' => ''))
        ));
        return $this;
    }
}

?>
