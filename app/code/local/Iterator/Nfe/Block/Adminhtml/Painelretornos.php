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
 * @observacao Esta classe pertence originalmente ao projeto NFePHP sendo assim todos os seus créditos serão mantidos.
 */

class Iterator_Nfe_Block_Adminhtml_Painelretornos extends Mage_Adminhtml_Block_Widget
{
    public function __construct() {
        parent::__construct();
        $this->setTemplate('iterator_nfe/painel_retornos.phtml');
    }
    
    public function getRetorno() {
        $retornoMensagem = null;
        $retorno = Mage::getModel('nfe/nferetorno')->load('1');
        if($retorno->getRetornoMensagem()) {
            $retornoMensagem = utf8_decode($retorno->getRetornoMensagem());
        } else {
            $retornoMensagem = 'Ainda não foram registrados envios de NF-e para serem autorizadas pelo SEFAZ.';
        }
        return $retornoMensagem;
    }
}
?>
