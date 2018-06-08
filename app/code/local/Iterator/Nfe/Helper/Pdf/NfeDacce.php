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

require_once(Mage::getBaseDir('lib') . '/fpdf/fpdf.php');
require_once(Mage::getBaseDir('lib') . '/fpdf/PdfNFePHP.class.php');

class Iterator_Nfe_Helper_Pdf_NfeDacce extends Mage_Core_Helper_Abstract {
    //publicas
    public $logoAlign='C'; //alinhamento do logo
    public $yDados=0;
    public $debugMode=0; //ativa ou desativa o modo de debug
    public $aEnd=array();
    //privadas
    protected $pdf; // objeto fpdf()
    protected $xml; // string XML NFe
    protected $logomarca=''; // path para logomarca em jpg
    protected $errMsg=''; // mesagens de erro
    protected $errStatus=FALSE;// status de erro TRUE um erro ocorreu FALSE sem erros
    protected $orientacao='P'; //orientação da DANFE P-Retrato ou L-Paisagem
    protected $papel='A4'; //formato do papel
    protected $destino = 'I'; //destivo do arquivo pdf I-borwser, S-retorna o arquivo, D-força download, F-salva em arquivo local
    protected $pdfDir=''; //diretorio para salvar o pdf com a opção de destino = F
    protected $fontePadrao='Times'; //Nome da Fonte para gerar o DANFE
    protected $version = '0.1.1';
    protected $wPrint; //largura imprimivel
    protected $hPrint; //comprimento imprimivel
    protected $wCanhoto; //largura do canhoto para a formatação paisagem
    protected $formatoChave="#### #### #### #### #### #### #### #### #### #### ####";
    //variaveis da carta de correção
    protected $id;
    protected $chNFe;
    protected $tpAmb;
    protected $cOrgao;
    protected $xCorrecao;
    protected $xCondUso;
    protected $dhEvento;
    protected $cStat;
    protected $xMotivo;
    protected $CNPJDest = '';
    protected $CPFDest = '';
    protected $dhRegEvento;
    protected $nProt;
    //objetos
    private $dom;
    private $procEventoNFe;
    private $evento;
    private $infEvento;
    private $retEvento;
    private $retInfEvento;


   /**
    * __construct
    * @package NFePHP
    * @name __construct
    * @version 1.0.1
    * @param string $docXML String XML do processamento de evento de CC-e
    * @param string $sOrientacao (Opcional) Orientação da impressão P-retrato L-Paisagem
    * @param string $sPapel Tamanho do papel (Ex. A4)
    * @param string $sPathLogo Caminho para o arquivo do logo
    * @param string $sDestino Estabelece a direção do envio do documento PDF I-browser D-browser com download S-
    * @param array $aEnd array com o endereço do emitente
    * @param string $sDirPDF Caminho para o diretorio de armazenamento dos arquivos PDF
    * @param string $fonteDANFE Nome da fonte alternativa do DAnfe
    * @param number $mododebug 0-Não 1-Sim e 2-nada (2 default)
    */
    function init($docXML='', $sOrientacao='',$sPapel='',$sPathLogo='', $sDestino='I', $aEnd='',$sDirPDF='',$fontePDF='',$mododebug=2) {
        if(is_numeric($mododebug)){
            $this->debugMode = $mododebug;
        }
        if($this->debugMode){
            //ativar modo debug
            error_reporting(E_ALL);ini_set('display_errors', 'On');
        } else {
            //desativar modo debug
            error_reporting(0);ini_set('display_errors', 'Off');
        }
        if (is_array($aEnd)){
            $this->aEnd = $aEnd;
        }
        $this->orientacao   = $sOrientacao;
        $this->papel        = $sPapel;
        $this->pdf          = '';
        $this->xml          = $docXML;
        $this->logomarca    = $sPathLogo;
        $this->destino      = $sDestino;
        $this->pdfDir       = $sDirPDF;
        // verifica se foi passa a fonte a ser usada
        if (empty($fontePDF)) {
            $this->fontePadrao = 'Times';
        } else {
            $this->fontePadrao = $fontePDF;
        }
        //se for passado o xml
        if (! empty($this->xml)) {
            $this->dom = new DOMDocument('1.0', 'utf-8');
            $this->formatOutput = false;
            $this->preserveWhiteSpace = false;
            if (is_string($this->xml)) {
                $this->dom->loadXML($this->xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            }
            $this->procEventoNFe = $this->dom->getElementsByTagName("procEventoNFe")->item(0);
            $this->evento        = $this->procEventoNFe->getElementsByTagName("evento")->item(0);
            $this->retEvento     = $this->procEventoNFe->getElementsByTagName("retEvento")->item(0);
            $this->infEvento     = $this->evento->getElementsByTagName("infEvento")->item(0);
            $this->retInfEvento  = $this->retEvento->getElementsByTagName("infEvento")->item(0);
            $tpEvento = $this->infEvento->getElementsByTagName("tpEvento")->item(0)->nodeValue;
            if($tpEvento != '110110'){
                return 'Um evento de CC-e deve ser passado!';
            }
            $this->id = str_replace('ID', '', $this->infEvento->getAttribute("Id"));
            $this->chNFe = $this->infEvento->getElementsByTagName("chNFe")->item(0)->nodeValue;
            $this->tpAmb = $this->infEvento->getElementsByTagName("tpAmb")->item(0)->nodeValue;
            $this->cOrgao = $this->infEvento->getElementsByTagName("cOrgao")->item(0)->nodeValue;
            $this->xCorrecao = $this->infEvento->getElementsByTagName("xCorrecao")->item(0)->nodeValue;
            $this->xCondUso = $this->infEvento->getElementsByTagName("xCondUso")->item(0)->nodeValue;
            $this->dhEvento = $this->infEvento->getElementsByTagName("dhEvento")->item(0)->nodeValue;
            $this->cStat = $this->retInfEvento->getElementsByTagName("cStat")->item(0)->nodeValue;
            $this->xMotivo = $this->retInfEvento->getElementsByTagName("xMotivo")->item(0)->nodeValue;
            $this->CNPJDest = !empty($this->retInfEvento->getElementsByTagName("CNPJDest")->item(0)->nodeValue)? $this->retInfEvento->getElementsByTagName("CNPJDest")->item(0)->nodeValue:'';
            $this->CPFDest =  !empty($this->retInfEvento->getElementsByTagName("CPFDest")->item(0)->nodeValue)? $this->retInfEvento->getElementsByTagName("CPFDest")->item(0)->nodeValue:'';
            $this->dhRegEvento = $this->retInfEvento->getElementsByTagName("dhRegEvento")->item(0)->nodeValue;
            $this->nProt = $this->retInfEvento->getElementsByTagName("nProt")->item(0)->nodeValue;
        }
    }//fim __construct

    /**
     * pBuildDACCE
     */
    private function pBuildDACCE()
    {
        $this->pdf = new PdfNFePHP($this->orientacao, 'mm', $this->papel);
        if( $this->orientacao == 'P' ){
            // margens do PDF
            $margSup = 2;
            $margEsq = 2;
            $margDir = 2;
            // posição inicial do relatorio
            $xInic = 1;
            $yInic = 1;
            if($this->papel =='A4'){ //A4 210x297mm
                $maxW = 210;
                $maxH = 297;
            }
        } else {
            // margens do PDF
            $margSup = 3;
            $margEsq = 3;
            $margDir = 3;
            // posição inicial do relatorio
            $xInic = 5;
            $yInic = 5;
            if($papel =='A4'){ //A4 210x297mm
                $maxH = 210;
                $maxW = 297;
            }
        }//orientação

        //largura imprimivel em mm
        $this->wPrint = $maxW-($margEsq+$xInic);
        //comprimento imprimivel em mm
        $this->hPrint = $maxH-($margSup+$yInic);
        // estabelece contagem de paginas
        $this->pdf->AliasNbPages();
        // fixa as margens
        $this->pdf->SetMargins($margEsq,$margSup,$margDir);
        $this->pdf->SetDrawColor(0,0,0);
        $this->pdf->SetFillColor(255,255,255);
        // inicia o documento
        $this->pdf->Open();
        // adiciona a primeira página
        $this->pdf->AddPage($this->orientacao, $this->papel);
        $this->pdf->SetLineWidth(0.1);
        $this->pdf->SetTextColor(0,0,0);
        //montagem da página
        $pag = 1;
        $x = $xInic;
        $y = $yInic;
        //coloca o cabeçalho
        $y = $this->pHeader($x,$y,$pag);
        //coloca os dados da CCe
        $y = $this->pBody($x,$y+15);
        //coloca os dados da CCe
        //$y = $this->pFooter($x,$y+$this->hPrint-20);


    } //fim pBuildDACCE

    /**
     * pHeader
     * @param type $x
     * @param type $y
     * @param type $pag
     * @return type
     */
    private function pHeader($x,$y,$pag)
    {
        $oldX = $x;
        $oldY = $y;
        $maxW = $this->wPrint;

        //####################################################################################
        //coluna esquerda identificação do emitente
        $w = round($maxW*0.41,0);// 80;
        if( $this->orientacao == 'P' ){
            $aFont = array('font'=>$this->fontePadrao,'size'=>6,'style'=>'I');
        }else{
            $aFont = array('font'=>$this->fontePadrao,'size'=>8,'style'=>'B');
        }
        $w1 = $w;
        $h=32;
        $oldY += $h;
        $this->pTextBox($x,$y,$w,$h);
        $texto = 'IDENTIFICAÇÃO DO EMITENTE';
        $this->pTextBox($x,$y,$w,5,$texto,$aFont,'T','C',0,'');
        if (is_file($this->logomarca)){
            $logoInfo = getimagesize($this->logomarca);
            //largura da imagem em mm
            $logoWmm = ($logoInfo[0]/72)*25.4;
            //altura da imagem em mm
            $logoHmm = ($logoInfo[1]/72)*25.4;
            if ($this->logoAlign=='L'){
                $nImgW = round($w/3,0);
                $nImgH = round($logoHmm * ($nImgW/$logoWmm),0);
                $xImg = $x+1;
                $yImg = round(($h-$nImgH)/2,0)+$y;
                //estabelecer posições do texto
                $x1 = round($xImg + $nImgW +1,0);
                $y1 = round($h/3+$y,0);
                $tw = round(2*$w/3,0);
            }
            if ($this->logoAlign=='C'){
                $nImgH = round($h/3,0);
                $nImgW = round($logoWmm * ($nImgH/$logoHmm),0);
                $xImg = round(($w-$nImgW)/2+$x,0);
                $yImg = $y+3;
                $x1 = $x;
                $y1 = round($yImg + $nImgH + 1,0);
                $tw = $w;
            }
            if($this->logoAlign=='R'){
                $nImgW = round($w/3,0);
                $nImgH = round($logoHmm * ($nImgW/$logoWmm),0);
                $xImg = round($x+($w-(1+$nImgW)),0);
                $yImg = round(($h-$nImgH)/2,0)+$y;
                $x1 = $x;
                $y1 = round($h/3+$y,0);
                $tw = round(2*$w/3,0);
            }
            $this->pdf->Image($this->logomarca, $xImg, $yImg, $nImgW, $nImgH);
        } else {
            $x1 = $x;
            $y1 = round($h/3+$y,0);
            $tw = $w;
        }

        //Nome emitente
        $aFont = array('font'=>$this->fontePadrao,'size'=>12,'style'=>'B');
        $texto = $this->aEnd['razao'];
        $this->pTextBox($x1,$y1,$tw,8,$texto,$aFont,'T','C',0,'');

        //endereço
        $y1 = $y1+6;
        $aFont = array('font'=>$this->fontePadrao,'size'=>8,'style'=>'');
        $lgr = $this->aEnd['logradouro'];
        $nro = $this->aEnd['numero'];
        $cpl = $this->aEnd['complemento'];
        $bairro = $this->aEnd['bairro'];
        $CEP = $this->aEnd['CEP'];
        $CEP = $this->pFormat($CEP,"#####-###");
        $mun = $this->aEnd['municipio'];
        $UF = $this->aEnd['UF'];
        $fone = $this->aEnd['telefone'];
        $email = $this->aEnd['email'];
        $foneLen = strlen($fone);
        if ($foneLen > 0 ){
            $fone2 = substr($fone,0,$foneLen-4);
            $fone1 = substr($fone,0,$foneLen-8);
            $fone = '(' . $fone1 . ') ' . substr($fone2,-4) . '-' . substr($fone,-4);
        } else {
            $fone = '';
        }
        if ($email != ''){
            $email = 'Email: '.$email;
        }
        $texto = $lgr . ", " . $nro . $cpl . "\n" . $bairro . " - " . $CEP . "\n" . $mun . " - " . $UF . " " . $fone . "\n" . $email;
        $this->pTextBox($x1,$y1-2,$tw,8,$texto,$aFont,'T','C',0,'');

        //##################################################

        $w2 = round($maxW - $w,0);
        $x += $w;
        $this->pTextBox($x,$y,$w2,$h);

        $y1 = $y + $h;
        $aFont = array('font'=>$this->fontePadrao,'size'=>16,'style'=>'B');
        $this->pTextBox($x,$y+2,$w2,8,'Representação Gráfica de CC-e',$aFont,'T','C',0,'');

        $aFont = array('font'=>$this->fontePadrao,'size'=>12,'style'=>'I');
        $this->pTextBox($x,$y+7,$w2,8,'(Carta de Correção Eletrônica)',$aFont,'T','C',0,'');

        $texto = 'ID do Evento: '.$this->id;
        $aFont = array('font'=>$this->fontePadrao,'size'=>10,'style'=>'');
        $this->pTextBox($x,$y+15,$w2,8,$texto,$aFont,'T','L',0,'');

        $tsHora = $this->pConvertTime($this->dhEvento);
        $texto = 'Criado em : '. date('d/m/Y   H:i:s',$tsHora);
        $this->pTextBox($x,$y+20,$w2,8,$texto,$aFont,'T','L',0,'');

        $tsHora = $this->pConvertTime($this->dhRegEvento);
        $texto = 'Prococolo: '.$this->nProt.'  -  Registrado na SEFAZ em: '.date('d/m/Y   H:i:s',$tsHora);
        $this->pTextBox($x,$y+25,$w2,8,$texto,$aFont,'T','L',0,'');

        //$cStat;
        //$tpAmb;
        //####################################################

        $x = $oldX;
        $this->pTextBox($x,$y1,$maxW,40);
        $sY = $y1+40;
        $texto = 'De acordo com as determinações legais vigentes, vimos por meio desta comunicar-lhe que a Nota Fiscal, abaixo referenciada, contêm irregularidades que estão destacadas e suas respectivas correções, solicitamos que sejam aplicadas essas correções ao executar seus lançamentos fiscais.';
        $aFont = array('font'=>$this->fontePadrao,'size'=>10,'style'=>'');
        $this->pTextBox($x+5,$y1,$maxW-5,20,$texto,$aFont,'T','L',0,'',false);

        //############################################
        $x = $oldX;
        $y = $y1;
        if ($this->CNPJDest != ''){
            $texto = 'CNPJ do Destinatário: '.$this->pFormat($this->CNPJDest,"##.###.###/####-##");
        }
        if ($this->CPFDest != ''){
            $texto = 'CPF do Destinatário: '.$this->pFormat($this->CPFDest,"###.###.###-##");
        }
        $aFont = array('font'=>$this->fontePadrao,'size'=>12,'style'=>'B');
        $this->pTextBox($x+2,$y+13,$w2,8,$texto,$aFont,'T','L',0,'');

        $numNF = substr($this->chNFe,25,9);
        $serie = substr($this->chNFe,22,3);
        $numNF = $this->pFormat($numNF,"###.###.###");
        $texto = "Nota Fiscal: " . $numNF .'  -   Série: '.$serie;
        $this->pTextBox($x+2,$y+19,$w2,8,$texto,$aFont,'T','L',0,'');

        $bW = 87;
        $bH = 15;
        $x = 55;
        $y = $y1+13;
        $w = $maxW;
        $this->pdf->SetFillColor(0,0,0);
        $this->pdf->Code128($x+(($w-$bW)/2),$y+2,$this->chNFe,$bW,$bH);
        $this->pdf->SetFillColor(255,255,255);
        $y1 = $y+2+$bH;
        $aFont = array('font'=>$this->fontePadrao,'size'=>10,'style'=>'');
        $texto = $this->pFormat($this->chNFe, $this->formatoChave);
        $this->pTextBox($x,$y1,$w-2,$h,$texto,$aFont,'T','C',0,'');

        //$sY += 1;
        $x = $oldX;
        $this->pTextBox($x,$sY,$maxW,15);
        $texto = $this->xCondUso;
        $aFont = array('font'=>$this->fontePadrao,'size'=>8,'style'=>'I');
        $this->pTextBox($x+2,$sY+2,$maxW-2,15,$texto,$aFont,'T','L',0,'',false);

        return $sY+2;
    }// fim pHeader

    /**
     * pBody
     * @param type $x
     * @param int $y
     */
    private function pBody($x,$y)
    {
        $maxW = $this->wPrint;
        $texto = 'CORREÇÕES A SEREM CONSIDERADAS';
        $aFont = array('font'=>$this->fontePadrao,'size'=>10,'style'=>'B');
        $this->pTextBox($x,$y,$maxW,5,$texto,$aFont,'T','L',0,'',false);

        $y += 5;
        $this->pTextBox($x,$y,$maxW,190);
        $texto = str_replace( ";" , PHP_EOL , $this->xCorrecao);
        $aFont = array('font'=>$this->fontePadrao,'size'=>12,'style'=>'B');
        $this->pTextBox($x+2,$y+2,$maxW-2,150,$texto,$aFont,'T','L',0,'',false);
    }//fim pBody

    /**
     * pFooter
     * @param type $x
     * @param type $y
     */
    private function pFooter($x,$y)
    {
        $w = $this->wPrint;
        $texto = "Este documento é uma representação gráfica da CCe e foi impresso apenas para sua informação e não possue validade fiscal.\n A CCe deve ser recebida e mantida em arquivo eletrônico XML e pode ser consultada através dos Portais das SEFAZ.";
        $aFont = array('font'=>$this->fontePadrao,'size'=>10,'style'=>'I');
        $this->pTextBox($x,$y,$w,20,$texto,$aFont,'T','C',0,'',false);

        $y = $this->hPrint -4;
        $texto = "Impresso em  ". date('d/m/Y   H:i:s');
        $w = $this->wPrint-4;
        $aFont = array('font'=>$this->fontePadrao,'size'=>6,'style'=>'I');
        $this->pTextBox($x,$y,$w,4,$texto,$aFont,'T','L',0,'');

        $texto = "DacceNFePHP ver. " . $this->version .  "  Powered by NFePHP (GNU/GPLv3 GNU/LGPLv3) © www.nfephp.org";
        $aFont = array('font'=>$this->fontePadrao,'size'=>6,'style'=>'I');
        $this->pTextBox($x,$y,$w,4,$texto,$aFont,'T','R',0,'http://www.nfephp.org');
    }//fim pFooter

    /**
     * printDACCE
     * @param type $nome
     * @param string $destino
     * @param type $printer
     * @return type
     */
    public function printDACCE($nome='',$destino='I',$printer=''){
        $this->pBuildDACCE();
        $arq = $this->pdf->Output($nome,$destino);
        if ( $destino == 'S' ){
            //aqui pode entrar a rotina de impressão direta
        }
        return $arq;
    }//fim printDACCE
    
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
}