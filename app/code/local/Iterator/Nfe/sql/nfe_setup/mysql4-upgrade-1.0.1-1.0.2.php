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

$installer = $this;
$installer->startSetup();

try {
$installer->run("
    ALTER TABLE {$installer->getTable('nfe/nfeproduto')} ADD COLUMN `tem_icms_destino` TINYINT(1) UNSIGNED NULL AFTER `tem_ii`;
        
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `v_bc_uf_dest` DOUBLE(15,2) NULL AFTER `ind_incentivo`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `p_fcp_uf_dest` DOUBLE(7,4) NULL AFTER `v_bc_uf_dest`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `p_icms_uf_dest` DOUBLE(7,4) NULL AFTER `p_fcp_uf_dest`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `p_icms_inter` DOUBLE(7,2) NULL AFTER `p_icms_uf_dest`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `p_icms_inter_part` DOUBLE(7,4) NULL AFTER `p_icms_inter`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `v_fcp_uf_dest` DOUBLE(15,2) NULL AFTER `p_icms_inter_part`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `v_icms_uf_dest` DOUBLE(15,2) NULL AFTER `v_fcp_uf_dest`;
    ALTER TABLE {$installer->getTable('nfe/nfeprodutoimposto')} ADD COLUMN `v_icms_uf_remet` DOUBLE(15,2) NULL AFTER `v_icms_uf_dest`;
        
    ALTER TABLE {$installer->getTable('nfe/nfe')} ADD COLUMN `v_fcp_uf_dest` DOUBLE(15,2) NULL AFTER `v_icms`;
    ALTER TABLE {$installer->getTable('nfe/nfe')} ADD COLUMN `v_icms_uf_dest` DOUBLE(15,2) NULL AFTER `v_fcp_uf_dest`;
    ALTER TABLE {$installer->getTable('nfe/nfe')} ADD COLUMN `v_icms_uf_remet` DOUBLE(15,2) NULL AFTER `v_icms_uf_dest`;
");
} catch (Exception $e) {
    
}

$installer->endSetup();


?>