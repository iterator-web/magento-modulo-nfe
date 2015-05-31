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

class Iterator_Nfe_Block_Adminhtml_Nfe_AcaoStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
   
    public function render(Varien_Object $row) {
        $status =  $row->getData('status');
        $descricao = null;
        switch($status) {
            case 0:
                $descricao = utf8_encode('<strong style="color:#77a97c;">AGUARDANDO APROVAÇÃO</strong>');
                break;
            case 1:
                $descricao = '<strong style="color:#e6a800;">AGUARDANDO ENVIO</strong>';
                break;
            case 2:
                $descricao = '<strong style="color:#737373;">AGUARDANDO RETORNO</strong>';
                break;
            case 3:
                $descricao = '<strong style="color:#000000;">AUTORIZADO</strong>';
                break;
            case 4:
                $descricao = utf8_encode('<strong style="color:#7b3c20;">AGUARDANDO CORREÇÃO</strong>');
                break;
            case 5:
                $descricao = '<strong style="color:#c1a0a0;">AGUARDANDO CANCELAMENTO</strong>';
                break;
            case 6:
                $descricao = '<strong style="color:#be3030;">CANCELADO</strong>';
                break;
            case 7:
                $descricao = '<strong style="color:#0aa219;">COMPLETO</strong>';
                break;
            case 8:
                $descricao = '<strong style="color:#995015;">DENEGADO</strong>';
                break;
            default:
                break;
        }
        return $descricao;
    }
}
?>
