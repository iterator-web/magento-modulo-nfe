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
require_once(Mage::getBaseDir('lib') . '/fpdi/fpdi.php');

class Iterator_Nfe_Helper_Pdf_AgruparDanfe extends Mage_Core_Helper_Abstract {
    
    var $files = array(); 
 
    function setFiles($files) { 
        $this->files = $files; 
    } 
 
    function concatPrint() {
        $pdf = new FPDI();
        foreach($this->files AS $file) { 
            $pagecount = $pdf->setSourceFile($file); 
            for ($i = 1; $i <= $pagecount; $i++) { 
                 $tplidx = $pdf->ImportPage($i); 
                 $s = $pdf->getTemplatesize($tplidx); 
                 $pdf->AddPage($s['w'] > $s['h'] ? 'L' : 'P', array($s['w'], $s['h'])); 
                 $pdf->useTemplate($tplidx); 
            } 
        }
        $pdf->Output('danfes.pdf', 'D');
    }
}