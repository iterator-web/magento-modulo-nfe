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
 * @observacao Esta classe possui m�todos que pertencem originalmente ao projeto NFePHP sendo assim todos os seus cr�ditos ser�o mantidos.
 */
class Iterator_Nfe_Helper_NfeTools extends Mage_Core_Helper_Abstract {
    /**
    * Este arquivo é parte do projeto NFePHP - Nota Fiscal eletrônica em PHP.
    *
    * Este programa é um software livre: você pode redistribuir e/ou modificá-lo
    * sob os termos da Licença Pública Geral GNU (GPL)como é publicada pela Fundação
    * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior
    * e/ou
    * sob os termos da Licença Pública Geral Menor GNU (LGPL) como é publicada pela Fundação
    * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
    *
    *
    * Este programa é distribuído na esperança que será útil, mas SEM NENHUMA
    * GARANTIA; nem mesmo a garantia explícita definida por qualquer VALOR COMERCIAL
    * ou de ADEQUA�?�?O PARA UM PROP�?SITO EM PARTICULAR,
    * veja a Licença Pública Geral GNU para mais detalhes.
    *
    * Você deve ter recebido uma cópia da Licença Publica GNU e da
    * Licença Pública Geral Menor GNU (LGPL) junto com este programa.
    * Caso contrário consulte <http://www.fsfla.org/svnwiki/trad/GPLv3> ou
    * <http://www.fsfla.org/svnwiki/trad/LGPLv3>.
    *
    * Está atualizada para :
    *      PHP 5.3
    *      Versão 3.10 dos webservices da SEFAZ com comunicação via SOAP 1.2
    *      e conforme Manual de Integração Versão 5
    *
    * Atenção: Esta classe não mantêm a compatibilidade com a versão 2.00 da SEFAZ !!!
    *
    * @package   NFePHP
    * @name      ToolsNFePHP
    * @version   3.1.00-alpha
    * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL v.3
    * @copyright 2009-2012 &copy; NFePHP
    * @link      http://www.nfephp.org/
    * @author    Roberto L. Machado <linux.rlm at gmail dot com>
    *
    *        CONTRIBUIDORES (em ordem alfabetica):
    *
    *              Allan Rett <allanlimao at gmail dot com>
    *              Antonio Neykson Turbano de Souza <neykson at gmail dot com>
    *              Bernardo Silva <bernardo at datamex dot com dot br>
    *              Bruno Bastos <brunomauro at gmail dot com>
    *              Bruno Lima <brunofileh at gmail.com>
    *              Bruno Tadeu Porto <brunotporto at gmail dot com>
    *              Daniel Viana <daniellista at gmail dot com>
    *              Diego Mosela <diego dot caicai at gmail dot com>
    *              Edilson Carlos Belluomini <edilson at maxihelp dot com dot br>
    *              Eduardo Gusmão <eduardo.intrasis at gmail dot com>
    *              Eduardo Pacheco <eduardo at onlyone dot com dot br>
    *              Fabio A. Silva <binhoouropreto at gmail dot com>
    *              Fabricio Veiga <fabriciostuff at gmail dot com>
    *              Felipe Bonato <montanhats at gmail dot com>
    *              Fernando Mertins <fernando dot mertins at gmail dot com>
    *              Gilmar de Paula Fiocca <gilmar at tecnixinfo dot com dot br>
    *              Giovani Paseto <giovaniw2 at gmail dot com>
    *              Giuliano Nascimento <giusoft at hotmail dot com>
    *              Glauber Cini <glaubercini at gmail dot com>
    *              Guilherme Filippo <guilherme at macromind dot com dot br>
    *              Jorge Luiz Rodrigues Tomé <jlrodriguestome at hotmail dot com>
    *              Leandro C. Lopez <leandro dot castoldi at gmail dot com>
    *              Mario Almeida <prog dot almeida at gmail.com>
    *              Nataniel Fiuza <natan at laxus dot com dot br>
    *              Odair Jose Santos Junior <odairsantosjunior at gmail dot com>
    *              Paulo Gabriel Coghi <paulocoghi at gmail dot com>
    *              Paulo Henrique Demori <phdemori at hotmail dot com>
    *              Rafael Stavarengo <faelsta at gmail dot com>
    *              Roberto Spadim <rspadim at gmail dot com>
    *              Romulo Cordeiro <rrromulo at gmail dot com>
    *              Vinicius L. Azevedo <vinilazev at gmail dot com>
    *              Walber da Silva Sales <eng dot walber at gmail dot com>
    *
    */
    
    /**
     * loadCerts
     * Carrega o certificado pfx e gera as chaves privada e publica no
     * formato pem para a assinatura e para uso do SOAP e registra as
     * variaveis de ambiente.
     * Esta função deve ser invocada antes das outras do sistema que
     * dependam do certificado.
     * Além disso esta função também avalia a validade do certificado.
     * Os certificados padrão A1 (que são usados pelo sistema) tem validade
     * limitada �  1 ano e caso esteja vencido a função retornará false.
     *
     * Resultado
     *  A função irá criar o certificado digital (chaves publicas e privadas)
     *  no formato pem e grava-los no diretorio indicado em $this->certsDir
     *  com os nomes :
     *     CNPJ_priKEY.pem
     *     CNPJ_pubKEY.pem
     *     CNPJ_certKEY.pem
     *  Estes arquivos tanbém serão carregados nas variáveis da classe
     *  $this->priKEY (com o caminho completo para o arquivo CNPJ_priKEY.pem)
     *  $this->pubKEY (com o caminho completo para o arquivo CNPJ_pubKEY.pem)
     *  $this->certKEY (com o caminho completo para o arquivo CNPJ_certKEY.pem)
     * Dependencias
     *   $this->pathCerts
     *   $this->nameCert
     *   $this->passKey
     *
     * @name loadCerts
     * @param  boolean $testaVal True testa a validade do certificado ou false não testa
     * @return boolean true se o certificado foi carregado e false se não
     */
    public function pLoadCerts($testaVal = true)
    {
        $certificado = array();
        try {
            if (!function_exists('openssl_pkcs12_read')) {
                $certificado['retorno'] = "Função não existente: openssl_pkcs12_read!!";
                return $certificado;
            }
            $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'certs' . DS;
            $cnpj = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj'));
            $certNome = Mage::getStoreConfig('nfe/nfe_opcoes/certificado');
            $certSenha = Mage::getStoreConfig('nfe/nfe_opcoes/senha');
            //monta o path completo com o nome da chave privada
            $priKey = $caminho.$cnpj.'_priKEY.pem';
            //monta o path completo com o nome da chave publica
            $pubKey = $caminho.$cnpj.'_pubKEY.pem';
            //monta o path completo com o nome do certificado (chave publica e privada) em formato pem
            $certKey = $caminho.$cnpj.'_certKEY.pem';
            //verificar se o nome do certificado e
            //o path foram carregados nas variaveis da classe
            if (!$certNome) {
                $certificado['retorno'] = "Um certificado deve ser passado para a classe pelo arquivo de configuração!! ";
                return $certificado;
            }
            //monta o caminho completo até o certificado pfx
            $pfxCert = $caminho.$certNome;
            //verifica se o arquivo existe
            if (!file_exists($pfxCert)) {
                $certificado['retorno'] = "Certificado não encontrado!! $pfxCert";
                return $certificado;
            }
            //carrega o certificado em um string
            $pfxContent = file_get_contents($pfxCert);
            //carrega os certificados e chaves para um array denominado $x509certdata
            if (!openssl_pkcs12_read($pfxContent, $x509certdata, $certSenha)) {
                $certificado['retorno'] = "O certificado não pode ser lido!! Provavelmente corrompido ou com formato inválido!!";
                return $certificado;
            }
            if ($testaVal) {
                //verifica sua validade
                if (!$aResp = $this->pValidCerts($x509certdata['cert'])) {
                    $certificado['retorno'] = "Certificado invalido!! - ".$aResp['error'];
                    return $certificado;
                }
            }
            //aqui verifica se existem as chaves em formato PEM
            //se existirem pega a data da validade dos arquivos PEM
            //e compara com a data de validade do PFX
            //caso a data de validade do PFX for maior que a data do PEM
            //deleta dos arquivos PEM, recria e prossegue
            $flagNovo = false;
            if (file_exists($pubKey)) {
                $cert = file_get_contents($pubKey);
                if (!$data = openssl_x509_read($cert)) {
                    //arquivo não pode ser lido como um certificado
                    //então deletar
                    $flagNovo = true;
                } else {
                    //pegar a data de validade do mesmo
                    $cert_data = openssl_x509_parse($data);
                    // reformata a data de validade;
                    $ano = substr($cert_data['validTo'], 0, 2);
                    $mes = substr($cert_data['validTo'], 2, 2);
                    $dia = substr($cert_data['validTo'], 4, 2);
                    //obtem o timeestamp da data de validade do certificado
                    $dValPubKey = gmmktime(0, 0, 0, $mes, $dia, $ano);
                    //compara esse timestamp com o do pfx que foi carregado
                    if ($dValPubKey < $aResp['pfxTimestamp']) {
                        //o arquivo PEM é de um certificado anterior
                        //então apagar os arquivos PEM
                        $flagNovo = true;
                    }//fim teste timestamp
                }//fim read pubkey
            } else {
                //arquivo não localizado
                $flagNovo = true;
            }//fim if file pubkey
            //verificar a chave privada em PEM
            if (!file_exists($priKey)) {
                //arquivo não encontrado
                $flagNovo = true;
            }
            //verificar o certificado em PEM
            if (!file_exists($certKey)) {
                //arquivo não encontrado
                $flagNovo = true;
            }
            //criar novos arquivos PEM
            if ($flagNovo) {
                if (file_exists($pubKey)) {
                    unlink($pubKey);
                }
                if (file_exists($priKey)) {
                    unlink($priKey);
                }
                if (file_exists($certKey)) {
                    unlink($certKey);
                }
                //recriar os arquivos pem com o arquivo pfx
                if (! file_put_contents($priKey, $x509certdata['pkey'])) {
                    $certificado['retorno'] = "Impossivel gravar no diretório!!! Permissão negada!!";
                    return $certificado;
                }
                file_put_contents($pubKey, $x509certdata['cert']);
                file_put_contents($certKey, $x509certdata['pkey']."\r\n". $x509certdata['cert']);
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        $certificado['retorno'] = 'sucesso';
        $certificado['priKey'] = $priKey;
        $certificado['pubKey'] = $pubKey;
        $certificado['certKey'] = $certKey;
        return $certificado;
    } //fim loadCerts
    /**
     * pValidCerts
     * Validaçao do cerificado digital, além de indicar
     * a validade, este metodo carrega a propriedade
     * mesesToexpire da classe que indica o numero de
     * meses que faltam para expirar a validade do mesmo
     * esta informacao pode ser utilizada para a gestao dos
     * certificados de forma a garantir que sempre estejam validos
     *
     * @name pValidCerts
     * @param    string  $cert Certificado digital no formato pem
     * @param    array   $aRetorno variavel passa por referência Array com os dados do certificado
     * @return  boolean true ou false
     */
    protected function pValidCerts($cert = '', &$aRetorno = '')
    {
        try {
            if ($cert == '') {
                $msg = "O certificado é um parâmetro obrigatorio.";
            }
            if (!$data = openssl_x509_read($cert)) {
                $msg = "O certificado não pode ser lido pelo SSL - $cert .";
            }
            $flagOK = true;
            $errorMsg = "";
            $cert_data = openssl_x509_parse($data);
            // reformata a data de validade;
            $ano = substr($cert_data['validTo'], 0, 2);
            $mes = substr($cert_data['validTo'], 2, 2);
            $dia = substr($cert_data['validTo'], 4, 2);
            //obtem o timestamp da data de validade do certificado
            $dValid = gmmktime(0, 0, 0, $mes, $dia, $ano);
            // obtem o timestamp da data de hoje
            $dHoje = gmmktime(0, 0, 0, date("m"), date("d"), date("Y"));
            // compara a data de validade com a data atual
            if ($dValid < $dHoje) {
                $flagOK = false;
                $errorMsg = "A Validade do certificado expirou em [" .$dia.'/'.$mes.'/'.$ano."]";
            } else {
                $flagOK = $flagOK && true;
            }
            //diferença em segundos entre os timestamp
            $diferenca = $dValid - $dHoje;
            // convertendo para dias
            $diferenca = round($diferenca /(60*60*24), 0);
            //carregando a propriedade
            $daysToExpire = $diferenca;
            // convertendo para meses e carregando a propriedade
            $numM = ($ano * 12 + $mes);
            $numN = (date("y") * 12 + date("m"));
            //numero de meses até o certificado expirar
            $monthsToExpire = ($numM-$numN);
            $aRetorno = array('status'=>$flagOK,'error'=>$errorMsg,'meses'=>$monthsToExpire,'dias'=>$daysToExpire,'pfxTimestamp'=>$dValid);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return $aRetorno;
    } //fim validCerts
    /**
     * cleanCerts
     * Retira as chaves de inicio e fim do certificado digital
     * para inclusão do mesmo na tag assinatura do xml
     *
     * @name cleanCerts
     * @param    $certFile
     * @return   mixed false ou string contendo a chave digital limpa
     */
    public function pCleanCerts($certFile)
    {
        try {
            //inicializa variavel
            $data = '';
            //carregar a chave publica do arquivo pem
            if (!$pubKey = file_get_contents($certFile)) {
                $msg = "Arquivo não encontrado - $certFile .";
                throw new nfephpException($msg);
            }
            //carrega o certificado em um array usando o LF como referencia
            $arCert = explode("\n", $pubKey);
            foreach ($arCert as $curData) {
                //remove a tag de inicio e fim do certificado
                if (strncmp($curData, '-----BEGIN CERTIFICATE', 22) != 0
                    && strncmp($curData, '-----END CERTIFICATE', 20) != 0) {
                    //carrega o resultado numa string
                    $data .= trim($curData);
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return $data;
    }//fim cleanCerts
}
