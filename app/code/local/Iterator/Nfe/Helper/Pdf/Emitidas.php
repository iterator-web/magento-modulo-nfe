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

require_once(Mage::getBaseDir('lib') . '/fpdf/fpdf.php');
require_once(Mage::getBaseDir('lib') . '/fpdf/ImprovedFPDF.php');

class Iterator_Nfe_Helper_Pdf_Emitidas extends Mage_Core_Helper_Abstract {
    
    public $pdf;

    public function __construct() {
        $this->init();
    }
    
    private function init() {
        $this->pdf = new ImprovedFPDF('P', 'in');
        $this->pdf->SetFont('Arial', '', 10);
    }

    public function render($emitidas) {
        $pdf    = $this->pdf;
        $k      = $pdf->k;
        $pdf->SetTitle('Documento - Relatório de Notas Fiscais Eletrônicas (NF-e) - '.Mage::getStoreConfig('general/store_information/name'));
        $pdf->SetAuthor(Mage::getStoreConfig('general/store_information/name'));
        $wInner = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $this->addPage($wInner);
        $this->writePreHeader($k, $pdf, $wInner);
        $this->writeHeader($pdf, $k, $wInner);
        $this->writeTitle($wInner, 'primeira');
        $pdf->Ln();
        
        // for utilizado somente para preencher planilha com dados repetidos, em produção apagar o for mantendo somente a chamada do método em "$this->WriteBody($entradas, $wInner);"
        //for($x=0; $x<=15; $x++) {
            $this->WriteBody($emitidas, $wInner);
        //}
        
        $pdf->Ln();
        $pdf->Ln();
        $this->writeFooter();

        $pdf->Output();
    }

    private function writeHeader($pdf, $k, $wInner) {
        $pdf->Rect(0.3, 0.3, $wInner + 0.2, 2);
        $yHeaderRect = $pdf->y + 10 / $k;
        $pdf->SetY($yHeaderRect);
        $pdf->SetFont('', 'B', 11);
        $pdf->CellXp($wInner + 1.6, 'DETALHES DA EMPRESA', 'C', 1);

        $pdf->SetFont('', '', 10);
        $wHeaderCols = $wInner / 3;

        $pdf->setLineHeightPadding(50 / $k);
        $this->labeledText($pdf, '', '', $wHeaderCols);
        $this->labeledText($pdf, 'CNPJ:', Mage::getStoreConfig('general/store_information/cnpj'), $wHeaderCols);
        $this->labeledText($pdf, utf8_encode('Razão:'), Mage::getStoreConfig('general/store_information/razao'), $wHeaderCols, 1);
        $this->labeledText($pdf, utf8_encode('Email:'), Mage::getStoreConfig('general/store_information/email'), $wHeaderCols * 2, 1);
        $this->labeledText($pdf, 'Telefone:', Mage::getStoreConfig('general/store_information/phone'), $wHeaderCols);
        $this->labeledText($pdf, 'Site:', Mage::getStoreConfig('general/store_information/site'), $wHeaderCols, 1);
        $yAboveFone = $pdf->y;
        $this->labeledText($pdf, utf8_encode('Endereço:'), Mage::getStoreConfig('general/store_information/address'), $wHeaderCols * 2, 1, 16 / $k);
        $yAboveAddress = $pdf->y;

        $yRec = ($yAboveFone > $yAboveAddress ? $yAboveFone : $yAboveAddress);
        $pdf->Rect($pdf->lMargin + 1.8, $yHeaderRect, $wInner - 2, $yRec - $yHeaderRect);
    }
    
    private function writeBody($emitidas, $wInner) {
        $pdf = $this->pdf;
        $div   = 25;
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(225, 225, 225);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(130, 130, 130);
        $pdf->SetFont('', 'B');
        $pdf->Cell(12/$div,5/$div,'Código',1,0,'C',true); 
        $pdf->Cell(20/$div,5/$div,'Pedido',1,0,'C',true);
        $pdf->Cell(20/$div,5/$div,'Data',1,0,'C',true);
        $pdf->Cell(20/$div,5/$div,'Tipo',1,0,'C',true);
        $pdf->Cell(15/$div,5/$div,'Série',1,0,'C',true);
        $pdf->Cell(25.4/$div,5/$div,'Número',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor Desconto',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor Frete',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor da NF',1,1,'C',true);
        $emitidasCount = 0;
        $emitidasTotal = count($emitidas);
        foreach ($emitidas as $emitida) {
            if ($pdf->y > $pdf->h - $pdf->tMargin - $pdf->bMargin) {
                $this->novaPagina($pdf, $wInner, $div);
            }
            $pdf->SetFont('', '');
            $pdf->Cell(12/$div,5/$div,$emitida->getNfeId(),1,0,'C',false);
            $pdf->Cell(20/$div,5/$div,$emitida->getPedidoIncrementId(),1,0,'C',false);
            $pdf->Cell(20/$div,5/$div,Mage::helper('core')->formatDate($emitida->getDhRecbto(), 'short'),1,0,'C',false);
            $pdf->Cell(20/$div,5/$div,($emitida->getTpNf() == '0') ? 'Entrada' : 'Saída',1,0,'C',false);
            $pdf->Cell(15/$div,5/$div,$emitida->getSerie(),1,0,'C',false);
            $pdf->Cell(25.4/$div,5/$div,$emitida->getNNf(),1,0,'C',false);
            $pdf->Cell(25/$div,5/$div,Mage::helper('core')->currency($emitida->getVDesc(), true, false),1,0,'C',false);
            $pdf->Cell(25/$div,5/$div,Mage::helper('core')->currency($emitida->getVFrete(), true, false),1,0,'C',false);
            $pdf->Cell(25/$div,5/$div,Mage::helper('core')->currency($emitida->getVNf(), true, false),1,1,'C',false);
            
            $descontoTotal += $emitida->getVDesc();
            $freteTotal += $emitida->getVFrete();
            $nfTotal += $emitida->getVNf();
            $emitidasCount++;
            if($emitidasCount == $emitidasTotal) {
                $pdf->Ln();$pdf->Ln();$pdf->Ln();$pdf->Ln();$pdf->Ln();
                $this->getTotalFinal($pdf, $wInner, $div, $emitidasTotal, $descontoTotal, $freteTotal, $nfTotal);
            }
        }
    }

    private function labeledText(ImprovedFPDF $pdf, $label, $text, $maxW, $ln = 0, $multLines = false) {
        $pdf->saveState();
        $pdf->SetFont('', '');
        $wLabel = $pdf->GetStringWidthXd($label . '  ');
        $pdf->SetFont('', 'B');
        $xLabel = $pdf->x - 0.5;
        $pdf->CellXp($wLabel, $label);
        $pdf->SetFont('', '');
        $maxTextW = $maxW - $wLabel;
        if ($multLines) {
            if (is_float($multLines) || is_int($multLines)) {
                $pdf->setLineHeightPadding($multLines);
            }
            $x = $pdf->x - $wLabel;
            $pdf->MultiCellXp($maxTextW, $text);
            if ($ln === 0) {
                $pdf->SetX($x + $maxTextW);
            } else if ($ln == 1) {
                $pdf->SetX($pdf->lMargin);
            } else if ($ln == 2) {
                $pdf->SetX($x);
            }
        } else {
            while ($text && $maxTextW < $pdf->GetStringWidth($text)) {
                $text = substr($text, 0, strlen($text) - 1);
            }
            $pdf->CellXp($maxTextW, $text, '', $ln);
            if ($ln == 2) {
                $pdf->x -= $wLabel;
            }
        }
        $lastX = $pdf->x + 2;
        $lastY = $pdf->y;
        $pdf->restoreLastState();
        if ($ln === 0) {
            $pdf->SetX($xLabel + $maxW);
        } else if ($ln === 1) {
            $pdf->SetXY($lastX, $lastY);
        } else if ($ln === 2) {
            $pdf->SetXY($lastX, $lastY);
        }
    }

    private function writePreHeader($k, $pdf, $wInner) {
        $logoEmpresa = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/images/iterator/report/logo.png';
        $wLogo        = 110 / $k;
        $lPosLogo     = $pdf->x;
        $pdf->Image($logoEmpresa, $lPosLogo, null, $wLogo);
        $lPosTitle = $lPosLogo + $wLogo;
        $pdf->SetXY($lPosTitle, $pdf->tMargin);
        $pdf->SetFont('', 'B', 15);
        $pdf->CellXp($wInner - $wLogo, Mage::getStoreConfig('general/store_information/name'), 'C', 1, 23 / $k);
    }

    private function writeTitle($wInner, $paginaNum) {
        $pdf = $this->pdf;
        $k   = $pdf->k;
        if($paginaNum == 'primeira') {
            $pdf->SetXY(0.3, 2.5);
        } else {
            $pdf->SetXY(0.3, 0.4);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->x = $pdf->lMargin;
            $pdf->CellXp($wInner, Mage::getStoreConfig('general/store_information/name'));
            $pdf->x = $pdf->lMargin;
            $pdf->CellXp($wInner, Mage::getStoreConfig('general/store_information/site'), 'R');
            $pdf->SetXY(0.3, 0.6);
        }
        $pdf->SetFont('', 'B', 15);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255,255,255);
        $pdf->CellXp($wInner + 0.2, utf8_encode('Relatório de Notas Fiscais Eletrônicas (NF-e)'), 'C', 1, 23 / $k, true, true);
    }
    
    private function writeFooter() {
        $pdf = $this->pdf;
        $pdf->AutoPageBreak = false;
        $wInner             = $pdf->w - $pdf->lMargin - $pdf->rMargin;
        $dataEmissao        = utf8_encode("Data de emissão: " . date('d/m/Y'));
        foreach ($pdf->pages as $pNumber => $page) {
            $pdf->page = $pNumber;
            $pdf->SetFont('Arial', '', 8);
            $pdf->x = $pdf->lMargin;
            $pdf->y = $pdf->h - $pdf->bMargin + 0.5;
            $pdf->CellXp($wInner, $dataEmissao);
            $pdf->x = $pdf->lMargin;
            $pdf->CellXp($wInner, Mage::getStoreConfig('general/store_information/name'), 'C');
            $pdf->x = $pdf->lMargin;
            $str    = utf8_encode("Página " . $pNumber . " de " . count($pdf->pages));
            $pdf->CellXp($wInner, $str, 'R');
        }
    }
    
    private function novaPagina($pdf, $wInner, $div) {
        $this->addPage($wInner);
        $this->writeTitle($wInner, 'seguinte');
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(225, 225, 225);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('', 'B');
        $pdf->Cell(12/$div,5/$div,'Código',1,0,'C',true); 
        $pdf->Cell(20/$div,5/$div,'Pedido',1,0,'C',true);
        $pdf->Cell(20/$div,5/$div,'Data',1,0,'C',true);
        $pdf->Cell(20/$div,5/$div,'Tipo',1,0,'C',true);
        $pdf->Cell(15/$div,5/$div,'Série',1,0,'C',true);
        $pdf->Cell(25.4/$div,5/$div,'Número',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor Desconto',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor Frete',1,0,'C',true);
        $pdf->Cell(25/$div,5/$div,'Valor da NF',1,1,'C',true);
    }

    private function addPage($wInner) {
        $pdf = $this->pdf;
        $pdf->AddPage();
        $pdf->Rect(0.2, 0.2, $wInner + 0.4, 11.1);
        $pdf->x = $pdf->lMargin;
        $pdf->y = $pdf->tMargin;
    }
    
    private function getTotalFinal($pdf, $wInner, $div, $emitidasTotal, $descontoTotal, $freteTotal, $nfTotal) {
        $pdf->SetFont('', 'B', 15);
        $pdf->SetFillColor(0, 0, 0);
        $pdf->SetTextColor(255,255,255);
        $pdf->SetDrawColor(130, 130, 130);
        $pdf->Cell(187/$div,8/$div,'Valores Totais das Notas Fiscais Eletrônicas (NF-e)',1,1,'C',true);
        if ($pdf->y > $pdf->h - $pdf->tMargin - $pdf->bMargin) {
            $this->novaPagina($pdf, $wInner);
        }
        $pdf->SetFont('Arial','',9);
        $pdf->SetFillColor(225, 225, 225);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(63.5/$div,6/$div,'Qtd. Total de NF-e Emitidas: ',1,0,'R',false);
        $pdf->SetFont('', 'B');
        $pdf->Cell(30/$div,6/$div,$emitidasTotal,1,0,'C',true);
        $pdf->SetFont('', '');
        $pdf->Cell(63.5/$div,6/$div,'Valor Total dos Descontos: ',1,0,'R',false);
        $pdf->SetFont('', 'B');
        $pdf->Cell(30/$div,6/$div,Mage::helper('core')->currency($descontoTotal, true, false),1,1,'C',true);
        $pdf->SetFont('', '');
        $pdf->Cell(63.5/$div,6/$div,'Valor Total dos Fretes: ',1,0,'R',false);
        $pdf->SetFont('', 'B');
        $pdf->Cell(30/$div,6/$div,Mage::helper('core')->currency($freteTotal, true, false),1,0,'C',true);
        $pdf->SetFont('', '');
        $pdf->Cell(63.5/$div,6/$div,'Valor Total das Notas Fiscais: ',1,0,'R',false);
        $pdf->SetFont('', 'B');
        $pdf->Cell(30/$div,6/$div,Mage::helper('core')->currency($nfTotal, true, false),1,0,'C',true);
    }
}