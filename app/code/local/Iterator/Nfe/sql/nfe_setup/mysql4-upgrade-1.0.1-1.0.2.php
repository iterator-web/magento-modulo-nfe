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