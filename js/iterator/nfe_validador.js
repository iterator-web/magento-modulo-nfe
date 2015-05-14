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

Validation.add('validar_cnpj', 'O CNPJ informado &eacute; inv&aacute;lido', function(valor){
    var resultado = false;
    if(valor === null || valor === '') {
        return true;
    } else {
        resultado = validarCNPJ(valor);
    }
    return resultado;
});

Validation.add('validar_cpf', 'O CPF informado &eacute; inv&aacute;lido', function(valor){
    var resultado = false;
    if(valor === null || valor === '') {
        return true;
    } else {
        resultado = validarCPF(valor);
    }
    return resultado;
});

Validation.add('validar_municipio', 'O Mun&iacute;cipio informado &eacute; inv&aacute;lido', function(valor) {
    var resultado = false;
    var caminhoController = BASE_URL; // URL Base definida em municipio_emitente.phtml para ser utilizada nesta validação
    new Ajax.Request(caminhoController,
    {
        method: 'get',
        asynchronous: false,
        parameters: {municipio: valor},
        onSuccess: successFunc,
        onFailure:  failureFunc
    });

    function successFunc(xhr) {
        var response = xhr.responseText.evalJSON();
        if(response['resultado'] === 'true') {
            resultado = true;
        } else {
            resultado = false;
        }
    }
    function failureFunc(response){
        alert(response.msg);
    }
    
    if(valor === null || valor === '') {
        resultado = true;
    }
    
    return resultado;
});

Event.observe(window, 'load', function() {
    new MaskedInput('#aamm', '99/99');
    new MaskedInput('#emitente_cnpj', '99.999.999/9999-99');
    new MaskedInput('#destinatario_cnpj', '99.999.999/9999-99');
    new MaskedInput('#cnpj', '99.999.999/9999-99');
    new MaskedInput('#trans_cnpj', '99.999.999/9999-99');
    new MaskedInput('#entrega_cnpj', '99.999.999/9999-99');
    new MaskedInput('#retirada_cnpj', '99.999.999/9999-99');
    new MaskedInput('#emitente_cpf', '999.999.999.99');
    new MaskedInput('#destinatario_cpf', '999.999.999.99');
    new MaskedInput('#cpf', '999.999.999.99');
    new MaskedInput('#trans_cpf', '999.999.999.99');
    new MaskedInput('#entrega_cpf', '999.999.999.99');
    new MaskedInput('#retirada_cpf', '999.999.999.99');
    new MaskedInput('#emitente_cep', '99999-999');
    new MaskedInput('#destinatario_cep', '99999-999');
    new MaskedInput('#emitente_fone', '(99) 9999-9999');
    new MaskedInput('#destinatario_fone', '(99) 9999-9999');
});

function validarCNPJ(cnpj) {
 
    cnpj = cnpj.replace(/[^\d]+/g,'');
 
    if(cnpj == '') return false;
     
    if (cnpj.length != 14)
        return false;
 
    // Elimina CNPJs invalidos conhecidos
    if (cnpj == "00000000000000" ||
        cnpj == "11111111111111" ||
        cnpj == "22222222222222" ||
        cnpj == "33333333333333" ||
        cnpj == "44444444444444" ||
        cnpj == "55555555555555" ||
        cnpj == "66666666666666" ||
        cnpj == "77777777777777" ||
        cnpj == "88888888888888" ||
        cnpj == "99999999999999")
        return false;
         
    // Valida DVs
    tamanho = cnpj.length - 2
    numeros = cnpj.substring(0,tamanho);
    digitos = cnpj.substring(tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(0))
        return false;
         
    tamanho = tamanho + 1;
    numeros = cnpj.substring(0,tamanho);
    soma = 0;
    pos = tamanho - 7;
    for (i = tamanho; i >= 1; i--) {
      soma += numeros.charAt(tamanho - i) * pos--;
      if (pos < 2)
            pos = 9;
    }
    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
    if (resultado != digitos.charAt(1))
          return false;
           
    return true;
}

function validarCPF(cpf) {  
    cpf = cpf.replace(/[^\d]+/g,'');    
    if(cpf == '') return false; 
    // Elimina CPFs invalidos conhecidos    
    if (cpf.length != 11 || 
        cpf == "00000000000" || 
        cpf == "11111111111" || 
        cpf == "22222222222" || 
        cpf == "33333333333" || 
        cpf == "44444444444" || 
        cpf == "55555555555" || 
        cpf == "66666666666" || 
        cpf == "77777777777" || 
        cpf == "88888888888" || 
        cpf == "99999999999")
            return false;       
    // Valida 1o digito 
    add = 0;    
    for (i=0; i < 9; i ++)       
        add += parseInt(cpf.charAt(i)) * (10 - i);  
        rev = 11 - (add % 11);  
        if (rev == 10 || rev == 11)     
            rev = 0;    
        if (rev != parseInt(cpf.charAt(9)))     
            return false;       
    // Valida 2o digito 
    add = 0;    
    for (i = 0; i < 10; i ++)        
        add += parseInt(cpf.charAt(i)) * (11 - i);  
    rev = 11 - (add % 11);  
    if (rev == 10 || rev == 11) 
        rev = 0;    
    if (rev != parseInt(cpf.charAt(10)))
        return false;       
    return true;   
}