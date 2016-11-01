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
            echo '<h1>SOLICITA��O INV�LIDA</h1>';
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
