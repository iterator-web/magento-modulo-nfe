/**
 * Iterator Sistemas Web
 *
 * NOTAS SOBRE LICENÇA
 *
 * Este arquivo de código-fonte está em vigência dentro dos termos da EULA.
 * Ao fazer uso deste arquivo em seu produto, automaticamente você está 
 * concordando com os termos do Contrato de Licença de Usuário Final(EULA)
 * propostos pela empresa Iterator Sistemas Web.
 * Contrato: http://www.iterator.com.br/licenca.txt
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

window.onload=carregarNfe;

Event.observe(document, 'change', respondToChange);

function respondToChange(event) {
    var elementId = event.element().id;
    if(elementId.indexOf('q_com') > -1 || elementId.indexOf('q_trib') > -1 || elementId.indexOf('v_un_com') > -1 || elementId.indexOf('v_un_trib') > -1 || 
            elementId.indexOf('u_com') > -1 || elementId.indexOf('u_trib') > -1) {
        replicarValorUnidade(elementId);        
    }
    /*
    if(elementId === 'v_frete' || elementId === 'v_prod' || 
            elementId === 'v_seg' || elementId === 'v_desc' || elementId === 'v_outro' || elementId === 'v_ipi') {
        converterFloat(''+elementId+'', document.getElementById(''+elementId+'').value);
        atualizarValorTotalNota();
    }
    */
    if(elementId.indexOf('q_com') > -1 || elementId.indexOf('q_trib') > -1 || elementId.indexOf('v_un_com') > -1 || elementId.indexOf('v_un_trib') > -1 || 
            elementId.indexOf('v_frete') > -1 || elementId.indexOf('v_seg') > -1 || elementId.indexOf('v_desc') > -1 || elementId.indexOf('v_outro') > -1 || 
            elementId.indexOf('v_bc') > -1 || elementId.indexOf('v_icms') > -1 || elementId.indexOf('v_bc_st') > -1 || elementId.indexOf('v_icms_st') > -1 ||
            elementId.indexOf('v_icms_deson') > -1 || elementId.indexOf('v_tot_trib') > -1 || elementId.indexOf('v_ll') > -1 || elementId.indexOf('v_ipi') > -1 ||
            elementId.indexOf('v_pis') > -1 || elementId.indexOf('v_cofins') > -1 || elementId.indexOf('v_prod') > -1 || elementId === 'v_st') {
        converterFloat(''+elementId+'', document.getElementById(''+elementId+'').value);
        atualizarValorTotalProduto(elementId);
        atualizarValorTotalNota();
    }
    if(elementId.indexOf('v_fcp_uf_dest') > -1 || elementId.indexOf('v_icms_uf_dest') > -1 || elementId.indexOf('v_icms_uf_remet') > -1) {
        converterFloat(''+elementId+'', document.getElementById(''+elementId+'').value);
        atualizarValorTotalIcmsDestino(elementId);
    }
    if(elementId === 'v_bc' || elementId === 'v_bc_st' || elementId === 'v_icms' || elementId === 'v_st' || elementId === 'v_tot_trib' ||
            elementId === 'v_icms_deson' || elementId === 'v_ll' || elementId === 'v_pis' || elementId === 'v_cofins' || elementId === 'v_serv' || 
            elementId === 'v_bc_iss' || elementId === 'v_iss' || elementId === 'v_pis_iss' || elementId === 'v_cofins_iss' || elementId === 'v_deducao' || 
            elementId === 'v_desc_incond' || elementId === 'v_desc_cond'  || elementId === 'v_iss_ret' || elementId === 'v_ret_pis' || elementId === 'v_ret_cofins' || 
            elementId === 'v_ret_csll' || elementId === 'v_bc_irrf' || elementId === 'v_irrf' || elementId === 'v_bc_ret_prev' || elementId === 'v_ret_prev' ||
            elementId === 'trans_v_serv' || elementId === 'trans_v_bc_ret' || elementId === 'trans_p_icms_ret' || elementId === 'trans_v_icms_ret' || 
            elementId.indexOf('peso_l') > -1 || elementId.indexOf('peso_b') > -1 || elementId.indexOf('v_desc_di') > -1 || elementId.indexOf('q_export') > -1 || 
            elementId.indexOf('v_afrmm') > -1 || elementId.indexOf('v_outro') > -1 || elementId.indexOf('v_desc') > -1 || elementId.indexOf('v_seg') > -1 || 
            elementId.indexOf('v_frete') > -1 || elementId.indexOf('v_prod') > -1 || elementId.indexOf('v_un_trib') > -1 || elementId.indexOf('v_un_com') > -1 ||
            elementId.indexOf('q_trib') > -1 || elementId.indexOf('q_com') > -1 || elementId.indexOf('q_lote') > -1 || elementId.indexOf('v_pmc') > -1 ||
            elementId.indexOf('p_mix_gn') > -1 || elementId.indexOf('q_temp') > -1 || elementId.indexOf('q_bc_prod') > -1 || elementId.indexOf('v_aliq_prod') > -1 ||
            elementId.indexOf('v_cide') > -1 || elementId.indexOf('v_tot_trib') > -1 || elementId.indexOf('v_bc') > -1 || elementId.indexOf('p_icms') > -1  ||
            elementId.indexOf('v_icms') > -1 || elementId.indexOf('p_red_bc') > -1 || elementId.indexOf('p_mva_st') > -1 || elementId.indexOf('v_bc_st') > -1 ||
            elementId.indexOf('p_icms_st') > -1 || elementId.indexOf('v_icms_st') > -1 || elementId.indexOf('p_red_bc_st') > -1 || elementId.indexOf('v_icms_deson') > -1 ||
            elementId.indexOf('v_icms_op') > -1 || elementId.indexOf('p_dif') > -1 || elementId.indexOf('v_icms_dif') > -1 || elementId.indexOf('v_bcst_ret') > -1 ||
            elementId.indexOf('p_bc_op') > -1 || elementId.indexOf('v_bc_st_dest') > -1 || elementId.indexOf('v_icms_st_dest') > -1 || elementId.indexOf('p_cred_sn') > -1 ||
            elementId.indexOf('v_cred_icms_sn') > -1 || elementId.indexOf('ipi_v_bc') > -1 || elementId.indexOf('p_ipi') > -1 || elementId.indexOf('q_unid') > -1 ||
            elementId.indexOf('v_unid') > -1 || elementId.indexOf('v_ipi') > -1 || elementId.indexOf('ii_v_bc') > -1 || elementId.indexOf('v_desp_adu') > -1 ||
            elementId.indexOf('v_ll') > -1 || elementId.indexOf('v_iof') > -1 || elementId.indexOf('pis_v_bc') > -1 || elementId.indexOf('cofins_v_bc') > -1 ||
            elementId.indexOf('p_pis') > -1 || elementId.indexOf('p_cofins') > -1 || elementId.indexOf('pis_v_aliq_prod') > -1 || elementId.indexOf('cofins_v_aliq_prod') > -1 ||
            elementId.indexOf('pis_q_bc_prod') > -1 || elementId.indexOf('cofins_q_bc_prod') > -1 || elementId.indexOf('v_pis') > -1 || elementId.indexOf('v_cofins') > -1 ||
            elementId.indexOf('issqn_v_bc') > -1 || elementId.indexOf('v_aliq') > -1 || elementId.indexOf('v_issqn') > -1 || elementId.indexOf('v_deducao') > -1 ||
            elementId.indexOf('issqn_v_outro') > -1 || elementId.indexOf('v_desc_incond') > -1 || elementId.indexOf('v_desc_cond') > -1 || elementId.indexOf('v_iss_ret') > -1 ||
            elementId.indexOf('c_servico') > -1 || elementId.indexOf('p_devol') > -1 || elementId.indexOf('v_ipi_devol') > -1 || elementId.indexOf('v_bc_uf_dest') > -1 || 
            elementId.indexOf('p_fcp_uf_dest') > -1 || elementId.indexOf('p_icms_uf_dest') > -1 || elementId.indexOf('p_icms_inter') > -1 || elementId.indexOf('p_icms_inter_part') > -1) {
        converterFloat(''+elementId+'', document.getElementById(''+elementId+'').value);
    }
    if(elementId.indexOf('q_vol') > -1) {
        converterInt(''+elementId+'', document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'fin_nfe') {
        exibirReferencia(document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'tipo_documento') {
        habilitarCamposReferencia(document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'destinatario_tipo_pessoa') {
        habilitarCamposTipoPessoa('destinatario_', document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'entrega_tipo_pessoa') {
        habilitarCamposTipoPessoa('entrega_', document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'retirada_tipo_pessoa') {
        habilitarCamposTipoPessoa('retirada_', document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'trans_tipo_pessoa') {
        habilitarCamposTipoPessoa('trans_', document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'tem_retirada') {
        exibirRetirada(document.getElementById(''+elementId+'').checked);
    }
    if(elementId === 'tem_entrega') {
        exibirEntrega(document.getElementById(''+elementId+'').checked);
    }
    if(elementId === 'tp_nf') {
        exibirImportExport(document.getElementById(''+elementId+'').value);
        habilitarDesabilitarDevolucao(document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'tem_importacao') {
        if(document.getElementById(''+elementId+'').checked) {
            habilitarImportExport('import');
        } else {
            desabilitarImportExport();
        }
    }
    if(elementId === 'tem_exportacao') {
        if(document.getElementById(''+elementId+'').checked) {
            habilitarImportExport('export');
            exibirExportacao('habilitar');
        } else {
            desabilitarImportExport();
            exibirExportacao('desabilitar');
        }
    }
    if(elementId === 'id_dest') {
        habilitarIcmsDestinoOperacao(document.getElementById(''+elementId+'').value);
    }
    if(elementId === 'destinatario_ind_ie_dest') {
        habilitarIcmsDestinoIndicador(document.getElementById(''+elementId+'').value);
    }
}

function carregarNfe() {
    var valorFinNfe = document.getElementById('fin_nfe').value;
    var valorTipoPessoaEmitente = document.getElementById('destinatario_tipo_pessoa').value;
    var valorTipoPessoaTransporte = document.getElementById('trans_tipo_pessoa').value;
    var valorTpNf = document.getElementById('tp_nf').value;
    var valorTemRetirada = document.getElementById('tem_retirada').checked;
    var valorTemEntrega = document.getElementById('tem_entrega').checked;
    var elementosMonetarios = ['v_bc', 'v_icms', 'v_icms_deson', 'v_fcp_uf_dest', 'v_icms_uf_dest', 'v_icms_uf_remet', 'v_bc_st', 'v_st', 'v_prod', 
        'v_frete', 'v_seg', 'v_desc', 'v_ll', 'v_ipi', 'v_pis', 'v_cofins', 'v_outro', 'v_nf', 'v_tot_trib'];
    exibirReferencia(valorFinNfe);
    habilitarCamposTipoPessoa('destinatario_', valorTipoPessoaEmitente);
    habilitarCamposTipoPessoa('trans_', valorTipoPessoaTransporte);
    exibirRetirada(valorTemRetirada);
    exibirEntrega(valorTemEntrega);
    exibirImportExport(valorTpNf);
    habilitarDesabilitarDevolucao(valorTpNf);
    formatarValores(elementosMonetarios);
}

function habilitarCamposTipoPessoa(identifcacao, tipo_pessoa) {
    if(tipo_pessoa === '1') {
        $(identifcacao+"cnpj").value = "";
        $(identifcacao+"cnpj").disabled=true;
        $(identifcacao+"cnpj").setStyle({background:"none"});
        $(identifcacao+"cnpj").removeClassName('required-entry');
        if (document.getElementById("advice-required-entry-"+identifcacao+"cnpj")) {
            $("advice-required-entry-"+identifcacao+"cnpj").setStyle({display:"none"});
        }
        if (document.getElementById("advice-validar_cnpj-"+identifcacao+"cnpj")) {
            $("advice-validar_cnpj-"+identifcacao+"cnpj").setStyle({display:"none"});
        }
        $(identifcacao+"cpf").disabled=false;
        $(identifcacao+"cpf").setStyle({backgroundColor:"#FFF"});
        if(identifcacao !== 'trans_') {
            $(identifcacao+"cpf").addClassName('required-entry');
        }
        if(identifcacao !== 'entrega_' && identifcacao !== 'retirada_' && identifcacao !== 'trans_') {
            $(identifcacao+"ind_ie_dest").value = "";
            $(identifcacao+"ind_ie_dest").disabled=true;
            $(identifcacao+"ind_ie_dest").setStyle({background:"none"});
            $(identifcacao+"isuf").value = "";
            $(identifcacao+"isuf").disabled=true;
            $(identifcacao+"isuf").setStyle({background:"none"});
            $(identifcacao+"ie").value = "";
            $(identifcacao+"ie").disabled=true;
            $(identifcacao+"ie").setStyle({background:"none"});
        }
        if(identifcacao === 'emitente_') {
            $(identifcacao+"iest").value = "";
            $(identifcacao+"iest").disabled=true;
            $(identifcacao+"iest").setStyle({background:"none"});
            $(identifcacao+"x_fant").value = "";
            $(identifcacao+"x_fant").disabled=true;
            $(identifcacao+"x_fant").setStyle({background:"none"});
        }
    } else if(tipo_pessoa === '2') {
        $(identifcacao+"cnpj").disabled=false;
        $(identifcacao+"cnpj").setStyle({backgroundColor:"#FFF"});
        if(identifcacao !== 'trans_') {
            $(identifcacao+"cnpj").addClassName('required-entry');
        }
        $(identifcacao+"cpf").value = "";
        $(identifcacao+"cpf").disabled=true;
        $(identifcacao+"cpf").setStyle({background:"none"});
        $(identifcacao+"cpf").removeClassName('required-entry');
        if (document.getElementById("advice-required-entry-"+identifcacao+"cpf")) {
            $("advice-required-entry-"+identifcacao+"cpf").setStyle({display:"none"});
        }
        if (document.getElementById("advice-validar_cpf-"+identifcacao+"cpf")) {
            $("advice-validar_cpf-"+identifcacao+"cpf").setStyle({display:"none"});
        }
        if(identifcacao !== 'entrega_' && identifcacao !== 'retirada_' && identifcacao !== 'trans_') {
            $(identifcacao+"ind_ie_dest").disabled=false;
            $(identifcacao+"ind_ie_dest").setStyle({backgroundColor:"#FFF"});
            $(identifcacao+"isuf").disabled=false;
            $(identifcacao+"isuf").setStyle({backgroundColor:"#FFF"});
            $(identifcacao+"ie").disabled=false;
            $(identifcacao+"ie").setStyle({backgroundColor:"#FFF"});
            if(identifcacao === 'emitente_') {
                $(identifcacao+"iest").disabled=false;
                $(identifcacao+"iest").setStyle({backgroundColor:"#FFF"});
                $(identifcacao+"x_fant").disabled=false;
                $(identifcacao+"x_fant").setStyle({backgroundColor:"#FFF"});
            }
        }
    }
}

function exibirReferencia(fin_nfe) {
    if(fin_nfe === '1') {
        $("tipo_documento").value = "";
        $("tipo_documento").disabled=false;
        $("tipo_documento").setStyle({background:"none"});
        $("tem_referencia").value = "";
        $("nfe_tabs_referenciado_section").setStyle({display:"none"});
        desabilitarCamposReferencia();
    } else {
        $("tipo_documento").disabled=false;
        $("tipo_documento").setStyle({backgroundColor:"#FFF"});
        $("tem_referencia").value = "1";
        $("nfe_tabs_referenciado_section").setStyle({display:"block"});
        if(!document.getElementById('tipo_documento').value) {
            habilitarCamposReferencia('refNFe');
        } else {
            habilitarCamposReferencia(document.getElementById('tipo_documento').value);
        }
    }
}

function habilitarCamposReferencia(tipo_documento) {
    if(tipo_documento == 'refNFe') {
        $("ref_nfe").disabled=false;
        $("ref_nfe").setStyle({backgroundColor:"#FFF"});
        $("ref_cte").value = "";
        $("ref_cte").disabled=true;
        $("ref_cte").setStyle({background:"none"});
        $("region_id").value = "";
        $("region_id").disabled=true;
        $("region_id").setStyle({background:"none"});
        $("aamm").value = "";
        $("aamm").disabled=true;
        $("aamm").setStyle({background:"none"});
        $("cpf").value = "";
        $("cpf").disabled=true;
        $("cpf").setStyle({background:"none"});
        $("cnpj").value = "";
        $("cnpj").disabled=true;
        $("cnpj").setStyle({background:"none"});
        $("ie").value = "";
        $("ie").disabled=true;
        $("ie").setStyle({background:"none"});
        $("mod").value = "";
        $("mod").disabled=true;
        $("mod").setStyle({background:"none"});
        $("serie").value = "";
        $("serie").disabled=true;
        $("serie").setStyle({background:"none"});
        $("n_nf").value = "";
        $("n_nf").disabled=true;
        $("n_nf").setStyle({background:"none"});
        $("n_ecf").value = "";
        $("n_ecf").disabled=true;
        $("n_ecf").setStyle({background:"none"});
        $("n_coo").value = "";
        $("n_coo").disabled=true;
        $("n_coo").setStyle({background:"none"});
    } else if(tipo_documento == 'refNF') {
        $("region_id").disabled=false;
        $("region_id").setStyle({backgroundColor:"#FFF"});
        $("aamm").disabled=false;
        $("aamm").setStyle({backgroundColor:"#FFF"});
        $("cnpj").disabled=false;
        $("cnpj").setStyle({backgroundColor:"#FFF"});
        $("mod").disabled=false;
        $("mod").setStyle({backgroundColor:"#FFF"});
        $("serie").disabled=false;
        $("serie").setStyle({backgroundColor:"#FFF"});
        $("n_nf").disabled=false;
        $("n_nf").setStyle({backgroundColor:"#FFF"});
        $("ref_nfe").value = "";
        $("ref_nfe").disabled=true;
        $("ref_nfe").setStyle({background:"none"});
        $("ref_cte").value = "";
        $("ref_cte").disabled=true;
        $("ref_cte").setStyle({background:"none"});
        $("cpf").value = "";
        $("cpf").disabled=true;
        $("cpf").setStyle({background:"none"});
        $("ie").value = "";
        $("ie").disabled=true;
        $("ie").setStyle({background:"none"});
        $("n_ecf").value = "";
        $("n_ecf").disabled=true;
        $("n_ecf").setStyle({background:"none"});
        $("n_coo").value = "";
        $("n_coo").disabled=true;
        $("n_coo").setStyle({background:"none"});
    } else if(tipo_documento == 'refNFP') {
        $("region_id").disabled=false;
        $("region_id").setStyle({backgroundColor:"#FFF"});
        $("aamm").disabled=false;
        $("aamm").setStyle({backgroundColor:"#FFF"});
        $("cnpj").disabled=false;
        $("cnpj").setStyle({backgroundColor:"#FFF"});
        $("cpf").disabled=false;
        $("cpf").setStyle({backgroundColor:"#FFF"});
        $("ie").disabled=false;
        $("ie").setStyle({backgroundColor:"#FFF"});
        $("mod").disabled=false;
        $("mod").setStyle({backgroundColor:"#FFF"});
        $("serie").disabled=false;
        $("serie").setStyle({backgroundColor:"#FFF"});
        $("n_nf").disabled=false;
        $("n_nf").setStyle({backgroundColor:"#FFF"});
        $("ref_cte").disabled=false;
        $("ref_cte").setStyle({backgroundColor:"#FFF"});
        $("ref_nfe").value = "";
        $("ref_nfe").disabled=true;
        $("ref_nfe").setStyle({background:"none"});
        $("n_ecf").value = "";
        $("n_ecf").disabled=true;
        $("n_ecf").setStyle({background:"none"});
        $("n_coo").value = "";
        $("n_coo").disabled=true;
        $("n_coo").setStyle({background:"none"});
    } else if(tipo_documento == 'refECF') {
        $("mod").disabled=false;
        $("mod").setStyle({backgroundColor:"#FFF"});
        $("n_ecf").disabled=false;
        $("n_ecf").setStyle({backgroundColor:"#FFF"});
        $("n_coo").disabled=false;
        $("n_coo").setStyle({backgroundColor:"#FFF"});
        $("ref_nfe").value = "";
        $("ref_nfe").disabled=true;
        $("ref_nfe").setStyle({background:"none"});
        $("ref_cte").value = "";
        $("ref_cte").disabled=true;
        $("ref_cte").setStyle({background:"none"});
        $("region_id").value = "";
        $("region_id").disabled=true;
        $("region_id").setStyle({background:"none"});
        $("aamm").value = "";
        $("aamm").disabled=true;
        $("aamm").setStyle({background:"none"});
        $("cpf").value = "";
        $("cpf").disabled=true;
        $("cpf").setStyle({background:"none"});
        $("cnpj").value = "";
        $("cnpj").disabled=true;
        $("cnpj").setStyle({background:"none"});
        $("ie").value = "";
        $("ie").disabled=true;
        $("ie").setStyle({background:"none"});
        $("serie").value = "";
        $("serie").disabled=true;
        $("serie").setStyle({background:"none"});
        $("n_nf").value = "";
        $("n_nf").disabled=true;
        $("n_nf").setStyle({background:"none"});
    }
}

function desabilitarCamposReferencia() {
    $("ref_nfe").value = "";
    $("ref_nfe").disabled=true;
    $("ref_nfe").setStyle({background:"none"});
    $("ref_cte").value = "";
    $("ref_cte").disabled=true;
    $("ref_cte").setStyle({background:"none"});
    $("region_id").value = "";
    $("region_id").disabled=true;
    $("region_id").setStyle({background:"none"});
    $("aamm").value = "";
    $("aamm").disabled=true;
    $("aamm").setStyle({background:"none"});
    $("cpf").value = "";
    $("cpf").disabled=true;
    $("cpf").setStyle({background:"none"});
    $("cnpj").value = "";
    $("cnpj").disabled=true;
    $("cnpj").setStyle({background:"none"});
    $("ie").value = "";
    $("ie").disabled=true;
    $("ie").setStyle({background:"none"});
    $("mod").value = "";
    $("mod").disabled=true;
    $("mod").setStyle({background:"none"});
    $("serie").value = "";
    $("serie").disabled=true;
    $("serie").setStyle({background:"none"});
    $("n_nf").value = "";
    $("n_nf").disabled=true;
    $("n_nf").setStyle({background:"none"});
    $("n_ecf").value = "";
    $("n_ecf").disabled=true;
    $("n_ecf").setStyle({background:"none"});
    $("n_coo").value = "";
    $("n_coo").disabled=true;
    $("n_coo").setStyle({background:"none"});
}

function exibirRetirada(tem_retirada) {
    var valorTipoPessoa = document.getElementById('retirada_tipo_pessoa').value;
    habilitarCamposTipoPessoa('retirada_', valorTipoPessoa);
    if(!tem_retirada) {
        $("nfe_tabs_retirada_section").setStyle({display:"none"});
        $("retirada_tipo_identificacao").value = "";
        $("retirada_cnpj").removeClassName('required-entry');
        $("retirada_cpf").removeClassName('required-entry');
        $("retirada_x_lgr").removeClassName('required-entry');
        $("retirada_nro").removeClassName('required-entry');
        $("retirada_x_bairro").removeClassName('required-entry');
        $("x_mun_retirada").removeClassName('required-entry');
        $("retirada_region_id").removeClassName('required-entry');
    } else {
        $("nfe_tabs_retirada_section").setStyle({display:"block"});
        $("retirada_tipo_identificacao").value = "retirada";
        $("retirada_x_lgr").addClassName('required-entry');
        $("retirada_nro").addClassName('required-entry');
        $("retirada_x_bairro").addClassName('required-entry');
        $("x_mun_retirada").addClassName('required-entry');
        $("retirada_region_id").addClassName('required-entry');
    }
}

function exibirEntrega(tem_entrega) {
    var valorTipoPessoa = document.getElementById('entrega_tipo_pessoa').value;
    habilitarCamposTipoPessoa('entrega_', valorTipoPessoa);
    if(!tem_entrega) {
        $("nfe_tabs_entrega_section").setStyle({display:"none"});
        $("entrega_tipo_identificacao").value = "";
        $("entrega_cnpj").removeClassName('required-entry');
        $("entrega_cpf").removeClassName('required-entry');
        $("entrega_x_lgr").removeClassName('required-entry');
        $("entrega_nro").removeClassName('required-entry');
        $("entrega_x_bairro").removeClassName('required-entry');
        $("x_mun_entrega").removeClassName('required-entry');
        $("entrega_region_id").removeClassName('required-entry');
    } else {
        $("nfe_tabs_entrega_section").setStyle({display:"block"});
        $("entrega_tipo_identificacao").value = "entrega";
        $("entrega_x_lgr").addClassName('required-entry');
        $("entrega_nro").addClassName('required-entry');
        $("entrega_x_bairro").addClassName('required-entry');
        $("x_mun_entrega").addClassName('required-entry');
        $("entrega_region_id").addClassName('required-entry');
    }
}

function exibirImportExport(tpNf) {
    if(tpNf === '1') {
        $("tem_exportacao").disabled=false;
        $("tem_exportacao").setStyle({backgroundColor:"#FFF"});
        $("tem_importacao").disabled=true;
        $("tem_importacao").setStyle({background:"none"});
        $("tem_importacao").checked = false;
        if(document.getElementById('tem_exportacao').checked) {
            habilitarImportExport('export');
            exibirExportacao('habilitar');
        } else {
            desabilitarImportExport();
            exibirExportacao('desabilitar');
        }
        $("nfe_tabs_compra_section").setStyle({display:"none"});
    } else if(tpNf === '0') {
        $("tem_importacao").disabled=false;
        $("tem_importacao").setStyle({backgroundColor:"#FFF"});
        $("tem_exportacao").disabled=true;
        $("tem_exportacao").setStyle({background:"none"});
        $("tem_exportacao").checked = false;
        if(document.getElementById('tem_importacao').checked) {
            habilitarImportExport('import');
        } else {
            desabilitarImportExport();
        }
        $("nfe_tabs_compra_section").setStyle({display:"block"});
        exibirExportacao('desabilitar');
    }
}

function habilitarImportExport(importExport) {
    if(importExport === 'export') {
        $$('a[href="#import"]').invoke("setStyle",{display:'none'});
        $$('div[name="import"]').invoke("setStyle",{display:'none'});
        $$('a[href="#export"]').invoke("setStyle",{display:'block'});
    } else if(importExport === 'import') {
        $$('a[href="#export"]').invoke("setStyle",{display:'none'});
        $$('div[name="export"]').invoke("setStyle",{display:'none'});
        $$('a[href="#import"]').invoke("setStyle",{display:'block'});
    }
}

function desabilitarImportExport() {
    $$('a[href="#import"]').invoke("setStyle",{display:'none'});
    $$('div[name="import"]').invoke("setStyle",{display:'none'});
    $$('a[href="#export"]').invoke("setStyle",{display:'none'});
    $$('div[name="export"]').invoke("setStyle",{display:'none'});
}

function exibirExportacao(processo) {
    if(processo === 'habilitar') {
        $("nfe_tabs_exportacao_section").setStyle({display:"block"});
    } else {
        $("nfe_tabs_exportacao_section").setStyle({display:"none"});
    }
}

function habilitarIcmsDestinoOperacao(destinoOperacao) {
    indicador = document.getElementById('destinatario_ind_ie_dest').value;
    if(destinoOperacao === '2' && indicador === '9') {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'block'});
    } else {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'none'});
    }
}

function habilitarIcmsDestinoIndicador(indicador) {
    destinoOperacao = document.getElementById('id_dest').value;
    if(indicador === '9' && destinoOperacao === '2') {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'block'});
    } else {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'none'});
    }
}

function habilitarIcmsDestino(destinoOperacao) {
    if(destinoOperacao === '2') {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'block'});
    } else {
        $$('a[href="#icms_destino"]').invoke("setStyle",{display:'none'});
    }
}

function habilitarDesabilitarDevolucao(tpNf) {
    if(tpNf === '1') {
        $$('[id^="p_devol"]').invoke("setStyle",{background:"none"}).invoke('disable');
        $$('[id^="v_ipi_devol"]').invoke("setStyle",{background:"none"}).invoke('disable');
    } else if(tpNf === '0') {
        $$('[id^="p_devol"]').invoke("setStyle",{backgroundColor:"#FFF"}).invoke('enable');
        $$('[id^="v_ipi_devol"]').invoke("setStyle",{backgroundColor:"#FFF"}).invoke('enable');
    }
}

function replicarValorUnidade(elementId) {
    if(elementId.indexOf('q_com') > -1) {
        var qTrib = elementId.replace('q_com', 'q_trib'); 
        $(''+qTrib+'').value = parseFloat(document.getElementById(elementId).value).toFixed(4);
    } else if(elementId.indexOf('q_trib') > -1) {
        var qCom = elementId.replace('q_trib', 'q_com'); 
        $(''+qCom+'').value = parseFloat(document.getElementById(elementId).value).toFixed(4);
    }
    if(elementId.indexOf('v_un_com') > -1) {
        var vUnTrib = elementId.replace('v_un_com', 'v_un_trib'); 
        $(''+vUnTrib+'').value = parseFloat(document.getElementById(elementId).value).toFixed(4);
    } else if(elementId.indexOf('v_un_trib') > -1) {
        var vUnCom = elementId.replace('v_un_trib', 'v_un_com'); 
        $(''+vUnCom+'').value = parseFloat(document.getElementById(elementId).value).toFixed(4);
    }
    if(elementId.indexOf('u_com') > -1) {
        var uTrib = elementId.replace('u_com', 'u_trib'); 
        $(''+uTrib+'').value = document.getElementById(elementId).value;
    } else if(elementId.indexOf('u_trib') > -1) {
        var uCom = elementId.replace('u_trib', 'u_com'); 
        $(''+uCom+'').value = document.getElementById(elementId).value;
    }
}

function atualizarValorTotalProduto(elementId) {
    var valorTotalProdutos = 0;
    var valorTotalDesconto = 0;
    var valorTotalFrete = 0;
    var valorTotalSeguro = 0;
    var valorTotalOutro = 0;
    var valorTotalTributos = 0;
    var bcIcmsTotal= 0;
    var vIcmsTotal= 0;
    var bcIcmsStTotal= 0;
    var vIcmsStTotal= 0;
    var vIcmsDesonTotal = 0;
    var vIITotal = 0;
    var vIpiTotal = 0;
    var vPisTotal = 0;
    var vCofinsTotal = 0;
    var produtosItensTotal = document.querySelectorAll('input[id^="produto"]');
    for(i = 0; i < produtosItensTotal.length; i++) {
        var valorTotal = 0;
        var qtd = document.getElementById('q_com'+[i]+'value').value;
        var valorUnitario = document.getElementById('v_un_com'+[i]+'value').value;
        var descontoItem = document.getElementById('v_desc'+[i]+'value').value;
        var vFreteItem = document.getElementById('v_frete'+[i]+'value').value;
        var vSegItem = document.getElementById('v_seg'+[i]+'value').value;
        var vOutro = document.getElementById('v_outro'+[i]+'value').value;
        var vTotTrib = document.getElementById('v_tot_trib'+[i]+'value').value;
        var vBc = document.getElementById('v_bc'+[i]+'value').value;
        var vIcms = document.getElementById('v_icms'+[i]+'value').value;
        var vBcSt = document.getElementById('v_bc_st'+[i]+'value').value;
        var vIcmsSt = document.getElementById('v_icms_st'+[i]+'value').value;
        var vIcmsDeson = document.getElementById('v_icms_deson'+[i]+'value').value;
        var vII = document.getElementById('v_ll'+[i]+'value').value;
        var vIpi = document.getElementById('v_ipi'+[i]+'value').value;
        var vPis = document.getElementById('v_pis'+[i]+'value').value;
        var vCofins = document.getElementById('v_cofins'+[i]+'value').value;
        if(vFreteItem === '') {
            vFreteItem = 0;
        }
        valorTotalFrete += parseFloat(vFreteItem);
        if(descontoItem === '') {
            descontoItem = 0;
        }
        valorTotalDesconto += parseFloat(descontoItem);
        if(vSegItem === '') {
            vSegItem = 0;
        }
        valorTotalSeguro += parseFloat(vSegItem);
        if(vOutro === '') {
            vOutro = 0;
        }
        valorTotalOutro += parseFloat(vOutro);
        if(vTotTrib === '') {
            vTotTrib = 0;
        }
        valorTotalTributos += parseFloat(vTotTrib);
        if(vBc === '') {
            vBc = 0;
        }
        bcIcmsTotal += parseFloat(vBc);
        if(vIcms === '') {
            vIcms = 0;
        }
        vIcmsTotal += parseFloat(vIcms);
        if(vBcSt === '') {
            vBcSt = 0;
        }
        bcIcmsStTotal += parseFloat(vBcSt);
        if(vIcmsSt === '') {
            vIcmsSt = 0;
        }
        vIcmsStTotal += parseFloat(vIcmsSt);
        if(vIcmsDeson === '') {
            vIcmsDeson = 0;
        }
        vIcmsDesonTotal += parseFloat(vIcmsDeson);
        if(vII === '') {
            vII = 0;
        }
        vIITotal += parseFloat(vII);
        if(vIpi === '') {
            vIpi = 0;
        }
        vIpiTotal += parseFloat(vIpi);
        if(vPis === '') {
            vPis = 0;
        }
        vPisTotal += parseFloat(vPis);
        if(vCofins === '') {
            vCofins = 0;
        }
        vCofinsTotal += parseFloat(vCofins);
        
        valorTotal = qtd * valorUnitario;
        $('v_prod'+[i]+'value').value = parseFloat(valorTotal).toFixed(4);
        valorTotalProdutos += valorTotal;
        
    }
    
    if(elementId.indexOf('v_desc') > -1 || elementId.indexOf('remover') > -1) {
        $('v_desc').value = parseFloat(valorTotalDesconto).toFixed(4);
    }
    if(elementId.indexOf('v_frete') > -1 || elementId.indexOf('remover') > -1) {
        $('v_frete').value = parseFloat(valorTotalFrete).toFixed(4);
    }
    if(elementId.indexOf('v_seg') > -1 || elementId.indexOf('remover') > -1) {
        $('v_seg').value = parseFloat(valorTotalSeguro).toFixed(4);
    }
    if(elementId.indexOf('v_outro') > -1 || elementId.indexOf('remover') > -1) {
        $('v_outro').value = parseFloat(valorTotalOutro).toFixed(4);
    }
    if(elementId.indexOf('v_tot_trib') > -1 || elementId.indexOf('remover') > -1) {
        $('v_tot_trib').value = parseFloat(valorTotalTributos).toFixed(4);
    }
    if(elementId.indexOf('v_bc') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_bc').value = parseFloat(bcIcmsTotal).toFixed(4);
    }
    if(elementId.indexOf('v_icms') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_icms').value = parseFloat(vIcmsTotal).toFixed(4);
    }
    if(elementId.indexOf('v_bc_st') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_bc_st').value = parseFloat(bcIcmsStTotal).toFixed(4);
    }
    if(elementId.indexOf('v_icms_st') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_st').value = parseFloat(vIcmsStTotal).toFixed(4);
    }
    if(elementId.indexOf('v_st') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_st').value = parseFloat(vIcmsStTotal).toFixed(4);
    }
    if(elementId.indexOf('v_icms_deson') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_icms_deson').value = parseFloat(vIcmsDesonTotal).toFixed(4);
    }
    if(elementId.indexOf('v_ll') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_ll').value = parseFloat(vIITotal).toFixed(4);
    }
    if(elementId.indexOf('v_ipi') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1 || elementId.indexOf('ipi_cst') > -1) {
        $('v_ipi').value = parseFloat(vIpiTotal).toFixed(4);
    }
    if(elementId.indexOf('v_pis') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1 || elementId.indexOf('pis_cst')) {
        $('v_pis').value = parseFloat(vPisTotal).toFixed(4);
    }
    if(elementId.indexOf('v_cofins') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('cst_csosn') > -1 || elementId.indexOf('operacao') > -1 || elementId.indexOf('cofins_cst')) {
        $('v_cofins').value = parseFloat(vCofinsTotal).toFixed(4);
    }
    
    $('v_prod').value = parseFloat(valorTotalProdutos).toFixed(4);
}

function atualizarValorTotalNota() {
    var valorTotalNota = 0;
    if(document.getElementById('v_frete').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_frete').value);
    }
    if(document.getElementById('v_prod').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_prod').value);
    }
    if(document.getElementById('v_st').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_st').value);
    }
    if(document.getElementById('v_seg').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_seg').value);
    }
    if(document.getElementById('v_desc').value !== '') {
        valorTotalNota -= parseFloat(document.getElementById('v_desc').value);
    }
    if(document.getElementById('v_outro').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_outro').value);
    }
    if(document.getElementById('v_ipi').value !== '') {
        valorTotalNota += parseFloat(document.getElementById('v_ipi').value);
    }
    $('v_nf').value = parseFloat(valorTotalNota).toFixed(4);
}

function atualizarValorTotalIcmsDestino(elementId) {
    var vFcpUfDestTotal = 0;
    var vIcmsUfDestTotal = 0;
    var vIcmsUfRemetTotal = 0;
    var produtosItensTotal = document.querySelectorAll('input[id^="produto"]');
    for(i = 0; i < produtosItensTotal.length; i++) {
        var vFcpUfDest = document.getElementById('v_fcp_uf_dest'+[i]+'value').value;
        var vIcmsUfDest = document.getElementById('v_icms_uf_dest'+[i]+'value').value;
        var vIcmsUfRemet = document.getElementById('v_icms_uf_remet'+[i]+'value').value;
        
        if(vFcpUfDest === '') {
            vFcpUfDest = 0;
        }
        vFcpUfDestTotal += parseFloat(vFcpUfDest);
        if(vIcmsUfDest === '') {
            vIcmsUfDest = 0;
        }
        vIcmsUfDestTotal += parseFloat(vIcmsUfDest);
        if(vIcmsUfRemet === '') {
            vIcmsUfRemet = 0;
        }
        vIcmsUfRemetTotal += parseFloat(vIcmsUfRemet);
    }
    
    if(elementId.indexOf('v_fcp_uf_dest') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_fcp_uf_dest').value = parseFloat(vFcpUfDestTotal).toFixed(4);
    }
    if(elementId.indexOf('v_icms_uf_dest') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_icms_uf_dest').value = parseFloat(vIcmsUfDestTotal).toFixed(4);
    }
    if(elementId.indexOf('v_icms_uf_remet') > -1 || elementId.indexOf('remover') > -1 || elementId.indexOf('operacao') > -1) {
        $('v_icms_uf_remet').value = parseFloat(vIcmsUfRemetTotal).toFixed(4);
    }
}

function formatarValores(elementosMonetarios) {
    for	(var i = 0; i < elementosMonetarios.length; i++) {
        if(!document.getElementById(''+elementosMonetarios[i]+'').value) {
            document.getElementById(''+elementosMonetarios[i]+'').value = 0;
        }
        converterFloat(''+elementosMonetarios[i]+'', document.getElementById(''+elementosMonetarios[i]+'').value);
    } 
}

function converterFloat(campo, valor) {
    var valorFormatado = parseFloat(valor.replace(',', '.')).toFixed(4);
    if(valorFormatado === 'NaN') {
        $(''+campo+'').value = '';
    } else {
        $(''+campo+'').value = valorFormatado;
    }
}

function converterInt(campo, valor) {
    var valorFormatado = parseInt(valor);
    if(valorFormatado === 'NaN') {
        $(''+campo+'').value = '';
    } else {
        $(''+campo+'').value = valorFormatado;
    }
}

function removerReboque(linhaRemovida) {
    $('placa'+[linhaRemovida]+'value').value = '';
    $('uf'+[linhaRemovida]+'value').value = '';
    $('rntc'+[linhaRemovida]+'value').value = '';
    $('vagao'+[linhaRemovida]+'value').value = '';
    $('balsa'+[linhaRemovida]+'value').value = '';
}

function removerVolume(linhaRemovida) {
    $('q_vol'+[linhaRemovida]+'value').value = '';
    $('esp'+[linhaRemovida]+'value').value = '';
    $('marca'+[linhaRemovida]+'value').value = '';
    $('n_vol'+[linhaRemovida]+'value').value = '';
    $('peso_l'+[linhaRemovida]+'value').value = '';
    $('peso_b'+[linhaRemovida]+'value').value = '';
}

function removerLacre(linhaRemovida) {
    $('n_lacre'+[linhaRemovida]+'value').value = '';
}

function removerCobranca(linhaRemovida) {
    $('cob_n_dup'+[linhaRemovida]+'value').value = '';
    $('cob_d_venc'+[linhaRemovida]+'value').value = '';
    $('cob_v_dup'+[linhaRemovida]+'value').value = '';
}

function removerItem(linhaRemovida) {
    $("operacao"+linhaRemovida+"value").removeClassName('required-entry');
    $$('[name^="itens[value][option_'+linhaRemovida+'"]').invoke('setValue', '');
    atualizarValorTotalProduto('remover');
    atualizarValorTotalNota();
}