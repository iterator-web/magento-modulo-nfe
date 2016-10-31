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

class Iterator_Nfe_Helper_NfeHelper extends Mage_Core_Helper_Abstract {
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
     * Tipo de ambiente produção
     */
    const AMBIENTE_PRODUCAO = 1;
    /**
     * Tipo de ambiente homologação
     */
    const AMBIENTE_HOMOLOGACAO = 2;
    /**
     * soapDebug
     * Mensagens de debug da comunicação SOAP
     * @var string
     */
    public $soapDebug = '';
    /**
     * Sefaz Virtual Ambiente Nacional (SVAN), alguns estados utilizam esta Sefaz Virtual.
     */
    const SVAN = 'SVAN';
    /**
     * Sefaz Virtual Rio Grande do Sul (SVRS), alguns estados utilizam esta Sefaz Virtual.
     */
    const SVRS = 'SVRS';
    /**
     * Sefaz Virtual de Contingência Ambiente Nacional (SVC-AN)
     */
    const CONTINGENCIA_SVCAN = 'SVCAN';
    /**
     * Sefaz Virtual de Contingência Rio Grande do Sul (SVC-RS)
     */
    const CONTINGENCIA_SVCRS = 'SVCRS';
    /**
     * URLPortal
     * Instância do WebService
     * @var string
     */
    private $URLPortal = 'http://www.portalfiscal.inf.br/nfe';
    /**
     * aliaslist
     * Lista dos aliases para os estados que usam Sefaz própria ou Sefaz Virtual
     * @var array
     */
    private $aliaslist = array(
        //unidades da Federação:
        'AC'=>'SVRS',
        'AL'=>'SVRS',
        'AM'=>'AM',
        'AN'=>'AN',
        'AP'=>'SVRS',
        'BA'=>'BA',
        'CE'=>'CE',
        'DF'=>'SVRS',
        'ES'=>'SVRS',
        'GO'=>'GO',
        'MA'=>'SVAN',
        'MG'=>'MG',
        'MS'=>'MS',
        'MT'=>'MT',
        'PA'=>'SVAN',
        'PB'=>'SVRS',
        'PE'=>'PE',
        'PI'=>'SVAN',
        'PR'=>'PR',
        'RJ'=>'SVRS',
        'RN'=>'SVRS',
        'RO'=>'SVRS',
        'RR'=>'SVRS',
        'RS'=>'RS',
        'SC'=>'SVRS',
        'SE'=>'SVRS',
        'SP'=>'SP',
        'TO'=>'SVRS',
        //demais autorizadores do projeto NF-e:
        'SVAN'=>'SVAN',
        'SVRS'=>'SVRS',
        'SVCAN'=>'SVCAN',
        'SVCRS'=>'SVCRS');
    
    /**
     * enableSVAN
     * Indica o acesso ao serviço SVAN: Sefaz Virtual Ambiente Nacional
     * @var boolean
     */
    public $enableSVAN = false;
    /**
     * enableSVRS
     * Indica o acesso ao serviço SVRS: Sefaz Virtual Rio Grande do Sul
     * @var boolean
     */
    public $enableSVRS = false;
    /**
     * enableSVCRS
     * Habilita contingência ao serviço SVC-RS: Sefaz Virtual de Contingência Rio Grande do Sul
     * @var boolean
     */
    public $enableSVCRS = false;
    /**
     * enableSVCAN
     * Habilita contingência ao serviço SVC-AN: Sefaz Virtual de Contingência Ambiente Nacional
     * @var boolean
     */
    public $enableSVCAN = false;
    /**
     * soapTimeout
     * Limite de tempo que o SOAP aguarda por uma conexão
     * @var integer 0-indefinidamente ou numero de segundos
     */
    public $soapTimeout = 10;
    /**
     * URLnfe
     * Instância do WebService
     * @var string
     */
    private $URLnfe = 'http://www.portalfiscal.inf.br/nfe';
    /**
     * timeZone
     * Zona de tempo GMT
     */
    protected $timeZone = '-03:00';
    
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
    
    /**
     * autoriza
     * Envia NFe para a SEFAZ autorizar.
     * ATENÇÃO! Este é o antigo método "sendLot()" que enviava lotes de NF-e versão "2.00"
     * consumindo o WS "NfeRecepcao2", agora este método está preparado apenas para a versão
     * "3.10" e por isso utiliza o WS "NfeAutorizacao" sempre em modo síncrono.
     *
     * @name autoriza
     * @package NFePHP
     * @author Roberto L. Machado <linux.rlm at gmail dot com>
     * @param string  $sxml   string com uma nota fiscal em xml
     * @param integer $idLote id do lote e um numero (numeração sequencial)
     * @param array   $aRetorno parametro passado por referencia contendo a resposta da consulta em um array
     * @param integer $indSinc Indicação webservice assíncrono (0) ou síncrono (1)
     * @return mixed string XML do retorno do webservice, ou false se ocorreu algum erro
     */
    public function autoriza($sxml, $idLote, &$aRetorno = array(), $indSinc = 1, $tpAmb, $ufEmitente, $cUf)
    {
        $protocolo = array();
        try {
            //retorno do método em array (esta estrutura espelha a estrutura do XML retornado pelo webservice
            //IMPORTANTE: esta estrutura varia parcialmente conforme o $indSinc
            $aRetorno = array(
                'bStat'=>false,
                'tpAmb'=>'',
                'verAplic'=>'',
                'cStat'=>'',
                'xMotivo'=>'',
                'cUF'=>'',
                'dhRecbto'=>'');
            if ($indSinc === 0) {
                //dados do recibo do lote (gerado apenas se o lote for aceito)
                $aRetorno['infRec'] = array('nRec'=>'','tMed'=>'');
            } elseif ($indSinc === 1) {
                //dados do protocolo de recebimento da NF-e
                $aRetorno['protNFe'] = array(
                    'versao'=>'',
                    'infProt'=>array( //informações do protocolo de autorização da NF-e
                        'tpAmb'=>'',
                        'verAplic'=>'',
                        'chNFe'=>'',
                        'dhRecbto'=>'',
                        'nProt'=>'',
                        'digVal'=>'',
                        'cStat'=>'',
                        'xMotivo'=>''));
            } else {
                $protocolo['retorno'] = 'Parametro indSinc deve ser inteiro 0 ou 1, verifique.';
                return $protocolo;
            }
            /*
            //verifica se alguma SVC esta habilitada, neste caso precisa recarregar os webservices
            if ($this->enableSVCAN) {
                $aURL = $this->pLoadSEFAZ($this->tpAmb, self::CONTINGENCIA_SVCAN);
            } elseif ($this->enableSVCRS) {
                $aURL = $this->pLoadSEFAZ($this->tpAmb, self::CONTINGENCIA_SVCRS);
            } else {
                $aURL = $this->aURL;
            }
             */
            $aURL = $this->pLoadSEFAZ($tpAmb, $ufEmitente);
            //identificação do serviço: autorização de NF-e
            $servico = 'NfeAutorizacao';
            //recuperação da versão
            $versao = $aURL[$servico]['version'];
            //recuperação da url do serviço
            $urlservico = $aURL[$servico]['URL'];
            //recuperação do método
            $metodo = $aURL[$servico]['method'];
            //montagem do namespace do serviço
            $namespace = $this->URLPortal.'/wsdl/'.$servico;
            //valida o parâmetro da string do XML da NF-e
            if (empty($sxml) || ! simplexml_load_string($sxml)) {
                $protocolo['retorno'] = 'XML de NF-e para autorizacao recebido no parametro parece invalido, verifique.';
                return $protocolo;
            }
            // limpa a variavel
            $sNFe = $sxml;
            //remove <?xml version="1.0" encoding=... e demais caracteres indesejados
            $sNFe = preg_replace("/<\?xml.*\?>/", "", $sNFe);
            $sNFe = str_replace(array("\r","\n","\s"), "", $sNFe);
            //montagem do cabeçalho da comunicação SOAP
            $cabec = "<nfeCabecMsg xmlns=\"$namespace\">"
                    . "<cUF>$cUf</cUF>"
                    . "<versaoDados>$versao</versaoDados>"
                    . "</nfeCabecMsg>";
            //montagem dos dados da mensagem SOAP
            $dados = "<nfeDadosMsg xmlns=\"$namespace\">"
                    . "<enviNFe xmlns=\"$this->URLPortal\" versao=\"$versao\">"
                    . "<idLote>$idLote</idLote>"
                    . "<indSinc>$indSinc</indSinc>$sNFe</enviNFe></nfeDadosMsg>";
            //envia dados via SOAP
            $retorno = $this->pSendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb);
            //verifica o retorno
            if ($retorno['resultado'] == 'erro') {
                $protocolo['retorno'] = $retorno['valor'];
                return $protocolo;
            }
            //tratar dados de retorno
            $doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno['valor'], LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = $this->pSimpleGetValue($doc, "cStat");
            $xMotivo = $this->pSimpleGetValue($doc, "xMotivo");
            //verifica o codigo do status da resposta, se vazio houve erro
            if ($cStat == '') {
                $protocolo['retorno'] = 'O retorno nao contem cStat verifique o debug do soap.';
                return $protocolo;
            } elseif ($indSinc === 0 && $cStat == '103') { //103-Lote recebido com sucesso
                $aRetorno['bStat'] = true;
            } elseif ($indSinc === 1 && $cStat == '104') { //104-Lote processado, podendo ter ou não o protNFe (#AR11 no layout)
                $aRetorno['bStat'] = true;
            } else {
                $protocolo['retorno'] = sprintf("%s - %s", $cStat, $xMotivo);
                return $protocolo;
            }
            // status da resposta do webservice
            $aRetorno['cStat'] = $cStat;
            // motivo da resposta (opcional)
            $aRetorno['xMotivo'] = $this->pSimpleGetValue($doc, "xMotivo");
            // data e hora da mensagem (opcional)
            if ($dhRecbto = $this->pSimpleGetValue($doc, "dhRecbto")) {
                $aRetorno['dhRecbto'] = date("d/m/Y H:i:s", $this->pConvertTime($dhRecbto));
            }
            //tipo do ambiente, versão do aplicativo e código da UF
            $aRetorno['tpAmb'] = $this->pSimpleGetValue($doc, "tpAmb");
            $aRetorno['verAplic'] = $this->pSimpleGetValue($doc, "verAplic");
            $aRetorno['cUF'] = $this->pSimpleGetValue($doc, "cUF");
            if ($indSinc == 1) {
                //retorno síncrono do webservice: dados do protocolo da NF-e
                $nodeProtNFe = $doc->getElementsByTagName('protNFe')->item(0);
                $nodeInfProt = $doc->getElementsByTagName('infProt')->item(0);
                $aRetorno['protNFe']['versao'] = $nodeProtNFe->getAttribute('versao');
                $infProt = array();
                $infProt['tpAmb'] = $this->pSimpleGetValue($nodeInfProt, "tpAmb");
                $infProt['verAplic'] = $this->pSimpleGetValue($nodeInfProt, "verAplic");
                $infProt['chNFe'] = $this->pSimpleGetValue($nodeInfProt, "chNFe");
                $dhRecbto = $this->pSimpleGetValue($nodeInfProt, "dhRecbto");
                $infProt['dhRecbto'] = date("Y-m-d H:i:s", $this->pConvertTime($dhRecbto));
                $infProt['digVal'] = $this->pSimpleGetValue($nodeInfProt, "digVal");
                $infProt['cStat'] = $this->pSimpleGetValue($nodeInfProt, "cStat");
                $infProt['xMotivo'] = $this->pSimpleGetValue($nodeInfProt, "xMotivo");
                //número do protocolo de autorização (opcional)
                $infProt['nProt'] = $this->pSimpleGetValue($nodeInfProt, "nProt");
                $aRetorno['protNFe']['infProt'] = $infProt;
                //nome do arquivo de retorno: chave da NF-e com sufixo "-prot"
                //$nome = $this->temDir.$infProt['chNFe'].'-prot.xml';
                if(!$infProt['nProt']) {
                    $protocolo['retorno'] = sprintf("%s - %s", $infProt['cStat'], $infProt['xMotivo']);
                    return $protocolo;
                } else {
                    $protocolo['retorno'] = 'sucesso';
                    $protocolo['infProt'] = $infProt;
                    $protocolo['protNFeVersao'] = $nodeProtNFe->getAttribute('versao');
                }
            } else {
                //retorno assíncrono do webservice: dados do recibo do lote
                $aRetorno['infRec'] = array();
                $aRetorno['infRec']['nRec'] = $this->pSimpleGetValue($doc, "nRec");
                $aRetorno['infRec']['tMed'] = $this->pSimpleGetValue($doc, "tMed");
                //nome do arquivo de retorno: ID do lote com sufixo "-prot"
                //$nome = $this->temDir.$idLote.'-rec.xml';
                $protocolo['retorno'] = 'sucesso';
                $protocolo['infRec'] = $aRetorno['infRec'];
            }
            //grava o retorno na pasta de temporários
            //$nome = $doc->save($nome);
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return $protocolo;
    }// fim autoriza
    
    /**
     * getProtocol
     * Solicita resposta do lote de Notas Fiscais ou o protocolo de
     * autorização da NFe
     * Caso $this->cStat == 105 Tentar novamente mais tarde
     *
     * @name getProtocol
     * @param  string   $recibo numero do recibo do envio do lote
     * @param  string   $chave  numero da chave da NFe de 44 digitos
     * @param   string   $tpAmb  numero do ambiente 1-producao e 2-homologação
     * @param   array    $aRetorno Array com os dados do protocolo
     * @return mixed    false ou xml do retorno do webservice
     */
    public function getProtocol($recibo = '', $chave = '', $tpAmb = '', &$aRetorno = array(), $siglaUF, $cUF)
    {
        $protocolo = array();
        try {
            //carrega defaults do array de retorno
            $aRetorno = array(
                'bStat'=>false,
                'verAplic'=>'',
                'cStat'=>'',
                'xMotivo'=>'',
                'cUF'=>'',
                'chNFe'=>'',
                'aProt'=>'',
                'aCanc'=>'',
                'xmlRetorno'=>'');
            /*
            if ($tpAmb == '') {
                $tpAmb = $this->tpAmb;
            }
            if (!in_array($tpAmb, array(self::AMBIENTE_PRODUCAO, self::AMBIENTE_HOMOLOGACAO))) {
                $tpAmb = self::AMBIENTE_HOMOLOGACAO;
            }
             */
            if (!$aURL = $this->pLoadSEFAZ($tpAmb, $siglaUF)) {
                $msg = "Erro no carregamento das informacoes da SEFAZ";
                $protocolo['retorno'] = $msg;
                return $protocolo;
            }
            $ctpEmissao = '';
            //verifica se a chave foi passada
            if ($chave != '') {
                //se sim extrair o cUF da chave
                $cUF = substr($chave, 0, 2);
                $ctpEmissao = substr($chave, 34, 1);
                /*
                //testar para ver se é o mesmo do emitente
                if ($cUF != $this->cUF || $tpAmb != $this->tpAmb) {
                    //se não for o mesmo carregar a sigla
                    $siglaUF = $this->siglaUFList[$cUF];
                    //recarrega as url referentes aos dados passados como parametros para a função
                    $aURL = $this->pLoadSEFAZ($tpAmb, $siglaUF);
                }
                 */
            }
            //verifica se alguma SVC esta habilitada
            if ($this->enableSVCAN) {
                $aURL = $this->pLoadSEFAZ($tpAmb, self::CONTINGENCIA_SVCAN);
            } elseif ($this->enableSVCRS) {
                $aURL = $this->pLoadSEFAZ($tpAmb, self::CONTINGENCIA_SVCRS);
            }
            if ($recibo == '' && $chave == '') {
                $protocolo['retorno'] = "ERRO. Favor indicar o numero do recibo ou a chave de acesso da NF-e.";
                return $protocolo;
            }
            if ($recibo != '' && $chave != '') {
                $protocolo['retorno'] = "ERRO. Favor indicar somente o numero do recibo ou a chave de acesso da NF-e.";
                return $protocolo;
            }
            //consulta pelo recibo
            if ($recibo != '' && $chave == '') {
                //buscar os protocolos pelo numero do recibo do lote
                //identifica��o do servi�o
                $servico = 'NfeRetAutorizacao';
                //recupera��o da vers�o
                $versao = $aURL[$servico]['version'];
                //recupera��o da url do servi�o
                $urlservico = $aURL[$servico]['URL'];
                //recupera��o do m�todo
                $metodo = $aURL[$servico]['method'];
                //montagem do namespace do servi�o
                $namespace = $this->URLPortal.'/wsdl/'.$servico;
                //montagem do cabe�alho da comunica��o SOAP
                $cabec = "<nfeCabecMsg xmlns=\"$namespace\">"
                        . "<cUF>$cUF</cUF>"
                        . "<versaoDados>$versao</versaoDados>"
                        . "</nfeCabecMsg>";
                //montagem dos dados da mensagem SOAP
                $dados = "<nfeDadosMsg xmlns=\"$namespace\">"
                        . "<consReciNFe xmlns=\"$this->URLPortal\" versao=\"$versao\">"
                        . "<tpAmb>$tpAmb</tpAmb>"
                        . "<nRec>$recibo</nRec>"
                        . "</consReciNFe>"
                        . "</nfeDadosMsg>";
                //nome do arquivo
                $nomeArq = $recibo.'-protrec.xml';
            }
            //consulta pela chave
            if ($recibo == '' && $chave != '') {
                //buscar o protocolo pelo numero da chave de acesso
                //identifica��o do servi�o
                $servico = 'NfeConsultaProtocolo';
                //recupera��o da vers�o
                $versao = $aURL[$servico]['version'];
                //recupera��o da url do servi�o
                $urlservico = $aURL[$servico]['URL'];
                //recupera��o do m�todo
                $metodo = $aURL[$servico]['method'];
                //montagem do namespace do servi�o
                $namespace = $this->URLPortal.'/wsdl/NfeConsulta2';
                //montagem do cabe�alho da comunica��o SOAP
                $cabec = "<nfeCabecMsg xmlns=\"$namespace\">"
                        . "<cUF>$cUF</cUF>"
                        . "<versaoDados>$versao</versaoDados>"
                        . "</nfeCabecMsg>";
                //montagem dos dados da mensagem SOAP
                $dados = "<nfeDadosMsg xmlns=\"$namespace\">"
                        . "<consSitNFe xmlns=\"$this->URLPortal\" versao=\"$versao\">"
                        . "<tpAmb>$tpAmb</tpAmb>"
                        . "<xServ>CONSULTAR</xServ>"
                        . "<chNFe>$chave</chNFe>"
                        . "</consSitNFe></nfeDadosMsg>";
            }
            //envia a solicitação via SOAP
            if (!$retorno = $this->pSendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb)) {
                $protocolo['retorno'] = "Nao houve retorno Soap verifique a mensagem de erro e o debug.";
                return $protocolo;
            }
            //tratar dados de retorno
            $doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $doc->formatOutput = false;
            $doc->preserveWhiteSpace = false;
            $doc->loadXML($retorno['valor'], LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $cStat = $this->pSimpleGetValue($doc, "cStat");
            $xMotivo = $this->pSimpleGetValue($doc, "xMotivo");
            //verifica se houve erro no código do status
            if ($cStat == '') {
                $protocolo['retorno'] = "Erro inesperado, cStat esta vazio.";
                return $protocolo;
            }
            $envelopeBodyNode = $doc->getElementsByTagNameNS('http://www.w3.org/2003/05/soap-envelope', 'Body')->item(0)->childNodes->item(0);
            //Disponibiliza o conteúdo xml do pacote de resposta (soap:Body) através do array de retorno
            $aRetorno['xmlRetorno'] = $doc->saveXML($envelopeBodyNode);
            //o retorno vai variar se for buscado o protocolo ou recibo
            //Retorno da consulta pela Chave da NF-e
            //retConsSitNFe 100 aceita 110 denegada 101 cancelada ou outro recusada
            // cStat xMotivo cUF chNFe protNFe retCancNFe
            if ($chave != '') {
                $aRetorno['bStat'] = true;
                $aRetorno['verAplic'] = $this->pSimpleGetValue($doc, 'verAplic');
                $aRetorno['cStat'] = $this->pSimpleGetValue($doc, 'cStat');
                $aRetorno['xMotivo'] = $this->pSimpleGetValue($doc, 'xMotivo');
                $aRetorno['cUF'] = $this->pSimpleGetValue($doc, 'cUF');
                $aRetorno['chNFe'] = $this->pSimpleGetValue($doc, 'chNFe');
                $infProt = $doc->getElementsByTagName('infProt')->item(0);
                $infCanc = $doc->getElementsByTagName('infCanc')->item(0);
                $procEventoNFe = $doc->getElementsByTagName('procEventoNFe');
                $aProt = array();
                if (isset($infProt)) {
                    foreach ($infProt->childNodes as $tnodes) {
                        $aProt[$tnodes->nodeName] = $tnodes->nodeValue;
                    }
                    if (!empty($aProt['dhRecbto'])) {
                        $aProt['dhRecbto'] = date("Y-m-d H:i:s", $this->pConvertTime($aProt['dhRecbto']));
                    } else {
                        $aProt['dhRecbto'] = '';
                    }
                    $aProt['xEvento'] = 'Autorização';
                }
                $aCanc = '';
                if (isset($infCanc)) {
                    foreach ($infCanc->childNodes as $tnodes) {
                        $aCanc[$tnodes->nodeName] = $tnodes->nodeValue;
                    }
                    if (!empty($aCanc['dhRecbto'])) {
                        $aCanc['dhRecbto'] = date("Y-m-d H:i:s", $this->pConvertTime($aCanc['dhRecbto']));
                    } else {
                        $aCanc['dhRecbto'] = '';
                    }
                    $aCanc['xEvento'] = 'Cancelamento';
                }
                $aEventos = '';
                if (! empty($procEventoNFe)) {
                    foreach ($procEventoNFe as $kEli => $evento) {
                        $infEvento = $evento->getElementsByTagName('infEvento');
                        foreach ($infEvento as $iE) {
                            if ($iE->getElementsByTagName('detEvento')->item(0) != "") {
                                continue;
                            }
                            foreach ($iE->childNodes as $tnodes) {
                                $aEventos[$kEli][$tnodes->nodeName] = $tnodes->nodeValue;
                            }
                            $aEventos[$kEli]['dhRegEvento'] = date("Y-m-d H:i:s", $this->pConvertTime($aEventos[$kEli]['dhRegEvento']));
                        }
                    }
                }
                $aRetorno['aProt'] = $aProt;
                $aRetorno['aCanc'] = $aCanc;
                $aRetorno['aEventos'] = $aEventos;
                //gravar o retorno na pasta temp apenas se a nota foi aprovada ou denegada
                if (in_array($aRetorno['cStat'], array('100', '101', '110', '301', '302'))) {
                    $protocolo['retorno'] = 'sucesso';
                    $protocolo['infProt'] = $aRetorno['aProt'];
                    $protocolo['infProt']['cStat'] = $aRetorno['cStat'];
                    $protocolo['aCanc'] = $aRetorno['aCanc'];
                    $protocolo['aEventos'] = $aRetorno['aEventos'];
                    /*
                    //nome do arquivo
                    $nomeArq = $chave.'-prot.xml';
                    $nome = $this->temDir.$nomeArq;
                    $nome = $doc->save($nome);
                     */
                } else {
                    $protocolo['retorno'] = sprintf("%s - %s", $aRetorno['cStat'], $aRetorno['xMotivo']);
                    return $protocolo;
                }
            }
            //Retorno da consulta pelo recibo
            //NFeRetRecepcao 104 tem retornos
            //nRec cStat xMotivo cUF cMsg xMsg protNfe* infProt chNFe dhRecbto nProt cStat xMotivo
            if ($recibo != '') {
                $countI = 0;
                $aRetorno['bStat'] = true;
                // status do serviço
                $aRetorno['cStat'] = $this->pSimpleGetValue($doc, 'cStat');
                // motivo da resposta (opcional)
                $aRetorno['xMotivo'] = $this->pSimpleGetValue($doc, 'xMotivo');
                // numero do recibo consultado
                $aRetorno['nRec'] = $this->pSimpleGetValue($doc, 'nRec');
                // tipo de ambiente
                $aRetorno['tpAmb'] = $this->pSimpleGetValue($doc, 'tpAmb');
                // versao do aplicativo que recebeu a consulta
                $aRetorno['verAplic'] = $this->pSimpleGetValue($doc, 'verAplic');
                // codigo da UF que atendeu a solicitacao
                $aRetorno['cUF'] = $this->pSimpleGetValue($doc, 'cUF');
                // codigo da mensagem da SEFAZ para o emissor (opcional)
                $aRetorno['cMsg'] = $this->pSimpleGetValue($doc, 'cMsg');
                // texto da mensagem da SEFAZ para o emissor (opcional)
                $aRetorno['xMsg'] = $this->pSimpleGetValue($doc, 'xMsg');
                if ($cStat == '104') {
                    //aqui podem ter varios retornos dependendo do numero de NFe enviadas no Lote e já processadas
                    $protNfe = $doc->getElementsByTagName('protNFe');
                    foreach ($protNfe as $d) {
                        $infProt = $d->getElementsByTagName('infProt')->item(0);
                        $protcStat = $infProt->getElementsByTagName('cStat')->item(0)->nodeValue;
                        $protxMotivo = $infProt->getElementsByTagName('xMotivo')->item(0)->nodeValue;
                        //pegar os dados do protolo para retornar
                        foreach ($infProt->childNodes as $tnode) {
                           $aProt[$tnode->nodeName] = $tnode->nodeValue;
                        }
                        $countI++;
                        //incluido increment para controlador de indice do array
                        //salvar o protocolo somente se a nota estiver approvada ou denegada
                        if (in_array($protcStat, array('100', '110', '301', '302'))) {
                            $protocolo['retorno'] = 'sucesso';
                            $protocolo['infProt'] = $aProt;
                            $protocolo['infProt']['cStat'] = $protcStat;
                            /*
                            $nomeprot = $this->temDir.$infProt->getElementsByTagName('chNFe')->item(0)->nodeValue.'-prot.xml';//id da nfe
                            //salvar o protocolo em arquivo
                            $novoprot = new DomDocumentNFePHP();
                            $pNFe = $novoprot->createElement("protNFe");
                            $pNFe->setAttribute("versao", "3.10");
                            // Importa o node e todo o seu conteudo
                            $node = $novoprot->importNode($infProt, true);
                            // acrescenta ao node principal
                            $pNFe->appendChild($node);
                            $novoprot->appendChild($pNFe);
                            $xml = $novoprot->saveXML();
                            $xml = str_replace(
                                '<?xml version="1.0" encoding="UTF-8  standalone="no"?>',
                                '<?xml version="1.0" encoding="UTF-8"?>',
                                $xml
                            );
                            $xml = str_replace(array("default:",":default","\r","\n","\s"), "", $xml);
                            $xml = str_replace("  ", " ", $xml);
                            $xml = str_replace("  ", " ", $xml);
                            $xml = str_replace("  ", " ", $xml);
                            $xml = str_replace("  ", " ", $xml);
                            $xml = str_replace("  ", " ", $xml);
                            $xml = str_replace("> <", "><", $xml);
                            file_put_contents($nomeprot, $xml);
                             */
                        } else { 
                            $protocolo['retorno'] = sprintf("%s - %s", $protcStat, $protxMotivo);
                            return $protocolo;
                        }//fim protcSat
                    } //fim foreach
                } else { 
                    $protocolo['retorno'] = sprintf("%s - %s", $cStat, $xMotivo);
                    return $protocolo;
                }//fim cStat
                //converter o horário do recebimento retornado pela SEFAZ em formato padrão
                if (isset($aProt)) {
                    foreach ($aProt as &$p) {
                        $p['dhRecbto'] = !empty($p['dhRecbto']) ?
                            date(
                                "Y-m-d H:i:s",
                                $this->pConvertTime($p['dhRecbto'])
                            ) : '';
                    }
                } else {
                    $aProt = array();
                }
                
                $aRetorno['aProt'] = $aProt; //passa o valor de $aProt para o array de retorno
                /*
                $nomeArq = $recibo.'-recprot.xml';
                $nome = $this->temDir.$nomeArq;
                $nome = $doc->save($nome);
                 */
            } //fim recibo
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        } //fim catch
        return $protocolo;
    } //fim getProtocol
    
    /**
     * addProt
     * Adiciona a tag do protocolo a NFe, preparando a mesma para impressão e envio ao destinatário.
     * Também pode ser usada para substituir o protocolo de autorização
     * pelo protocolo de cancelamento, nesse caso apenas para a gestão interna
     * na empresa, esse arquivo com o cancelamento não deve ser enviado ao cliente.
     *
     * @name addProt
     * @param string $nfefile path completo para o arquivo contendo a NFe
     * @param string $protfile path completo para o arquivo contendo o protocolo, cancelamento ou evento de cancelamento
     * @return string Retorna a NFe com o protocolo
     */
    public function addProt($nfefile = '', $protocolo, $protNFeVersao, $acao)
    {
        $xmlProtocolado = array();
        try {
            if ($nfefile == '' /*|| $protfile == ''*/) {
                $msg = 'Para adicionar o protocolo, ambos os caminhos devem ser passados.'
                       .' Para a nota e para o protocolo!';
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            if (!is_file($nfefile) /*|| !is_file($protfile)*/) {
                $msg = 'Algum dos arquivos não foi localizado no caminho indicado ! '.$nfefile/*. ' ou ' .$protfile*/;
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            //carrega o arquivo na variável
            $docnfe = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $docnfe->formatOutput = false;
            $docnfe->preserveWhiteSpace = false;
            $xmlnfe = file_get_contents($nfefile);
            if (! $docnfe->loadXML($xmlnfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)) {
                $msg = 'O arquivo indicado como NFe não é um XML! '.$nfefile;
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            $nfe = $docnfe->getElementsByTagName("NFe")->item(0);
            if (!isset($nfe)) {
                $msg = 'O arquivo indicado como NFe não é um xml de NFe! '.$nfefile;
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            $infNFe = $docnfe->getElementsByTagName("infNFe")->item(0);
            $versao = trim($infNFe->getAttribute("versao"));
            $chaveId = trim($infNFe->getAttribute("Id"));
            $chave = preg_replace('/[^0-9]/', '', $chaveId);
            $DigestValue = !empty($docnfe->getElementsByTagName('DigestValue')->item(0)->nodeValue) ? $docnfe->getElementsByTagName('DigestValue')->item(0)->nodeValue : '';
            if ($DigestValue == '') {
                $msg = 'O XML da NFe não está assinado! '.$nfefile;
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            /*
            //carrega o protocolo e seus dados
            //protocolo do lote enviado
            $prot = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $prot->formatOutput = false;
            $prot->preserveWhiteSpace = false;
            $xmlprot = file_get_contents($protfile);
            if (! $prot->loadXML($xmlprot, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)) {
                $msg = 'O arquivo indicado para ser protocolado na NFe é um XML! '.$protfile;
                throw new nfephpException($msg);
            }
             */
            //protocolo de autorização
            //$protNFe = $prot->getElementsByTagName("protNFe")->item(0);
            if ($acao == 'protNFe') {
                $protver     = $protNFeVersao;
                $tpAmb       = $protocolo['tpAmb'];
                $verAplic    = $protocolo['verAplic'];
                $chNFe       = $protocolo['chNFe'];
                $dhRecbto    = str_replace(' ', 'T', $protocolo['dhRecbto']);
                $nProt       = $protocolo['nProt'];
                $digVal      = $protocolo['digVal'];
                $cStat       = $protocolo['cStat'];
                $xMotivo     = $protocolo['xMotivo'];
                if ($DigestValue != $digVal) {
                    $msg = 'Inconsistência! O DigestValue da NFe não combina com o do digVal do protocolo indicado!';
                    $xmlProtocolado['retorno'] = $msg;
                    return $xmlProtocolado;
                }
            } else if($acao == 'retCancNFe') {
                //cancelamento antigo
                //$retCancNFe = $prot->getElementsByTagName("retCancNFe")->item(0);
                /*
                $protver     = trim($retCancNFe->getAttribute("versao"));
                $tpAmb       = $retCancNFe->getElementsByTagName("tpAmb")->item(0)->nodeValue;
                $verAplic    = $retCancNFe->getElementsByTagName("verAplic")->item(0)->nodeValue;
                $chNFe       = $retCancNFe->getElementsByTagName("chNFe")->item(0)->nodeValue;
                $dhRecbto    = $retCancNFe->getElementsByTagName("dhRecbto")->item(0)->nodeValue;
                $nProt       = $retCancNFe->getElementsByTagName("nProt")->item(0)->nodeValue;
                $cStat       = $retCancNFe->getElementsByTagName("cStat")->item(0)->nodeValue;
                $xMotivo     = $retCancNFe->getElementsByTagName("xMotivo")->item(0)->nodeValue;
                $digVal      = $DigestValue;
                 */
            } else if($acao == 'retEvento') {
                //cancelamento por evento NOVO
                //$retEvento = $prot->getElementsByTagName("retEvento")->item(0);
                $protver     = $protNFeVersao;
                $tpAmb       = $protocolo['tpAmb'];
                $verAplic    = $protocolo['verAplic'];
                $chNFe       = $protocolo['chNFe'];
                $dhRecbto    = str_replace(' ', 'T', $protocolo['dhRecbto']).Mage::getStoreConfig('nfe/nfe_opcoes/horario');
                $nProt       = $protocolo['nProt'];
                $cStat       = $protocolo['cStat'];
                $tpEvento    = '110111';
                $xMotivo     = $protocolo['xMotivo'];
                $digVal      = $DigestValue;
                /*
                if ($tpEvento != '110111') {
                    $msg = $tpEvento.' - '.$xMotivo;
                    $xmlProtocolado['retorno'] = $msg;
                    return $xmlProtocolado;
                }
                 */
            }
            /*
            if (!isset($protNFe) && !isset($retCancNFe) && !isset($retEvento)) {
                $msg = 'O arquivo indicado para ser protocolado a NFe não é um protocolo nem de cancelamento! '
                       .$protfile;
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
             */
            if ($chNFe != $chave) {
                $msg = 'O protocolo indicado pertence a outra NFe ... os numertos das chaves não combinam !';
                $xmlProtocolado['retorno'] = $msg;
                return $xmlProtocolado;
            }
            //cria a NFe processada com a tag do protocolo
            $procnfe = new DOMDocument('1.0', 'utf-8');
            $procnfe->formatOutput = false;
            $procnfe->preserveWhiteSpace = false;
            //cria a tag nfeProc
            $nfeProc = $procnfe->createElement('nfeProc');
            $procnfe->appendChild($nfeProc);
            //estabele o atributo de versão
            $nfeProc_att1 = $nfeProc->appendChild($procnfe->createAttribute('versao'));
            $nfeProc_att1->appendChild($procnfe->createTextNode($protver));
            //estabelece o atributo xmlns
            $nfeProc_att2 = $nfeProc->appendChild($procnfe->createAttribute('xmlns'));
            $nfeProc_att2->appendChild($procnfe->createTextNode($this->URLnfe));
            //inclui a tag NFe
            $node = $procnfe->importNode($nfe, true);
            $nfeProc->appendChild($node);
            //cria tag protNFe
            $protNFe = $procnfe->createElement('protNFe');
            $nfeProc->appendChild($protNFe);
            //estabele o atributo de versão
            $protNFe_att1 = $protNFe->appendChild($procnfe->createAttribute('versao'));
            $protNFe_att1->appendChild($procnfe->createTextNode($versao));
            //cria tag infProt
            $infProt = $procnfe->createElement('infProt');
            $infProt_att1 = $infProt->appendChild($procnfe->createAttribute('Id'));
            $infProt_att1->appendChild($procnfe->createTextNode('ID'.$nProt));
            $protNFe->appendChild($infProt);
            $infProt->appendChild($procnfe->createElement('tpAmb', $tpAmb));
            $infProt->appendChild($procnfe->createElement('verAplic', $verAplic));
            $infProt->appendChild($procnfe->createElement('chNFe', $chNFe));
            $infProt->appendChild($procnfe->createElement('dhRecbto', $dhRecbto));
            $infProt->appendChild($procnfe->createElement('nProt', $nProt));
            $infProt->appendChild($procnfe->createElement('digVal', $digVal));
            $infProt->appendChild($procnfe->createElement('cStat', $cStat));
            $infProt->appendChild($procnfe->createElement('xMotivo', $xMotivo));
            //salva o xml como string em uma variável
            $procXML = $procnfe->saveXML();
            //remove as informações indesejadas
            $procXML = str_replace(
                array('default:',':default',"\n","\r","\s"),
                '',
                $procXML
            );
            $procXML = str_replace(
                'NFe xmlns="http://www.portalfiscal.inf.br/nfe" xmlns="http://www.w3.org/2000/09/xmldsig#"',
                'NFe xmlns="http://www.portalfiscal.inf.br/nfe"',
                $procXML
            );
            $xmlProtocolado['retorno'] = 'sucesso';
            $xmlProtocolado['xml'] = $procXML;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return $xmlProtocolado;
    } //fim addProt
    
    /**
     * M�todo pertence a classe ToolsNFePHP.class.php do projeto NFE-PHP
     * signXML
     * Assinador TOTALMENTE baseado em PHP para arquivos XML
     * este assinador somente utiliza comandos nativos do PHP para assinar
     * os arquivos XML
     *
     * @name signXML
     * @param  mixed $docxml Path para o arquivo xml ou String contendo o arquivo XML a ser assinado
     * @param  string $tagid TAG do XML que devera ser assinada
     * @return mixed false se houve erro ou string com o XML assinado
     */
    public function assinarXml($docxml, $tagid = '', $nfe, $operacao) {
        $msg = 'sucesso';
        try {
            $certificado = $this->pLoadCerts();
            if($certificado['retorno'] != 'sucesso') {
                return $certificado['retorno'];
            }
            if ($tagid == '') {
                $msg = "Uma tag deve ser indicada para que seja assinada!!";
                return $msg;
            }
            if ($docxml == '') {
                $msg = "Um xml deve ser passado para que seja assinado!!";
                return $msg;
            }
            if (! is_file($certificado['priKey'])) {
                $msg = "Arquivo da chave privada parece invalido, verifique!!";
                return $msg;
            }
            if (is_file($docxml)) {
                $xml = file_get_contents($docxml);
            } else {
                $xml = $docxml;
            }
            //obter a chave privada para a assinatura
            //modificado para permitir a leitura de arquivos maiores
            //que o normal que é cerca de 2kBytes.
            if (! $filep = fopen($certificado['priKey'], "r")) {
                $msg = "Erro ao ler arquivo da chave privada!!";
                return $msg;
            }
            $priv_key = '';
            while (! feof($filep)) {
                $priv_key .= fread($filep, 8192);
            }
            fclose($filep);
            $pkeyid = openssl_get_privatekey($priv_key);
            //limpeza do xml com a retirada dos CR, LF e TAB
            $order = array("\r\n", "\n", "\r", "\t");
            $replace = '';
            $xml = str_replace($order, $replace, $xml);
            // Habilita a manipulaçao de erros da libxml
            libxml_use_internal_errors(true);
            //limpa erros anteriores que possam estar em memória
            libxml_clear_errors();
            //carrega o documento DOM
            $xmldoc = new DOMDocument('1.0', 'utf-8');
            $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
            $xmldoc->formatOutput = false;
            //é muito importante deixar ativadas as opçoes para limpar os espacos em branco
            //e as tags vazias
            if ($xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)) {
                $root = $xmldoc->documentElement;
            } else {
                $msg = "Erro ao carregar XML, provavel erro na passagem do parâmetro docxml ou no próprio xml!!";
                $errors = libxml_get_errors();
                if (!empty($errors)) {
                    $countI = 1;
                    foreach ($errors as $error) {
                        $msg .= "\n  [$countI]-".trim($error->message);
                        $countI++;
                    }
                    libxml_clear_errors();
                }
                return $msg;
            }
            //extrair a tag com os dados a serem assinados
            $node = $xmldoc->getElementsByTagName($tagid)->item(0);
            if (!isset($node)) {
                $msg = "A tag < $tagid > não existe no XML!!";
                return $msg;
            }
            //extrai o atributo ID com o numero da NFe de 44 digitos
            $Id = $node->getAttribute("Id");
            //extrai e canoniza os dados da tag para uma string
            $dados = $node->C14N(false, false, null, null);
            //calcular o hash dos dados
            $hashValue = hash('sha1', $dados, true);
            //converte o valor para base64 para serem colocados no xml
            $digValue = base64_encode($hashValue);
            //monta a tag da assinatura digital
            $Signature = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
            $root->appendChild($Signature);
            $SignedInfo = $xmldoc->createElement('SignedInfo');
            $Signature->appendChild($SignedInfo);
            //estabelece o método de canonização
            $newNode = $xmldoc->createElement('CanonicalizationMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            //estabelece o método de assinatura
            $newNode = $xmldoc->createElement('SignatureMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            //indica a referencia da assinatura
            $Reference = $xmldoc->createElement('Reference');
            $SignedInfo->appendChild($Reference);
            $Reference->setAttribute('URI', '#'.$Id);
            //estabelece as tranformações
            $Transforms = $xmldoc->createElement('Transforms');
            $Reference->appendChild($Transforms);
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            //estabelece o método de calculo do hash
            $newNode = $xmldoc->createElement('DigestMethod');
            $Reference->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            //carrega o valor do hash
            $newNode = $xmldoc->createElement('DigestValue', $digValue);
            $Reference->appendChild($newNode);
            //extrai e canoniza os dados a serem assinados para uma string
            $dados = $SignedInfo->C14N(false, false, null, null);
            //inicializa a variavel que irá receber a assinatura
            $signature = '';
            //executa a assinatura digital usando o resource da chave privada
            openssl_sign($dados, $signature, $pkeyid);
            //codifica assinatura para o padrão base64
            $signatureValue = base64_encode($signature);
            //insere o valor da assinatura digtal
            $newNode = $xmldoc->createElement('SignatureValue', $signatureValue);
            $Signature->appendChild($newNode);
            //insere a chave publica usada para conferencia da assinatura digital
            $KeyInfo = $xmldoc->createElement('KeyInfo');
            $Signature->appendChild($KeyInfo);
            //X509Data
            $X509Data = $xmldoc->createElement('X509Data');
            $KeyInfo->appendChild($X509Data);
            //carrega o certificado sem as tags de inicio e fim
            $cert = $this->pCleanCerts($certificado['pubKey']);
            //X509Certificate
            $newNode = $xmldoc->createElement('X509Certificate', $cert);
            $X509Data->appendChild($newNode);
            //grava em uma string o objeto DOM
            $xml = $xmldoc->saveXML();
            //libera a chave privada da memoria
            openssl_free_key($pkeyid);
            if($operacao == 'inutilizar' || $operacao == 'cancelar' || $operacao == 'corrigir') {
                return $xml;
            }
            // Salva o XML
            if($nfe->getTpNf() == '0') {
                $tipo = 'entrada';
            } else {
                $tipo = 'saida';
            }
            $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS;
            $this->salvarXml($xml, $caminho, $nfe->getIdTag());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return 'sucesso';
    } //fim signXML
    
    /**
     * M�todo pertence a classe ToolsNFePHP.class.php do projeto NFE-PHP
     * validXML
     * Verifica o xml com base no xsd
     * Esta função pode validar qualquer arquivo xml do sistema de NFe
     * Há um bug no libxml2 para versões anteriores a 2.7.3
     * que causa um falso erro na validação da NFe devido ao
     * uso de uma marcação no arquivo tiposBasico_v1.02.xsd
     * onde se le {0 , } substituir por *
     * A validação não deve ser feita após a inclusão do protocolo !!!
     * Caso seja passado uma NFe ainda não assinada a falta da assinatura será desconsiderada.
     * @name validXML
     * @author Roberto L. Machado <linux.rlm at gmail dot com>
     * @param    string  $xml  string contendo o arquivo xml a ser validado ou seu path
     * @param    string  $xsdfile Path completo para o arquivo xsd
     * @param    array   $aError Variável passada como referencia irá conter as mensagens de erro se houverem
     * @return   boolean
     */
    public function validarXml($xml = '', $xsdFile = '', &$aError = array()) {
        try {
            $flagOK = true;
            // Habilita a manipulaçao de erros da libxml
            libxml_use_internal_errors(true);
            //limpar erros anteriores que possam estar em memória
            libxml_clear_errors();
            //verifica se foi passado o xml
            if (strlen($xml)==0) {
                $msg = 'Você deve passar o conteudo do xml assinado como parâmetro '
                       .'ou o caminho completo até o arquivo.';
                return $msg;
            }
            // instancia novo objeto DOM
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false; //elimina espaços em branco
            $dom->formatOutput = false;
            // carrega o xml tanto pelo string contento o xml como por um path
            if (is_file($xml)) {
                $dom->load($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            } else {
                $dom->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            }
            // pega a assinatura
            $Signature = $dom->getElementsByTagName('Signature')->item(0);
            //recupera os erros da libxml
            $errors = libxml_get_errors();
            if (!empty($errors)) {
                //o dado passado como $docXml não é um xml
                $msg = 'O dado informado não é um XML ou não foi encontrado. '
                        . 'Você deve passar o conteudo de um arquivo xml assinado como parâmetro.';
                return $msg;
            }
            if ($xsdFile=='') {
                if (is_file($xml)) {
                    $contents = file_get_contents($xml);
                } else {
                    $contents = $xml;
                }
                $sxml = simplexml_load_string($contents);
                $nome = $sxml->getName();
                $sxml = null;
                //determinar qual o arquivo de schema válido
                //buscar o nome do scheme
                switch ($nome) {
                    case 'evento':
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $tpEvento = $node->getElementsByTagName('tpEvento')->item(0)->nodeValue;
                        switch ($tpEvento) {
                            case '110110':
                                //carta de correção
                                $xsdFile = "CCe_v$ver.xsd";
                                break;
                            default:
                                $xsdFile = "";
                                break;
                        }
                        break;
                    case 'envEvento':
                        //obtem o node com a versão
                        $node = $dom->getElementsByTagName('evento')->item(0);
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $tpEvento = $node->getElementsByTagName('tpEvento')->item(0)->nodeValue;
                        switch ($tpEvento) {
                            case '110110':
                                //carta de correção
                                $xsdFile = "envCCe_v$ver.xsd";
                                break;
                            default:
                                $xsdFile = "envEvento_v$ver.xsd";
                                break;
                        }
                        break;
                    case 'NFe':
                        //obtem o node com a versão
                        $node = $dom->getElementsByTagName('infNFe')->item(0);
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = "nfe_v$ver.xsd";
                        break;
                    case 'nfeProc':
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = "procNFe_v$ver.xsd";
                        break;
                    default:
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = $nome."_v".$ver.".xsd";
                        break;
                }
                $schemeVersion = 'PL_008h2';
                $diretorio = Mage::getBaseDir(). DS . 'nfe' . DS . 'schemes' . DS . $schemeVersion . DS;
                $aFile = $diretorio.$xsdFile;
                if (empty($aFile) || empty($aFile[0])) {
                    $msg = "Erro na localização do schema xsd.\n";
                    return $msg;
                } else {
                    $xsdFile = $aFile;
                }
            }
            //limpa erros anteriores
            libxml_clear_errors();
            // valida o xml com o xsd
            if (!$dom->schemaValidate($xsdFile)) {
                /**
                 * Se não foi possível validar, você pode capturar
                 * todos os erros em um array
                 * Cada elemento do array $arrayErrors
                 * será um objeto do tipo LibXmlError
                 */
                // carrega os erros em um array
                $aIntErrors = libxml_get_errors();
                $flagOK = false;
                if (!isset($Signature)) {
                    // remove o erro de falta de assinatura
                    foreach ($aIntErrors as $k => $intError) {
                        if (strpos($intError->message, '( {http://www.w3.org/2000/09/xmldsig#}Signature )') !== false) {
                            // remove o erro da assinatura, se tiver outro meio melhor (atravez dos erros de codigo) e alguem souber como tratar por eles, por favor contribua...
                            unset($aIntErrors[$k]);
                        }
                    }
                    reset($aIntErrors);
                    $flagOK = true;
                }//fim teste Signature
                $msg = '';
                foreach ($aIntErrors as $intError) {
                    $flagOK = false;
                    $en = array("{http://www.portalfiscal.inf.br/nfe}"
                                ,"[facet 'pattern']"
                                ,"The value"
                                ,"is not accepted by the pattern"
                                ,"has a length of"
                                ,"[facet 'minLength']"
                                ,"this underruns the allowed minimum length of"
                                ,"[facet 'maxLength']"
                                ,"this exceeds the allowed maximum length of"
                                ,"Element"
                                ,"attribute"
                                ,"is not a valid value of the local atomic type"
                                ,"is not a valid value of the atomic type"
                                ,"Missing child element(s). Expected is"
                                ,"The document has no document element"
                                ,"[facet 'enumeration']"
                                ,"one of"
                                ,"failed to load external entity"
                                ,"Failed to locate the main schema resource at"
                                ,"This element is not expected. Expected is"
                                ,"is not an element of the set");

                    $pt = array(""
                                ,"[Erro 'Layout']"
                                ,"O valor"
                                ,"não é aceito para o padrão."
                                ,"tem o tamanho"
                                ,"[Erro 'Tam. Min']"
                                ,"deve ter o tamanho mínimo de"
                                ,"[Erro 'Tam. Max']"
                                ,"Tamanho máximo permitido"
                                ,"Elemento"
                                ,"Atributo"
                                ,"não é um valor válido"
                                ,"não é um valor válido"
                                ,"Elemento filho faltando. Era esperado"
                                ,"Falta uma tag no documento"
                                ,"[Erro 'Conteúdo']"
                                ,"um de"
                                ,"falha ao carregar entidade externa"
                                ,"Falha ao tentar localizar o schema principal em"
                                ,"Este elemento não é esperado. Esperado é"
                                ,"não é um dos seguintes possiveis");

                    switch ($intError->level) {
                        case LIBXML_ERR_WARNING:
                            $aError[] = " Atençao $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                        case LIBXML_ERR_ERROR:
                            $aError[] = " Erro $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                        case LIBXML_ERR_FATAL:
                            $aError[] = " Erro Fatal $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                    }
                    $msg .= str_replace($en, $pt, $intError->message);
                }
            } else {
                $flagOK = true;
            }
            if (!$flagOK) {
                return $msg;
            }
        } catch (nfephpException $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return 'sucesso';
    } //fim validXML
    
    /**
     * Solicita inutilizaçaao de uma série de números de NF. O processo de inutilização
     * será gravado na "pasta Inutilizadas".
     * 
     * ATENÇÃO: este webservice *não* é oferecido pelas SVC (Sefaz Virtual de Contingência)
     * conforme NT 2013.007 versão "1.02" de Dezembro/2013.
     *
     * @name inutNF
     * @param string  $nAno     ano com 2 digitos
     * @param string  $nSerie   serie da NF 1 até 3 digitos
     * @param integer $nIni     numero inicial 1 até 9 digitos zero a esq
     * @param integer $nFin     numero Final 1 até 9 digitos zero a esq
     * @param string  $xJust    justificativa 15 até 255 digitos
     * @param string  $tpAmb    Tipo de ambiente 1-produção ou 2 homologação
     * @param array   $aRetorno Array com os dados de Retorno
     * @return mixed false ou string com o xml do processo de inutilização
     */
    public function inutNF($nAno = '', $nSerie = '1', $nIni = '', $nFin = '', $xJust = '', $tpAmb = '', &$aRetorno = array(), $siglaUf, $ufEmitente, $modeloNf, $cnpjEmitente)
    {
        $xmlInutilizado = array();
        //retorno da função
        $aRetorno = array(
            'bStat'=>false,
            'tpAmb'=>'',
            'verAplic'=>'',
            'cStat'=>'',
            'xMotivo'=>'',
            'cUF'=>'',
            'ano'=>'',
            'CNPJ'=>'',
            'mod'=>'',
            'serie'=>'',
            'nNFIni'=>'',
            'nNFFin'=>'',
            'dhRecbto'=>'',
            'nProt'=>'');
        //valida dos dados de entrada
        if ($nAno == '' || $nIni == '' || $nFin == '' || $xJust == '') {
            $msg = "Não foi passado algum dos parametos necessários ANO=$nAno inicio=$nIni "
                   ."fim=$nFin justificativa=$xJust.\n";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //valida justificativa
        if (strlen($xJust) < 15) {
            $msg = "A justificativa deve ter pelo menos 15 digitos!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        if (strlen($xJust) > 255) {
            $msg = "A justificativa deve ter no máximo 255 digitos!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //remove acentos e outros caracteres da justificativa
        $xJust = $this->pCleanString($xJust);
        // valida o campo ano
        if (strlen($nAno) > 2) {
            $msg = "O ano tem mais de 2 digitos. Corrija e refaça o processo!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        } else {
            if (strlen($nAno) < 2) {
                $msg = "O ano tem menos de 2 digitos. Corrija e refaça o processo!!";
                $xmlInutilizado['retorno'] = $msg;
                return $xmlInutilizado;
            }
        }
        //valida o campo serie
        if (strlen($nSerie) == 0 || strlen($nSerie) > 3) {
            $msg = "O campo serie está errado: $nSerie. Corrija e refaça o processo!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //valida o campo numero inicial
        if (strlen($nIni) < 1 || strlen($nIni) > 9) {
            $msg = "O campo numero inicial está errado: $nIni. Corrija e refaça o processo!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //valida o campo numero final
        if (strlen($nFin) < 1 || strlen($nFin) > 9) {
            $msg = "O campo numero final está errado: $nFin. Corrija e refaça o processo!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //valida contingencias, nao podem estar habilitadas pois este serviço não se aplica para SVC
        if ($this->enableSVCAN || $this->enableSVCRS) {
            $msg = "Inutilizacao nao pode ser usada em contingencia SVC!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        /*
        //valida tipo de ambiente
        if ($tpAmb == '') {
            $tpAmb = $this->tpAmb;
        }
        if ($tpAmb == $this->tpAmb) {
            $aURL = $this->aURL;
        } else {
            $aURL = $this->pLoadSEFAZ($tpAmb, $this->siglaUF);
        }
         */
        if (!$aURL = $this->pLoadSEFAZ($tpAmb, $siglaUf)) {
            $msg = "Erro no carregamento das informacoes da SEFAZ";
            $protocolo['retorno'] = $msg;
            return $protocolo;
        }
        //identificação do serviço
        $servico = 'NfeInutilizacao';
        //recuperação da versão
        $versao = $aURL[$servico]['version'];
        //recuperação da url do serviço
        $urlservico = $aURL[$servico]['URL'];
        //recuperação do método
        $metodo = $aURL[$servico]['method'];
        //montagem do namespace do serviço
        $namespace = $this->URLPortal.'/wsdl/'.$servico.'2';
        //Identificador da TAG a ser assinada formada com Código da UF +
        //Ano (2 posições) + CNPJ + modelo + série + nro inicial e nro final
        //precedida do literal “ID”
        // 43 posições
        //     2      4       6       20      22    25       34      43
        //     2      2       2       14       2     3        9       9
        $id = 'ID'
                . $ufEmitente
                . $nAno
                . $cnpjEmitente
                . $modeloNf
                . str_pad($nSerie, 3, '0', STR_PAD_LEFT)
                . str_pad($nIni, 9, '0', STR_PAD_LEFT)
                . str_pad($nFin, 9, '0', STR_PAD_LEFT);
        //montagem do cabeçalho da comunicação SOAP
        $cabec = '<nfeCabecMsg xmlns="'.$namespace.'"><cUF>'
                . $ufEmitente.'</cUF><versaoDados>'.$versao.'</versaoDados></nfeCabecMsg>';
        //montagem do corpo da mensagem
        $dXML = '<inutNFe xmlns="'.$this->URLnfe.'" versao="'.$versao.'">';
        $dXML .= '<infInut Id="'.$id.'">';
        $dXML .= '<tpAmb>'.$tpAmb.'</tpAmb>';
        $dXML .= '<xServ>INUTILIZAR</xServ>';
        $dXML .= '<cUF>'.$ufEmitente.'</cUF>';
        $dXML .= '<ano>'.$nAno.'</ano>';
        $dXML .= '<CNPJ>'.$cnpjEmitente.'</CNPJ>';
        $dXML .= '<mod>55</mod>';
        $dXML .= '<serie>'.$nSerie.'</serie>';
        $dXML .= '<nNFIni>'.$nIni.'</nNFIni>';
        $dXML .= '<nNFFin>'.$nFin.'</nNFFin>';
        $dXML .= '<xJust>'.$xJust.'</xJust>';
        $dXML .= '</infInut>';
        $dXML .= '</inutNFe>';
        //assina a lsolicitação de inutilização
        //ADICIONAR NESTE PONTO CHAMADA AO M�TODO ASSINARXML() DE NFERN QUE RETORNA UM SUCESSO E J� PERSISTE O XML QUE DEPOIS PRECISA SER RECUPERADO - FAZER ALTERA��O PARA PASSAGEM DE PARAMETRO QUE INFORMA INUTILIZA��O PARA ENT�O RETORNAR A STRING DO XML VIA RETURN E SAIR DO M�TODO SEM SALVAR O XML NAQUELE PONTO
        $dXML = $this->assinarXML($dXML, 'infInut', '', 'inutilizar');
        $dados = '<nfeDadosMsg xmlns="'.$namespace.'">'.$dXML.'</nfeDadosMsg>';
        //remove as tags xml que porventura tenham sido inclusas
        $dados = $this->pClearXml($dados, true);
        /*
        //grava a solicitação de inutilização
        if (!file_put_contents($this->temDir.$id.'-pedInut.xml', $dXML)) {
            $msg = "Falha na gravacao do pedido de inutilização!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
         * 
         */
        //envia a solicitação via SOAP
        $retorno = $this->pSendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb);
        //verifica o retorno
        if (!$retorno) {
            $msg = "Nao houve retorno Soap verifique o debug!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //tratar dados de retorno
        $doc = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
        $doc->formatOutput = false;
        $doc->preserveWhiteSpace = false;
        $doc->loadXML($retorno['valor'], LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $cStat = !empty($doc->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                $doc->getElementsByTagName('cStat')->item(0)->nodeValue : '';
        $xMotivo = !empty($doc->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                $doc->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
        // tipo de ambiente
        $aRetorno['tpAmb'] = $doc->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        // verssão do aplicativo
        $aRetorno['verAplic'] = $doc->getElementsByTagName('verAplic')->item(0)->nodeValue;
        // status do serviço
        $aRetorno['cStat'] = $cStat;
        // motivo da resposta (opcional)
        $aRetorno['xMotivo'] = $xMotivo;
        // Código da UF que atendeu a solicitação
        $aRetorno['cUF'] = $doc->getElementsByTagName('cUF')->item(0)->nodeValue;
        // Ano de inutilização da numeração
        $aRetorno['ano'] = $doc->getElementsByTagName('ano')->item(0)->nodeValue;
        // CNPJ do emitente
        $aRetorno['CNPJ'] = $doc->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        // Modelo da NF-e
        $aRetorno['mod'] = $doc->getElementsByTagName('mod')->item(0)->nodeValue;
        // Série da NF-e
        $aRetorno['serie'] = $doc->getElementsByTagName('serie')->item(0)->nodeValue;
        // Número da NF-e inicial a ser inutilizada
        $aRetorno['nNFIni'] = $doc->getElementsByTagName('nNFIni')->item(0)->nodeValue;
        // Número da NF-e final a ser inutilizada
        $aRetorno['nNFFin'] = $doc->getElementsByTagName('nNFFin')->item(0)->nodeValue;
        // data e hora do retorno a operação (opcional)
        $aRetorno['dhRecbto'] = !empty($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue) ?
                                 date("d/m/Y H:i:s", $this->pConvertTime($doc->getElementsByTagName('dhRecbto')->item(0)->nodeValue)) : '';
        // Número do Protocolo de Inutilização
        $aRetorno['nProt'] = $doc->getElementsByTagName('nProt')->item(0)->nodeValue;
        if ($cStat == '') {
            //houve erro
            $msg = "Nao houve retorno Soap verifique o debug!!";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        //verificar o status da solicitação
        if ($cStat != '102') {
            //houve erro
            $msg = "Rejeição : $cStat - $xMotivo";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
        $aRetorno['bStat'] = true;
        //gravar o retorno na pasta temp
        /*
        $nome = $this->temDir.$id.'-retInut.xml';
        $nome = $doc->save($nome);
         */
        $retInutNFe = $doc->getElementsByTagName("retInutNFe")->item(0);
        //preparar o processo de inutilização
        $inut = new DOMDocument('1.0', 'utf-8');
        $inut->formatOutput = false;
        $inut->preserveWhiteSpace = false;
        $inut->loadXML($dXML, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $inutNFe = $inut->getElementsByTagName("inutNFe")->item(0);
        //Processo completo solicitação + protocolo
        $procInut = new DOMDocument('1.0', 'utf-8');
        $procInut->formatOutput = false;
        $procInut->preserveWhiteSpace = false;
        //cria a tag procInutNFe
        $procInutNFe = $procInut->createElement('procInutNFe');
        $procInut->appendChild($procInutNFe);
        //estabele o atributo de versão
        $inutProc_att1 = $procInutNFe->appendChild($procInut->createAttribute('versao'));
        $inutProc_att1->appendChild($procInut->createTextNode($versao));
        //estabelece o atributo xmlns
        $inutProc_att2 = $procInutNFe->appendChild($procInut->createAttribute('xmlns'));
        $inutProc_att2->appendChild($procInut->createTextNode($this->URLPortal));
        //carrega o node cancNFe
        $node1 = $procInut->importNode($inutNFe, true);
        $procInutNFe->appendChild($node1);
        //carrega o node retEvento
        $node2 = $procInut->importNode($retInutNFe, true);
        $procInutNFe->appendChild($node2);
        //salva o xml como string em uma variável
        $procXML = $procInut->saveXML();
        //remove as informações indesejadas
        $procXML  = $this->pClearXml($procXML, false);
        /*
        //salva o arquivo xml
        if (! file_put_contents($this->inuDir."$id-procInut.xml", $procXML)) {
            $msg = "Falha na gravacao da procInut!!\n";
            $xmlInutilizado['retorno'] = $msg;
            return $xmlInutilizado;
        }
         */
        $xmlInutilizado['retorno'] = 'sucesso';
        $xmlInutilizado['xml'] = $procXML;
        return $xmlInutilizado;
    } //fim inutNFe
    
    /**
     * envCCe
     * Envia carta de correção da Nota Fiscal para a SEFAZ.
     * 
     * ATENÇÃO! Serviço indisponível para SVC-XX.
     *
     * @name envCCe
     * @param   string $chNFe Chave da NFe
     * @param   string $xCorrecao Descrição da Correção entre 15 e 1000 caracteres
     * @param   string $nSeqEvento numero sequencial da correção d 1 até 20
     *                             isso deve ser mantido na base de dados e
     *                             as correções consolidadas, isto é a cada nova correção
     *                             devem ser inclusas as anteriores no texto.
     *                             O Web Service não permite a duplicidade de numeração
     *                             e nem controla a ordem crescente
     * @param   integer $tpAmb Tipo de ambiente
     * @param   array    $aResp Array com os dados do protocolo
     * @return mixed false ou xml com a CCe
     */
    public function envCCe($chNFe = '', $xCorrecao = '', $nSeqEvento = '1', $tpAmb = '', &$aResp = array(), $siglaUf, $ufEmitente, $cnpjEmitente)
    {
        $xmlCorrigido = array();
        $aResp = array(
            'cStat' => false,
            'versao' => null,
            'idLote' => null,
            'tpAmb' => null,
            'verAplic' => null,
            'cOrgao' => null,
            'cStat' => null,
            'xMotivo' => null,
            'retEvento' => array(
                'versao' => null,
                'xMotivo' => null,
                'infEvento' => array(
                    'id' => null,
                    'tpAmb' => null,
                    'verAplic' => null,
                    'cOrgao' => null,
                    'cStat' => null,
                    'xMotivo' => null,
                    'chNFe' => null,
                    'tpEvento' => null,
                    'xEvento' => null,
                    'nSeqEvento' => null,
                    'CNPJDest' => null,
                    'CPFDest' => null,
                    'emailDest' => null,
                    'dhRegEvento' => null,
                    'nProt' => null)));

        try {
            //testa se os dados da carta de correção foram passados
            if ($chNFe == '' || $xCorrecao == '') {
                $msg = "Dados para a carta de correção não podem ser vazios.";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            if (strlen($chNFe) != 44) {
                $msg = "Uma chave de NFe válida não foi passada como parâmetro $chNFe.";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            //se o numero sequencial do evento não foi informado ou se for invalido
            if ($nSeqEvento == '' || strlen($nSeqEvento) > 2 || !is_numeric($nSeqEvento) || $nSeqEvento < 1) {
                $msg = "Número sequencial da correção não encontrado ou é maior "
                        . "que 99 ou contêm caracteres não numéricos [$nSeqEvento]";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            if (strlen($xCorrecao) < 15 || strlen($xCorrecao) > 1000) {
                $msg = "O texto da correção deve ter entre 15 e 1000 caracteres!";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            //limpa o texto de correção para evitar surpresas
            $xCorrecao = $this->pCleanString($xCorrecao);
            /*
            //ajusta ambiente
            if ($tpAmb == '') {
                $tpAmb = $this->tpAmb;
            }
            $aURL = $this->aURL;
            */
            if (!$aURL = $this->pLoadSEFAZ($tpAmb, $siglaUf)) {
                $msg = "Erro no carregamento das informacoes da SEFAZ";
                $protocolo['retorno'] = $msg;
                return $protocolo;
            }
            $numLote = $this->pGeraNumLote();
            //Data e hora do evento no formato AAAA-MM-DDTHH:MM:SSTZD (UTC)
            $validarCampos = Mage::helper('nfe/ValidarCampos');
            $dataAtual = $validarCampos->getHoraCerta(date('Y-m-d H:i:s'));
            $dhEvento = str_replace(' ', 'T', $dataAtual).Mage::getStoreConfig('nfe/nfe_opcoes/horario');
            //se o envio for para svan mudar o numero no orgão para 91
            if ($this->enableSVAN) {
                $cOrgao='90';
            } else {
                $cOrgao=$ufEmitente;
            }
            //montagem do namespace do serviço
            $servico = 'RecepcaoEvento';
            //recuperação da versão
            $versao = $aURL[$servico]['version'];
            //recuperação da url do serviço
            $urlservico = $aURL[$servico]['URL'];
            //recuperação do método
            $metodo = $aURL[$servico]['method'];
            //montagem do namespace do serviço
            $namespace = $this->URLPortal.'/wsdl/'.$servico;
            //estabelece o codigo do tipo de evento
            $tpEvento = '110110';
            //de acordo com o manual versão 5 de março de 2012
            // 2   +    6     +    44         +   2  = 54 digitos
            //“ID” + tpEvento + chave da NF-e + nSeqEvento
            //garantir que existam 2 digitos em nSeqEvento para montar o ID com 54 digitos
            if (strlen(trim($nSeqEvento))==1) {
                $zenSeqEvento = str_pad($nSeqEvento, 2, "0", STR_PAD_LEFT);
            } else {
                $zenSeqEvento = trim($nSeqEvento);
            }
            $chaveId = "ID".$tpEvento.$chNFe.$zenSeqEvento;
            $descEvento = 'Carta de Correcao';
            $xCondUso = 'A Carta de Correcao e disciplinada pelo paragrafo 1o-A do '
                    . 'art. 7o do Convenio S/N, de 15 de dezembro de 1970 e pode ser utilizada '
                    . 'para regularizacao de erro ocorrido na emissao de documento fiscal, desde que o '
                    . 'erro nao esteja relacionado com: I - as variaveis que determinam o valor do imposto '
                    . 'tais como: base de calculo, aliquota, diferenca de preco, quantidade, valor da '
                    . 'operacao ou da prestacao; II - a correcao de dados cadastrais que implique mudanca '
                    . 'do remetente ou do destinatario; III - a data de emissao ou de saida.';
            //monta mensagem
            $Ev='';
            $Ev .= "<evento xmlns=\"$this->URLPortal\" versao=\"$versao\">";
            $Ev .= "<infEvento Id=\"$chaveId\">";
            $Ev .= "<cOrgao>$cOrgao</cOrgao>";
            $Ev .= "<tpAmb>$tpAmb</tpAmb>";
            $Ev .= "<CNPJ>$cnpjEmitente</CNPJ>";
            $Ev .= "<chNFe>$chNFe</chNFe>";
            $Ev .= "<dhEvento>$dhEvento</dhEvento>";
            $Ev .= "<tpEvento>$tpEvento</tpEvento>";
            $Ev .= "<nSeqEvento>$nSeqEvento</nSeqEvento>";
            $Ev .= "<verEvento>$versao</verEvento>";
            $Ev .= "<detEvento versao=\"$versao\">";
            $Ev .= "<descEvento>$descEvento</descEvento>";
            $Ev .= "<xCorrecao>$xCorrecao</xCorrecao>";
            $Ev .= "<xCondUso>$xCondUso</xCondUso>";
            $Ev .= "</detEvento></infEvento></evento>";
            //assinatura dos dados
            $tagid = 'infEvento';
            $Ev = $this->assinarXML($Ev, $tagid, '', 'corrigir');
            $Ev = $this->pClearXml($Ev, true);
            //carrega uma matriz temporária com os eventos assinados
            //montagem dos dados
            $dados = '';
            $dados .= "<envEvento xmlns=\"$this->URLPortal\" versao=\"$versao\">";
            $dados .= "<idLote>$numLote</idLote>";
            $dados .= $Ev;
            $dados .= "</envEvento>";
            //montagem da mensagem
            $cabec = "<nfeCabecMsg xmlns=\"$namespace\"><cUF>$ufEmitente</cUF>"
                    . "<versaoDados>$versao</versaoDados></nfeCabecMsg>";
            $dados = "<nfeDadosMsg xmlns=\"$namespace\">$dados</nfeDadosMsg>";
            //grava solicitação em temp
            /*
            if (! file_put_contents($this->temDir."$chNFe-$nSeqEvento-envCCe.xml", $Ev)) {
                $msg = "Falha na gravacao do arquivo envCCe!!";
                throw new nfephpException($msg);
            }
             */
            //envia dados via SOAP
            $retorno = $this->pSendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb);
            //verifica o retorno
            if (!$retorno) {
                //não houve retorno
                $msg = "Nao houve retorno Soap verifique a mensagem de erro e o debug!!";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            //tratar dados de retorno
            $xmlretCCe = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlretCCe->formatOutput = false;
            $xmlretCCe->preserveWhiteSpace = false;
            $xmlretCCe->loadXML($retorno['valor'], LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $retEnvEvento = $xmlretCCe->getElementsByTagName('retEnvEvento')->item(0);
            $retEvento = $xmlretCCe->getElementsByTagName("retEvento")->item(0);
            $cStat = !empty($retEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $xMotivo = !empty($retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            if ($cStat == '') {
                //houve erro
                $msg = "cStat está em branco, houve erro na comunicação Soap "
                        . "verifique a mensagem de erro e o debug!!";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            //erro no processamento cStat <> 128
            if ($cStat != 135) {
                //se cStat <> 135 houve erro e o lote foi rejeitado
                $msg = "Retorno de ERRO: $cStat - $xMotivo";
                $xmlCorrigido['retorno'] = $msg;
                return $xmlCorrigido;
            }
            //a correção foi aceita cStat == 135
            $aResp['cStat'] = true;
            //carregar a CCe
            $xmlenvCCe = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlenvCCe->formatOutput = false;
            $xmlenvCCe->preserveWhiteSpace = false;
            $xmlenvCCe->loadXML($Ev, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $evento = $xmlenvCCe->getElementsByTagName("evento")->item(0);
            //Processo completo solicitação + protocolo
            $xmlprocCCe = new DOMDocument('1.0', 'utf-8');
            $xmlprocCCe->formatOutput = false;
            $xmlprocCCe->preserveWhiteSpace = false;
            //cria a tag procEventoNFe
            $procEventoNFe = $xmlprocCCe->createElement('procEventoNFe');
            $xmlprocCCe->appendChild($procEventoNFe);
            //estabele o atributo de versão
            $eventProc_att1 = $procEventoNFe->appendChild($xmlprocCCe->createAttribute('versao'));
            $eventProc_att1->appendChild($xmlprocCCe->createTextNode($versao));
            //estabelece o atributo xmlns
            $eventProc_att2 = $procEventoNFe->appendChild($xmlprocCCe->createAttribute('xmlns'));
            $eventProc_att2->appendChild($xmlprocCCe->createTextNode($this->URLPortal));
            //carrega o node evento
            $node1 = $xmlprocCCe->importNode($evento, true);
            $procEventoNFe->appendChild($node1);
            //carrega o node retEvento
            $node2 = $xmlprocCCe->importNode($retEvento, true);
            $procEventoNFe->appendChild($node2);
            //salva o xml como string em uma variável
            $procXML = $xmlprocCCe->saveXML();
            //remove as informações indesejadas
            $procXML = $this->pClearXml($procXML, false);
            //estrutura "retEnvEvento"
            $aRespVersao = $retEnvEvento->getAttribute('versao');
            $aResp['versao'] = !empty($aRespVersao) ? $retEnvEvento->getAttribute('versao') : '';
            $aResp['idLote'] = !empty($retEnvEvento->getElementsByTagName('idLote')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('idLote')->item(0)->nodeValue : '';
            $aResp['tpAmb'] = !empty($retEnvEvento->getElementsByTagName('tpAmb')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('tpAmb')->item(0)->nodeValue : '';
            $aResp['verAplic'] = !empty($retEnvEvento->getElementsByTagName('verAplic')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('verAplic')->item(0)->nodeValue : '';
            $aResp['cOrgao'] = !empty($retEnvEvento->getElementsByTagName('cOrgao')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('cOrgao')->item(0)->nodeValue : '';
            $aResp['cStat'] = !empty($retEnvEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $aResp['xMotivo'] = !empty($retEnvEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            //estrutura "retEvento"/"infEvento"
            $aRetEvento = array();
            $aInfEvento = array();
            $aRetEventoVersao = $retEvento->getAttribute('versao');
            $aInfEventoId = $retEvento->getElementsByTagName('infEvento')->item(0)->getAttribute('id');
            $aRetEvento['versao'] = !empty($aRetEventoVersao) ? $aRetEventoVersao : '';
            $aInfEvento['id'] = !empty($aInfEventoId) ? $aInfEventoId : '';
            $aInfEvento['tpAmb'] = !empty($retEvento->getElementsByTagName('tpAmb')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('tpAmb')->item(0)->nodeValue : '';
            $aInfEvento['verAplic'] = !empty($retEvento->getElementsByTagName('verAplic')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('verAplic')->item(0)->nodeValue : '';
            $aInfEvento['cOrgao'] = !empty($retEvento->getElementsByTagName('cOrgao')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('cOrgao')->item(0)->nodeValue : '';
            $aInfEvento['cStat'] = !empty($retEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $aInfEvento['xMotivo'] = !empty($retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            $aInfEvento['chNFe'] = !empty($retEvento->getElementsByTagName('chNFe')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('chNFe')->item(0)->nodeValue : '';
            $aInfEvento['tpEvento'] = !empty($retEvento->getElementsByTagName('tpEvento')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('tpEvento')->item(0)->nodeValue : '';
            $aInfEvento['nSeqEvento'] = !empty($retEvento->getElementsByTagName('nSeqEvento')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('nSeqEvento')->item(0)->nodeValue : '';
            $aInfEvento['CNPJDest'] = !empty($retEvento->getElementsByTagName('CNPJDest')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('CNPJDest')->item(0)->nodeValue : '';
            $aInfEvento['CPFDest'] = !empty($retEvento->getElementsByTagName('CPFDest')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('CPFDest')->item(0)->nodeValue : '';
            $aInfEvento['emailDest'] = !empty($retEvento->getElementsByTagName('emailDest')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('emailDest')->item(0)->nodeValue : '';
            $aInfEvento['dhRegEvento'] = !empty($retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue : '';
            $aInfEvento['nProt'] = !empty($retEvento->getElementsByTagName('nProt')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('nProt')->item(0)->nodeValue : '';
            //adiciona os arrays na estrutura de retorno ficando
            //retorno = array('retEvento'=>array('infEvento'=>array()))
            $aRetEvento['infEvento'] = $aInfEvento;
            $aResp['retEvento'] = $aRetEvento;
            /*
            //salva o arquivo xml
            if (!file_put_contents($this->cccDir."$chNFe-$nSeqEvento-procCCe.xml", $procXML)) {
                $msg = "Falha na gravacao da procCCe!!";
                $this->pSetError($msg);
                throw new nfephpException($msg);
            }
             */
        } catch (Exception $e) {
            $xmlCorrigido['retorno'] = $e;
            return $xmlCorrigido;
        }
        $xmlCorrigido['retorno'] = 'sucesso';
        $xmlCorrigido['xml'] = $procXML;
        return $xmlCorrigido;
    }//fim envCCe
    
    /**
     * cancelEvent
     * Solicita o cancelamento de NFe autorizada
     * - O xml do evento de cancelamento será salvo na pasta Canceladas
     *
     * @name cancelEvent
     * @param string $chNFe
     * @param string $nProt
     * @param string $xJust
     * @param number $tpAmb
     * @param array  $aRetorno
     */
    public function cancelEvent($chNFe = '', $nProt = '', $xJust = '', $tpAmb = '', &$aRetorno = array(), $siglaUf, $ufEmitente, $cnpjEmitente)
    {
        $xmlCancelado = array();
        try {
            //retorno da função
            $aRetorno = array(
                'bStat'=>false,
                'tpAmb'=>'',
                'verAplic'=>'',
                'cStat'=>'',
                'xMotivo'=>'',
                'nProt'=>'',
                'chNFe'=>'',
                'dhRecbto'=>'');
            //validação dos dados de entrada
            if ($chNFe == '' || $nProt == '' || $xJust == '') {
                $msg = "Não foi passado algum dos parâmetros necessários "
                        . "ID=$chNFe ou protocolo=$nProt ou justificativa=$xJust.";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            if ($tpAmb == '') {
                //$tpAmb = $this->tpAmb;
            }
            if (strlen($xJust) < 15) {
                $msg = "A justificativa deve ter pelo menos 15 digitos!!";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            if (strlen($xJust) > 255) {
                $msg = "A justificativa deve ter no máximo 255 digitos!!";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            if (strlen($chNFe) != 44) {
                $msg = "Uma chave de NFe válida não foi passada como parâmetro $chNFe.";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            //estabelece o codigo do tipo de evento CANCELAMENTO
            $tpEvento = '110111';
            $descEvento = 'Cancelamento';
            //para cancelamento o numero sequencia do evento sempre será 1
            $nSeqEvento = '1';
            //remove qualquer caracter especial
            $xJust = $this->pCleanString($xJust);
            //verifica se alguma das contingências está habilitada
            /*
            if ($this->enableSVCAN) {
                $aURL = $this->pLoadSEFAZ($tpAmb, self::CONTINGENCIA_SVCAN);
            } elseif ($this->enableSVCRS) {
                $aURL = $this->pLoadSEFAZ($tpAmb, self::CONTINGENCIA_SVCRS);
            } else {
                $aURL = $this->aURL;
            }
             */
            if (!$aURL = $this->pLoadSEFAZ($tpAmb, $siglaUf)) {
                $msg = "Erro no carregamento das informacoes da SEFAZ";
                $protocolo['retorno'] = $msg;
                return $protocolo;
            }
            $numLote = $this->pGeraNumLote();
            //Data e hora do evento no formato AAAA-MM-DDTHH:MM:SSTZD (UTC)
            $validarCampos = Mage::helper('nfe/ValidarCampos');
            $dataAtual = $validarCampos->getHoraCerta(date('Y-m-d H:i:s'));
            $dhEvento = str_replace(' ', 'T', $dataAtual).Mage::getStoreConfig('nfe/nfe_opcoes/horario');
            //se o envio for para svan mudar o numero no orgão para 91
            if ($this->enableSVAN) {
                $cOrgao='90';
            } else {
                $cOrgao=$ufEmitente;
            }
            //montagem do namespace do serviço
            $servico = 'RecepcaoEvento';
            //recuperação da versão
            $versao = '1.00';
            //recuperação da url do serviço
            $urlservico = $aURL[$servico]['URL'];
            //recuperação do método
            $metodo = $aURL[$servico]['method'];
            //montagem do namespace do serviço
            $namespace = $this->URLPortal.'/wsdl/'.$servico;
            //de acordo com o manual versão 5 de março de 2012
            // 2   +    6     +    44         +   2  = 54 digitos
            //“ID” + tpEvento + chave da NF-e + nSeqEvento
            //garantir que existam 2 digitos em nSeqEvento para montar o ID com 54 digitos
            if (strlen(trim($nSeqEvento))==1) {
                $zenSeqEvento = str_pad($nSeqEvento, 2, "0", STR_PAD_LEFT);
            } else {
                $zenSeqEvento = trim($nSeqEvento);
            }
            $eventId = "ID".$tpEvento.$chNFe.$zenSeqEvento;
            //monta mensagem
            $Ev = '';
            $Ev .= "<evento xmlns=\"$this->URLPortal\" versao=\"$versao\">";
            $Ev .= "<infEvento Id=\"$eventId\">";
            $Ev .= "<cOrgao>$cOrgao</cOrgao>";
            $Ev .= "<tpAmb>$tpAmb</tpAmb>";
            $Ev .= "<CNPJ>$cnpjEmitente</CNPJ>";
            $Ev .= "<chNFe>$chNFe</chNFe>";
            $Ev .= "<dhEvento>$dhEvento</dhEvento>";
            $Ev .= "<tpEvento>$tpEvento</tpEvento>";
            $Ev .= "<nSeqEvento>$nSeqEvento</nSeqEvento>";
            $Ev .= "<verEvento>$versao</verEvento>";
            $Ev .= "<detEvento versao=\"$versao\">";
            $Ev .= "<descEvento>$descEvento</descEvento>";
            $Ev .= "<nProt>$nProt</nProt>";
            $Ev .= "<xJust>$xJust</xJust>";
            $Ev .= "</detEvento></infEvento></evento>";
            //assinatura dos dados
            $tagid = 'infEvento';
            $Ev = $this->assinarXML($Ev, $tagid, '', 'cancelar');
            $Ev = $this->pClearXml($Ev, true);
            //carrega uma matriz temporária com os eventos assinados
            //montagem dos dados
            $dados = '';
            $dados .= "<envEvento xmlns=\"$this->URLPortal\" versao=\"$versao\">";
            $dados .= "<idLote>$numLote</idLote>";
            $dados .= $Ev;
            $dados .= "</envEvento>";
            //montagem da mensagem
            $cabec = "<nfeCabecMsg xmlns=\"$namespace\"><cUF>$ufEmitente</cUF>"
                    . "<versaoDados>$versao</versaoDados></nfeCabecMsg>";
            $dados = "<nfeDadosMsg xmlns=\"$namespace\">$dados</nfeDadosMsg>";
            //grava solicitação em temp
            /*
            $arqName = $this->temDir."$chNFe-$nSeqEvento-eventCanc.xml";
            if (!file_put_contents($arqName, $Ev)) {
                $msg = "Falha na gravacao do arquivo $arqName";
                $this->pSetError($msg);
            }
             */
            //envia dados via SOAP
            $retorno = $this->pSendSOAP($urlservico, $namespace, $cabec, $dados, $metodo, $tpAmb);
            //verifica o retorno
            if (!$retorno) {
                //não houve retorno
                $msg = "Nao houve retorno Soap verifique a mensagem de erro e o debug!!";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            //tratar dados de retorno
            $xmlretEvent = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlretEvent->formatOutput = false;
            $xmlretEvent->preserveWhiteSpace = false;
            $xmlretEvent->loadXML($retorno['valor'], LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $retEnvEvento = $xmlretEvent->getElementsByTagName("retEnvEvento")->item(0);
            $cStat = !empty($retEnvEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $xMotivo = !empty($retEnvEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                    $retEnvEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            if ($cStat == '') {
                //houve erro
                $msg = "cStat está em branco, houve erro na comunicação Soap "
                        . "verifique a mensagem de erro e o debug!!";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            //tratar erro de versão do XML
            if ($cStat == '238' || $cStat == '239') {
                /*
                $this->pTrata239($retorno, $this->siglaUF, $tpAmb, $servico, $versao);
                 */
                $msg = "Versão do arquivo XML não suportada no webservice!!";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            //erro no processamento cStat <> 128
            if ($cStat != 128) {
                //se cStat <> 135 houve erro e o lote foi rejeitado
                $msg = "Retorno de ERRO: $cStat - $xMotivo";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            //o lote foi processado cStat == 128
            $retEvento = $xmlretEvent->getElementsByTagName("retEvento")->item(0);
            $cStat = !empty($retEvento->getElementsByTagName('cStat')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue : '';
            $xMotivo = !empty($retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue) ?
                    $retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue : '';
            if ($cStat != 135 && $cStat != 155) {
                //se cStat <> 135 houve erro e o lote foi rejeitado
                $msg = "Retorno de ERRO: $cStat - $xMotivo";
                $xmlCancelado['retorno'] = $msg;
                return $xmlCancelado;
            }
            $aRetorno['bStat'] = true;
            // tipo de ambiente
            $aRetorno['tpAmb'] = $retEvento->getElementsByTagName('tpAmb')->item(0)->nodeValue;
            // verssão do aplicativo
            $aRetorno['verAplic'] = $retEvento->getElementsByTagName('verAplic')->item(0)->nodeValue;
            // status do serviço
            $aRetorno['cStat'] = $retEvento->getElementsByTagName('cStat')->item(0)->nodeValue;
            // motivo da resposta (opcional)
            $aRetorno['xMotivo'] = $retEvento->getElementsByTagName('xMotivo')->item(0)->nodeValue;
            // Numero de Protocolo
            $aRetorno['nProt'] = $retEvento->getElementsByTagName('nProt')->item(0)->nodeValue;
            // Chave
            $aRetorno['chNFe'] = $retEvento->getElementsByTagName('chNFe')->item(0)->nodeValue;
            // data e hora da mensagem (opcional)
            $aRetorno['dhRecbto'] = !empty($retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue) ?
                                    date("Y-m-d H:i:s", $this->pConvertTime($retEvento->getElementsByTagName('dhRegEvento')->item(0)->nodeValue)) : '';
            //o evento foi aceito cStat == 135 ou cStat == 155
            //carregar o evento
            $xmlenvEvento = new DOMDocument('1.0', 'utf-8'); //cria objeto DOM
            $xmlenvEvento->formatOutput = false;
            $xmlenvEvento->preserveWhiteSpace = false;
            $xmlenvEvento->loadXML($Ev, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            $evento = $xmlenvEvento->getElementsByTagName("evento")->item(0);
            //Processo completo solicitação + protocolo
            $xmlprocEvento = new DOMDocument('1.0', 'utf-8');
            $xmlprocEvento->formatOutput = false;
            $xmlprocEvento->preserveWhiteSpace = false;
            //cria a tag procEventoNFe
            $procEventoNFe = $xmlprocEvento->createElement('procEventoNFe');
            $xmlprocEvento->appendChild($procEventoNFe);
            //estabele o atributo de versão
            $eventProc_att1 = $procEventoNFe->appendChild($xmlprocEvento->createAttribute('versao'));
            $eventProc_att1->appendChild($xmlprocEvento->createTextNode($versao));
            //estabelece o atributo xmlns
            $eventProc_att2 = $procEventoNFe->appendChild($xmlprocEvento->createAttribute('xmlns'));
            $eventProc_att2->appendChild($xmlprocEvento->createTextNode($this->URLPortal));
            //carrega o node evento
            $node1 = $xmlprocEvento->importNode($evento, true);
            $procEventoNFe->appendChild($node1);
            //carrega o node retEvento
            $node2 = $xmlprocEvento->importNode($retEvento, true);
            $procEventoNFe->appendChild($node2);
            //salva o xml como string em uma variável
            $procXML = $xmlprocEvento->saveXML();
            //remove as informações indesejadas
            $procXML = $this->pClearXml($procXML, false);
            //salva o arquivo xml
            /*
            $arqName = $this->canDir."$chNFe-$nSeqEvento-procCanc.xml";
            if (!file_put_contents($arqName, $procXML)) {
                $msg = "Falha na gravacao do arquivo $arqName";
                $this->pSetError($msg);
            }
             */
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        $xmlCancelado['retorno'] = 'sucesso';
        $xmlCancelado['infProt'] = $aRetorno;
        return $xmlCancelado;
    } //fim cancEvent
    
    /**
     * loadSEFAZ
     * Extrai o URL, nome do serviço e versão dos webservices das SEFAZ de
     * todos os Estados da Federação, a partir do arquivo XML de configurações,
     * onde este é estruturado para os modelos 55 (NF-e) e 65 (NFC-e) já que
     * os endereços dos webservices podem ser diferentes.
     *
     * @name loadSEFAZ
     * @param  string $tpAmb     Pode ser "2-homologacao" ou "1-producao"
     * @param  string $sUF       Sigla da Unidade da Federação (ex. SP, RS, etc..)
     * @return mixed             false se houve erro ou array com os dados dos URLs da SEFAZ
     * @see /config/nfe_ws3_modXX.xml
     */
    protected function pLoadSEFAZ($tpAmb = '', $sUF = '')
    {
        try {
            $spathXML = Mage::getBaseDir(). DS . 'nfe' . DS . 'config' . DS . 'nfe_ws3_mod55.xml';
            //verifica se o arquivo xml pode ser encontrado no caminho indicado
            if (!file_exists($spathXML)) {
                $errMsg = "O arquivo XML \"$spathXML\" nao foi encontrado";
            }
            //carrega o xml
            if (!$xmlWS = simplexml_load_file($spathXML)) {
                $errMsg = "O arquivo XML \"$spathXML\" parece ser invalido";
            }
            //variável de retorno do método
            $aUrl = array();
            //testa parametro tpAmb
            if ($tpAmb == '') {
                $tpAmb = $this->tpAmb;
            } elseif ($tpAmb == self::AMBIENTE_PRODUCAO) {
                $sAmbiente = 'producao';
            } else {
                //força homologação em qualquer outra situação
                $tpAmb = self::AMBIENTE_HOMOLOGACAO;
                $sAmbiente = 'homologacao';
            }
            //valida e extrai a variável cUF da lista
            if (!isset($this->aliaslist[$sUF])) {
                $errMsg = "UF \"$sUF\" nao encontrada na lista de alias";
            }
            $alias = $this->aliaslist[$sUF];
            //verifica se deve habilitar SVAN ou SVRS (ambos por padrão iniciam desabilitados)
            if ($alias == self::SVAN) {
                $this->enableSVAN = true;
            } elseif ($alias == self::SVRS) {
                $this->enableSVRS = true;
            }
            //estabelece a expressão xpath de busca
            $xpathExpression = "/WS/UF[sigla='$alias']/$sAmbiente";
            //para cada "nó" no xml que atenda aos critérios estabelecidos
            foreach ($xmlWS->xpath($xpathExpression) as $gUF) {
                //para cada "nó filho" retonado
                foreach ($gUF->children() as $child) {
                    $u = (string) $child[0];
                    $aUrl[$child->getName()]['URL'] = $u;
                    // em cada um desses nós pode haver atributos como a identificação
                    // do nome do webservice e a sua versão
                    foreach ($child->attributes() as $a => $b) {
                        $aUrl[$child->getName()][$a] = (string) $b;
                    }
                }
            }
            //verifica se existem outros serviços exclusivos para esse estado
            if ($alias == self::SVAN || $alias == self::SVRS) {
                //para cada "nó" no xml que atenda aos critérios estabelecidos
                foreach ($xmlWS->xpath($xpathExpression) as $gUF) {
                    //para cada "nó filho" retonado
                    foreach ($gUF->children() as $child) {
                        $u = (string) $child[0];
                        $aUrl[$child->getName()]['URL'] = $u;
                        // em cada um desses nós pode haver atributos como a identificação
                        // do nome do webservice e a sua versão
                        foreach ($child->attributes() as $a => $b) {
                            $aUrl[$child->getName()][$a] = (string) $b;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return $aUrl;
    } //fim loadSEFAZ
    
    /**
     * pSendSOAP
     * Função alternativa para estabelecer comunicaçao com servidor SOAP 1.2 da SEFAZ,
     * usando as chaves publica e privada parametrizadas na contrução da classe.
     * Conforme Manual de Integração Versão 4.0.1 Utilizando cURL e não o SOAP nativo
     *
     * @name pSendSOAP
     * @param string $urlsefaz
     * @param string $namespace
     * @param string $cabecalho
     * @param string $dados
     * @param string $metodo
     * @param numeric $ambiente
     * @param string $siglaUF sem uso mantido apenas para compatibilidade com sendSOAP
     * @return mixed false se houve falha ou o retorno em xml do SEFAZ
     */
    protected function pSendSOAP($urlsefaz, $namespace, $cabecalho, $dados, $metodo, $ambiente = '', $siglaUF = '')
    {
        $retorno = array();
        try {
            $certificado = $this->pLoadCerts();
            if ($urlsefaz == '') {
                $msg = "URL do webservice não disponível no arquivo xml das URLs da SEFAZ.";
                $retorno['resultado'] = 'erro';
                $retorno['valor'] = $msg;
                return $retorno;
            }
            if ($ambiente == '') {
                $retorno['resultado'] = 'erro';
                $retorno['valor'] = 'Campo ambiente vazio.';
                return $retorno;
            }
            $data = '';
            $data .= '<?xml version="1.0" encoding="utf-8"?>';
            $data .= '<soap12:Envelope ';
            $data .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $data .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
            $data .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
            $data .= '<soap12:Header>';
            $data .= $cabecalho;
            $data .= '</soap12:Header>';
            $data .= '<soap12:Body>';
            $data .= $dados;
            $data .= '</soap12:Body>';
            $data .= '</soap12:Envelope>';
            //[Informational 1xx]
            $cCode['100']="Continue";
            $cCode['101']="Switching Protocols";
            //[Successful 2xx]
            $cCode['200']="OK";
            $cCode['201']="Created";
            $cCode['202']="Accepted";
            $cCode['203']="Non-Authoritative Information";
            $cCode['204']="No Content";
            $cCode['205']="Reset Content";
            $cCode['206']="Partial Content";
            //[Redirection 3xx]
            $cCode['300']="Multiple Choices";
            $cCode['301']="Moved Permanently";
            $cCode['302']="Found";
            $cCode['303']="See Other";
            $cCode['304']="Not Modified";
            $cCode['305']="Use Proxy";
            $cCode['306']="(Unused)";
            $cCode['307']="Temporary Redirect";
            //[Client Error 4xx]
            $cCode['400']="Bad Request";
            $cCode['401']="Unauthorized";
            $cCode['402']="Payment Required";
            $cCode['403']="Forbidden";
            $cCode['404']="Not Found";
            $cCode['405']="Method Not Allowed";
            $cCode['406']="Not Acceptable";
            $cCode['407']="Proxy Authentication Required";
            $cCode['408']="Request Timeout";
            $cCode['409']="Conflict";
            $cCode['410']="Gone";
            $cCode['411']="Length Required";
            $cCode['412']="Precondition Failed";
            $cCode['413']="Request Entity Too Large";
            $cCode['414']="Request-URI Too Long";
            $cCode['415']="Unsupported Media Type";
            $cCode['416']="Requested Range Not Satisfiable";
            $cCode['417']="Expectation Failed";
            //[Server Error 5xx]
            $cCode['500']="Internal Server Error";
            $cCode['501']="Not Implemented";
            $cCode['502']="Bad Gateway";
            $cCode['503']="Service Unavailable";
            $cCode['504']="Gateway Timeout";
            $cCode['505']="HTTP Version Not Supported";

            $tamanho = strlen($data);
            $parametros = array(
                'Content-Type: application/soap+xml;charset=utf-8;action="'.$namespace."/".$metodo.'"',
                'SOAPAction: "'.$metodo.'"',
                "Content-length: $tamanho");
            $aspas = '"';
            $oCurl = curl_init();
            /*
            if (is_array($this->aProxy)) {
                curl_setopt($oCurl, CURLOPT_HTTPPROXYTUNNEL, 1);
                curl_setopt($oCurl, CURLOPT_PROXYTYPE, "CURLPROXY_HTTP");
                curl_setopt($oCurl, CURLOPT_PROXY, $this->aProxy['IP'].':'.$this->aProxy['PORT']);
                if ($this->aProxy['PASS'] != '') {
                    curl_setopt($oCurl, CURLOPT_PROXYUSERPWD, $this->aProxy['USER'].':'.$this->aProxy['PASS']);
                    curl_setopt($oCurl, CURLOPT_PROXYAUTH, "CURLAUTH_BASIC");
                } //fim if senha proxy
            }//fim if aProxy
             */
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soapTimeout);
            curl_setopt($oCurl, CURLOPT_URL, $urlsefaz.'');
            curl_setopt($oCurl, CURLOPT_PORT, 443);
            curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
            curl_setopt($oCurl, CURLOPT_HEADER, 1); //retorna o cabeçalho de resposta
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 3);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2); // verifica o host evita MITM
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($oCurl, CURLOPT_SSLCERT, $certificado['pubKey']);
            curl_setopt($oCurl, CURLOPT_SSLKEY, $certificado['priKey']);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $parametros);
            $xml = curl_exec($oCurl);
            $info = curl_getinfo($oCurl); //informações da conexão
            $txtInfo ="";
            $txtInfo .= "URL=$info[url]\n";
            $txtInfo .= "Content type=$info[content_type]\n";
            $txtInfo .= "Http Code=$info[http_code]\n";
            $txtInfo .= "Header Size=$info[header_size]\n";
            $txtInfo .= "Request Size=$info[request_size]\n";
            $txtInfo .= "Filetime=$info[filetime]\n";
            $txtInfo .= "SSL Verify Result=$info[ssl_verify_result]\n";
            $txtInfo .= "Redirect Count=$info[redirect_count]\n";
            $txtInfo .= "Total Time=$info[total_time]\n";
            $txtInfo .= "Namelookup=$info[namelookup_time]\n";
            $txtInfo .= "Connect Time=$info[connect_time]\n";
            $txtInfo .= "Pretransfer Time=$info[pretransfer_time]\n";
            $txtInfo .= "Size Upload=$info[size_upload]\n";
            $txtInfo .= "Size Download=$info[size_download]\n";
            $txtInfo .= "Speed Download=$info[speed_download]\n";
            $txtInfo .= "Speed Upload=$info[speed_upload]\n";
            $txtInfo .= "Download Content Length=$info[download_content_length]\n";
            $txtInfo .= "Upload Content Length=$info[upload_content_length]\n";
            $txtInfo .= "Start Transfer Time=$info[starttransfer_time]\n";
            $txtInfo .= "Redirect Time=$info[redirect_time]\n";
            $txtInfo .= "Certinfo=".print_r($info['certinfo'], true)."\n";
            $lenN = strlen($xml);
            $posX = stripos($xml, "<");
            if ($posX !== false) {
                $xml = substr($xml, $posX, $lenN-$posX);
            } else {
                $xml = '';
            }
            $this->soapDebug = $data."\n\n".$txtInfo."\n".$xml;
            if ($xml === false || $posX === false) {
                //não houve retorno
                $msg = curl_error($oCurl);
                if (isset($info['http_code'])) {
                    $msg .= $info['http_code'].$cCode[$info['http_code']];
                }
                $retorno['resultado'] = 'erro';
                $retorno['valor'] = $msg;
                return $retorno;
            } else {
                //houve retorno mas ainda pode ser uma mensagem de erro do webservice
                if ($info['http_code'] > 300) {
                    $msg = $info['http_code'].$cCode[$info['http_code']];
                    $retorno['resultado'] = 'erro';
                    $retorno['valor'] = $msg;
                    return $retorno;
                }
            }
            curl_close($oCurl);
            $retorno['resultado'] = 'sucesso';
            $retorno['valor'] = $xml;
            return $retorno;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
    } //fim sendSOAP
    
    /**
     * pSimpleGetValue
     * Extrai o valor do node DOM
     * @param object $theObj Instancia de DOMDocument ou DOMElement
     * @param string $keyName identificador da TAG do xml
     * @param string $extraTextBefore prefixo do retorno
     * @param string extraTextAfter sufixo do retorno
     * @param number itemNum numero do item a ser retornado
     * @return string
     */
    protected function pSimpleGetValue($theObj, $keyName, $extraTextBefore = '', $extraTextAfter = '', $itemNum = 0)
    {
        if (empty($theObj)) {
            return '';
        }
        if (!($theObj instanceof DOMDocument) && !($theObj instanceof DOMElement)) {
            return (
                "Metodo CommonNFePHP::pSimpleGetValue() "
                . "com parametro do objeto invalido, verifique!"
            );
        }
        $vct = $theObj->getElementsByTagName($keyName)->item($itemNum);
        if (isset($vct)) {
            return $extraTextBefore . trim($vct->nodeValue) . $extraTextAfter;
        }
        return '';
    }
    
    /**
     * convertTime
     * Converte o campo data/hora retornado pelo webservice em um timestamp unix
     *
     * @name convertTime
     * @param  string $DataHora Exemplo: "2014-03-28T14:39:54-03:00"
     * @return float
     */
    protected function pConvertTime($dataHora = '')
    {
        $timestampDH = 0;
        if ($dataHora) {
            $aDH = explode('T', $dataHora);
            $adDH = explode('-', $aDH[0]);
            $atDH = explode(':', substr($aDH[1], 0, 8));//substring para recuperar apenas a hora, sem o fuso horário
            $timestampDH = mktime($atDH[0], $atDH[1], $atDH[2], $adDH[1], $adDH[2], $adDH[0]);
        }
        return $timestampDH;
    } //fim convertTime
    
    public function getXmlNfe($nfe) {
        if($nfe->getTpNf() == '0') {
            $tipo = 'entrada';
        } else {
            $tipo = 'saida';
        }
        $xml = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS . $nfe->getIdTag().'.xml';
        return $xml; 
    }
    
    public function gerarDanfe($xmlNfe, $nfe, $acao) {
        if($nfe->getTpNf() == '0') {
            $tipo = 'entrada';
        } else {
            $tipo = 'saida';
        }
        $formatoImpressao = Mage::getStoreConfig('nfe/danfe_opcoes/formato');
        if($formatoImpressao == 'portraite') {
            $formato = 'P';
        } else {
            $formato = 'L';
        }
        $logo = Mage::getBaseDir(). DS . 'nfe' . DS . 'imagens' . DS . 'logo.png';
        $pdf = Mage::getBaseDir(). DS . 'nfe' . DS . 'pdf' . DS . $tipo . DS . $nfe->getIdTag().'.pdf';
        $nfeDanfe = Mage::helper('nfe/pdf_nfeDanfe');
        $nfeDanfe->init($xmlNfe, $formato, 'A4', $logo, 'I', '');
        $nfeDanfe->montaDANFE($formato, 'A4', 'C');
        $nfeDanfe->printDANFE($pdf, $acao);
    }
    
    public function gerarDanfes($nfeIds) {
        $arquivos = array();
        foreach($nfeIds as $nfeId) {
            $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
            if($nfe->getTpNf() == '0') {
                $tipo = 'entrada';
            } else {
                $tipo = 'saida';
            }
            $arquivos[] = Mage::getBaseDir(). DS . 'nfe' . DS . 'pdf' . DS . $tipo . DS . $nfe->getIdTag().'.pdf';
        }
        $agruparDanfe = Mage::helper('nfe/pdf_AgruparDanfe');
        $agruparDanfe->setFiles($arquivos); 
        $agruparDanfe->concatPrint(); 
    }
    
    public function gerarDacce($xmlNfe, $nfe, $acao) {
        $nfeIdentificacaoEmitente = Mage::getModel('nfe/nfeidentificacao')->getCollection()
            ->addFieldToFilter('nfe_id', array('eq' => $nfe->getNfeId()))
            ->addFieldToFilter('tipo_identificacao', array('eq' => 'emit'))
            ->getFirstItem();
        $emailRemetente = Mage::getStoreConfig('trans_email/ident_sales',Mage::app()->getStore()->getStoreId());
        $aEnd = array(
            'razao' => $nfeIdentificacaoEmitente->getXNome(), 
            'logradouro' => $nfeIdentificacaoEmitente->getXLgr(), 
            'numero' => $nfeIdentificacaoEmitente->getNro(), 
            'complemento' => $nfeIdentificacaoEmitente->getXCpl(), 
            'bairro' => $nfeIdentificacaoEmitente->getXBairro(), 
            'CEP' => $nfeIdentificacaoEmitente->getCep(), 
            'municipio' => $nfeIdentificacaoEmitente->getXMun(), 
            'UF' => $nfeIdentificacaoEmitente->getUf(), 
            'telefone' => $nfeIdentificacaoEmitente->getFone(), 
            'email' => $emailRemetente['email']
        );
        $formatoImpressao = Mage::getStoreConfig('nfe/danfe_opcoes/formato');
        if($formatoImpressao == 'portraite') {
            $formato = 'P';
        } else {
            $formato = 'L';
        }
        $logo = Mage::getBaseDir(). DS . 'nfe' . DS . 'imagens' . DS . 'logo.png';
        $pdf = Mage::getBaseDir(). DS . 'nfe' . DS . 'pdf' . DS . 'corrigido' . DS . str_replace('NF', 'CC', $nfe->getIdTag()).'.pdf';
        $nfeDacce = Mage::helper('nfe/pdf_nfeDacce');
        $nfeDacce->init($xmlNfe, $formato, 'A4', $logo, 'I', $aEnd, '');
        $nfeDacce->printDACCE($pdf, $acao);
    }
    
    public function enviarEmail($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        if(strpos($order->getCustomerEmail(),'extra.com.br') === false) {
            $downloadsDetalhes = $this->getDownloads($nfe, '');
            $sender = Mage::getStoreConfig('trans_email/ident_sales',Mage::app()->getStore()->getStoreId());
            $mail = Mage::getModel('core/email_template');
            $mail->setDesignConfig(array(
                'area'  => 'frontend',
                'store' => Mage::app()->getStore()->getStoreId()
            ));
            $emailBcc = Mage::getStoreConfig('nfe/emitente_opcoes/email_bcc');
            if($emailBcc) {
                $mail->addBCC($emailBcc);
            }
            $mail->sendTransactional(
                'nfe_email_template',
                $sender,
                $order->getCustomerEmail(),
                null,
                array(
                    'store' => Mage::app()->getStore(),
                    'order' => $order,
                    'nfe_chve' => substr($nfe->getIdTag(),3),
                    'xml_url' => $downloadsDetalhes['xml_url'],
                    'pdf_url' => $downloadsDetalhes['pdf_url'],
                    'xml_img' => $downloadsDetalhes['xml_img'],
                    'pdf_img' => $downloadsDetalhes['pdf_img'],
                    'nfe_name' => utf8_encode('Nota Fiscal Eletr�nica')
                )
            );
        }
    }
    
    public function setCompleto($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setData('status', 'nfe_enviada');
        $order->addStatusToHistory(nfe_enviada, 
        'O processo de emissão da Nota Fiscal Eletrônica (NF-e) foi completado e o cliente foi notificado com sucesso.<br/>
         Série: '.$nfe->getSerie().'<br/>   
         Número: '.$nfe->getNNf().'<br/>   
         Chave: '.substr($nfe->getIdTag(),3).'<br/>
         Status: Completo');
        $order->save();
        if(strpos($order->getCustomerEmail(),'extra.com.br') !== false) {
            $order->addStatusToHistory(nfe_enviada, 
            'chave de acesso: '.substr($nfe->getIdTag(),3));
            $order->save();
        }
        $nfe->setStatus('7');
        $nfe->setMensagem(utf8_encode('Processo de emiss�o da NF-e completo.'));
        $nfe->save();
    }
    
    public function setRetirado($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setData('status', 'nfe_retirada');
        $order->addStatusToHistory(nfe_retirada, 
        'O processo de retirada da Nota Fiscal Eletrônica (NF-e) foi completado e o número inutilizado.<br/>
         Status: Inutilizado');
        $order->save();
        $nfe->setStatus('9');
        $nfe->setMensagem(utf8_encode('A NF-e foi retirada e o n�mero inutilizado.'));
        $nfe->save();
    }
    
    public function setCorrigido($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->addStatusToHistory($order->getStatus(),
        'O processo de correção da Nota Fiscal Eletrônica (NF-e) foi completado e uma CC-e foi gerada.<br/>
         Status: Completo');
        $order->save();
        $nfe->setMensagem(utf8_encode('A CC-e foi gerada para a NF-e com sucesso.'));
        $nfe->save();
    }
    
    public function setCancelado($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setData('status', 'nfe_cancelada');
        $order->addStatusToHistory(nfe_cancelada, 
        'O processo de cancelamento da Nota Fiscal Eletrônica (NF-e) foi completado.<br/>
         Status: Cancelado');
        $order->save();
        $nfe->setStatus('6');
        $nfe->setMensagem(utf8_encode('Cancelado pelo org�o respons�vel.'));
        $nfe->save();
    }
    
    public function setDenegado($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setData('status', 'nfe_denegada');
        $order->addStatusToHistory(nfe_denegada, 
        'O processo de emissão da Nota Fiscal Eletrônica (NF-e) não foi completado, pois o pedido de emissão foi denegado.<br/>
         Status: Denegado');
        $order->save();
    }
    
    public function setDevolvido($nfe) {
        $order = Mage::getModel('sales/order')->loadByIncrementId($nfe->getPedidoIncrementId());
        $order->setData('state', Mage_Sales_Model_Order::STATE_CLOSED);
        $order->setData('status', 'closed');
        $order->addStatusToHistory(closed, 
        'O processo de emissão da Nota Fiscal Eletrônica (NF-e) de devolução foi completado.<br/>
         Série: '.$nfe->getSerie().'<br/>   
         Número: '.$nfe->getNNf().'<br/>   
         Chave: '.substr($nfe->getIdTag(),3).'<br/>
         Status: Completo');
        $order->save();
        $nfe->setStatus('7');
        $nfe->setMensagem(utf8_encode('Processo de emiss�o da NF-e completo.'));
        $nfe->save();
    }
    
    public function getDownloads($nfe, $documentoTipo) {
        $downloadsDetalhes = array();
        if($documentoTipo == 'inutilizado') {
            $tipo = 'inutilizado';
        } else if($documentoTipo == 'corrigido') {
            $tipo = 'corrigido';
        } else if($nfe->getTpNf() == '0') {
            $tipo = 'entrada';
        } else {
            $tipo = 'saida';
        }
        $downloadsDetalhes['pdf_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). 'nfe/operacoes/download/formato/pdf/tipo/' . $tipo . '/key/' . rtrim(strtr(base64_encode(substr($nfe->getIdTag(),3)), '+/', '-_'), '=');
        $downloadsDetalhes['xml_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). 'nfe/operacoes/download/formato/xml/tipo/' . $tipo . '/key/' . rtrim(strtr(base64_encode(substr($nfe->getIdTag(),3)), '+/', '-_'), '=');
        $downloadsDetalhes['pdf_img'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). 'nfe/' . 'imagens/' . 'pdf_logo.png';
        $downloadsDetalhes['xml_img'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB). 'nfe/' . 'imagens/' . 'xml_logo.png';
        
        return $downloadsDetalhes;
    }
    
    public function xmlString($xmlNfe) {
        $aNFe = file_get_contents($xmlNfe);
        $xmldoc = new DOMDocument('1.0', 'utf-8');
        $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
        $xmldoc->formatOutput = false;
        $xmldoc->loadXML($aNFe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $sXml = $xmldoc->saveXML();
        return $sXml;
    }
    
    public function salvarXml($xmlNfe, $caminho, $idTag) {
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->preservWhiteSpace = false; //elimina espaços em branco
        $doc->formatOutput = false;
        $doc->loadXML($xmlNfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $doc->save($caminho.$idTag.'.xml');
    }
    
    public function salvarXmlInutilizado($xmlNfe, $nfe) {
        $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . 'inutilizado' . DS;
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->preservWhiteSpace = false; //elimina espaços em branco
        $doc->formatOutput = false;
        $doc->loadXML($xmlNfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $doc->save($caminho.$nfe->getIdTag().'.xml');
    }
    
    public function salvarXmlCorrigido($xmlNfe, $nfe) {
        $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . 'corrigido' . DS;
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->preservWhiteSpace = false; //elimina espaços em branco
        $doc->formatOutput = false;
        $doc->loadXML($xmlNfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $doc->save($caminho.str_replace('NF', 'CC', $nfe->getIdTag()).'.xml');
    }
    
    /**
     * pClearXml
     * Remove \r \n \s \t 
     * @param string $xml
     * @param boolean $remEnc remover encoding
     * @return string
     */
    private function pClearXml($xml = '', $remEnc = false)
    {
        $retXml = $xml;
        if ($remEnc) {
            $retXml = str_replace('<?xml version="1.0"?>', '', $retXml);
            $retXml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $retXml);
            $retXml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $retXml);
        }
        $retXml = str_replace("xmlns:default=\"http://www.w3.org/2000/09/xmldsig#\"", '', $retXml);
        $retXml = str_replace('default:', '', $retXml);
        $retXml = str_replace(':default', '', $retXml);
        $retXml = str_replace("\n", '', $retXml);
        $retXml = str_replace("\r", '', $retXml);
        $retXml = str_replace("\s", '', $retXml);
        $retXml = str_replace("\t", '', $retXml);
        return $retXml;
    }
    
    /**
     * cleanString
     * Remove todos dos caracteres espceiais do texto e os acentos
     *
     * @name cleanString
     * @return  string Texto sem caractere especiais
     */
    private function pCleanString($texto)
    {
        $aFind = array('&','á','à','ã','â','é','ê','í','ó','ô','õ','ú','ü',
            'ç','Á','À','Ã','Â','É','Ê','Í','Ó','Ô','Õ','Ú','Ü','Ç');
        $aSubs = array('e','a','a','a','a','e','e','i','o','o','o','u','u',
            'c','A','A','A','A','E','E','I','O','O','O','U','U','C');
        $novoTexto = str_replace($aFind, $aSubs, $texto);
        $novoTexto = preg_replace("/[^a-zA-Z0-9 @,-.;:\/]/", "", $novoTexto);
        return $novoTexto;
    }//fim cleanString
    
    /**
     * Gera numero de lote com base em microtime
     * @return string 
     */
    private function pGeraNumLote()
    {
        return substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15);
    }
}
