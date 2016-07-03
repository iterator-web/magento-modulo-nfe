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

class Iterator_Nfe_Model_Source_Emissao {
    
    public function toOptionArray() {
        return array(
            array('value' => '1', 'label' => utf8_encode('Emiss�o normal (n�o em conting�ncia)')),
            array('value' => '2', 'label' => utf8_encode('Conting�ncia FS-IA, com impress�o do DANFE em formul�rio de seguran�a')),
            array('value' => '3', 'label' => utf8_encode('Conting�ncia SCAN (Sistema de Conting�ncia do Ambiente Nacional)')),
            array('value' => '4', 'label' => utf8_encode('Conting�ncia DPEC (Declara��o Pr�via da Emiss�o em Conting�ncia)')),
            array('value' => '5', 'label' => utf8_encode('Conting�ncia FS-DA, com impress�o do DANFE em formul�rio de seguran�a')),
            array('value' => '6', 'label' => utf8_encode('Conting�ncia SVC-AN (SEFAZ Virtual de Conting�ncia do AN)')),
            array('value' => '7', 'label' => utf8_encode('Conting�ncia SVC-RS (SEFAZ Virtual de Conting�ncia do RS)')),
            array('value' => '9', 'label' => utf8_encode('Conting�ncia off-line da NFC-e (as demais op��es de conting�ncia s�o v�lidas tamb�m para a NFC-e)')),
        );
    }
}

