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

class Iterator_Nfe_OperacoesController extends Mage_Core_Controller_Front_Action {
    
    public function downloadAction() {
        $params = $this->getRequest()->getParams();
        $chave = $params['key'];
        $formato = $params['formato'];
        $tipo = $params['tipo'];
        $chaveDecrypt = base64_decode(str_pad(strtr($chave, '-_', '+/'), strlen($chave) % 4, '=', STR_PAD_RIGHT));
        $identificador = 'NFe';
        if($tipo == 'corrigido') {
            $identificador = 'CCe';
        }
        $filepath = Mage::getBaseDir(). DS . 'nfe' . DS . $formato . DS . $tipo . DS . $identificador.$chaveDecrypt . '.'.$formato;
        if(!is_file($filepath) || !is_readable($filepath)) {
            echo '<h1>SOLICITAÇÃO INVÁLIDA</h1>';
            exit();
        }
        $this->getResponse()
                    ->setHttpResponseCode(200)
                    ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                    ->setHeader('Pragma', 'public', true)
                    ->setHeader('Content-type', 'application/force-download')
                    ->setHeader('Content-Length', filesize($filepath))
                    ->setHeader('Content-Disposition', 'attachment' . '; filename=' . basename($filepath));
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        readfile($filepath);
        exit;
    }
}
?>
