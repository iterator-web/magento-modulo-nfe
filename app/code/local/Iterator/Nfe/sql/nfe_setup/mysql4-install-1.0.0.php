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
$installer->run("
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfe')}` (
    `nfe_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `pedido_increment_id` VARCHAR(18) NULL,
    `info_recibo` VARCHAR(60) NULL,
    `dh_recibo` DATETIME NULL,
    `status` TINYINT(1) UNSIGNED NULL,
    `mensagem` VARCHAR(255) NULL,
    `versao` VARCHAR(4) NULL,
    `id` VARCHAR(47) NULL,
    `c_uf` TINYINT(2) UNSIGNED NULL,
    `c_nf` INT(8) UNSIGNED NULL,
    `nat_op` VARCHAR(60) NULL,
    `ind_pag` TINYINT(1) UNSIGNED NULL,
    `mod` VARCHAR(2) NULL,
    `serie` INT(3) UNSIGNED NULL,
    `n_nf` INT(9) UNSIGNED NULL,
    `dh_emi` DATETIME NULL,
    `dh_sai_ent` DATETIME NULL,
    `tp_nf` TINYINT(1) UNSIGNED NULL,
    `id_dest` TINYINT(1) UNSIGNED NULL,
    `c_mun_fg` INT(7) UNSIGNED NULL,
    `tp_imp` TINYINT(1) UNSIGNED NULL,
    `tp_emis` TINYINT(1) UNSIGNED NULL,
    `c_dv` TINYINT(1) UNSIGNED NULL,
    `tp_amb` TINYINT(1) UNSIGNED NULL,
    `fin_nfe` TINYINT(1) UNSIGNED NULL,
    `ind_final` TINYINT(1) UNSIGNED NULL,
    `ind_pres` TINYINT(1) UNSIGNED NULL,
    `proc_emi` TINYINT(1) UNSIGNED NULL,
    `ver_proc` VARCHAR(20) NULL,
    `dh_cont` DATETIME NULL,
    `x_just` VARCHAR(256) NULL,
    `v_bc` DOUBLE(15,2) NULL,
    `v_icms` DOUBLE(15,2) NULL,
    `v_icms_deson` DOUBLE(15,2) NULL,
    `v_bc_st` DOUBLE(15,2) NULL,
    `v_st` DOUBLE(15,2) NULL,
    `v_prod` DOUBLE(15,2) NULL,
    `v_frete` DOUBLE(15,2) NULL,
    `v_seg` DOUBLE(15,2) NULL,
    `v_desc` DOUBLE(15,2) NULL,
    `v_ll` DOUBLE(15,2) NULL,
    `v_ipi` DOUBLE(15,2) NULL,
    `v_pis` DOUBLE(15,2) NULL,
    `v_cofins` DOUBLE(15,2) NULL,
    `v_outro` DOUBLE(15,2) NULL,
    `v_nf` DOUBLE(15,2) NULL,
    `v_tot_trib` DOUBLE(15,2) NULL,
    `v_serv` DOUBLE(15,2) NULL,
    `v_iss` DOUBLE(15,2) NULL,
    `d_compet` DATE NULL,
    `v_deducao` DOUBLE(15,2) NULL,
    `v_desc_incond` DOUBLE(15,2) NULL,
    `v_desc_cond` DOUBLE(15,2) NULL,
    `v_iss_ret` DOUBLE(15,2) NULL,
    `c_reg_trib` TINYINT(2) NULL,
    `v_ret_pis` DOUBLE(15,2) NULL,
    `v_ret_cofins` DOUBLE(15,2) NULL,
    `v_ret_csll` DOUBLE(15,2) NULL,
    `v_bc_irrf` DOUBLE(15,2) NULL,
    `v_irrf` DOUBLE(15,2) NULL,
    `v_bc_ret_prev` DOUBLE(15,2) NULL,
    `v_ret_prev` DOUBLE(15,2) NULL,
    `trans_mod_frete` TINYINT(1) UNSIGNED NULL,
    `trans_cnpj` INT(14) UNSIGNED NULL,
    `trans_cpf` INT(11) UNSIGNED NULL,
    `trans_x_nome` VARCHAR(60) NULL,
    `trans_ie` VARCHAR(14) NULL,
    `trans_x_ender` VARCHAR(60) NULL,
    `trans_x_mun` VARCHAR(60) NULL,
    `trans_uf` VARCHAR(2) NULL,
    `trans_v_serv` DOUBLE(15,2) NULL,
    `trans_v_bc_ret` DOUBLE(15,2) NULL,
    `trans_p_icms_ret` DOUBLE(7,4) NULL,
    `trans_v_icms_ret` DOUBLE(15,2) NULL,
    `trans_cfop` INT(4) UNSIGNED NULL,
    `trans_c_mun_fg` INT(7) UNSIGNED NULL,
    `trans_placa` VARCHAR(7) NULL,
    `trans_veic_uf` VARCHAR(2) NULL,
    `trans_rntc` VARCHAR(20) NULL,
    `trans_vagao` VARCHAR(20) NULL,
    `trans_balsa` VARCHAR(20) NULL,
    `trans_q_vol` INT(15) UNSIGNED NULL,
    `trans_esp` VARCHAR(60) NULL,
    `trans_marca` VARCHAR(60) NULL,
    `trans_n_vol` VARCHAR(60) NULL,
    `trans_peso_l` DOUBLE(15,3) NULL,
    `trans_peso_b` DOUBLE(15,3) NULL,
    `trans_n_lacre` VARCHAR(60) NULL,
    `cob_n_fat` VARCHAR(60) NULL,
    `cob_v_orig` DOUBLE(15,2) NULL,
    `cob_v_desc` DOUBLE(15,2) NULL,
    `cob_v_liq` DOUBLE(13,2) NULL,
    `cob_n_dup` VARCHAR(60) NULL,
    `cob_d_venc` DATE NULL,
    `cob_v_dup` DOUBLE(15,2) NULL,
    `pag_t_pag` TINYINT(2) UNSIGNED NULL,
    `pag_v_pag` DOUBLE(15,2) NULL,
    `pag_cnpj` VARCHAR(14) NULL,
    `pag_t_band` TINYINT(2) UNSIGNED NULL,
    `pag_c_aut` VARCHAR(20) NULL,
    `inf_inf_ad_fisco` TEXT NULL,
    `inf_inf_cpl` TEXT NULL,
    `inf_x_campo` VARCHAR(20) NULL,
    `inf_x_texto` VARCHAR(60) NULL,
    `inf_n_proc` VARCHAR(60) NULL,
    `inf_ind_proc` TINYINT(1) UNSIGNED NULL,
    `exp_uf_saida_pais` VARCHAR(2) NULL,
    `exp_x_loc_exporta` VARCHAR(60) NULL,
    `exp_x_loc_despacho` VARCHAR(60) NULL,
    `comp_x_n_emp` VARCHAR(22) NULL,
    `comp_x_ped` VARCHAR(60) NULL,
    `comp_x_cont` VARCHAR(60) NULL,
    `tem_referencia` TINYINT(1) UNSIGNED NULL,
    `tem_retirada` TINYINT(1) UNSIGNED NULL,
    `tem_entrega` TINYINT(1) UNSIGNED NULL,
    `tem_importacao` TINYINT(1) UNSIGNED NULL,
    `tem_exportacao` TINYINT(1) UNSIGNED NULL,
    PRIMARY KEY (`nfe_id`))
  ENGINE = InnoDB DEFAULT CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfereferenciado')}` (
    `referenciado_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nfe_id` INT(12) UNSIGNED NOT NULL,
    `tipo_documento` VARCHAR(10) NULL COMMENT 'refNFe - refNF - refNFP - refECF',
    `ref_nfe` INT(44) UNSIGNED NULL,
    `c_uf` TINYINT(2) UNSIGNED NULL,
    `aamm` INT(4) UNSIGNED NULL,
    `cnpj` INT(14) UNSIGNED NULL,
    `mod` INT(2) UNSIGNED NULL,
    `serie` INT(3) UNSIGNED NULL,
    `n_nf` INT(9) UNSIGNED NULL,
    `cpf` INT(11) UNSIGNED NULL,
    `ie` VARCHAR(14) NULL,
    `ref_cte` INT(44) UNSIGNED NULL,
    `n_ecf` INT(3) UNSIGNED NULL,
    `n_coo` INT(6) UNSIGNED NULL,
    PRIMARY KEY (`referenciado_id`),
    INDEX `fk_iterator_nfe_referenciado_nfe_idx` (`nfe_id` ASC),
    CONSTRAINT `fk_iterator_nfe_referenciado_nfe`
      FOREIGN KEY (`nfe_id`)
      REFERENCES `{$installer->getTable('nfe/nfe')}` (`nfe_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfeidentificacao')}` (
    `identificacao_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `nfe_id` INT(12) UNSIGNED NOT NULL,
    `tipo_identificacao` VARCHAR(45) NULL COMMENT 'emit - dest - retirada - entrega',
    `cnpj` INT(14) UNSIGNED NULL,
    `cpf` INT(11) UNSIGNED NULL,
    `x_nome` VARCHAR(60) NULL,
    `x_fant` VARCHAR(60) NULL,
    `x_lgr` VARCHAR(60) NULL,
    `nro` VARCHAR(60) NULL,
    `x_cpl` VARCHAR(60) NULL,
    `x_bairro` VARCHAR(60) NULL,
    `c_mun` INT(7) UNSIGNED NULL,
    `x_mun` VARCHAR(60) NULL,
    `uf` VARCHAR(2) NULL,
    `cep` INT(8) UNSIGNED NULL,
    `c_pais` INT(4) UNSIGNED NULL,
    `x_pais` VARCHAR(60) NULL,
    `fone` INT(14) UNSIGNED NULL,
    `ie` VARCHAR(14) NULL,
    `iest` INT(14) UNSIGNED NULL,
    `im` VARCHAR(15) NULL,
    `cnae` INT(7) UNSIGNED NULL,
    `crt` TINYINT(1) UNSIGNED NULL,
    `id_estrangeiro` VARCHAR(20) NULL,
    `ind_ie_dest` TINYINT(1) UNSIGNED NULL,
    `isuf` INT(9) UNSIGNED NULL,
    `email` VARCHAR(60) NULL,
    PRIMARY KEY (`identificacao_id`),
    INDEX `fk_iterator_nfe_identificacao_nfe1_idx` (`nfe_id` ASC),
    CONSTRAINT `fk_iterator_nfe_identificacao_nfe1`
      FOREIGN KEY (`nfe_id`)
      REFERENCES `{$installer->getTable('nfe/nfe')}` (`nfe_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfeproduto')}` (
    `produto_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Referente ao campo nItem do manual',
    `nfe_id` INT(12) UNSIGNED NOT NULL,
    `c_prod` VARCHAR(60) NULL,
    `c_ean` INT(14) UNSIGNED NULL,
    `x_prod` VARCHAR(120) NULL,
    `ncm` INT(8) UNSIGNED NULL,
    `nve` VARCHAR(6) NULL,
    `extipi` INT(3) UNSIGNED NULL,
    `cfop` INT(4) UNSIGNED NULL,
    `u_com` VARCHAR(6) NULL,
    `q_com` INT(4) UNSIGNED NULL,
    `v_un_com` INT(10) UNSIGNED NULL,
    `v_prod` DOUBLE(15,2) UNSIGNED NULL,
    `c_ean_trib` INT(14) UNSIGNED NULL,
    `u_trib` VARCHAR(6) NULL,
    `q_trib` INT(11) UNSIGNED NULL,
    `v_un_trib` INT(10) UNSIGNED NULL,
    `v_frete` DOUBLE(15,2) UNSIGNED NULL,
    `v_seg` DOUBLE(15,2) UNSIGNED NULL,
    `v_desc` DOUBLE(15,2) UNSIGNED NULL,
    `v_outro` DOUBLE(15,2) UNSIGNED NULL,
    `ind_tot` INT(1) UNSIGNED NULL,
    `x_ped` VARCHAR(15) NULL,
    `n_item_ped` INT(6) UNSIGNED NULL,
    `v_tot_trib` DOUBLE(15,2) NULL,
    `p_devol` DOUBLE(5,2) NULL,
    `v_ipi_devol` DOUBLE(15,2) NULL,
    `inf_ad_prod` TEXT NULL,
    `eh_especifico` TINYINT(1) UNSIGNED NULL,
    `tem_icms` TINYINT(1) UNSIGNED NULL,
    `tem_icms_st` TINYINT(1) UNSIGNED NULL,
    `tem_pis` TINYINT(1) UNSIGNED NULL,
    `tem_pis_st` TINYINT(1) UNSIGNED NULL,
    `tem_cofins` TINYINT(1) UNSIGNED NULL,
    `tem_cofins_st` TINYINT(1) UNSIGNED NULL,
    `tem_ipi` TINYINT(1) UNSIGNED NULL,
    `tem_issqn` TINYINT(1) UNSIGNED NULL,
    `tem_di` TINYINT(1) UNSIGNED NULL,
    `tem_ii` TINYINT(1) UNSIGNED NULL,
    PRIMARY KEY (`produto_id`),
    INDEX `fk_iterator_nfe_produto_nfe1_idx` (`nfe_id` ASC),
    CONSTRAINT `fk_iterator_nfe_produto_nfe1`
      FOREIGN KEY (`nfe_id`)
      REFERENCES `{$installer->getTable('nfe/nfe')}` (`nfe_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfeprodutoimportexport')}` (
    `importexport_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `produto_id` INT(12) UNSIGNED NOT NULL,
    `tipo_operacao` VARCHAR(45) NULL COMMENT 'importacao - exportacao',
    `n_di` VARCHAR(12) NULL,
    `d_di` DATE NULL,
    `x_loc_desemb` VARCHAR(60) NULL,
    `uf_desemb` VARCHAR(2) NULL,
    `d_desemb` DATE NULL,
    `tp_via_transp` TINYINT(2) NULL,
    `v_afrmm` DOUBLE(15,2) NULL,
    `tp_intermedio` TINYINT(1) NULL,
    `cnpj` INT(14) NULL,
    `uf_terceiro` VARCHAR(2) NULL,
    `c_exportador` VARCHAR(60) NULL,
    `n_adicao` INT(3) NULL,
    `n_seq_adic` INT(3) NULL,
    `c_fabricante` VARCHAR(60) NULL,
    `v_desc_di` DOUBLE(15,2) NULL,
    `n_draw` INT(11) NULL,
    `n_re` INT(12) NULL,
    `ch_nfe` INT(44) NULL,
    `q_export` DOUBLE(15,4) NULL,
    `n_fci` VARCHAR(36) NULL,
    PRIMARY KEY (`importexport_id`),
    INDEX `fk_iterator_nfe_produto_impexp_nfe_produto1_idx` (`produto_id` ASC),
    CONSTRAINT `fk_iterator_nfe_produto_impexp_nfe_produto1`
      FOREIGN KEY (`produto_id`)
      REFERENCES `{$installer->getTable('nfe/nfeproduto')}` (`produto_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfeprodutoespecifico')}` (
    `especifico_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `produto_id` INT(12) UNSIGNED NOT NULL,
    `tipo_especifico` VARCHAR(45) NULL COMMENT 'veicProd - med - arma - comb',
    `tp_op` TINYINT(1) UNSIGNED NULL,
    `chassi` VARCHAR(17) NULL,
    `c_cor` VARCHAR(4) NULL,
    `x_cor` VARCHAR(40) NULL,
    `pot` VARCHAR(4) NULL,
    `cilin` VARCHAR(4) NULL,
    `peso_l` VARCHAR(9) NULL,
    `peso_b` VARCHAR(9) NULL,
    `n_serie` VARCHAR(15) NULL,
    `tp_comb` VARCHAR(2) NULL,
    `n_motor` VARCHAR(21) NULL,
    `cmt` VARCHAR(9) NULL,
    `dist` VARCHAR(4) NULL,
    `ano_mod` INT(4) UNSIGNED NULL,
    `ano_fab` INT(4) UNSIGNED NULL,
    `tp_pint` VARCHAR(1) NULL,
    `tp_veic` TINYINT(2) UNSIGNED NULL,
    `esp_veic` TINYINT(1) UNSIGNED NULL,
    `vin` VARCHAR(1) NULL,
    `cond_veic` TINYINT(1) UNSIGNED NULL,
    `c_mod` INT(6) UNSIGNED NULL,
    `c_cor_denatran` TINYINT(2) UNSIGNED NULL,
    `lota` INT(3) UNSIGNED NULL,
    `tp_rest` TINYINT(1) NULL,
    `n_lote` VARCHAR(20) NULL,
    `q_lote` DOUBLE(11,3) NULL,
    `d_fab` DATE NULL,
    `d_val` DATE NULL,
    `v_pmc` DOUBLE(15,2) NULL,
    `tp_arma` TINYINT(1) UNSIGNED NULL,
    `n_cano` VARCHAR(15) NULL,
    `desc` VARCHAR(256) NULL,
    `c_prod_anp` INT(9) NULL,
    `p_mix_gn` DOUBLE(6,4) NULL,
    `codif` INT(21) NULL,
    `q_temp` DOUBLE(16,4) NULL,
    `uf_cons` VARCHAR(2) NULL,
    `q_bc_prod` DOUBLE(16,4) NULL,
    `v_aliq_prod` DOUBLE(15,4) NULL,
    `v_cide` DOUBLE(15,2) NULL,
    PRIMARY KEY (`especifico_id`),
    INDEX `fk_iterator_nfe_produto_esp_nfe_produto1_idx` (`produto_id` ASC),
    CONSTRAINT `fk_iterator_nfe_produto_esp_nfe_produto1`
      FOREIGN KEY (`produto_id`)
      REFERENCES `{$installer->getTable('nfe/nfeproduto')}` (`produto_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nfeprodutoimposto')}` (
    `imposto_id` INT(12) UNSIGNED NOT NULL AUTO_INCREMENT,
    `produto_id` INT(12) UNSIGNED NOT NULL,
    `tipo_imposto` VARCHAR(45) NULL COMMENT 'icms - icmsst - pis - pisst - cofins - cofinsst - ipi - issqn - di - ll',
    `tipo_icms` VARCHAR(45) NULL COMMENT 'ICMS00 - ICMS10 - ICMS20 - ICMS30 - ICMS40 - ICMS51 - ICMS60 - ICMS70 - ICMS90 - ICMSPart - ICMSST - ICMSSN101 - ICMSSN102 - ICMSSN201 - ICMSSN202 - ICMSSN500 - ICMSSN900',
    `tipo_ipi` VARCHAR(45) NULL COMMENT 'IPITrib - IPINT',
    `tipo_pis` VARCHAR(45) NULL COMMENT 'PISAliq - PISQtde - PISNT - PISOutr - PISST',
    `tipo_cofins` VARCHAR(45) NULL COMMENT 'COFINSAliq - COFINSQtde - COFINSNT - COFINSOutr - COFINSST',
    `orig` TINYINT(1) UNSIGNED NULL,
    `cst` TINYINT(2) UNSIGNED NULL,
    `mod_bc` TINYINT(1) UNSIGNED NULL,
    `v_bc` DOUBLE(15,2) NULL,
    `p_icms` DOUBLE(7,4) NULL,
    `v_icms` DOUBLE(15,2) NULL,
    `mod_bc_st` TINYINT(1) UNSIGNED NULL,
    `p_mva_st` DOUBLE(7,4) NULL,
    `p_red_bc_st` DOUBLE(7,4) NULL,
    `v_bc_st` DOUBLE(15,2) NULL,
    `p_icms_st` DOUBLE(7,4) NULL,
    `v_icms_st` DOUBLE(15,2) NULL,
    `p_red_bc` DOUBLE(7,4) NULL,
    `v_icms_deson` DOUBLE(15,2) NULL,
    `mot_des_icms` TINYINT(2) UNSIGNED NULL,
    `v_icms_op` DOUBLE(15,2) NULL,
    `p_dif` DOUBLE(7,4) NULL,
    `v_icms_dif` DOUBLE(15,2) NULL,
    `v_bcst_ret` DOUBLE(15,2) NULL,
    `v_icms_st_ret` DOUBLE(15,2) NULL,
    `p_bc_op` DOUBLE(7,4) NULL,
    `uf_st` VARCHAR(2) NULL,
    `v_bc_st_dest` DOUBLE(15,2) NULL,
    `v_icms_st_dest` DOUBLE(15,2) NULL,
    `cso_sn` INT(3) UNSIGNED NULL,
    `p_cred_sn` DOUBLE(7,4) NULL,
    `v_cred_icms_sn` DOUBLE(15,2) NULL,
    `cl_enq` VARCHAR(5) NULL,
    `cnpj_prod` INT(14) UNSIGNED NULL,
    `c_selo` VARCHAR(60) NULL,
    `q_selo` INT(12) UNSIGNED NULL,
    `c_enq` VARCHAR(3) NULL,
    `p_ipi` DOUBLE(7,4) NULL,
    `q_unid` DOUBLE(16,4) NULL,
    `v_unid` DOUBLE(15,4) NULL,
    `v_ipi` DOUBLE(15,2) NULL,
    `v_desp_adu` DOUBLE(15,2) NULL,
    `v_ll` DOUBLE(15,2) NULL,
    `v_iof` DOUBLE(15,2) NULL,
    `p_pis` DOUBLE(7,4) NULL,
    `v_pis` DOUBLE(15,2) NULL,
    `q_bc_prod` DOUBLE(16,4) NULL,
    `v_aliq_prod` DOUBLE(15,4) NULL,
    `p_cofins` DOUBLE(7,4) NULL,
    `v_cofins` DOUBLE(7,4) NULL,
    `v_aliq` DOUBLE(7,4) NULL,
    `v_issqn` DOUBLE(15,2) NULL,
    `c_mun_fg` INT(7) UNSIGNED NULL,
    `c_list_serv` VARCHAR(5) NULL,
    `v_deducao` DOUBLE(15,2) NULL,
    `v_outro` DOUBLE(15,2) NULL,
    `v_desc_incond` DOUBLE(15,2) NULL,
    `v_desc_cond` DOUBLE(15,2) NULL,
    `v_iss_ret` DOUBLE(15,2) NULL,
    `ind_iss` TINYINT(2) NULL,
    `c_servico` VARCHAR(20) NULL,
    `c_mun` INT(7) UNSIGNED NULL,
    `c_pais` INT(4) UNSIGNED NULL,
    `n_processo` VARCHAR(30) NULL,
    `ind_incentivo` TINYINT(1) UNSIGNED NULL,
    PRIMARY KEY (`imposto_id`),
    INDEX `fk_iterator_nfe_produto_imp_nfe_produto2_idx` (`produto_id` ASC),
    CONSTRAINT `fk_iterator_nfe_produto_imp_nfe_produto2`
      FOREIGN KEY (`produto_id`)
      REFERENCES `{$installer->getTable('nfe/nfeproduto')}` (`produto_id`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION)
  ENGINE = InnoDB CHARSET=utf8;
  
  CREATE  TABLE IF NOT EXISTS `{$installer->getTable('nfe/nferange')}` (
    `numero` INT(9) UNSIGNED NOT NULL,
    `serie` INT(3) UNSIGNED NULL,
    `valor_inicio` TINYINT(1) UNSIGNED NULL,
    PRIMARY KEY (`numero`))
  ENGINE = InnoDB DEFAULT CHARSET=utf8;
  ");

$status = Mage::getModel('sales/order_status');
$status->setStatus('nfe_aguardando')->setLabel('NF-e Aguardando')
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();
$status->setStatus('nfe_enviada')->setLabel('NF-e Enviada')
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();
$status->setStatus('nfe_cancelada')->setLabel('NF-e Cancelada')
    ->assignState(Mage_Sales_Model_Order::STATE_PROCESSING)
    ->save();

$installer->endSetup();

?>