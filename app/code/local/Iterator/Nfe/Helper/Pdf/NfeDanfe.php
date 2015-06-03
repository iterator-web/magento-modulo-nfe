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

//define o caminho base da instalação do sistema
if (!defined('PATH_ROOT')) {
    define('PATH_ROOT', dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR);
}
//ajuste do tempo limite de resposta do processo
set_time_limit(1800);
//definição do caminho para o diretorio com as fontes do FDPF
if (!defined('FPDF_FONTPATH')) {
    define('FPDF_FONTPATH', 'font/');
}
//situação externa do documento
if (!defined('NFEPHP_SITUACAO_EXTERNA_CANCELADA')) {
    define('NFEPHP_SITUACAO_EXTERNA_CANCELADA', 1);
    define('NFEPHP_SITUACAO_EXTERNA_DENEGADA', 2);
    define('NFEPHP_SITUACAO_EXTERNA_DPEC', 3);
    define('NFEPHP_SITUACAO_EXTERNA_NONE', 0);
}

require_once(Mage::getBaseDir('lib') . '/fpdf/fpdf.php');
require_once(Mage::getBaseDir('lib') . '/fpdf/PdfNFePHP.class.php');

class Iterator_Nfe_Helper_Pdf_NfeDanfe extends Mage_Core_Helper_Abstract {
    /**
    * Este arquivo é parte do projeto NFePHP - Nota Fiscal eletrônica em PHP.
    *
    * Este programa é um software livre: você pode redistribuir e/ou modificá-lo
    * sob os termos da Licença Pública Geral GNU como é publicada pela Fundação
    * para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
    * e/ou
    * sob os termos da Licença Pública Geral Menor GNU (LGPL) como é publicada pela
    * Fundação para o Software Livre, na versão 3 da licença, ou qualquer versão posterior.
    *
    * Este programa é distribuído na esperança que será útil, mas SEM NENHUMA
    * GARANTIA; nem mesmo a garantia explícita definida por qualquer VALOR COMERCIAL
    * ou de ADEQUAÇÃO PARA UM PROPÓSITO EM PARTICULAR,
    * veja a Licença Pública Geral GNU para mais detalhes.
    *
    * Você deve ter recebido uma cópia da Licença Publica GNU e da
    * Licença Pública Geral Menor GNU (LGPL) junto com este programa.
    * Caso contrário consulte
    * <http://www.fsfla.org/svnwiki/trad/GPLv3>
    * ou
    * <http://www.fsfla.org/svnwiki/trad/LGPLv3>.
    *
    * @package     NFePHP
    * @name        DanfeNFePHP.class.php
    * @version     2.2.3
    * @license     http://www.gnu.org/licenses/gpl.html GNU/GPL v.3
    * @license     http://www.gnu.org/licenses/lgpl.html GNU/LGPL v.3
    * @copyright   2009-2012 &copy; NFePHP
    * @link        http://www.nfephp.org/
    * @author      Roberto L. Machado <linux.rlm at gmail dot com>
    * @author      Marcos Diez <marcos at unitron dot com dot br>
    *
    *        CONTRIBUIDORES (por ordem alfabetica):
    *              Abdenego Santos <abdenego at gmail dot com>
    *              André Ferreira de Morais <andrefmoraes at gmail dot com>
    *              Bruno J R Lima <brunofileh at gmail dot com>
    *              Chrystian Toigo <ctoigo at gmail dot com>
    *              Djalma Fadel Junior <dfadel at ferasoft dot com dot br>
    *              Eduardo Gusmão <eduardo dot intrasis at gmail dot com>
    *              Faruk Mustafa Zahra < farukz at gmail dot com >
    *              Felipe Bonato <montanhats at gmail dot com>
    *              Fernando Mertins <fernando dot mertins at gmail dot com>
    *              Guilherme Calabria Filho <guiga at gmail dot com>
    *              Leandro C. Lopez <leandro.castoldi at gmail dot com>
    *              Paulo Gabriel Coghi < paulocoghi at gmail dot com>
    *              Rafael Stavarengo <faelsta at gmail dot com>
    *              Renato Zaccaron Gonzaga <renato at zaccaron dot com dot br>
    *              Roberto Spadim <roberto at spadim dot com dot br>
    *              Vinicius Souza <vdssgmu at gmail dot com>
    *
    *
    * NOTA: De acordo com a ISO o formato OficioII não existe mais e portanto só devemos
    *       usar o padrão A4.
    *
    */
    
    /**
     * alinhamento padrão do logo (C-Center)
     * @var string 
     */
    public $logoAlign='C';
    /**
     * Posição
     * @var float 
     */
    public $yDados=0;
    /**
     * Situação 
     * @var integer
     */
    public $situacaoExterna=0;
    /**
     * Numero DPEC
     * @var string
     */
    public $numero_registro_dpec='';
    /**
     * quantidade de canhotos a serem montados, geralmente 1 ou 2
     * @var integer 
     */
    public $qCanhoto=1;
    
    // INÍCIO ATRIBUTOS DE PARÂMETROS DE EXIBIÇÃO
    /**
     * Parâmetro para exibir ou ocultar os valores do PIS/COFINS.
     * @var boolean
     */
    public $exibirPIS=true;
    /**
     * Parâmetro para exibir ou ocultar o texto sobre valor aproximado dos tributos.
     * @var boolean
     */
    public $exibirValorTributos=true;
    /**
     * Parâmetro para exibir ou ocultar o texto adicional sobre a forma de pagamento
     * e as informações de fatura/duplicata.
     * @var boolean
     */
    public $exibirTextoFatura=false;
    /**
     * Parâmetro do controle se deve concatenar automaticamente informações complementares
     * na descrição do produto, como por exemplo, informações sobre impostos.
     * @var boolean
     */
    public $descProdInfoComplemento=true;
    /**
     * Parâmetro do controle se deve gerar quebras de linha com "\n" a partir de ";" na descrição do produto.
     * @var boolean
     */
    public $descProdQuebraLinha=true;
    // FIM ATRIBUTOS DE PARÂMETROS DE EXIBIÇÃO
    
    /**
     * objeto fpdf()
     * @var object 
     */
    protected $pdf;
    /**
     * XML NFe
     * @var string
     */
    protected $xml;
    /**
     * path para logomarca em jpg
     * @var string
     */
    protected $logomarca='';
    /**
     * mesagens de erro
     * @var string
     */
    protected $errMsg='';
    /**
     * status de erro true um erro ocorreu false sem erros
     * @var boolean
     */
    protected $errStatus=false;
    /**
     * orientação da DANFE 
     * P-Retrato ou 
     * L-Paisagem
     * @var string
     */
    protected $orientacao='L';
    /**
     * formato do papel
     * @var string
     */
    protected $papel='A4';
    /**
     * destino do arquivo pdf 
     * I-borwser,
     * S-retorna o arquivo,
     * D-força download,
     * F-salva em arquivo local
     * @var string
     */
    protected $destino = 'I';
    /**
     * diretorio para salvar o pdf com a opção de destino = F
     * @var string 
     */
    protected $pdfDir='';
    /**
     * Nome da Fonte para gerar o DANFE
     * @var string
     */
    protected $fontePadrao='Times';
    /**
     * versão
     * @var string 
     */
    protected $version = '2.2.3';
    /**
     * Texto
     * @var string 
     */
    protected $textoAdic = '';
    /**
     * Largura
     * @var float
     */
    protected $wAdic = 0;
    /**
     * largura imprimivel, em milímetros
     * @var float 
     */
    protected $wPrint;
    /**
     * Comprimento (altura) imprimivel, em milímetros
     * @var float
     */
    protected $hPrint;
    /**
     * largura do canhoto (25mm) apenas para a formatação paisagem
     * @var float
     */
    protected $wCanhoto=25;
    /**
     * Formato chave
     * @var string
     */
    protected $formatoChave="#### #### #### #### #### #### #### #### #### #### ####";
    /**
     * quantidade de itens já processados na montagem do DANFE
     * @var integer
     */
    protected $qtdeItensProc;
    
    /**
     * Document
     * @var DOMDocument
     */
    protected $dom;
    /**
     * Node
     * @var DOMNode
     */
    protected $infNFe;
    /**
     * Node
     * @var DOMNode
     */
    protected $ide;
    /**
     * Node
     * @var DOMNode 
     */
    protected $entrega;
    /**
     * Node
     * @var DOMNode
     */
    protected $retirada;
    /**
     * Node
     * @var DOMNode
     */
    protected $emit;
    /**
     * Node
     * @var DOMNode
     */
    protected $dest;
    /**
     * Node
     * @var DOMNode
     */
    protected $enderEmit;
    /**
     * Node
     * @var DOMNode
     */
    protected $enderDest;
    /**
     * Node
     * @var DOMNode
     */
    protected $det;
    /**
     * Node
     * @var DOMNode
     */
    protected $cobr;
    /**
     * Node
     * @var DOMNode
     */
    protected $dup;
    /**
     * Node
     * @var DOMNode
     */
    protected $ICMSTot;
    /**
     * Node
     * @var DOMNode
     */
    protected $ISSQNtot;
    /**
     * Node
     * @var DOMNode
     */
    protected $transp;
    /**
     * Node
     * @var DOMNode
     */
    protected $transporta;
    /**
     * Node
     * @var DOMNode
     */
    protected $veicTransp;
    /**
     * Node reboque
     * @var DOMNode
     */
    protected $reboque;
    /**
     * Node infAdic
     * @var DOMNode 
     */
    protected $infAdic;
    /**
     * Tipo de emissão
     * @var integer 
     */
    protected $tpEmis;
    /**
     * Node infProt
     * @var DOMNode 
     */
    protected $infProt;
    /**
     * 1-Retrato/ 2-Paisagem
     * @var integer 
     */
    protected $tpImp;
    /**
     * Node compra
     * @var DOMNode
     */
    protected $compra;
    /**
     * ativa ou desativa o modo de debug
     * @var integer
     */
    protected $debugMode=2;

    /**
     * __construct
     * @name __construct
     * @param string $docXML Conteúdo XML da NF-e (com ou sem a tag nfeProc)
     * @param string $sOrientacao (Opcional) Orientação da impressão P-retrato L-Paisagem
     * @param string $sPapel Tamanho do papel (Ex. A4)
     * @param string $sPathLogo Caminho para o arquivo do logo
     * @param string $sDestino Estabelece a direção do envio do documento PDF I-browser D-browser com download S-
     * @param string $sDirPDF Caminho para o diretorio de armazenamento dos arquivos PDF
     * @param string $fonteDANFE Nome da fonte alternativa do DAnfe
     * @param integer $mododebug 0-Não 1-Sim e 2-nada (2 default)
     */
    public function init(
        $docXML = '',
        $sOrientacao = '',
        $sPapel = '',
        $sPathLogo = '',
        $sDestino = 'I',
        $sDirPDF = '',
        $fonteDANFE = '',
        $mododebug = 2
    ) {
        if (is_numeric($mododebug)) {
            $this->debugMode = $mododebug;
        }
        if ($mododebug == 1) {
            //ativar modo debug
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        }
        if ($mododebug == 0) {
            //desativar modo debug
            error_reporting(0);
            ini_set('display_errors', 'Off');
        }
        $this->orientacao   = 'L';
        $this->papel        = $sPapel;
        $this->pdf          = '';
        $this->xml          = $docXML;
        $this->logomarca    = $sPathLogo;
        $this->destino      = $sDestino;
        $this->pdfDir       = $sDirPDF;
        // verifica se foi passa a fonte a ser usada
        if (empty($fonteDANFE)) {
            $this->fontePadrao = 'Times';
        } else {
            $this->fontePadrao = $fonteDANFE;
        }
        //se for passado o xml
        if (! empty($this->xml)) {
            $this->dom = new DOMDocument('1.0', 'utf-8');
            $this->formatOutput = false;
            $this->preserveWhiteSpace = false;
            if (is_string($this->xml)) {
                $this->dom->loadXML($this->xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            }
            $this->nfeProc    = $this->dom->getElementsByTagName("nfeProc")->item(0);
            $this->infNFe     = $this->dom->getElementsByTagName("infNFe")->item(0);
            $this->ide        = $this->dom->getElementsByTagName("ide")->item(0);
            $this->entrega    = $this->dom->getElementsByTagName("entrega")->item(0);
            $this->retirada   = $this->dom->getElementsByTagName("retirada")->item(0);
            $this->emit       = $this->dom->getElementsByTagName("emit")->item(0);
            $this->dest       = $this->dom->getElementsByTagName("dest")->item(0);
            $this->enderEmit  = $this->dom->getElementsByTagName("enderEmit")->item(0);
            $this->enderDest  = $this->dom->getElementsByTagName("enderDest")->item(0);
            $this->det        = $this->dom->getElementsByTagName("det");
            $this->cobr       = $this->dom->getElementsByTagName("cobr")->item(0);
            $this->dup        = $this->dom->getElementsByTagName('dup');
            $this->ICMSTot    = $this->dom->getElementsByTagName("ICMSTot")->item(0);
            $this->ISSQNtot   = $this->dom->getElementsByTagName("ISSQNtot")->item(0);
            $this->transp     = $this->dom->getElementsByTagName("transp")->item(0);
            $this->transporta = $this->dom->getElementsByTagName("transporta")->item(0);
            $this->veicTransp = $this->dom->getElementsByTagName("veicTransp")->item(0);
            $this->reboque    = $this->dom->getElementsByTagName("reboque")->item(0);
            $this->infAdic    = $this->dom->getElementsByTagName("infAdic")->item(0);
            $this->compra     = $this->dom->getElementsByTagName("compra")->item(0);
            $this->tpEmis     = $this->ide->getElementsByTagName("tpEmis")->item(0)->nodeValue;
            $this->tpImp      = $this->ide->getElementsByTagName("tpImp")->item(0)->nodeValue;
            $this->infProt    = $this->dom->getElementsByTagName("infProt")->item(0);
            //valida se o XML é uma NF-e modelo 55, pois não pode ser 65 (NFC-e)
            if ($this->pSimpleGetValue($this->ide, "mod") != '55') {
                return "O xml do DANFE deve ser uma NF-e modelo 55";
            }
        }
    } //fim __construct

    /**
     * simpleConsistencyCheck
     * @return bool Retorna se o documento se parece com um DANFE (condicao necessaria porem nao suficiente)
    */
    public function simpleConsistencyCheck()
    {
        if ($this->xml == null || $this->infNFe == null || $this->ide == null) {
            return false;
        }
        return true;
    } //fim simpleConsistencyCheck

    /**
     * monta
     *
     * @name monta
     * @param string $orientacao
     * @param string $papel
     * @param string $logoAlign
     * @return string
     */
    public function monta(
        $orientacao = '',
        $papel = 'A4',
        $logoAlign = 'C',
        $situacaoExterna = NFEPHP_SITUACAO_EXTERNA_NONE,
        $classPdf = false,
        $dpecNumReg = ''
    ) {
        return $this->montaDANFE(
            $orientacao,
            $papel,
            $logoAlign,
            $situacaoExterna,
            $classPdf,
            $dpecNumReg
        );
    }//fim monta

    /**
     * printDocument
     *
     * @param string $nome
     * @param string $destino
     * @param string $printer
     * @return object pdf
     */
    public function printDocument($nome = '', $destino = 'I', $printer = '')
    {
        return $this->printDANFE($nome, $destino, $printer);
    }

    /**
     * montaDANFE
     * Monta a DANFE conforme as informações fornecidas para a classe durante sua
     * construção. Constroi DANFEs com até 3 páginas podendo conter até 56 itens.
     * A definição de margens e posições iniciais para a impressão são estabelecidas
     * pelo conteúdo da funçao e podem ser modificados.
     * @param string $orientacao (Opcional) Estabelece a orientação da impressão
     *  (ex. P-retrato), se nada for fornecido será usado o padrão da NFe
     * @param string $papel (Opcional) Estabelece o tamanho do papel (ex. A4)
     * @return string O ID da NFe numero de 44 digitos extraido do arquivo XML
     */
    public function montaDANFE(
        $orientacao = 'L',
        $papel = 'A4',
        $logoAlign = 'C',
        $situacaoExterna = NFEPHP_SITUACAO_EXTERNA_NONE,
        $classPdf = false,
        $depecNumReg = ''
    ) {
        //se a orientação estiver em branco utilizar o padrão estabelecido na NF
        if ($orientacao == '') {
            if ($this->tpImp == '1') {
                $orientacao = 'P';
            } else {
                $orientacao = 'L';
            }
        }
        $this->orientacao = $orientacao;
        $this->pAdicionaLogoPeloCnpj();
        $this->papel = $papel;
        $this->logoAlign = $logoAlign;
        $this->situacao_externa = $situacaoExterna;
        $this->numero_registro_dpec = $depecNumReg;
        //instancia a classe pdf
        if ($classPdf) {
            $this->pdf = $classPdf;
        } else {
            $this->pdf = new PdfNFePHP($this->orientacao, 'mm', $this->papel);
        }
        //margens do PDF, em milímetros. Obs.: a margem direita é sempre igual à
        //margem esquerda. A margem inferior *não* existe na FPDF, é definida aqui
        //apenas para controle se necessário ser maior do que a margem superior
        $margSup = 10;
        $margEsq = 10;
        $margInf = 10;
        // posição inicial do conteúdo, a partir do canto superior esquerdo da página
        $xInic = $margEsq;
        $yInic = $margSup;
        if ($this->orientacao == 'P') {
            if ($papel == 'A4') {
                $maxW = 210;
                $maxH = 297;
            }
        } else {
            if ($papel == 'A4') {
                $maxH = 210;
                $maxW = 297;
                //se paisagem multiplica a largura do canhoto pela quantidade de canhotos
                $this->wCanhoto *= $this->qCanhoto;
            }
        }
        //total inicial de paginas
        $totPag = 1;
        //largura imprimivel em mm: largura da folha menos as margens esq/direita
        $this->wPrint = $maxW-($margEsq*2);
        //comprimento (altura) imprimivel em mm: altura da folha menos as margens
        //superior e inferior
        $this->hPrint = $maxH-$margSup-$margInf;
        // estabelece contagem de paginas
        $this->pdf->AliasNbPages();
        // fixa as margens
        $this->pdf->SetMargins($margEsq, $margSup);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetFillColor(255, 255, 255);
        // inicia o documento
        $this->pdf->Open();
        // adiciona a primeira página
        $this->pdf->AddPage($this->orientacao, $this->papel);
        $this->pdf->SetLineWidth(0.1);
        $this->pdf->SetTextColor(0, 0, 0);

        //##################################################################
        // CALCULO DO NUMERO DE PAGINAS A SEREM IMPRESSAS
        //##################################################################
        //Verificando quantas linhas serão usadas para impressão das duplicatas
        $linhasDup = 0;
        if (($this->dup->length > 0) && ($this->dup->length <= 7)) {
            $linhasDup = 1;
        } elseif (($this->dup->length > 7) && ($this->dup->length <= 14)) {
            $linhasDup = 2;
        } elseif (($this->dup->length > 14) && ($this->dup->length <= 21)) {
            $linhasDup = 3;
        } elseif ($this->dup->length > 21) {   // TODO fmertins 20/08/14: mudar para "else" apenas? E acho que a variavel deveria receber outro valor, ja que esta igual a 3 que dá na mesma da condição anterior, parece ser bug? Talvez atribuir 4 ao inves de 3?
            $linhasDup = 3;
        }
        //verifica se será impressa a linha dos serviços ISSQN
        $linhaISSQN = 0;
        if ((isset($this->ISSQNtot)) && ($this->pSimpleGetValue($this->ISSQNtot, 'vServ') > 0)) {
            $linhaISSQN = 1;
        }
        //calcular a altura necessária para os dados adicionais
        if ($this->orientacao == 'P') {
            $this->wAdic = round($this->wPrint*0.66, 0);
        } else {
            $this->wAdic = round(($this->wPrint-$this->wCanhoto)*0.5, 0);
        }
        $fontProduto = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        $this->textoAdic = '';
        if (isset($this->retirada)) {
            $txRetCNPJ = ! empty($this->retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("CNPJ")->item(0)->nodeValue :
                '';
            $txRetxLgr = ! empty($this->retirada->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("xLgr")->item(0)->nodeValue :
                '';
            $txRetnro = ! empty($this->retirada->getElementsByTagName("nro")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("nro")->item(0)->nodeValue :
                's/n';
            $txRetxCpl = $this->pSimpleGetValue($this->retirada, "xCpl", " - ");
            $txRetxBairro = ! empty($this->retirada->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("xBairro")->item(0)->nodeValue :
                '';
            $txRetxMun = ! empty($this->retirada->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("xMun")->item(0)->nodeValue :
                '';
            $txRetUF = ! empty($this->retirada->getElementsByTagName("UF")->item(0)->nodeValue) ?
                $this->retirada->getElementsByTagName("UF")->item(0)->nodeValue :
                '';
            $this->textoAdic .= "LOCAL DE RETIRADA : ".
                    $txRetCNPJ.
                    '-' .
                    $txRetxLgr .
                    ', ' .
                    $txRetnro .
                    ' ' .
                    $txRetxCpl .
                    ' - ' .
                    $txRetxBairro .
                    ' ' .
                    $txRetxMun .
                    ' - ' .
                    $txRetUF .
                    "\r\n";
        }
        //dados do local de entrega da mercadoria
        if (isset($this->entrega)) {
            $txRetCNPJ = ! empty($this->entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("CNPJ")->item(0)->nodeValue : '';
            $txRetxLgr = ! empty($this->entrega->getElementsByTagName("xLgr")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("xLgr")->item(0)->nodeValue : '';
            $txRetnro = ! empty($this->entrega->getElementsByTagName("nro")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("nro")->item(0)->nodeValue : 's/n';
            $txRetxCpl = $this->pSimpleGetValue($this->entrega, "xCpl", " - ");
            $txRetxBairro = ! empty($this->entrega->getElementsByTagName("xBairro")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("xBairro")->item(0)->nodeValue : '';
            $txRetxMun = ! empty($this->entrega->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("xMun")->item(0)->nodeValue : '';
            $txRetUF = ! empty($this->entrega->getElementsByTagName("UF")->item(0)->nodeValue) ?
                $this->entrega->getElementsByTagName("UF")->item(0)->nodeValue : '';
            if ($this->textoAdic != '') {
                $this->textoAdic .= ". \r\n";
            }
            $this->textoAdic .= "LOCAL DE ENTREGA : ".$txRetCNPJ.'-'.$txRetxLgr.', '.$txRetnro.' '.$txRetxCpl.
               ' - '.$txRetxBairro.' '.$txRetxMun.' - '.$txRetUF."\r\n";
        }
        //informações adicionais
        $this->textoAdic .= $this->pGeraInformacoesDasNotasReferenciadas();
        if (isset($this->infAdic)) {
            $i = 0;
            if ($this->textoAdic != '') {
                $this->textoAdic .= ". \r\n";
            }
            $this->textoAdic .= ! empty($this->infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue) ?
                'Inf. Contribuinte: ' .
                trim($this->pAnfavea($this->infAdic->getElementsByTagName("infCpl")->item(0)->nodeValue)) : '';
            $infPedido = $this->pGeraInformacoesDaTagCompra();
            if ($infPedido != "") {
                $this->textoAdic .= $infPedido;
            }
            $this->textoAdic .= $this->pSimpleGetValue($this->dest, "email", ' Email do Destinatário: ');
            $this->textoAdic .= ! empty($this->infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue) ?
                "\r\n Inf. fisco: " .
                trim($this->infAdic->getElementsByTagName("infAdFisco")->item(0)->nodeValue) : '';
            $obsCont = $this->infAdic->getElementsByTagName("obsCont");
            if (isset($obsCont)) {
                foreach ($obsCont as $obs) {
                    $campo =  $obsCont->item($i)->getAttribute("xCampo");
                    $xTexto = ! empty($obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue) ?
                        $obsCont->item($i)->getElementsByTagName("xTexto")->item(0)->nodeValue : '';
                    $this->textoAdic .= "\r\n" . $campo . ':  ' . trim($xTexto);
                    $i++;
                }
            }
        }
        //INCLUSO pela NT 2013.003 Lei da Transparência
        //verificar se a informação sobre o valor aproximado dos tributos
        //já se encontra no campo de informações adicionais
        if ($this->exibirValorTributos) {
            $flagVTT = strpos(strtolower(trim($this->textoAdic)), 'valor');
            $flagVTT = $flagVTT || strpos(strtolower(trim($this->textoAdic)), 'vl');
            $flagVTT = $flagVTT && strpos(strtolower(trim($this->textoAdic)), 'aprox');
            $flagVTT = $flagVTT && (strpos(strtolower(trim($this->textoAdic)), 'trib') ||
                    strpos(strtolower(trim($this->textoAdic)), 'imp'));
            $vTotTrib = $this->pSimpleGetValue($this->ICMSTot, 'vTotTrib');
            if ($vTotTrib != '' && !$flagVTT) {
                $this->textoAdic .= "\n Valor Aproximado dos Tributos : R$ " . number_format($vTotTrib, 2, ",", ".");
            }
        }
        //fim da alteração NT 2013.003 Lei da Transparência
        $this->textoAdic = str_replace(";", "\n", $this->textoAdic);
        $alinhas = explode("\n", $this->textoAdic);
        $numlinhasdados = 0;
        foreach ($alinhas as $linha) {
            $numlinhasdados += $this->pGetNumLines($linha, $this->wAdic, $fontProduto);
        }
        $hdadosadic = round(($numlinhasdados+3) * $this->pdf->FontSize, 0);
        if ($hdadosadic < 10) {
            $hdadosadic = 10;
        }
        //altura disponivel para os campos da DANFE
        $hcabecalho = 47;//para cabeçalho
        $hdestinatario = 25;//para destinatario
        $hduplicatas = 12;//para cada grupo de 7 duplicatas
        $himposto = 18;// para imposto
        $htransporte = 25;// para transporte
        $hissqn = 11;// para issqn
        $hfooter = 5;// para rodape
        $hCabecItens = 4;//cabeçalho dos itens
        //alturas disponiveis para os dados
        $hDispo1 = $this->hPrint - ($hcabecalho +
            $hdestinatario + ($linhasDup * $hduplicatas) + $himposto + $htransporte +
            ($linhaISSQN * $hissqn) + $hdadosadic + $hfooter + $hCabecItens +
            $this->pSizeExtraTextoFatura());
        if ($this->orientacao == 'P') {
            $hDispo1 -= 23 * $this->qCanhoto;//para canhoto
        } else {
            $hcanhoto = $this->hPrint;//para canhoto
        }
        $hDispo2 = $this->hPrint - ($hcabecalho + $hfooter + $hCabecItens)-4;
        //Contagem da altura ocupada para impressão dos itens
        $fontProduto = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        $i = 0;
        $numlinhas = 0;
        $hUsado = $hCabecItens;
        $w2 = round($this->wPrint*0.31, 0);
        while ($i < $this->det->length) {
            $texto = $this->pDescricaoProduto($this->det->item($i));
            $numlinhas = $this->pGetNumLines($texto, $w2, $fontProduto);
            $hUsado += round(($numlinhas * $this->pdf->FontSize)+1, 0);
            $i++;
        } //fim da soma das areas de itens usadas
        $qtdeItens = $i; //controle da quantidade de itens no DANFE
        if ($hUsado > $hDispo1) {
            //serão necessárias mais paginas
            $hOutras = $hUsado - $hDispo1;
            $totPag = 1 + ceil($hOutras / $hDispo2);
        } else {
            //sera necessaria apenas uma pagina
            $totPag = 1;
        }
        //montagem da primeira página
        $pag = 1;
        $x = $xInic;
        $y = $yInic;
        //coloca o(s) canhoto(s) da NFe
        if ($this->orientacao == 'P') {
            for ($i = 1; $i <= $this->qCanhoto; $i++) {
                $y = $this->pCanhoto($x, $y);
            }
        } else {
            for ($i = 1; $i <= $this->qCanhoto; $i++) {
                $this->pCanhoto($x, $y);
                $x = 25 * $i;
            }
        }
        //coloca o cabeçalho
        $y = $this->pCabecalhoDANFE($x, $y, $pag, $totPag);
        //coloca os dados do destinatário
        $y = $this->pDestinatarioDANFE($x, $y+1);
        //coloca os dados das faturas
        $y = $this->pFaturaDANFE($x, $y+1);
        //coloca os dados dos impostos e totais da NFe
        $y = $this->pImpostoDANFE($x, $y+1);
        //coloca os dados do trasnporte
        $y = $this->pTransporteDANFE($x, $y+1);
        //itens da DANFE
        $nInicial = 0;
        $y = $this->pItensDANFE($x, $y+1, $nInicial, $hDispo1, $pag, $totPag);
        //coloca os dados do ISSQN
        if ($linhaISSQN == 1) {
            $y = $this->pIssqnDANFE($x, $y+4);
        } else {
            $y += 4;
        }
        //coloca os dados adicionais da NFe
        $y = $this->pDadosAdicionaisDANFE($x, $y, $hdadosadic);
        //coloca o rodapé da página
        if ($this->orientacao == 'P') {
            //$this->pRodape($xInic, $y-1);
        } else {
            //$this->pRodape($xInic, $this->hPrint + 8);
        }
        //loop para páginas seguintes
        for ($n = 2; $n <= $totPag; $n++) {
            // fixa as margens
            $this->pdf->SetMargins($margEsq, $margSup);
            //adiciona nova página
            $this->pdf->AddPage($this->orientacao, $this->papel);
            //ajusta espessura das linhas
            $this->pdf->SetLineWidth(0.1);
            //seta a cor do texto para petro
            $this->pdf->SetTextColor(0, 0, 0);
            // posição inicial do relatorio
            $x = $xInic;
            $y = $yInic;
            //coloca o cabeçalho na página adicional
            $y = $this->pCabecalhoDANFE($x, $y, $n, $totPag);
            //coloca os itens na página adicional
            $y = $this->pItensDANFE($x, $y+1, $nInicial, $hDispo2, $pag, $totPag);
            //coloca o rodapé da página
            if ($this->orientacao == 'P') {
                //$this->pRodape($xInic, $y + 4);
            } else {
                //$this->pRodape($xInic, $this->hPrint + 3);
            }
            //se estiver na última página e ainda restar itens para inserir, adiciona mais uma página
            if ($n == $totPag && $this->qtdeItensProc < $qtdeItens) {
                $totPag++;
            }
        }
        //retorna o ID na NFe
        if ($classPdf!==false) {
            $aR = array(
             'id'=>str_replace('NFe', '', $this->infNFe->getAttribute("Id")),
             'classe_PDF'=>$this->pdf);
            return $aR;
        } else {
            return str_replace('NFe', '', $this->infNFe->getAttribute("Id"));
        }
    }//fim da função montaDANFE

    /**
     * anfavea
     * Função para transformar o campo cdata do padrão ANFAVEA para
     * texto imprimível
     * @param string $cdata campo CDATA
     * @return string conteúdo do campo CDATA como string
     */
    private function pAnfavea($cdata = '')
    {
        if ($cdata == '') {
            return '';
        }
        //remove qualquer texto antes ou depois da tag CDATA
        $cdata = str_replace('<![CDATA[', '<CDATA>', $cdata);
        $cdata = str_replace(']]>', '</CDATA>', $cdata);
        $cdata = preg_replace('/\s\s+/', ' ', $cdata);
        $cdata = str_replace("> <", "><", $cdata);
        $len = strlen($cdata);
        $startPos = strpos($cdata, '<');
        if ($startPos === false) {
            return $cdata;
        }
        for ($x=$len; $x>0; $x--) {
            if (substr($cdata, $x, 1) == '>') {
                $endPos = $x;
                break;
            }
        }
        if ($startPos > 0) {
            $parte1 = substr($cdata, 0, $startPos);
        } else {
            $parte1 = '';
        }
        $parte2 = substr($cdata, $startPos, $endPos-$startPos+1);
        if ($endPos < $len) {
            $parte3 = substr($cdata, $endPos + 1, $len - $endPos - 1);
        } else {
            $parte3 = '';
        }
        $texto = trim($parte1).' '.trim($parte3);
        if (strpos($parte2, '<CDATA>') === false) {
            $cdata = '<CDATA>'.$parte2.'</CDATA>';
        } else {
            $cdata = $parte2;
        }
        //carrega o xml CDATA em um objeto DOM
        $dom = new DomDocumentNFePHP();
        $dom->loadXML($cdata, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        //$xml = $dom->saveXML();
        //grupo CDATA infADprod
        $id = $dom->getElementsByTagName('id')->item(0);
        $div = $dom->getElementsByTagName('div')->item(0);
        $entg = $dom->getElementsByTagName('entg')->item(0);
        $dest = $dom->getElementsByTagName('dest')->item(0);
        $ctl = $dom->getElementsByTagName('ctl')->item(0);
        $ref = $dom->getElementsByTagName('ref')->item(0);
        if (isset($id)) {
            if ($id->hasAttributes()) {
                foreach ($id->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($div)) {
            if ($div->hasAttributes()) {
                foreach ($div->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($entg)) {
            if ($entg->hasAttributes()) {
                foreach ($entg->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($dest)) {
            if ($dest->hasAttributes()) {
                foreach ($dest->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($ctl)) {
            if ($ctl->hasAttributes()) {
                foreach ($ctl->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($ref)) {
            if ($ref->hasAttributes()) {
                foreach ($ref->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        //grupo CADATA infCpl
        $t = $dom->getElementsByTagName('transmissor')->item(0);
        $r = $dom->getElementsByTagName('receptor')->item(0);
        $versao = ! empty($dom->getElementsByTagName('versao')->item(0)->nodeValue) ?
            'Versao:'.$dom->getElementsByTagName('versao')->item(0)->nodeValue.' ' : '';
        $especieNF = ! empty($dom->getElementsByTagName('especieNF')->item(0)->nodeValue) ?
            'Especie:'.$dom->getElementsByTagName('especieNF')->item(0)->nodeValue.' ' : '';
        $fabEntrega = ! empty($dom->getElementsByTagName('fabEntrega')->item(0)->nodeValue) ?
            'Entrega:'.$dom->getElementsByTagName('fabEntrega')->item(0)->nodeValue.' ' : '';
        $dca = ! empty($dom->getElementsByTagName('dca')->item(0)->nodeValue) ?
            'dca:'.$dom->getElementsByTagName('dca')->item(0)->nodeValue.' ' : '';
        $texto .= "".$versao.$especieNF.$fabEntrega.$dca;
        if (isset($t)) {
            if ($t->hasAttributes()) {
                $texto .= " Transmissor ";
                foreach ($t->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        if (isset($r)) {
            if ($r->hasAttributes()) {
                $texto .= " Receptor ";
                foreach ($r->attributes as $attr) {
                    $name = $attr->nodeName;
                    $value = $attr->nodeValue;
                    $texto .= " $name : $value";
                }
            }
        }
        return $texto;
    }//fim anfavea

    /**
     * printDANFE
     * Esta função envia a DANFE em PDF criada para o dispositivo informado.
     * O destino da impressão pode ser :
     * I-browser
     * D-browser com download
     * F-salva em um arquivo local com o nome informado
     * S-retorna o documento como uma string e o nome é ignorado.
     * Para enviar o pdf diretamente para uma impressora indique o
     * nome da impressora e o destino deve ser 'S'.
     *
     * @param string $nome Path completo com o nome do arquivo pdf
     * @param string $destino Direção do envio do PDF
     * @param string $printer Identificação da impressora no sistema
     * @return string Caso o destino seja S o pdf é retornado como uma string
     * @todo Rotina de impressão direta do arquivo pdf criado
     */
    public function printDANFE($nome = '', $destino = 'I', $printer = '')
    {
        $arq = $this->pdf->Output($nome, $destino);
        if ($destino == 'S') {
            //aqui pode entrar a rotina de impressão direta
        }
        return $arq;

        /*
           Opção 1 - exemplo de script shell usando acroread
             #!/bin/sh
            if ($# == 2) then
                set printer=$2
            else
                set printer=$PRINTER
            fi
            if ($1 != "") then
                cat ${1} | acroread -toPostScript | lpr -P $printer
                echo ${1} sent to $printer ... OK!
            else
                echo PDF Print: No filename defined!
            fi
            Opção 2 -
            salvar pdf em arquivo temporario
            converter pdf para ps usando pdf2ps do linux
            imprimir ps para printer usando lp ou lpr
            remover os arquivos temporarios pdf e ps
            Opção 3 -
            salvar pdf em arquivo temporario
            imprimir para printer usando lp ou lpr com system do php
            remover os arquivos temporarios pdf
        */
    } //fim função printDANFE


    protected function pNotaCancelada()
    {
        if (!isset($this->nfeProc)) {
            return false;
        }
        //NÃO ERA NECESSÁRIO ESSA FUNÇÃO POIS SÓ SE USA 1
        //VEZ NO ARQUIVO INTEIRO
        $cStat = $this->pSimpleGetValue($this->nfeProc, "cStat");
        return $cStat == '101' ||
                $cStat == '151' ||
                $cStat == '135' ||
                $cStat == '155' ||
                $this->situacao_externa == NFEPHP_SITUACAO_EXTERNA_CANCELADA;
    }

    protected function pNotaDPEC()
    {
        return $this->situacao_externa==NFEPHP_SITUACAO_EXTERNA_DPEC && $this->numero_registro_dpec!='';
    }

    protected function pNotaDenegada()
    {
        if (!isset($this->nfeProc)) {
            return false;
        }
        //NÃO ERA NECESSÁRIO ESSA FUNÇÃO POIS SÓ SE USA
        //1 VEZ NO ARQUIVO INTEIRO
        $cStat = $this->pSimpleGetValue($this->nfeProc, "cStat");
        return $cStat == '110' ||
               $cStat == '301' ||
               $cStat == '302' ||
               $this->situacao_externa==NFEPHP_SITUACAO_EXTERNA_DENEGADA;
    }

    /**
     *cabecalhoDANFE
     * Monta o cabelhalho da DANFE (retrato e paisagem)
     *
     * @param number $x Posição horizontal inicial, canto esquerdo
     * @param number $y Posição vertical inicial, canto superior
     * @param number $pag Número da Página
     * @param number$totPag Total de páginas
     * @return number Posição vertical final
     */
    protected function pCabecalhoDANFE($x = 0, $y = 0, $pag = '1', $totPag = '1')
    {
        $oldX = $x;
        $oldY = $y;
        if ($this->orientacao == 'P') {
              $maxW = $this->wPrint;
        } else {
            if ($pag == 1) { // primeira página
                $maxW = $this->wPrint - $this->wCanhoto;
            } else { // páginas seguintes
                $maxW = $this->wPrint;
            }
        }
        //####################################################################################
        //coluna esquerda identificação do emitente
        $w = round($maxW*0.41, 0);
        if ($this->orientacao == 'P') {
            $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'I');
        } else {
            $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'B');
        }
        $w1 = $w;
        $h=32;
        $oldY += $h;
        $this->pTextBox($x, $y, $w, $h);
        $texto = 'IDENTIFICAÇÃO DO EMITENTE';
        $this->pTextBox($x, $y, $w, 5, $texto, $aFont, 'T', 'C', 0, '');
        //estabelecer o alinhamento
        //pode ser left L, center C, right R, full logo L
        //se for left separar 1/3 da largura para o tamanho da imagem
        //os outros 2/3 serão usados para os dados do emitente
        //se for center separar 1/2 da altura para o logo e 1/2 para os dados
        //se for right separa 2/3 para os dados e o terço seguinte para o logo
        //se não houver logo centraliza dos dados do emitente
        // coloca o logo
        if (is_file($this->logomarca)) {
            $logoInfo=getimagesize($this->logomarca);
            //largura da imagem em mm
            $logoWmm = ($logoInfo[0]/72)*25.4;
            //altura da imagem em mm
            $logoHmm = ($logoInfo[1]/72)*25.4;
            if ($this->logoAlign=='L') {
                $nImgW = round($w/3, 0);
                $nImgH = round($logoHmm * ($nImgW/$logoWmm), 0);
                $xImg = $x+1;
                $yImg = round(($h-$nImgH)/2, 0)+$y;
                //estabelecer posições do texto
                $x1 = round($xImg + $nImgW +1, 0);
                $y1 = round($h/3+$y, 0);
                $tw = round(2*$w/3, 0);
            } elseif ($this->logoAlign=='C') {
                $nImgH = round($h/2.7, 0);
                $nImgW = round($logoWmm * ($nImgH/$logoHmm), 0);
                $xImg = round(($w-$nImgW)/2+$x, 0);
                $yImg = $y+3;
                $x1 = $x;
                $y1 = round($yImg + $nImgH + 1, 0);
                $tw = $w;
            } elseif ($this->logoAlign=='R') {
                $nImgW = round($w/3, 0);
                $nImgH = round($logoHmm * ($nImgW/$logoWmm), 0);
                $xImg = round($x+($w-(1+$nImgW)), 0);
                $yImg = round(($h-$nImgH)/2, 0)+$y;
                $x1 = $x;
                $y1 = round($h/3+$y, 0);
                $tw = round(2*$w/3, 0);
            } elseif ($this->logoAlign=='F') {
                $nImgH = round($h-5, 0);
                $nImgW = round($logoWmm * ($nImgH/$logoHmm), 0);
                $xImg = round(($w-$nImgW)/2+$x, 0);
                $yImg = $y+3;
                $x1 = $x;
                $y1 = round($yImg + $nImgH + 1, 0);
                $tw = $w;
            }
            $this->pdf->Image($this->logomarca, $xImg, $yImg, $nImgW, $nImgH);
        } else {
            $x1 = $x;
            $y1 = round($h/3+$y, 0);
            $tw = $w;
        }
        // monta as informações apenas se diferente de full logo
        if ($this->logoAlign !== 'F') {
            //Nome emitente
            $aFont = array('font'=>$this->fontePadrao, 'size'=>12, 'style'=>'B');
            $texto = $this->emit->getElementsByTagName("xNome")->item(0)->nodeValue;
            $this->pTextBox($x1, $y1, $tw, 8, $texto, $aFont, 'T', 'C', 0, '');
            //endereço
            $y1 = $y1+5;
            $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'');
            $fone = ! empty($this->enderEmit->getElementsByTagName("fone")->item(0)->nodeValue) ? $this->enderEmit->getElementsByTagName("fone")->item(0)->nodeValue : '';
            $foneLen = strlen($fone);
            if ($foneLen > 0) {
                $fone2 = substr($fone, 0, $foneLen-4);
                $fone1 = substr($fone, 0, $foneLen-8);
                $fone = '(' . $fone1 . ') ' . substr($fone2, -4) . '-' . substr($fone, -4);
            } else {
                $fone = '';
            }
            $lgr = $this->pSimpleGetValue($this->enderEmit, "xLgr");
            $nro = $this->pSimpleGetValue($this->enderEmit, "nro");
            $cpl = $this->pSimpleGetValue($this->enderEmit, "xCpl", " - ");
            $bairro = $this->pSimpleGetValue($this->enderEmit, "xBairro");
            $CEP = $this->pSimpleGetValue($this->enderEmit, "CEP");
            $CEP = $this->pFormat($CEP, "#####-###");
            $mun = $this->pSimpleGetValue($this->enderEmit, "xMun");
            $UF = $this->pSimpleGetValue($this->enderEmit, "UF");
            $texto = $lgr . ", " . $nro . $cpl . "\n" . $bairro . " - "
                    . $CEP . "\n" . $mun . " - " . $UF . " "
                    . "Fone/Fax: " . $fone;
            $this->pTextBox($x1, $y1, $tw, 8, $texto, $aFont, 'T', 'C', 0, '');
        }

        //####################################################################################
        //coluna central Danfe
        $x += $w;
        $w=round($maxW * 0.17, 0);//35;
        $w2 = $w;
        $h = 32;
        $this->pTextBox($x, $y, $w, $h);

        if (! $this->pNotaCancelada()) {
            // A PRINCIPIO NÃO PRECISAVA, POIS A NFE ESTÁ AUTORIZADA,
            // SÓ SE RETIRA O DANFE PARA NOTAS NÃO AUTORIZADAS
            $texto = "DANFE";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>14, 'style'=>'B');
            $this->pTextBox($x, $y+1, $w, $h, $texto, $aFont, 'T', 'C', 0, '');
            $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'');
            $texto = 'Documento Auxiliar da Nota Fiscal Eletrônica';
            $h = 20;
            $this->pTextBox($x, $y+6, $w, $h, $texto, $aFont, 'T', 'C', 0, '', false);
        }

        $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'');
        $texto = '0 - ENTRADA';
        $y1 = $y + 14;
        $h = 8;
        $this->pTextBox($x+2, $y1, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        $texto = '1 - SAÍDA';
        $y1 = $y + 17;
        $this->pTextBox($x+2, $y1, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        //tipo de nF
        $aFont = array('font'=>$this->fontePadrao, 'size'=>12, 'style'=>'B');
        $y1 = $y + 13;
        $h = 7;
        $texto = $this->ide->getElementsByTagName('tpNF')->item(0)->nodeValue;
        $this->pTextBox($x+27, $y1, 5, $h, $texto, $aFont, 'C', 'C', 1, '');
        //numero da NF
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $y1 = $y + 20;
        $numNF = str_pad($this->ide->getElementsByTagName('nNF')->item(0)->nodeValue, 9, "0", STR_PAD_LEFT);
        $numNF = $this->pFormat($numNF, "###.###.###");
        $texto = "Nº. " . $numNF;
        $this->pTextBox($x, $y1, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
        //Série
        $y1 = $y + 23;
        $serie = str_pad($this->ide->getElementsByTagName('serie')->item(0)->nodeValue, 3, "0", STR_PAD_LEFT);
        $texto = "Série " . $serie;
        $this->pTextBox($x, $y1, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
        //numero paginas
        $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'I');
        $y1 = $y + 26;
        $texto = "Folha " . $pag . "/" . $totPag;
        $this->pTextBox($x, $y1, $w, $h, $texto, $aFont, 'C', 'C', 0, '');

        //####################################################################################
        //coluna codigo de barras
        $x += $w;
        $w = ($maxW-$w1-$w2);//85;
        $w3 = $w;
        $h = 32;
        $this->pTextBox($x, $y, $w, $h);
        $this->pdf->SetFillColor(0, 0, 0);
        $chave_acesso = str_replace('NFe', '', $this->infNFe->getAttribute("Id"));
        $bW = 75;
        $bH = 12;
        //codigo de barras
        $this->pdf->Code128($x+(($w-$bW)/2), $y+2, $chave_acesso, $bW, $bH);
        //linhas divisorias
        $this->pdf->Line($x, $y+4+$bH, $x+$w, $y+4+$bH);
        $this->pdf->Line($x, $y+12+$bH, $x+$w, $y+12+$bH);
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $y1 = $y+4+$bH;
        $h = 7;
        $texto = 'CHAVE DE ACESSO';
        $this->pTextBox($x, $y1, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'B');
        $y1 = $y+8+$bH;
        $texto = $this->pFormat($chave_acesso, $this->formatoChave);
        $this->pTextBox($x+2, $y1, $w-2, $h, $texto, $aFont, 'T', 'C', 0, '');
        $y1 = $y+12+$bH;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'');
        $chaveContingencia="";
        if ($this->pNotaDPEC()) {
            $cabecalhoProtoAutorizacao = 'NÚMERO DE REGISTRO DPEC';
        } else {
            $cabecalhoProtoAutorizacao = 'PROTOCOLO DE AUTORIZAÇÃO DE USO';
        }
        if (($this->tpEmis == 2 || $this->tpEmis == 5) && !$this->pNotaDPEC()) {
            $cabecalhoProtoAutorizacao = "DADOS DA NF-E";
            $chaveContingencia = $this->pGeraChaveAdicionalDeContingencia();
            $this->pdf->SetFillColor(0, 0, 0);
            //codigo de barras
            $this->pdf->Code128($x+11, $y1+1, $chaveContingencia, $bW*.9, $bH/2);
        } else {
            $texto = 'Consulta de autenticidade no portal nacional da NF-e';
            $this->pTextBox($x+2, $y1, $w-2, $h, $texto, $aFont, 'T', 'C', 0, '');
            $y1 = $y+16+$bH;
            $texto = 'www.nfe.fazenda.gov.br/portal ou no site da Sefaz Autorizadora';
            $this->pTextBox(
                $x+2,
                $y1,
                $w-2,
                $h,
                $texto,
                $aFont,
                'T',
                'C',
                0,
                'http://www.nfe.fazenda.gov.br/portal ou no site da Sefaz Autorizadora'
            );
        }

        //####################################################################################
        //Dados da NF do cabeçalho
        //natureza da operação
        $texto = 'NATUREZA DA OPERAÇÃO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $w = $w1+$w2;
        $y = $oldY;
        $oldY += $h;
        $x = $oldX;
        $h = 7;
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->ide->getElementsByTagName("natOp")->item(0)->nodeValue;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        $x += $w;
        $w = $w3;
        //PROTOCOLO DE AUTORIZAÇÃO DE USO ou DADOS da NF-E
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $cabecalhoProtoAutorizacao, $aFont, 'T', 'L', 1, '');
        // algumas NFe podem estar sem o protocolo de uso portanto sua existencia deve ser
        // testada antes de tentar obter a informação.
        // NOTA : DANFE sem protocolo deve existir somente no caso de contingência !!!
        // Além disso, existem várias NFes em contingência que eu recebo com protocolo de autorização.
        // Na minha opinião, deveríamos mostra-lo, mas o  manual  da NFe v4.01 diz outra coisa...
        if (($this->tpEmis == 2 || $this->tpEmis == 5) && !$this->pNotaDPEC()) {
            $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'B');
            $texto = $this->pFormat($chaveContingencia, "#### #### #### #### #### #### #### #### ####");
            $cStat = '';
        } else {
            $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
            if ($this->pNotaDPEC()) {
                $texto = $this->numero_registro_dpec;
                $cStat = '';
            } else {
                if (isset($this->nfeProc)) {
                    $texto = ! empty($this->nfeProc->getElementsByTagName("nProt")->item(0)->nodeValue) ?
                            $this->nfeProc->getElementsByTagName("nProt")->item(0)->nodeValue : '';
                    $tsHora = $this->pConvertTime($this->nfeProc->getElementsByTagName("dhRecbto")->item(0)->nodeValue);
                    if ($texto != '') {
                        $texto .= "  -  " . date('d/m/Y H:i:s', $tsHora);
                    }
                    $cStat = $this->nfeProc->getElementsByTagName("cStat")->item(0)->nodeValue;
                } else {
                    $texto = '';
                    $cStat = '';
                }
            }
        }
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //####################################################################################
        //INSCRIÇÃO ESTADUAL
        $w = round($maxW * 0.333, 0);
        $y += $h;
        $oldY += $h;
        $x = $oldX;
        $texto = 'INSCRIÇÃO ESTADUAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->pSimpleGetValue($this->emit, "IE");
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //INSCRIÇÃO ESTADUAL DO SUBST. TRIBUT.
        $x += $w;
        $texto = 'INSCRIÇÃO ESTADUAL DO SUBST. TRIBUT.';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = ! empty($this->emit->getElementsByTagName("IEST")->item(0)->nodeValue) ? $this->emit->getElementsByTagName("IEST")->item(0)->nodeValue : '';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //CNPJ
        $x += $w;
        $w = ($maxW-(2*$w));
        $texto = 'CNPJ';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->emit->getElementsByTagName("CNPJ")->item(0)->nodeValue;
        $texto = $this->pFormat($texto, "##.###.###/####-##");
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');

        //####################################################################################
        //Indicação de NF Homologação, cancelamento e falta de protocolo
        $tpAmb = $this->ide->getElementsByTagName('tpAmb')->item(0)->nodeValue;
        //indicar cancelamento
        if ($this->pNotaCancelada()) {
            //101 Cancelamento
            $x = 10;
            $y = $this->hPrint-130;
            $h = 25;
            $w = $maxW-(2*$x);
            $this->pdf->SetTextColor(90, 90, 90);
            $texto = "NFe CANCELADA";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $this->pdf->SetTextColor(0, 0, 0);
        }

        if ($this->pNotaDPEC() || $this->tpEmis == 4) {
            //DPEC
            $x = 10;
            $y = $this->hPrint-130;
            $h = 25;
            $w = $maxW-(2*$x);
            $this->pdf->SetTextColor(200, 200, 200);
            $texto = "DANFE impresso em contingência -\n".
                     "DPEC regularmente recebido pela Receita\n".
                     "Federal do Brasil";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $this->pdf->SetTextColor(0, 0, 0);
        }
        if ($this->pNotaDenegada()) {
            //110 301 302 Denegada
            $x = 10;
            $y = $this->hPrint-130;
            $h = 25;
            $w = $maxW-(2*$x);
            $this->pdf->SetTextColor(90, 90, 90);
            $texto = "NFe USO DENEGADO";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $y += $h;
            $h = 5;
            $w = $maxW-(2*$x);
            if (isset($this->infProt)) {
                $xMotivo = $this->infProt->getElementsByTagName("xMotivo")->item(0)->nodeValue;
            } else {
                $xMotivo = '';
            }
            $texto = "SEM VALOR FISCAL\n".$xMotivo;
            $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $this->pdf->SetTextColor(0, 0, 0);
        }
        //indicar sem valor
        if ($tpAmb != 1) {
            $x = 10;
            if ($this->orientacao == 'P') {
                $y = round($this->hPrint*2/3, 0);
            } else {
                $y = round($this->hPrint/2, 0);
            }
            $h = 5;
            $w = $maxW-(2*$x);
            $this->pdf->SetTextColor(90, 90, 90);
            $texto = "SEM VALOR FISCAL";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $aFont = array('font'=>$this->fontePadrao, 'size'=>30, 'style'=>'B');
            $texto = "AMBIENTE DE HOMOLOGAÇÃO";
            $this->pTextBox($x, $y+14, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            $this->pdf->SetTextColor(0, 0, 0);
        } else {
            $x = 10;
            if ($this->orientacao == 'P') {
                $y = round($this->hPrint*2/3, 0);
            } else {
                $y = round($this->hPrint/2, 0);
            }//fim orientacao
            $h = 5;
            $w = $maxW-(2*$x);
            $this->pdf->SetTextColor(90, 90, 90);
            //indicar FALTA DO PROTOCOLO se NFe não for em contingência
            if (($this->tpEmis == 2 || $this->tpEmis == 5) && !$this->pNotaDPEC()) {
                //Contingência
                $texto = "DANFE Emitido em Contingência";
                $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
                $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
                $aFont = array('font'=>$this->fontePadrao, 'size'=>30, 'style'=>'B');
                $texto = "devido à problemas técnicos";
                $this->pTextBox($x, $y+12, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
            } else {
                if (!isset($this->nfeProc)) {
                    if (!$this->pNotaDPEC()) {
                        $texto = "SEM VALOR FISCAL";
                        $aFont = array('font'=>$this->fontePadrao, 'size'=>48, 'style'=>'B');
                        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
                    }
                    $aFont = array('font'=>$this->fontePadrao, 'size'=>30, 'style'=>'B');
                    $texto = "FALTA PROTOCOLO DE APROVAÇÃO DA SEFAZ";
                    if (!$this->pNotaDPEC()) {
                        $this->pTextBox($x, $y+12, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
                    } else {
                        $this->pTextBox($x, $y+25, $w, $h, $texto, $aFont, 'C', 'C', 0, '');
                    }
                }//fim nefProc
            }//fim tpEmis
            $this->pdf->SetTextColor(0, 0, 0);
        }
        return $oldY;
    } //fim cabecalhoDANFE

    /**
     * destinatarioDANFE
     * Monta o campo com os dados do destinatário na DANFE. (retrato e paisagem)
     * @name destinatarioDANFE
     * @param number $x Posição horizontal canto esquerdo
     * @param number $y Posição vertical canto superior
     * @return number Posição vertical final
     */
    protected function pDestinatarioDANFE($x = 0, $y = 0)
    {
        //####################################################################################
        //DESTINATÁRIO / REMETENTE
        $oldX = $x;
        $oldY = $y;
        if ($this->orientacao == 'P') {
            $maxW = $this->wPrint;
        } else {
            $maxW = $this->wPrint - $this->wCanhoto;
        }
        $w = $maxW;
        $h = 7;
        $texto = 'DESTINATÁRIO / REMETENTE';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        //NOME / RAZÃO SOCIAL
        $w = round($maxW*0.61, 0);
        $w1 = $w;
        $y += 3;
        $texto = 'NOME / RAZÃO SOCIAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("xNome")->item(0)->nodeValue;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        if ($this->orientacao == 'P') {
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'L', 0, '');
        } else {
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'L', 1, '');
        }
        //CNPJ / CPF
        $x += $w;
        $w = round($maxW*0.23, 0);
        $w2 = $w;
        $texto = 'CNPJ / CPF';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        //Pegando valor do CPF/CNPJ
        if (! empty($this->dest->getElementsByTagName("CNPJ")->item(0)->nodeValue)) {
            $texto = $this->pFormat(
                $this->dest->getElementsByTagName("CNPJ")->item(0)->nodeValue,
                "###.###.###/####-##"
            );
        } else {
            $texto = ! empty($this->dest->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                    $this->pFormat(
                        $this->dest->getElementsByTagName("CPF")->item(0)->nodeValue,
                        "###.###.###-##"
                    ) : '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //DATA DA EMISSÃO
        $x += $w;
        $w = $maxW-($w1+$w2);
        $wx = $w;
        $texto = 'DATA DA EMISSÃO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $dEmi = ! empty($this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue) ?
                $this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue : '';
        if ($dEmi == '') {
            $dEmi = ! empty($this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue) ?
                    $this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue : '';
            $aDemi = explode('T', $dEmi);
            $dEmi = $aDemi[0];
        }
        $texto = $this->pYmd2dmy($dEmi);
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        if ($this->orientacao == 'P') {
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        } else {
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 1, '');
        }
        //ENDEREÇO
        $w = round($maxW*0.47, 0);
        $w1 = $w;
        $y += $h;
        $x = $oldX;
        $texto = 'ENDEREÇO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("xLgr")->item(0)->nodeValue;
        $texto .= ', ' . $this->dest->getElementsByTagName("nro")->item(0)->nodeValue;
        $texto .= $this->pSimpleGetValue($this->dest, "xCpl", " - ");

        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'L', 0, '', true);
        //BAIRRO / DISTRITO
        $x += $w;
        $w = round($maxW*0.21, 0);
        $w2 = $w;
        $texto = 'BAIRRO / DISTRITO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("xBairro")->item(0)->nodeValue;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //CEP
        $x += $w;
        $w = $maxW-$w1-$w2-$wx;
        $w2 = $w;
        $texto = 'CEP';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = ! empty($this->dest->getElementsByTagName("CEP")->item(0)->nodeValue) ?
                $this->dest->getElementsByTagName("CEP")->item(0)->nodeValue : '';
        $texto = $this->pFormat($texto, "#####-###");
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //DATA DA SAÍDA
        $x += $w;
        $w = $wx;
        $texto = 'DATA DA SAÍDA';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $dSaiEnt = ! empty($this->ide->getElementsByTagName("dSaiEnt")->item(0)->nodeValue) ?
                $this->ide->getElementsByTagName("dSaiEnt")->item(0)->nodeValue : '';
        if ($dSaiEnt == '') {
            $dSaiEnt = ! empty($this->ide->getElementsByTagName("dhSaiEnt")->item(0)->nodeValue) ?
                    $this->ide->getElementsByTagName("dhSaiEnt")->item(0)->nodeValue : '';
            $aDsaient = explode('T', $dSaiEnt);
            $dSaiEnt = $aDsaient[0];
        }
        $texto = $this->pYmd2dmy($dSaiEnt);
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //MUNICÍPIO
        $w = $w1;
        $y += $h;
        $x = $oldX;
        $texto = 'MUNICÍPIO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("xMun")->item(0)->nodeValue;
        if (strtoupper(trim($texto)) == "EXTERIOR") {
            $texto .= " - " .  $this->dest->getElementsByTagName("xPais")->item(0)->nodeValue;
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'L', 0, '');
        //UF
        $x += $w;
        $w = 8;
        $texto = 'UF';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("UF")->item(0)->nodeValue;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //FONE / FAX
        $x += $w;
        $w = round(($maxW -$w1-$wx-8)/2, 0);
        $w3 = $w;
        $texto = 'FONE / FAX';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = ! empty($this->dest->getElementsByTagName("fone")->item(0)->nodeValue) ?
                $this->pFormat($this->dest->getElementsByTagName("fone")->item(0)->nodeValue, '(##) ####-####') : '';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //INSCRIÇÃO ESTADUAL
        $x += $w;
        $w = $maxW -$w1-$wx-8-$w3;
        $texto = 'INSCRIÇÃO ESTADUAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $this->dest->getElementsByTagName("IE")->item(0)->nodeValue;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //HORA DA SAÍDA
        $x += $w;
        $w = $wx;
        $texto = 'HORA DA SAÍDA';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = ! empty($this->ide->getElementsByTagName("hSaiEnt")->item(0)->nodeValue) ?
                $this->ide->getElementsByTagName("hSaiEnt")->item(0)->nodeValue:"";
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        return ($y + $h);
    } //fim da função destinatarioDANFE

     /**
     * pGetTextoFatura
     * Gera a String do Texto da Fatura
     * @name getTextoFatura
     * @return a String com o texto ou "";
     */
    protected function pGetTextoFatura()
    {
        if (isset($this->cobr)) {
            $fat = $this->cobr->getElementsByTagName("fat")->item(0);
            if (isset($fat)) {
                $textoIndPag="";
                $indPag = $this->pSimpleGetValue($this->ide, "indPag");
                if ($indPag == 0) {
                    $textoIndPag = "Pagamento à Vista - ";
                } elseif ($indPag == 1) {
                    $textoIndPag = "Pagamento à Prazo - ";
                }
                $nFat = $this->pSimpleGetValue($fat, "nFat", "Fatura: ");
                $vOrig = $this->pSimpleGetValue($fat, "vOrig", " Valor Original: ");
                $vDesc = $this->pSimpleGetValue($fat, "vDesc", " Desconto: ");
                $vLiq = $this->pSimpleGetValue($fat, "vLiq", " Valor Líquido: ");
                $texto = $textoIndPag . $nFat . $vOrig . $vDesc . $vLiq;
                return $texto;
            }
        }
        return "";
    } //fim getTextoFatura

     /**
     * pSizeExtraTextoFatura
     * Calcula o espaço ocupado pelo texto da fatura. Este espaço só é utilizado quando não houver duplicata.
     * @name pSizeExtraTextoFatura
     * @return integer
     */
    protected function pSizeExtraTextoFatura()
    {
        $textoFatura = $this->pGetTextoFatura();
        //verificar se existem duplicatas
        if ($this->dup->length == 0 && $textoFatura !== "") {
            return 10;
        }
        return 0;
    }

    /**
     * faturaDANFE
     * Monta o campo de duplicatas da DANFE (retrato e paisagem)
     * @name faturaDANFE
     * @param number $x Posição horizontal canto esquerdo
     * @param number $y Posição vertical canto superior
     * @return number Posição vertical final
     */
    protected function pFaturaDANFE($x, $y)
    {
        $linha = 1;
        $h = 8+3;
        $oldx = $x;
        $textoFatura = $this->pGetTextoFatura();
        //verificar se existem duplicatas
        if ($this->dup->length > 0 || $textoFatura !== "") {
            //#####################################################################
            //FATURA / DUPLICATA
            $texto = "FATURA / DUPLICATA";
            if ($this->orientacao == 'P') {
                $w = $this->wPrint;
            } else {
                $w = 271;
            }
            $h = 8;
            $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
            $y += 3;
            $dups = "";
            $dupcont = 0;
            $nFat = $this->dup->length;
            if ($textoFatura !== "" && $this->exibirTextoFatura) {
                $myH=6;
                $myW = $this->wPrint;
                if ($this->orientacao == 'L') {
                    $myW -= $this->wCanhoto;
                }
                $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'');
                $this->pTextBox($x, $y, $myW, $myH, $textoFatura, $aFont, 'C', 'L', 1, '');
                $y+=$myH+1;
            }
            if ($this->orientacao == 'P') {
                $w = round($this->wPrint/7.018, 0)-1;
            } else {
                $w = 28;
            }
            $increm = 1;
            foreach ($this->dup as $k => $d) {
                $nDup = ! empty($this->dup->item($k)->getElementsByTagName('nDup')->item(0)->nodeValue) ?
                        $this->dup->item($k)->getElementsByTagName('nDup')->item(0)->nodeValue : '';
                $dDup = ! empty($this->dup->item($k)->getElementsByTagName('dVenc')->item(0)->nodeValue) ?
                        $this->pYmd2dmy($this->dup->item($k)->getElementsByTagName('dVenc')->item(0)->nodeValue) : '';
                $vDup = ! empty($this->dup->item($k)->getElementsByTagName('vDup')->item(0)->nodeValue) ?
                        'R$ ' . number_format(
                            $this->dup->item($k)->getElementsByTagName('vDup')->item(0)->nodeValue,
                            2,
                            ",",
                            "."
                        ) : '';
                $h = 8;
                $texto = '';
                if ($nDup!='0' && $nDup!='') {
                    $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
                    $this->pTextBox($x, $y, $w, $h, 'Num.', $aFont, 'T', 'L', 1, '');
                    $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
                    $this->pTextBox($x, $y, $w, $h, $nDup, $aFont, 'T', 'R', 0, '');
                } else {
                    $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
                    $this->pTextBox($x, $y, $w, $h, ($dupcont+1)."", $aFont, 'T', 'L', 1, '');
                }
                $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
                $this->pTextBox($x, $y, $w, $h, 'Venc.', $aFont, 'C', 'L', 0, '');
                $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
                $this->pTextBox($x, $y, $w, $h, $dDup, $aFont, 'C', 'R', 0, '');
                $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
                $this->pTextBox($x, $y, $w, $h, 'Valor', $aFont, 'B', 'L', 0, '');
                $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
                $this->pTextBox($x, $y, $w, $h, $vDup, $aFont, 'B', 'R', 0, '');
                $x += $w+$increm;
                $dupcont += 1;
                if ($this->orientacao == 'P') {
                    $maxDupCont = 6;
                } else {
                    $maxDupCont = 8;
                }
                if ($dupcont > $maxDupCont) {
                    $y += 9;
                    $x = $oldx;
                    $dupcont = 0;
                    $linha += 1;
                }
                if ($linha == 5) {
                    $linha = 4;
                    break;
                }
            }
            if ($dupcont == 0) {
                $y -= 9;
                $linha--;
            }
            return ($y+$h);
        } else {
            $linha = 0;
            return ($y-2);
        }
    } //fim da função faturaDANFE

    /**
     * impostoDanfeHelper
     * Auxilia a montagem dos campos de impostos e totais da DANFE
     * @name impostoDanfeHelper
     * @param float $x Posição horizontal canto esquerdo
     * @param float $y Posição vertical canto superior
     * @param float $w Largura do campo
     * @param float $h Altura do campo
     * @param float $h Título do campo
     * @param float $h Valor do imposto
     */
    protected function pImpostoDanfeHelper($x, $y, $w, $h, $titulo, $valorImposto)
    {
        $fontTitulo = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $fontValor = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $titulo, $fontTitulo, 'T', 'L', 1, '');
        $this->pTextBox($x, $y, $w, $h, $valorImposto, $fontValor, 'B', 'R', 0, '');
    }

    /**
     * impostoDANFE
     * Monta o campo de impostos e totais da DANFE (retrato e paisagem)
     * @param number $x Posição horizontal canto esquerdo
     * @param number $y Posição vertical canto superior
     * @return number Posição vertical final
     */
    protected function pImpostoDANFE($x, $y)
    {
        $oldX = $x;
        //#####################################################################
        $texto = "CÁLCULO DO IMPOSTO";
        if ($this->orientacao == 'P') {
            $maxW = $this->wPrint;
            $wPis = 18;
            $w1 = 31;
        } else {
            $maxW = $this->wPrint - $this->wCanhoto;
            $wPis = 20;
            $w1 = 40;
        }
        if (! $this->exibirPIS) {
            $wPis = 0;
            if ($this->orientacao == 'P') {
                $w1+= 2;
            } else {
                $w1+= 3;
            }
        }
        $w= $maxW;
        $w2 = $maxW-(5*$w1+$wPis);
        $w = $w1;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, 8, $texto, $aFont, 'T', 'L', 0, '');
        //BASE DE CÁLCULO DO ICMS
        $y += 3;
        $h = 7;
        $texto = 'BASE DE CÁLCULO DO ICMS';
        $valorImposto = number_format($this->ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue, 2, ",", ".");
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR DO ICMS
        $x += $w;
        $texto = 'VALOR DO ICMS';
        $valorImposto = number_format($this->ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue, 2, ",", ".");
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //BASE DE CÁLCULO DO ICMS S.T.
        $x += $w;
        $texto = 'BASE DE CÁLC. ICMS S.T.';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vBCST")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR DO ICMS SUBSTITUIÇÃO
        $x += $w;
        $texto = 'VALOR DO ICMS SUBST.';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vST")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR II
        $x += $w;
        $texto = 'VALOR IMP. IMPORTAÇÃO';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vII")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vII")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR DO PIS
        if ($this->exibirPIS) {
            $x += $w;
            $w=$wPis;
            $texto = 'VALOR DO PIS';
            $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vPIS")->item(0)->nodeValue) ?
                    number_format(
                        $this->ICMSTot->getElementsByTagName("vPIS")->item(0)->nodeValue,
                        2,
                        ",",
                        "."
                    ) : '0, 00';
        } else {
            $texto = '';
            $valorImposto = '';
        }
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR TOTAL DOS PRODUTOS
        $x += $w;
        $w = $w2;
        $texto = 'VALOR TOTAL DOS PRODUTOS';
        $valorImposto = number_format($this->ICMSTot->getElementsByTagName("vProd")->item(0)->nodeValue, 2, ",", ".");
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //#####################################################################
        //VALOR DO FRETE
        $w = $w1;
        $y += $h;
        $x = $oldX;
        $h = 7;
        $texto = 'VALOR DO FRETE';
        $valorImposto = number_format($this->ICMSTot->getElementsByTagName("vFrete")->item(0)->nodeValue, 2, ",", ".");
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR DO SEGURO
        $x += $w;
        $texto = 'VALOR DO SEGURO';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vSeg")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //DESCONTO
        $x += $w;
        $texto = 'DESCONTO';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vDesc")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //OUTRAS DESPESAS
        $x += $w;
        $texto = 'OUTRAS DESPESAS';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue) ?
                number_format(
                    $this->ICMSTot->getElementsByTagName("vOutro")->item(0)->nodeValue,
                    2,
                    ",",
                    "."
                ) : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR TOTAL DO IPI
        $x += $w;
        $texto = 'VALOR TOTAL DO IPI';
        $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue) ?
                number_format($this->ICMSTot->getElementsByTagName("vIPI")->item(0)->nodeValue, 2, ",", ".") : '0, 00';
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR DO COFINS
        if ($this->exibirPIS) {
            $x += $w;
            $w = $wPis;
            $texto = 'VALOR DA COFINS';
            $valorImposto = ! empty($this->ICMSTot->getElementsByTagName("vCOFINS")->item(0)->nodeValue) ?
                    number_format(
                        $this->ICMSTot->getElementsByTagName("vCOFINS")->item(0)->nodeValue,
                        2,
                        ",",
                        "."
                    ) : '0, 00';
        } else {
            $texto = '';
            $valorImposto = '';
        }
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        //VALOR TOTAL DA NOTA
        $x += $w;
        $w = $w2;
        $texto = 'VALOR TOTAL DA NOTA';
        $valorImposto = number_format($this->ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue, 2, ",", ".");
        $this->pImpostoDanfeHelper($x, $y, $w, $h, $texto, $valorImposto);
        return ($y+$h);
    } //fim impostoDANFE

    /**
     * transporteDANFE
     * Monta o campo de transportes da DANFE (retrato e paisagem)
     * @name transporteDANFE
     * @param float $x Posição horizontal canto esquerdo
     * @param float $y Posição vertical canto superior
     * @return float Posição vertical final
     */
    protected function pTransporteDANFE($x, $y)
    {
        $oldX = $x;
        if ($this->orientacao == 'P') {
            $maxW = $this->wPrint;
        } else {
            $maxW = $this->wPrint - $this->wCanhoto;
        }
        //#####################################################################
        //TRANSPORTADOR / VOLUMES TRANSPORTADOS
        $texto = "TRANSPORTADOR / VOLUMES TRANSPORTADOS";
        $w = $maxW;
        $h = 7;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        //NOME / RAZÃO SOCIAL
        $w1 = $maxW*0.29;
        $y += 3;
        $texto = 'NOME / RAZÃO SOCIAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->transporta)) {
            $texto = ! empty($this->transporta->getElementsByTagName("xNome")->item(0)->nodeValue) ?
                    $this->transporta->getElementsByTagName("xNome")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'B', 'L', 0, '');
        //FRETE POR CONTA
        $x += $w1;
        $w2 = $maxW*0.15;
        $texto = 'FRETE POR CONTA';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        $tipoFrete = ! empty($this->transp->getElementsByTagName("modFrete")->item(0)->nodeValue) ?
                $this->transp->getElementsByTagName("modFrete")->item(0)->nodeValue : '0';
        switch($tipoFrete) {
            case 0:
                $texto = "(0) Emitente";
                break;
            case 1:
                $texto = "(1) Dest/Rem";
                break;
            case 2:
                $texto = "(2) Terceiros";
                break;
            case 9:
                $texto = "(9) Sem Frete";
                break;
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'C', 'C', 1, '');
        //CÓDIGO ANTT
        $x += $w2;
        $texto = 'CÓDIGO ANTT';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->veicTransp)) {
            $texto = ! empty($this->veicTransp->getElementsByTagName("RNTC")->item(0)->nodeValue) ?
                    $this->veicTransp->getElementsByTagName("RNTC")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //PLACA DO VEÍC
        $x += $w2;
        $texto = 'PLACA DO VEÍCULO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->veicTransp)) {
            $texto = ! empty($this->veicTransp->getElementsByTagName("placa")->item(0)->nodeValue) ?
                    $this->veicTransp->getElementsByTagName("placa")->item(0)->nodeValue : '';
        } elseif (isset($this->reboque)) {
            $texto = ! empty($this->reboque->getElementsByTagName("placa")->item(0)->nodeValue) ?
                    $this->reboque->getElementsByTagName("placa")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //UF
        $x += $w2;
        $w3 = round($maxW*0.04, 0);
        $texto = 'UF';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->veicTransp)) {
            $texto = ! empty($this->veicTransp->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $this->veicTransp->getElementsByTagName("UF")->item(0)->nodeValue : '';
        } elseif (isset($this->reboque)) {
            $texto = ! empty($this->reboque->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $this->reboque->getElementsByTagName("UF")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'B', 'C', 0, '');
        //CNPJ / CPF
        $x += $w3;
        $w = $maxW-($w1+3*$w2+$w3);
        $texto = 'CNPJ / CPF';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->transporta)) {
            $texto = ! empty($this->transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue) ?
                    $this->pFormat(
                        $this->transporta->getElementsByTagName("CNPJ")->item(0)->nodeValue,
                        "##.###.###/####-##"
                    ) : '';
            if ($texto == '') {
                $texto = ! empty($this->transporta->getElementsByTagName("CPF")->item(0)->nodeValue) ?
                        $this->pFormat(
                            $this->transporta->getElementsByTagName("CPF")->item(0)->nodeValue,
                            "###.###.###-##"
                        ) : '';
            }
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //#####################################################################
        //ENDEREÇO
        $y += $h;
        $x = $oldX;
        $h = 7;
        $w1 = $maxW*0.44;
        $texto = 'ENDEREÇO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->transporta)) {
            $texto = ! empty($this->transporta->getElementsByTagName("xEnder")->item(0)->nodeValue) ?
                    $this->transporta->getElementsByTagName("xEnder")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'B', 'L', 0, '');
        //MUNICÍPIO
        $x += $w1;
        $w2 = round($maxW*0.30, 0);
        $texto = 'MUNICÍPIO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->transporta)) {
            $texto = ! empty($this->transporta->getElementsByTagName("xMun")->item(0)->nodeValue) ?
                    $this->transporta->getElementsByTagName("xMun")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //UF
        $x += $w2;
        $w3 = round($maxW*0.04, 0);
        $texto = 'UF';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->transporta)) {
            $texto = ! empty($this->transporta->getElementsByTagName("UF")->item(0)->nodeValue) ?
                    $this->transporta->getElementsByTagName("UF")->item(0)->nodeValue : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'B', 'C', 0, '');
        //INSCRIÇÃO ESTADUAL
        $x += $w3;
        $w = $maxW-($w1+$w2+$w3);
        $texto = 'INSCRIÇÃO ESTADUAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = '';
        if (isset($this->transporta)) {
            if (! empty($this->transporta->getElementsByTagName("IE")->item(0)->nodeValue)) {
                $texto = $this->transporta->getElementsByTagName("IE")->item(0)->nodeValue;
            }
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'C', 0, '');
        //Tratar Multiplos volumes
        $volumes = $this->transp->getElementsByTagName('vol');
        $quantidade = 0;
        $especie = '';
        $marca = '';
        $numero = '';
        $texto = '';
        $pesoBruto=0;
        $pesoLiquido=0;
        foreach ($volumes as $volume) {
            $quantidade += ! empty($volume->getElementsByTagName("qVol")->item(0)->nodeValue) ?
                    $volume->getElementsByTagName("qVol")->item(0)->nodeValue : 0;
            $pesoBruto += ! empty($volume->getElementsByTagName("pesoB")->item(0)->nodeValue) ?
                    $volume->getElementsByTagName("pesoB")->item(0)->nodeValue : 0;
            $pesoLiquido += ! empty($volume->getElementsByTagName("pesoL")->item(0)->nodeValue) ?
                    $volume->getElementsByTagName("pesoL")->item(0)->nodeValue : 0;
            $texto = ! empty($this->transp->getElementsByTagName("esp")->item(0)->nodeValue) ?
                    $this->transp->getElementsByTagName("esp")->item(0)->nodeValue : '';
            if ($texto != $especie && $especie != '') {
                //tem várias especies
                $especie = 'VARIAS';
            } else {
                $especie = $texto;
            }
            $texto = ! empty($this->transp->getElementsByTagName("marca")->item(0)->nodeValue) ?
                    $this->transp->getElementsByTagName("marca")->item(0)->nodeValue : '';
            if ($texto != $marca && $marca != '') {
                //tem várias especies
                $marca = 'VARIAS';
            } else {
                $marca = $texto;
            }
            $texto = ! empty($this->transp->getElementsByTagName("nVol")->item(0)->nodeValue) ?
                    $this->transp->getElementsByTagName("nVol")->item(0)->nodeValue : '';
            if ($texto != $numero && $numero != '') {
                //tem várias especies
                $numero = 'VARIOS';
            } else {
                $numero = $texto;
            }
        }

        //#####################################################################
        //QUANTIDADE
        $y += $h;
        $x = $oldX;
        $h = 7;
        $w1 = round($maxW*0.10, 0);
        $texto = 'QUANTIDADE';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $quantidade;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'B', 'C', 0, '');
        //ESPÉCIE
        $x += $w1;
        $w2 = round($maxW*0.17, 0);
        $texto = 'ESPÉCIE';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $especie;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //MARCA
        $x += $w2;
        $texto = 'MARCA';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = ! empty($this->transp->getElementsByTagName("marca")->item(0)->nodeValue) ?
                $this->transp->getElementsByTagName("marca")->item(0)->nodeValue : '';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //NUMERAÇÃO
        $x += $w2;
        $texto = 'NUMERAÇÃO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'T', 'L', 1, '');
        $texto = $numero;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'B', 'C', 0, '');
        //PESO BRUTO
        $x += $w2;
        $w3 = round($maxW*0.20, 0);
        $texto = 'PESO BRUTO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (is_numeric($pesoBruto) && $pesoBruto > 0) {
            $texto = number_format($pesoBruto, 3, ",", ".");
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'B', 'R', 0, '');
        //PESO LÍQUIDO
        $x += $w3;
        $w = $maxW -($w1+3*$w2+$w3);
        $texto = 'PESO LÍQUIDO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (is_numeric($pesoLiquido) && $pesoLiquido > 0) {
            $texto = number_format($pesoLiquido, 3, ",", ".");
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'R', 0, '');
        return ($y+$h);
    } //fim transporteDANFE

    /**
     * descricaoProduto
     * Monta a string de descrição de cada Produto
     * @name descricaoProduto
     * @param DOMNode itemProd
     * @return string descricao do produto
     */
    protected function pDescricaoProduto($itemProd)
    {
        $prod = $itemProd->getElementsByTagName('prod')->item(0);
        $ICMS = $itemProd->getElementsByTagName("ICMS")->item(0);
        $impostos = '';
        if (! empty($ICMS)) {
            $pRedBC = ! empty($ICMS->getElementsByTagName("pRedBC")->item(0)->nodeValue) ?
                    number_format($ICMS->getElementsByTagName("pRedBC")->item(0)->nodeValue, 2, ",", ".") : '';
            if ($pRedBC != 0) {	// redução da base de cáclulo do ICMS
                $impostos .= " pRedBC=$pRedBC%";
            }
            $ivaTxt = ! empty($ICMS->getElementsByTagName("pMVAST")->item(0)->nodeValue) ?
                    number_format($ICMS->getElementsByTagName("pMVAST")->item(0)->nodeValue, 2, ",", ".") : '';
            if ($ivaTxt != '') {
                $impostos = " IVA=$ivaTxt%";
            }
            $icmsStTxt = ! empty($ICMS->getElementsByTagName("pICMSST")->item(0)->nodeValue) ?
                    number_format($ICMS->getElementsByTagName("pICMSST")->item(0)->nodeValue, 2, ",", ".") : '';
            if ($icmsStTxt != '') {
                $impostos .= " pIcmsSt=$icmsStTxt%";
            }
            $bcIcmsSt = ! empty($ICMS->getElementsByTagName("vBCST")->item(0)->nodeValue) ?
                    number_format($ICMS->getElementsByTagName("vBCST")->item(0)->nodeValue, 2, ",", ".") : '';
            if ($bcIcmsSt != '') {
                $impostos .= " BcIcmsSt=$bcIcmsSt";
            }
            $vIcmsSt = ! empty($ICMS->getElementsByTagName("vICMSST")->item(0)->nodeValue) ?
                    number_format($ICMS->getElementsByTagName("vICMSST")->item(0)->nodeValue, 2, ",", ".") : '';
            if ($vIcmsSt != '') {
                $impostos .= " vIcmsSt=$vIcmsSt";
            }
        }
        $infAdProd = ! empty($itemProd->getElementsByTagName('infAdProd')->item(0)->nodeValue) ?
                substr($this->pAnfavea($itemProd->getElementsByTagName('infAdProd')->item(0)->nodeValue), 0, 500) : '';
        if (! empty($infAdProd)) {
            $infAdProd = trim($infAdProd);
            $infAdProd .= ' ';
        }
        $medTxt='';
        $med = $prod->getElementsByTagName("med");
        if (isset($med)) {
            $i = 0;
            while ($i < $med->length) {
                $medTxt .= $this->pSimpleGetValue($med->item($i), 'nLote', ' Lote: ');
                $medTxt .= $this->pSimpleGetValue($med->item($i), 'qLote', ' Quant: ');
                $medTxt .= $this->pSimpleGetDate($med->item($i), 'dFab', ' Fab: ');
                $medTxt .= $this->pSimpleGetDate($med->item($i), 'dVal', ' Val: ');
                $medTxt .= $this->pSimpleGetValue($med->item($i), 'vPMC', ' PMC: ');
                $i++;
            }
            if ($medTxt != '') {
                $medTxt.= ' ';
            }
        }
        //NT2013.006 FCI
        $nFCI = (! empty($itemProd->getElementsByTagName('nFCI')->item(0)->nodeValue)) ?
                ' FCI:'.$itemProd->getElementsByTagName('nFCI')->item(0)->nodeValue : '';
        $tmp_ad=$infAdProd . ($this->descProdInfoComplemento ? $medTxt . $impostos . $nFCI : '');
        $texto = $prod->getElementsByTagName("xProd")->item(0)->nodeValue . (strlen($tmp_ad)!=0?"\n    ".$tmp_ad:'');
        if ($this->descProdQuebraLinha) {
            $texto = str_replace(";", "\n", $texto);
        }
        return $texto;
    } //fim descricaoProduto

    /**
     * itensDANFE
     * Monta o campo de itens da DANFE (retrato e paisagem)
     * @name itensDANFE
     * @param float $x Posição horizontal canto esquerdo
     * @param float $y Posição vertical canto superior
     * @param float $nInicio Número do item inicial
     * @param float $max Número do item final
     * @param float $hmax Altura máxima do campo de itens em mm
     * @return float Posição vertical final
     */
    protected function pItensDANFE($x, $y, &$nInicio, $hmax, $pag = 0, $totpag = 0)
    {
        $oldX = $x;
        $oldY = $y;
        $totItens = $this->det->length;
        //#####################################################################
        //DADOS DOS PRODUTOS / SERVIÇOS
        $texto = "DADOS DOS PRODUTOS / SERVIÇOS ";
        if ($this->orientacao == 'P') {
            $w = $this->wPrint;
        } else {
            if ($nInicio < 2) { // primeira página
                $w = $this->wPrint - $this->wCanhoto;
            } else { // páginas seguintes
                $w = $this->wPrint;
            }
        }
        $h = 4;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        $y += 3;
        //desenha a caixa dos dados dos itens da NF
        $hmax += 1;
        $texto = '';
        $this->pTextBox($x, $y, $w, $hmax);
        //##################################################################################
        // cabecalho LOOP COM OS DADOS DOS PRODUTOS
        //CÓDIGO PRODUTO
        $texto = "CÓDIGO PRODUTO";
        $w1 = round($w*0.09, 0);
        $h = 4;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w1, $y, $x+$w1, $y+$hmax);
        //DESCRIÇÃO DO PRODUTO / SERVIÇO
        $x += $w1;
        $w2 = round($w*0.31, 0);
        $texto = 'DESCRIÇÃO DO PRODUTO / SERVIÇO';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w2, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w2, $y, $x+$w2, $y+$hmax);
        //NCM/SH
        $x += $w2;
        $w3 = round($w*0.06, 0);
        $texto = 'NCM/SH';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w3, $y, $x+$w3, $y+$hmax);
        //O/CST
        $x += $w3;
        $w4 = round($w*0.04, 0);
        $texto = 'CST';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w4, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w4, $y, $x+$w4, $y+$hmax);
        //CFOP
        $x += $w4;
        $w5 = round($w*0.04, 0);
        $texto = 'CFOP';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w5, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w5, $y, $x+$w5, $y+$hmax);
        //UN
        $x += $w5;
        $w6 = round($w*0.03, 0);
        $texto = 'UN';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w6, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w6, $y, $x+$w6, $y+$hmax);
        //QUANT
        $x += $w6;
        $w7 = round($w*0.07, 0);
        $texto = 'QUANT';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w7, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w7, $y, $x+$w7, $y+$hmax);
        //VALOR UNIT
        $x += $w7;
        $w8 = round($w*0.06, 0);
        $texto = 'VALOR UNIT';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w8, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w8, $y, $x+$w8, $y+$hmax);
        //VALOR TOTAL
        $x += $w8;
        $w9 = round($w*0.06, 0);
        $texto = 'VALOR TOTAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w9, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w9, $y, $x+$w9, $y+$hmax);
        //B.CÁLC ICMS
        $x += $w9;
        $w10 = round($w*0.06, 0);
        $texto = 'B.CÁLC ICMS';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w10, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w10, $y, $x+$w10, $y+$hmax);
        //VALOR ICMS
        $x += $w10;
        $w11 = round($w*0.06, 0);
        $texto = 'VALOR ICMS';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w11, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w11, $y, $x+$w11, $y+$hmax);
        //VALOR IPI
        $x += $w11;
        $w12 = round($w*0.05, 0);
        $texto = 'VALOR IPI';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w12, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w12, $y, $x+$w12, $y+$hmax);
        //ALÍQ. ICMS
        $x += $w12;
        $w13 = round($w*0.035, 0);
        $texto = 'ALÍQ. ICMS';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w13, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($x+$w13, $y, $x+$w13, $y+$hmax);
        //ALÍQ. IPI
        $x += $w13;
        $w14 = $w-($w1+$w2+$w3+$w4+$w5+$w6+$w7+$w8+$w9+$w10+$w11+$w12+$w13);
        $texto = 'ALÍQ. IPI';
        $this->pTextBox($x, $y, $w14, $h, $texto, $aFont, 'C', 'C', 0, '', false);
        $this->pdf->Line($oldX, $y+$h+1, $oldX + $w, $y+$h+1);
        $y += 5;
        //##################################################################################
        // LOOP COM OS DADOS DOS PRODUTOS
        $i = 0;
        $hUsado = 4;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        foreach ($this->det as $d) {
            if ($i >= $nInicio) {
                $thisItem = $this->det->item($i);
                //carrega as tags do item
                $prod = $thisItem->getElementsByTagName("prod")->item(0);
                $imposto = $this->det->item($i)->getElementsByTagName("imposto")->item(0);
                $ICMS = $imposto->getElementsByTagName("ICMS")->item(0);
                $IPI  = $imposto->getElementsByTagName("IPI")->item(0);
                $textoProduto = $this->pDescricaoProduto($thisItem);
                $linhaDescr = $this->pGetNumLines($textoProduto, $w2, $aFont);
                $h = round(($linhaDescr * $this->pdf->FontSize)+1, 0);
                $hUsado += $h;
                if ($hUsado >= $hmax && $i < $totItens) {
                    //ultrapassa a capacidade para uma única página
                    //o restante dos dados serão usados nas proximas paginas
                    $nInicio = $i;
                    break;
                }
                $y_linha=$y+$h;
                // linha entre itens
                $this->pdf->DashedHLine($oldX, $y_linha, $w, 0.1, 120);
                //corrige o x
                $x=$oldX;
                //codigo do produto
                $texto = $prod->getElementsByTagName("cProd")->item(0)->nodeValue;
                $this->pTextBox($x, $y, $w1, $h, $texto, $aFont, 'T', 'C', 0, '');
                $x += $w1;
                //DESCRIÇÃO
                if ($this->orientacao == 'P') {
                    $this->pTextBox($x, $y, $w2, $h, $textoProduto, $aFont, 'T', 'L', 0, '', false);
                } else {
                    $this->pTextBox($x, $y, $w2, $h, $textoProduto, $aFont, 'T', 'C', 0, '', false);
                }
                $x += $w2;
                //NCM
                $texto = ! empty($prod->getElementsByTagName("NCM")->item(0)->nodeValue) ?
                        $prod->getElementsByTagName("NCM")->item(0)->nodeValue : '';
                $this->pTextBox($x, $y, $w3, $h, $texto, $aFont, 'T', 'C', 0, '');
                $x += $w3;
                //CST
                if (isset($ICMS)) {
                    $origem =  $this->pSimpleGetValue($ICMS, "orig");
                    $cst =  $this->pSimpleGetValue($ICMS, "CST");
                    $csosn =  $this->pSimpleGetValue($ICMS, "CSOSN");
                    $texto = $origem.$cst.$csosn;
                    $this->pTextBox($x, $y, $w4, $h, $texto, $aFont, 'T', 'C', 0, '');
                }
                //CFOP
                $x += $w4;
                $texto = $prod->getElementsByTagName("CFOP")->item(0)->nodeValue;
                $this->pTextBox($x, $y, $w5, $h, $texto, $aFont, 'T', 'C', 0, '');
                //Unidade
                $x += $w5;
                $texto = $prod->getElementsByTagName("uCom")->item(0)->nodeValue;
                $this->pTextBox($x, $y, $w6, $h, $texto, $aFont, 'T', 'C', 0, '');
                $x += $w6;
                if ($this->orientacao == 'P') {
                    $alinhamento = 'R';
                } else {
                    $alinhamento = 'C';
                }
                // QTDADE
                $texto = number_format($prod->getElementsByTagName("qCom")->item(0)->nodeValue, 4, ",", ".");
                $this->pTextBox($x, $y, $w7, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                $x += $w7;
                // Valor Unitário
                $texto = number_format($prod->getElementsByTagName("vUnCom")->item(0)->nodeValue, 4, ",", ".");
                $this->pTextBox($x, $y, $w8, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                $x += $w8;
                // Valor do Produto
                $texto = number_format($prod->getElementsByTagName("vProd")->item(0)->nodeValue, 2, ",", ".");
                $this->pTextBox($x, $y, $w9, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                //Valor da Base de calculo
                $x += $w9;
                if (isset($ICMS)) {
                    $texto = ! empty($ICMS->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                            number_format(
                                $ICMS->getElementsByTagName("vBC")->item(0)->nodeValue,
                                2,
                                ",",
                                "."
                            ) : '0, 00';
                    $this->pTextBox($x, $y, $w10, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                }
                //Valor do ICMS
                $x += $w10;
                if (isset($ICMS)) {
                    $texto = ! empty($ICMS->getElementsByTagName("vICMS")->item(0)->nodeValue) ?
                            number_format(
                                $ICMS->getElementsByTagName("vICMS")->item(0)->nodeValue,
                                2,
                                ",",
                                "."
                            ) : '0, 00';
                    $this->pTextBox($x, $y, $w11, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                }
                //Valor do IPI
                $x += $w11;
                if (isset($IPI)) {
                    $texto = ! empty($IPI->getElementsByTagName("vIPI")->item(0)->nodeValue) ?
                            number_format($IPI->getElementsByTagName("vIPI")->item(0)->nodeValue, 2, ",", ".") :'';
                } else {
                    $texto = '';
                }
                $this->pTextBox($x, $y, $w12, $h, $texto, $aFont, 'T', $alinhamento, 0, '');
                // %ICMS
                $x += $w12;
                if (isset($ICMS)) {
                    $texto = ! empty($ICMS->getElementsByTagName("pICMS")->item(0)->nodeValue) ?
                            number_format(
                                $ICMS->getElementsByTagName("pICMS")->item(0)->nodeValue,
                                2,
                                ",",
                                "."
                            ) : '0, 00';
                    $this->pTextBox($x, $y, $w13, $h, $texto, $aFont, 'T', 'C', 0, '');
                }
                //%IPI
                $x += $w13;
                if (isset($IPI)) {
                    $texto = ! empty($IPI->getElementsByTagName("pIPI")->item(0)->nodeValue) ?
                            number_format($IPI->getElementsByTagName("pIPI")->item(0)->nodeValue, 2, ",", ".") : '';
                } else {
                    $texto = '';
                }
                $this->pTextBox($x, $y, $w14, $h, $texto, $aFont, 'T', 'C', 0, '');
                $y += $h;
                $i++;
                //incrementa o controle dos itens processados.
                $this->qtdeItensProc++;
            } else {
                $i++;
            }
        }
        return $oldY+$hmax;
    } // fim itensDANFE

    /**
     * issqnDANFE
     * Monta o campo de serviços do DANFE
     * @name issqnDANFE (retrato e paisagem)
     * @param float $x Posição horizontal canto esquerdo
     * @param float $y Posição vertical canto superior
     * @return float Posição vertical final
     */
    protected function pIssqnDANFE($x, $y)
    {
        $oldX = $x;
        //#####################################################################
        //CÁLCULO DO ISSQN
        $texto = "CÁLCULO DO ISSQN";
        $w = $this->wPrint;
        $h = 7;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 0, '');
        //INSCRIÇÃO MUNICIPAL
        $y += 3;
        $w = round($this->wPrint*0.23, 0);
        $texto = 'INSCRIÇÃO MUNICIPAL';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        //inscrição municipal
        $texto = ! empty($this->emit->getElementsByTagName("IM")->item(0)->nodeValue) ?
                $this->emit->getElementsByTagName("IM")->item(0)->nodeValue : '';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'L', 0, '');
        //VALOR TOTAL DOS SERVIÇOS
        $x += $w;
        $texto = 'VALOR TOTAL DOS SERVIÇOS';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->ISSQNtot)) {
            $texto = ! empty($this->ISSQNtot->getElementsByTagName("vServ")->item(0)->nodeValue) ?
                    $this->ISSQNtot->getElementsByTagName("vServ")->item(0)->nodeValue : '';
            $texto = number_format($texto, 2, ",", ".");
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'R', 0, '');
        //BASE DE CÁLCULO DO ISSQN
        $x += $w;
        $texto = 'BASE DE CÁLCULO DO ISSQN';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->ISSQNtot)) {
            $texto = ! empty($this->ISSQNtot->getElementsByTagName("vBC")->item(0)->nodeValue) ?
                    $this->ISSQNtot->getElementsByTagName("vBC")->item(0)->nodeValue : '';
            $texto = ! empty($texto) ? number_format($texto, 2, ",", ".") : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'R', 0, '');
        //VALOR TOTAL DO ISSQN
        $x += $w;
        if ($this->orientacao == 'P') {
            $w = $this->wPrint - (3 * $w);
        } else {
            $w = $this->wPrint - (3 * $w)-$this->wCanhoto;
        }
        $texto = 'VALOR TOTAL DO ISSQN';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        if (isset($this->ISSQNtot)) {
            $texto = ! empty($this->ISSQNtot->getElementsByTagName("vISS")->item(0)->nodeValue) ?
                    $this->ISSQNtot->getElementsByTagName("vISS")->item(0)->nodeValue : '';
            $texto = ! empty($texto) ? number_format($texto, 2, ",", ".") : '';
        } else {
            $texto = '';
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'B', 'R', 0, '');
        return ($y+$h+1);
    } //fim issqnDANFE

    /**
     *dadosAdicionaisDANFE
     * Coloca o grupo de dados adicionais da NFe. (retrato e paisagem)
     * @name dadosAdicionaisDANFE
     * @param float $x Posição horizontal canto esquerdo
     * @param float $y Posição vertical canto superior
     * @param float $h altura do campo
     * @return float Posição vertical final (eixo Y)
     */
    protected function pDadosAdicionaisDANFE($x, $y, $h)
    {
        //##################################################################################
        //DADOS ADICIONAIS
        $texto = "DADOS ADICIONAIS";
        if ($this->orientacao == 'P') {
              $w = $this->wPrint;
        } else {
              $w = $this->wPrint-$this->wCanhoto;
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'B');
        $this->pTextBox($x, $y, $w, 8, $texto, $aFont, 'T', 'L', 0, '');
        //INFORMAÇÕES COMPLEMENTARES
        $texto = "INFORMAÇÕES COMPLEMENTARES";
        $y += 3;
        $w = $this->wAdic;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        //o texto com os dados adicionais foi obtido na função montaDANFE
        //e carregado em uma propriedade privada da classe
        //$this->wAdic com a largura do campo
        //$this->textoAdic com o texto completo do campo
        $y += 1;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        $this->pTextBox($x, $y+2, $w-2, $h-3, $this->textoAdic, $aFont, 'T', 'L', 0, '', false);
        //RESERVADO AO FISCO
        $texto = "RESERVADO AO FISCO";
        $x += $w;
        $y -= 1;
        if ($this->orientacao == 'P') {
            $w = $this->wPrint-$w;
        } else {
            $w = $this->wPrint-$w-$this->wCanhoto;
        }
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'B');
        $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'T', 'L', 1, '');
        //inserir texto informando caso de contingência
        // 1 - Normal - emissão normal;
        // 2 - Contingência FS - emissão em contingência com impressão do DANFE em Formulário de Segurança;
        // 3 - Contingência SCAN - emissão em contingência no Sistema de Contingência do Ambiente Nacional;
        // 4 - Contingência DPEC - emissão em contingência com envio da Declaração Prévia de Emissão em Contingência;
        // 5 - Contingência FS-DA - emissão em contingência com impressão do DANFE em Formulário de
        //     Segurança para Impressão de Documento Auxiliar de Documento Fiscal Eletrônico (FS-DA);
        // 6 - Contingência SVC-AN
        // 7 - Contingência SVC-RS
        $xJust = $this->pSimpleGetValue($this->ide, 'xJust', 'Justificativa: ');
        $dhCont = $this->pSimpleGetValue($this->ide, 'dhCont', ' Entrada em contingência : ');
        $texto = '';
        switch($this->tpEmis) {
            case 2:
                $texto = 'CONTINGÊNCIA FS' . $dhCont . $xJust;
                break;
            case 3:
                $texto = 'CONTINGÊNCIA SCAN' . $dhCont . $xJust;
                break;
            case 4:
                $texto = 'CONTINGÊNCIA DPEC' . $dhCont . $xJust;
                break;
            case 5:
                $texto = 'CONTINGÊNCIA FSDA' . $dhCont . $xJust;
                break;
            case 6:
                $texto = 'CONTINGÊNCIA SVC-AN' . $dhCont . $xJust;
                break;
            case 7:
                $texto = 'CONTINGÊNCIA SVC-RS' . $dhCont . $xJust;
                break;
        }
        $y += 2;
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        $this->pTextBox($x, $y, $w-2, $h-3, $texto, $aFont, 'T', 'L', 0, '', false);
        return $y+$h;
    } //fim dadosAdicionaisDANFE

    /**
     * pRodape
     * Monta o rodapé no final da DANFE com a data/hora de impressão e informações
     * sobre a API NfePHP
     * @name pRodape
     * @param float $xInic Posição horizontal canto esquerdo
     * @param float $yFinal Posição vertical final para impressão
     * @return void
     */
    protected function pRodape($x, $y)
    {
        $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'I');
        $texto = "Impresso em ". date('d/m/Y') . " as " . date('H:i:s');
        $this->pTextBox($x, $y, $this->wPrint, 0, $texto, $aFont, 'T', 'L', false);
        $texto = "DanfeNFePHP ver. " . $this->version .  "  Powered by NFePHP (GNU/GPLv3 GNU/LGPLv3) © www.nfephp.org";
        $this->pTextBox($x, $y, $this->wPrint, 0, $texto, $aFont, 'T', 'R', false, 'http://www.nfephp.org');
    } //fim pRodape

    /**
     * pCcanhotoDANFE
     * Monta o canhoto da DANFE (retrato e paisagem)
     * @name canhotoDANFE
     * @param number $x Posição horizontal canto esquerdo
     * @param number $y Posição vertical canto superior
     * @return number Posição vertical final
     * 
     * TODO 21/07/14 fmertins: quando orientação L-paisagem, o canhoto está sendo gerado incorretamente
     * 
     */
    protected function pCanhoto($x, $y)
    {
        $oldX = $x;
        $oldY = $y;
        //#################################################################################
        //canhoto
        //identificação do tipo de nf entrada ou saida
        $tpNF = $this->ide->getElementsByTagName('tpNF')->item(0)->nodeValue;
        if ($tpNF == '0') {
            //NFe de Entrada
            $emitente = '';
            $emitente .= $this->dest->getElementsByTagName("xNome")->item(0)->nodeValue . " - ";
            $emitente .= $this->enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue . ", ";
            $emitente .= $this->enderDest->getElementsByTagName("nro")->item(0)->nodeValue . " - ";
            $emitente .= $this->pSimpleGetValue($this->enderDest, "xCpl", " - ", " ");
            $emitente .= $this->enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue . " ";
            $emitente .= $this->enderDest->getElementsByTagName("xMun")->item(0)->nodeValue . "-";
            $emitente .= $this->enderDest->getElementsByTagName("UF")->item(0)->nodeValue . "";
            $destinatario = $this->emit->getElementsByTagName("xNome")->item(0)->nodeValue . " ";
        } else {
            //NFe de Saída
            $emitente = $this->emit->getElementsByTagName("xNome")->item(0)->nodeValue . " ";
            $destinatario = '';
            $destinatario .= $this->dest->getElementsByTagName("xNome")->item(0)->nodeValue . " - ";
            $destinatario .= $this->enderDest->getElementsByTagName("xLgr")->item(0)->nodeValue . ", ";
            $destinatario .= $this->enderDest->getElementsByTagName("nro")->item(0)->nodeValue . " ";
            $destinatario .= $this->pSimpleGetValue($this->enderDest, "xCpl", " - ", " ");
            $destinatario .= $this->enderDest->getElementsByTagName("xBairro")->item(0)->nodeValue . " ";
            $destinatario .= $this->enderDest->getElementsByTagName("xMun")->item(0)->nodeValue . "-";
            $destinatario .= $this->enderDest->getElementsByTagName("UF")->item(0)->nodeValue . " ";
        }
        //identificação do sistema emissor
        //linha separadora do canhoto
        if ($this->orientacao == 'P') {
            $w = round($this->wPrint * 0.81, 0);
        } else {
            //linha separadora do canhoto - 238
            //posicao altura
            $y = $this->wPrint-85;
            //altura
            $w = $this->wPrint-85-24;
        }
        $h = 10;
        //desenha caixa
        $texto = '';
        $aFont = array('font'=>$this->fontePadrao, 'size'=>7, 'style'=>'');
        $aFontSmall = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
        if ($this->orientacao == 'P') {
            $this->pTextBox($x, $y, $w, $h, $texto, $aFont, 'C', 'L', 1, '', false);
        } else {
            $this->pTextBox90($x, $y, $w, $h, $texto, $aFont, 'C', 'L', 1, '', false);
        }
        $numNF = str_pad($this->ide->getElementsByTagName('nNF')->item(0)->nodeValue, 9, "0", STR_PAD_LEFT);
        $serie = str_pad($this->ide->getElementsByTagName('serie')->item(0)->nodeValue, 3, "0", STR_PAD_LEFT);
        $texto = "RECEBEMOS DE ";
        $texto .= $emitente;
        $texto .= " OS PRODUTOS E/OU SERVIÇOS CONSTANTES DA NOTA FISCAL ELETRÔNICA INDICADA ";
        if ($this->orientacao == 'P') {
            $texto .= "ABAIXO";
        } else {
            $texto .= "AO LADO";
        }
        $texto .= ". EMISSÃO: ";
        $dEmi = ! empty($this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue) ?
                $this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue : '';
        if ($dEmi == '') {
            $dEmi = ! empty($this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue) ?
                    $this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue : '';
            $aDemi = explode('T', $dEmi);
            $dEmi = $aDemi[0];
        }
        $texto .= $this->pYmd2dmy($dEmi) ." ";
        $texto .= "VALOR TOTAL: R$ ";
        $texto .= number_format($this->ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue, 2, ",", ".") . " ";
        $texto .= "DESTINATÁRIO: ";
        $texto .= $destinatario;
        if ($this->orientacao == 'P') {
            $this->pTextBox($x, $y, $w-1, $h, $texto, $aFont, 'C', 'L', 0, '', false);
            $x1 = $x + $w;
            $w1 = $this->wPrint - $w;
            $texto = "NF-e";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>14, 'style'=>'B');
            $this->pTextBox($x1, $y, $w1, 18, $texto, $aFont, 'T', 'C', 0, '');
            $texto = "Nº. " . $this->pFormat($numNF, "###.###.###") . " \n";
            $texto .= "Série $serie";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>10, 'style'=>'B');
            $this->pTextBox($x1, $y, $w1, 18, $texto, $aFont, 'C', 'C', 1, '');
            //DATA DE RECEBIMENTO
            $texto = "DATA DE RECEBIMENTO";
            $y += $h;
            $w2 = round($this->wPrint*0.17, 0); //35;
            $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
            $this->pTextBox($x, $y, $w2, 8, $texto, $aFont, 'T', 'L', 1, '');
            //IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR
            $x += $w2;
            $w3 = $w-$w2;
            $texto = "IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR";
            $this->pTextBox($x, $y, $w3, 8, $texto, $aFont, 'T', 'L', 1, '');
            $x = $oldX;
            $y += 9;
            $this->pdf->DashedHLine($x, $y, $this->wPrint, 0.1, 80);
            $y += 2;
            return $y;
        } else {
            $x--;
            $x = $this->pTextBox90($x, $y, $w-1, $h, $texto, $aFontSmall, 'C', 'L', 0, '', false);
            //NUMERO DA NOTA FISCAL LOGO NFE
            $w1 = 16;
            $x1 = $oldX;
            $y = $oldY;
            $texto = "NF-e";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>14, 'style'=>'B');
            $this->pTextBox($x1, $y, $w1, 18, $texto, $aFont, 'T', 'C', 0, '');
            $texto = "Nº.\n" . $this->pFormat($numNF, "###.###.###") . " \n";
            $texto .= "Série $serie";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>8, 'style'=>'B');
            $this->pTextBox($x1, $y, $w1, 18, $texto, $aFont, 'C', 'C', 1, '');
            //DATA DO RECEBIMENTO
            $texto = "DATA DO RECEBIMENTO";
            $y = $this->wPrint-85;
            $x = 12;
            $w2 = round($this->wPrint*0.17, 0); //35;
            $aFont = array('font'=>$this->fontePadrao, 'size'=>6, 'style'=>'');
            $this->pTextBox90($x, $y, $w2, 8, $texto, $aFont, 'T', 'L', 1, '');
            //IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR
            $y -= $w2;
            $w3 = $w-$w2;
            $texto = "IDENTIFICAÇÃO E ASSINATURA DO RECEBEDOR";
            $aFont = array('font'=>$this->fontePadrao, 'size'=>5.7, 'style'=>'');
            $x = $this->pTextBox90($x, $y, $w3, 8, $texto, $aFont, 'T', 'L', 1, '');
            $this->pdf->DashedVLine(23, $oldY, 0.1, $this->wPrint-20, 67);
            return $x;
        }
    } //fim pCanhotoDANFE

    /**
     * pGeraInformacoesDaTagCompra
     * Devolve uma string contendo informação sobre as tag <compra><xNEmp>, <xPed> e <xCont> ou string vazia.
     * Aviso: Esta função não leva em consideração dados na tag xPed do item.
     *
     * @name pGeraInformacoesDaTagCompra
     * @return string com as informacoes dos pedidos.
     */
    protected function pGeraInformacoesDaTagCompra()
    {
        $saida = "";
        if (isset($this->compra)) {
            if (! empty($this->compra->getElementsByTagName("xNEmp")->item(0)->nodeValue)) {
                $saida .= " Nota de Empenho: " . $this->compra->getElementsByTagName("xNEmp")->item(0)->nodeValue;
            }
            if (! empty($this->compra->getElementsByTagName("xPed")->item(0)->nodeValue)) {
                $saida .= " Pedido: " . $this->compra->getElementsByTagName("xPed")->item(0)->nodeValue;
            }
            if (! empty($this->compra->getElementsByTagName("xCont")->item(0)->nodeValue)) {
                $saida .= " Contrato: " . $this->compra->getElementsByTagName("xCont")->item(0)->nodeValue;
            }
        }
        return $saida;
    } // fim geraInformacoesDaTagCompra

    /**
     * pGeraChaveAdicionalDeContingencia
     *
     * @name pGeraChaveAdicionalDeContingencia
     * @return string chave
     */
    protected function pGeraChaveAdicionalDeContingencia()
    {
        //cUF tpEmis CNPJ vNF ICMSp ICMSs DD  DV
        // Quantidade de caracteres  02   01      14  14    01    01  02 01
        $forma  = "%02d%d%s%014d%01d%01d%02d";
        $cUF    = $this->ide->getElementsByTagName('cUF')->item(0)->nodeValue;
        $CNPJ   = "00000000000000" . $this->emit->getElementsByTagName('CNPJ')->item(0)->nodeValue;
        $CNPJ   = substr($CNPJ, -14);
        $vNF    = $this->ICMSTot->getElementsByTagName("vNF")->item(0)->nodeValue * 100;
        $vICMS  = $this->ICMSTot->getElementsByTagName("vICMS")->item(0)->nodeValue;
        if ($vICMS > 0) {
            $vICMS = 1;
        }
        $icmss  = $this->ICMSTot->getElementsByTagName("vBC")->item(0)->nodeValue;
        if ($icmss > 0) {
            $icmss = 1;
        }
        $dEmi = ! empty($this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue) ?
                $this->ide->getElementsByTagName("dEmi")->item(0)->nodeValue : '';
        if ($dEmi == '') {
            $dEmi = ! empty($this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue) ?
                    $this->ide->getElementsByTagName("dhEmi")->item(0)->nodeValue : '';
            $aDemi = explode('T', $dEmi);
            $dEmi = $aDemi[0];
        }
        $dd  = $dEmi;
        $rpos = strrpos($dd, '-');
        $dd  = substr($dd, $rpos +1);
        $chave = sprintf($forma, $cUF, $this->tpEmis, $CNPJ, $vNF, $vICMS, $icmss, $dd);
        $chave = $chave . $this->pModulo11($chave);
        return $chave;
    } //fim geraChaveAdicionalDeContingencia

    /**
     * pGeraInformacoesDasNotasReferenciadas
     * Devolve uma string contendo informação sobre as notas referenciadas. Suporta N notas, eletrônicas ou não
     * Exemplo: NFe Ref.: série: 01 número: 01 emit: 11.111.111/0001-01
     * em 10/2010 [0000 0000 0000 0000 0000 0000 0000 0000 0000 0000 0000]
     *
     * @return string Informacoes a serem adicionadas no rodapé sobre notas referenciadas.
     */
    protected function pGeraInformacoesDasNotasReferenciadas()
    {
        $formaNfeRef = "\r\nNFe Ref.: série:%d número:%d emit:%s em %s [%s]";
        $formaCTeRef = "\r\nCTe Ref.: série:%d número:%d emit:%s em %s [%s]";
        $formaNfRef = "\r\nNF  Ref.: série:%d numero:%d emit:%s em %s modelo: %d";
        $formaECFRef = "\r\nECF Ref.: modelo: %s ECF:%d COO:%d";
        $formaNfpRef = "\r\nNFP Ref.: série:%d número:%d emit:%s em %s modelo: %d IE:%s";
        $saida='';
        $nfRefs = $this->ide->getElementsByTagName('NFref');
        if (empty($nfRefs)) {
            return $saida;
        }
        foreach ($nfRefs as $nfRef) {
            if (empty($nfRef)) {
                continue;
            }
            $refNFe = $nfRef->getElementsByTagName('refNFe');
            foreach ($refNFe as $chave_acessoRef) {
                $chave_acesso = $chave_acessoRef->nodeValue;
                $chave_acessoF = $this->pFormat($chave_acesso, $this->formatoChave);
                $data = substr($chave_acesso, 4, 2)."/20".substr($chave_acesso, 2, 2);
                $cnpj = $this->pFormat(substr($chave_acesso, 6, 14), "##.###.###/####-##");
                $serie  = substr($chave_acesso, 22, 3);
                $numero = substr($chave_acesso, 25, 9);
                $saida .= sprintf($formaNfeRef, $serie, $numero, $cnpj, $data, $chave_acessoF);
            }
            $refNF = $nfRef->getElementsByTagName('refNF');
            foreach ($refNF as $umaRefNFe) {
                $data = $umaRefNFe->getElementsByTagName('AAMM')->item(0)->nodeValue;
                $cnpj = $umaRefNFe->getElementsByTagName('CNPJ')->item(0)->nodeValue;
                $mod = $umaRefNFe->getElementsByTagName('mod')->item(0)->nodeValue;
                $serie = $umaRefNFe->getElementsByTagName('serie')->item(0)->nodeValue;
                $numero = $umaRefNFe->getElementsByTagName('nNF')->item(0)->nodeValue;
                $data = substr($data, 2, 2) . "/20" . substr($data, 0, 2);
                $cnpj = $this->pFormat($cnpj, "##.###.###/####-##");
                $saida .= sprintf($formaNfRef, $serie, $numero, $cnpj, $data, $mod);
            }
            $refCTe = $nfRef->getElementsByTagName('refCTe');
            foreach ($refCTe as $chave_acessoRef) {
                $chave_acesso = $chave_acessoRef->nodeValue;
                $chave_acessoF = $this->pFormat($chave_acesso, $this->formatoChave);
                $data = substr($chave_acesso, 4, 2)."/20".substr($chave_acesso, 2, 2);
                $cnpj = $this->pFormat(substr($chave_acesso, 6, 14), "##.###.###/####-##");
                $serie  = substr($chave_acesso, 22, 3);
                $numero = substr($chave_acesso, 25, 9);
                $saida .= sprintf($formaCTeRef, $serie, $numero, $cnpj, $data, $chave_acessoF);
            }
            $refECF = $nfRef->getElementsByTagName('refECF');
            foreach ($refECF as $umaRefNFe) {
                $mod	= $umaRefNFe->getElementsByTagName('mod')->item(0)->nodeValue;
                $nECF	= $umaRefNFe->getElementsByTagName('nECF')->item(0)->nodeValue;
                $nCOO	= $umaRefNFe->getElementsByTagName('nCOO')->item(0)->nodeValue;
                $saida .= sprintf($formaECFRef, $mod, $nECF, $nCOO);
            }
            $refNFP = $nfRef->getElementsByTagName('refNFP');
            foreach ($refNFP as $umaRefNFe) {
                $data = $umaRefNFe->getElementsByTagName('AAMM')->item(0)->nodeValue;
                $cnpj = ! empty($umaRefNFe->getElementsByTagName('CNPJ')->item(0)->nodeValue) ?
                    $umaRefNFe->getElementsByTagName('CNPJ')->item(0)->nodeValue :
                    '';
                $cpf = ! empty($umaRefNFe->getElementsByTagName('CPF')->item(0)->nodeValue) ?
                        $umaRefNFe->getElementsByTagName('CPF')->item(0)->nodeValue : '';
                $mod = $umaRefNFe->getElementsByTagName('mod')->item(0)->nodeValue;
                $serie = $umaRefNFe->getElementsByTagName('serie')->item(0)->nodeValue;
                $numero = $umaRefNFe->getElementsByTagName('nNF')->item(0)->nodeValue;
                $ie = $umaRefNFe->getElementsByTagName('IE')->item(0)->nodeValue;
                $data = substr($data, 2, 2) . "/20" . substr($data, 0, 2);
                if ($cnpj == '') {
                    $cpf_cnpj = $this->pFormat($cpf, "###.###.###-##");
                } else {
                    $cpf_cnpj = $this->pFormat($cnpj, "##.###.###/####-##");
                }
                $saida .= sprintf($formaNfpRef, $serie, $numero, $cpf_cnpj, $data, $mod, $ie);
            }
        }
        return $saida;
    } // fim geraInformacoesDasNotasReferenciadas
    
    /**
     * pAdicionaLogoPeloCnpj
     * @param none
     * @return none
     */
    protected function pAdicionaLogoPeloCnpj()
    {
        if (!isset($this->logomarca)) {
            return;
        }
        if ($this->logomarca != '') {
            return;
        }
        if (!isset($this->emit)) {
            return;
        }
        //se não foi passado o caminho para o logo procurar diretorio abaixo
        $imgPath = "logos/" . $this->emit->getElementsByTagName("CNPJ")->item(0)->nodeValue . ".jpg";
        if (file_exists($imgPath)) {
            $this->logomarca = $imgPath;
            return;
        }
        //procurar diretorio acima do anterior
        $imgPath = "../" . $imgPath;
        if (file_exists($imgPath)) {
            $this->logomarca = $imgPath;
            return;
        }
        //procurar diretorio acima do anterior
        $imgPath = "../" . $imgPath;
        if (file_exists($imgPath)) {
            $this->logomarca = $imgPath;
            return;
        }
        //procurar diretorio acima do anterior
        $imgPath = "../" . $imgPath;
        if (file_exists($imgPath)) {
            $this->logomarca = $imgPath;
            return;
        }
    }
    
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
     * pGetNumLines
     * Obtem o numero de linhas usadas pelo texto usando a fonte especifidada
     * 
     * @param string $text
     * @param number $width
     * @param array $aFont
     * @return number numero de linhas
     */
    protected function pGetNumLines($text, $width, $aFont = array('font'=>'Times','size'=>8,'style'=>''))
    {
        $text = trim($text);
        $this->pdf->SetFont($aFont['font'], $aFont['style'], $aFont['size']);
        $n = $this->pdf->WordWrap($text, $width-0.2);
        return $n;
    }
    
    /**
     * pTextBox
     * Cria uma caixa de texto com ou sem bordas. Esta função perimite o alinhamento horizontal
     * ou vertical do texto dentro da caixa.
     * Atenção : Esta função é dependente de outras classes de FPDF
     * Ex. $this->pTextBox(2,20,34,8,'Texto',array('fonte'=>$this->fontePadrao,
     * 'size'=>10,'style='B'),'C','L',FALSE,'http://www.nfephp.org')
     *
     * @param number $x Posição horizontal da caixa, canto esquerdo superior
     * @param number $y Posição vertical da caixa, canto esquerdo superior
     * @param number $w Largura da caixa
     * @param number $h Altura da caixa
     * @param string $text Conteúdo da caixa
     * @param array $aFont Matriz com as informações para formatação do texto com fonte, tamanho e estilo
     * @param string $vAlign Alinhamento vertical do texto, T-topo C-centro B-base
     * @param string $hAlign Alinhamento horizontal do texto, L-esquerda, C-centro, R-direita
     * @param boolean $border TRUE ou 1 desenha a borda, FALSE ou 0 Sem borda
     * @param string $link Insere um hiperlink
     * @param boolean $force Se for true força a caixa com uma unica linha 
     * e para isso atera o tamanho do fonte até caber no espaço, 
     * se falso mantem o tamanho do fonte e usa quantas linhas forem necessárias
     * @param number $hmax
     * @param number $vOffSet incremento forçado na na posição Y
     * @return number $height Qual a altura necessária para desenhar esta textBox
     */
    protected function pTextBox(
        $x,
        $y,
        $w,
        $h,
        $text = '',
        $aFont = array('font'=>'Times','size'=>8,'style'=>''),
        $vAlign = 'T',
        $hAlign = 'L',
        $border = 1,
        $link = '',
        $force = true,
        $hmax = 0,
        $vOffSet = 0
    ) {
        $oldY = $y;
        $temObs = false;
        $resetou = false;
        if ($w < 0) {
            return $y;
        }
        if (is_object($text)) {
            $text = '';
        }
        if (is_string($text)) {
            //remover espaços desnecessários
            $text = trim($text);
            //converter o charset para o fpdf
            $text = utf8_decode($text);
        } else {
            $text = (string) $text;
        }
        //desenhar a borda da caixa
        if ($border) {
            $this->pdf->RoundedRect($x, $y, $w, $h, 0.8, '1234', 'D');
        }
        //estabelecer o fonte
        $this->pdf->SetFont($aFont['font'], $aFont['style'], $aFont['size']);
        //calcular o incremento
        $incY = $this->pdf->FontSize; //tamanho da fonte na unidade definida
        if (!$force) {
            //verificar se o texto cabe no espaço
            $n = $this->pdf->WordWrap($text, $w);
        } else {
            $n = 1;
        }
        //calcular a altura do conjunto de texto
        $altText = $incY * $n;
        //separar o texto em linhas
        $lines = explode("\n", $text);
        //verificar o alinhamento vertical
        if ($vAlign == 'T') {
            //alinhado ao topo
            $y1 = $y+$incY;
        }
        if ($vAlign == 'C') {
            //alinhado ao centro
            $y1 = $y + $incY + (($h-$altText)/2);
        }
        if ($vAlign == 'B') {
            //alinhado a base
            $y1 = ($y + $h)-0.5;
        }
        //para cada linha
        foreach ($lines as $line) {
            //verificar o comprimento da frase
            $texto = trim($line);
            $comp = $this->pdf->GetStringWidth($texto);
            if ($force) {
                $newSize = $aFont['size'];
                while ($comp > $w) {
                    //estabelecer novo fonte
                    $this->pdf->SetFont($aFont['font'], $aFont['style'], --$newSize);
                    $comp = $this->pdf->GetStringWidth($texto);
                }
            }
            //ajustar ao alinhamento horizontal
            if ($hAlign == 'L') {
                $x1 = $x+0.5;
            }
            if ($hAlign == 'C') {
                $x1 = $x + (($w - $comp)/2);
            }
            if ($hAlign == 'R') {
                $x1 = $x + $w - ($comp+0.5);
            }
            //escrever o texto
            if ($vOffSet > 0) {
                if ($y1 > ($oldY+$vOffSet)) {
                    if (!$resetou) {
                        $y1 = $oldY;
                        $resetou = true;
                    }
                    $this->pdf->Text($x1, $y1, $texto);
                }
            } else {
                $this->pdf->Text($x1, $y1, $texto);
            }
            //incrementar para escrever o proximo
            $y1 += $incY;
            if (($hmax > 0) && ($y1 > ($y+($hmax-1)))) {
                $temObs = true;
                break;
            }
        }
        return ($y1-$y)-$incY;
    } // fim função __textBox

    /**
     * pTextBox90
     * Cria uma caixa de texto com ou sem bordas. Esta função permite o alinhamento horizontal
     * ou vertical do texto dentro da caixa, rotacionando-o em 90 graus, essa função precisa que
     * a classe PDF contenha a função Rotate($angle,$x,$y);
     * Atenção : Esta função é dependente de outras classes de FPDF
     * Ex. $this->__textBox90(2,20,34,8,'Texto',array('fonte'=>$this->fontePadrao,
     * 'size'=>10,'style='B'),'C','L',FALSE,'http://www.nfephp.org')
     *
     * @param number $x Posição horizontal da caixa, canto esquerdo superior
     * @param number $y Posição vertical da caixa, canto esquerdo superior
     * @param number $w Largura da caixa
     * @param number $h Altura da caixa
     * @param string $text Conteúdo da caixa
     * @param array $aFont Matriz com as informações para formatação do texto com fonte, tamanho e estilo
     * @param string $vAlign Alinhamento vertical do texto, T-topo C-centro B-base
     * @param string $hAlign Alinhamento horizontal do texto, L-esquerda, C-centro, R-direita
     * @param boolean $border TRUE ou 1 desenha a borda, FALSE ou 0 Sem borda
     * @param string $link Insere um hiperlink
     * @param boolean $force Se for true força a caixa com uma unica 
     * linha e para isso atera o tamanho do fonte até caber no espaço, 
     * se falso mantem o tamanho do fonte e usa quantas linhas forem necessárias
     * @param number $hmax
     * @param number $vOffSet incremento forçado na na posição Y
     * @return number $height Qual a altura necessária para desenhar esta textBox
     */
    protected function pTextBox90(
        $x,
        $y,
        $w,
        $h,
        $text = '',
        $aFont = array('font'=>'Times','size'=>8,'style'=>''),
        $vAlign = 'T',
        $hAlign = 'L',
        $border = 1,
        $link = '',
        $force = true,
        $hmax = 0,
        $vOffSet = 0
    ) {
        //Rotacionado
        $this->pdf->Rotate(90, $x, $y);
        $oldY = $y;
        $temObs = false;
        $resetou = false;
        if ($w < 0) {
            return $y;
        }
        if (is_object($text)) {
            $text = '';
        }
        if (is_string($text)) {
            //remover espaços desnecessários
            $text = trim($text);
            //converter o charset para o fpdf
            $text = utf8_decode($text);
        } else {
            $text = (string) $text;
        }
        //desenhar a borda da caixa
        if ($border) {
            $this->pdf->RoundedRect($x, $y, $w, $h, 0.8, '1234', 'D');
        }
        //estabelecer o fonte
        $this->pdf->SetFont($aFont['font'], $aFont['style'], $aFont['size']);
        //calcular o incremento
        $incY = $this->pdf->FontSize; //tamanho da fonte na unidade definida
        if (!$force) {
            //verificar se o texto cabe no espaço
            $n = $this->pdf->WordWrap($text, $w);
        } else {
            $n = 1;
        }
        //calcular a altura do conjunto de texto
        $altText = $incY * $n;
        //separar o texto em linhas
        $lines = explode("\n", $text);
        //verificar o alinhamento vertical
        if ($vAlign == 'T') {
            //alinhado ao topo
            $y1 = $y+$incY;
        }
        if ($vAlign == 'C') {
            //alinhado ao centro
            $y1 = $y + $incY + (($h-$altText)/2);
        }
        if ($vAlign == 'B') {
            //alinhado a base
            $y1 = ($y + $h)-0.5;
        }
        //para cada linha
        foreach ($lines as $line) {
            //verificar o comprimento da frase
            $texto = trim($line);
            $comp = $this->pdf->GetStringWidth($texto);
            if ($force) {
                $newSize = $aFont['size'];
                while ($comp > $w) {
                    //estabelecer novo fonte
                    $this->pdf->SetFont($aFont['font'], $aFont['style'], --$newSize);
                    $comp = $this->pdf->GetStringWidth($texto);
                }
            }
            //ajustar ao alinhamento horizontal
            if ($hAlign == 'L') {
                $x1 = $x+0.5;
            }
            if ($hAlign == 'C') {
                $x1 = $x + (($w - $comp)/2);
            }
            if ($hAlign == 'R') {
                $x1 = $x + $w - ($comp+0.5);
            }
            //escrever o texto
            if ($vOffSet > 0) {
                if ($y1 > ($oldY+$vOffSet)) {
                    if (!$resetou) {
                        $y1 = $oldY;
                        $resetou = true;
                    }
                    $this->pdf->Text($x1, $y1, $texto);
                }
            } else {
                $this->pdf->Text($x1, $y1, $texto);
            }
            //incrementar para escrever o proximo
            $y1 += $incY;
            if (($hmax > 0) && ($y1 > ($y+($hmax-1)))) {
                $temObs = true;
                break;
            }
        }
        //Zerando rotação
        $this->pdf->Rotate(0, $x, $y);
        return ($y1-$y)-$incY;
    }
    
    /**
     * pYmd2dmy
     * Converte datas no formato YMD (ex. 2009-11-02) para o formato brasileiro 02/11/2009)
     * @param string $data Parâmetro extraido da NFe
     * @return string Formatada para apresentação da data no padrão brasileiro
     */
    protected function pYmd2dmy($data = '')
    {
        if ($data == '') {
            return '';
        }
        $needle = "/";
        if (strstr($data, "-")) {
            $needle = "-";
        }
        $dt = explode($needle, $data);
        return "$dt[2]/$dt[1]/$dt[0]";
    }
    
    /**
     * pFormat
     * Função de formatação de strings onde o cerquilha # é um coringa
     * que será substituido por digitos contidos em campo.
     * @param string $campo String a ser formatada
     * @param string $mascara Regra de formatção da string (ex. ##.###.###/####-##)
     * @return string Retorna o campo formatado
     */
    protected function pFormat($campo = '', $mascara = '')
    {
        if ($campo == '' || $mascara == '') {
            return $campo;
        }
        //remove qualquer formatação que ainda exista
        $sLimpo = preg_replace("(/[' '-./ t]/)", '', $campo);
        // pega o tamanho da string e da mascara
        $tCampo = strlen($sLimpo);
        $tMask = strlen($mascara);
        if ($tCampo > $tMask) {
            $tMaior = $tCampo;
        } else {
            $tMaior = $tMask;
        }
        //contar o numero de cerquilhas da mascara
        $aMask = str_split($mascara);
        $z=0;
        $flag=false;
        foreach ($aMask as $letra) {
            if ($letra == '#') {
                $z++;
            }
        }
        if ($z > $tCampo) {
            //o campo é menor que esperado
            $flag=true;
        }
        //cria uma variável grande o suficiente para conter os dados
        $sRetorno = '';
        $sRetorno = str_pad($sRetorno, $tCampo+$tMask, " ", STR_PAD_LEFT);
        //pega o tamanho da string de retorno
        $tRetorno = strlen($sRetorno);
        //se houve entrada de dados
        if ($sLimpo != '' && $mascara !='') {
            //inicia com a posição do ultimo digito da mascara
            $x = $tMask;
            $y = $tCampo;
            $cI = 0;
            for ($i = $tMaior-1; $i >= 0; $i--) {
                if ($cI < $z) {
                    // e o digito da mascara é # trocar pelo digito do campo
                    // se o inicio da string da mascara for atingido antes de terminar
                    // o campo considerar #
                    if ($x > 0) {
                        $digMask = $mascara[--$x];
                    } else {
                        $digMask = '#';
                    }
                    //se o fim do campo for atingido antes do fim da mascara
                    //verificar se é ( se não for não use
                    if ($digMask=='#') {
                        $cI++;
                        if ($y > 0) {
                            $sRetorno[--$tRetorno] = $sLimpo[--$y];
                        } else {
                            //$sRetorno[--$tRetorno] = '';
                        }
                    } else {
                        if ($y > 0) {
                            $sRetorno[--$tRetorno] = $mascara[$x];
                        } else {
                            if ($mascara[$x] =='(') {
                                $sRetorno[--$tRetorno] = $mascara[$x];
                            }
                        }
                        $i++;
                    }
                }
            }
            if (!$flag) {
                if ($mascara[0] != '#') {
                    $sRetorno = '(' . trim($sRetorno);
                }
            }
            return trim($sRetorno);
        } else {
            return '';
        }
    }
    
    /**
     * pConvertTime
     * Converte a imformação de data e tempo contida na NFe
     * 
     * @param string $DH Informação de data e tempo extraida da NFe
     * @return timestamp UNIX Para uso com a funçao date do php
     */
    protected function pConvertTime($DH = '')
    {
        if ($DH == '') {
            return '';
        }
        $aDH = explode('T', $DH);
        $adDH = explode('-', $aDH[0]);
        $inter = explode('-', $aDH[1]);
        $atDH = explode(':', $inter[0]);
        $timestampDH = mktime($atDH[0], $atDH[1], $atDH[2], $adDH[1], $adDH[2], $adDH[0]);
        return $timestampDH;
    }

    /**
     * pFormat
     * Função de formatação de strings onde o cerquilha # é um coringa
     * que será substituido por digitos contidos em campo.
     * @param string $campo String a ser formatada
     * @param string $mascara Regra de formatção da string (ex. ##.###.###/####-##)
     * @return string Retorna o campo formatado
     */
}