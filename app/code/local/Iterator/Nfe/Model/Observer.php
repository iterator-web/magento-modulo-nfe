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

class Iterator_Nfe_Model_Observer extends Mage_Core_Model_Abstract {
    
    public function autorizarNfe() {
        $enviosSucesso = true;
        $nfeCollection = Mage::getModel('nfe/nfe')->getCollection()
                        ->addFieldToFilter('status', array('in' => array('0','1','2','3','4')));
        foreach($nfeCollection as $nfe) {
            if($nfe->getStatus() == '0') {
                $this->setRetorno(utf8_encode('A fila de envios cont�m uma NF-e que ainda est� aguardando aprova��o. O n�mero da NF-e �: '.$nfe->getNNf()));
                $enviosSucesso = false;
                break;
            } else if($nfe->getStatus() == '4') {
                continue;
            } else if($nfe->getStatus() == '3') {
                
            } else if($nfe->getStatus() == '2') {
                
            } else if($nfe->getStatus() == '1') {
                $indSinc = 0;
                $nfeTools = Mage::Helper('nfe/nfeTools');
                $xmlNfe = $nfeTools->getXmlNfe($nfe);
                $sXml = $this->xmlString($xmlNfe);
                $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
                $aRetorno = array();
                $protocolo = $nfeTools->autoriza($aNFe, $nfe->getNfeId(), $aRetorno, $indSinc, $nfe->getTpAmb(), $estadoEmitente->getCode(), $nfe->getCUf());
                if($protocolo['retorno'] == 'sucesso') {
                    if($indSinc == 1) {
                        $nfe->setVerAplic($protocolo['infProt']['verAplic']);
                        $nfe->setDhRecbto($protocolo['infProt']['dhRecbto']);
                        $nfe->setNProt($protocolo['infProt']['nProt']);
                        $nfe->setDigVal($protocolo['infProt']['digVal']);
                        $nfe->setDhCStat($protocolo['infProt']['cStat']);
                        $nfe->setXMotivo($protocolo['infProt']['xMotivo']);
                        $xmlProtocolado = $nfeTools->addProt($xmlNfe, $protocolo['infProt'], $protocolo['protNFeVersao'], 'protNFe');
                        if($xmlProtocolado['retorno'] == 'sucesso') {
                            $this->salvarXml($xmlProtocolado['xml'], $nfe);
                            $nfe->setStatus('3');
                            $nfe->setMensagem(utf8_encode('Autorizado pelo org�o respons�vel.'));
                            $nfe->save();
                        } else {
                            $nfe->setStatus('4');
                            $nfe->setMensagem(utf8_encode('Aguardando corre��o para envio ao org�o respons�vel. Erro: '.utf8_decode($xmlProtocolado['retorno'])));
                            $nfe->save();
                            $this->setRetorno(utf8_encode('A fila de envios teve problemas durante a protocola��o da NF-e n�mero: '.$nfe->getNNf(). '. O problema relatado: '.utf8_decode($xmlProtocolado['retorno'])));
                            $enviosSucesso = false;
                        }
                    } else {
                        $nfe->setNRec($protocolo['infRec']['nRec']);
                        $nfe->setStatus('2');
                        $nfe->setMensagem(utf8_encode('Aguardando retorno do org�o respons�vel.'));
                        $nfe->save();
                    }
                } else {
                    if(strpos($protocolo['retorno'],'204') !== false || strpos($protocolo['retorno'],'656') !== false) {
                        $nfe->setStatus('2');
                        $nfe->setMensagem(utf8_encode('Aguardando para envio ao org�o respons�vel. Erro: '.utf8_decode($protocolo['retorno'])));
                    } else if(strpos($protocolo['retorno'],'110') !== false || strpos($protocolo['retorno'],'205') !== false || strpos($protocolo['retorno'],'233') !== false || 
                            strpos($protocolo['retorno'],'234') !== false || strpos($protocolo['retorno'],'301') !== false || strpos($protocolo['retorno'],'302') !== false) {
                        $nfe->setStatus('8');
                        $nfe->setMensagem(utf8_encode('A utiliza��o da NF-e foi denegada. Erro: '.utf8_decode($protocolo['retorno'])));
                    } else {
                        $nfe->setStatus('4');
                        $nfe->setMensagem(utf8_encode('Aguardando corre��o para envio ao org�o respons�vel. Erro: '.utf8_decode($protocolo['retorno'])));
                    }
                    $nfe->save();
                    $this->setRetorno(utf8_encode('A fila de envios teve problemas durante o envio da NF-e n�mero: '.$nfe->getNNf(). '. O problema relatado: '.utf8_decode($protocolo['retorno'])));
                    $enviosSucesso = false;
                }
            }
        }
        if($enviosSucesso) {
            $this->setRetorno(utf8_encode('A fila de envios est� vazia. Todas as NF-e foram enviadas com sucesso.'));
        }
    }
    
    private function setRetorno($mensagem) {
        $retorno = Mage::getModel('nfe/nferetorno')->load('1');
        $retorno->setRetornoId('1');
        $retorno->setRetornoMensagem($mensagem);
        $retorno->save();
    }
    
    private function salvarXml($xmlNfe, $nfe) {
        if($nfe->getTpNf() == '0') {
            $tipo = 'entrada';
        } else {
            $tipo = 'saida';
        }
        $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS;
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->preservWhiteSpace = false; //elimina espaços em branco
        $doc->formatOutput = false;
        $doc->loadXML($xmlNfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $doc->save($caminho.$nfe->getIdTag().'.xml');
    }
    
    private function xmlString($xmlNfe) {
        $aNFe = file_get_contents($xmlNfe);
        $xmldoc = new DOMDocument('1.0', 'utf-8');
        $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
        $xmldoc->formatOutput = false;
        $xmldoc->loadXML($aNFe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $sXml = $xmldoc->saveXML();
        return $sXml;
    }
}

?>
