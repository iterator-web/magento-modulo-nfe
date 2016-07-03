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

function carregarNfe($adminUrl, skinUrl, nfeId, nNf) {
    new Ajax.Request($adminUrl,
    {
        method		: 'post',
        parameters	: "nfe_id=" + nfeId,
        onComplete	: function(transport)
        {
            abrirModalWindow("DADOS REFERENTES A NF-E ("+nNf+")", transport.responseText, 1030, 400, skinUrl);
        }
    });
}

function abrirModalWindow(title, content, width, height, skinUrl) {
    $$('HTML')[0].setStyle({overflow: 'hidden'});

    var viewport = document.viewport;

    var windowWidth = (width) ? width : 300;
    var windowHeight = (height) ? height : 200;

    var styleWidth = width + 'px';
    var styleHeight = height + 'px';
    var styleTop = (viewport.getScrollOffsets()[1] + (viewport.getHeight() / 2)- (windowHeight / 2)) + 'px';
    var styleLeftMargin = "-" +  (windowWidth / 2) + 'px';

    var windowBodyWidth = (windowWidth - 80) + 'px';
    var windowBodyHeight = (windowHeight - 50) + 'px';

    // mascara que cobre o fundo da tela
    var mask = document.createElement("div");
    mask.id = 'iterator-page-mask';
    mask.setStyle
    ({
        'width'					: viewport.getWidth() + "px",
        'height'				: viewport.getHeight() + "px",
        'position'				: 'absolute',
        'zIndex'				: '9000',
        'backgroundColor'                       : '#ccc',
        'top'					: viewport.getScrollOffsets()[1] + 'px',
        'left'					: '0',
        'display'				: 'none'
    });


    // janela modal
    var modalWindow = document.createElement("div");
    modalWindow.id = 'iterator-modal-window';
    $(modalWindow).setStyle
    ({
            'width'				: styleWidth,
            'height'				: styleHeight,
            'top'				: styleTop,
            'marginLeft'			: styleLeftMargin,
            'position'				: 'absolute',
            'zIndex'				: '9001',
            'backgroundColor'                   : 'transparent',
            'left'				: '50%',
            'display'				: 'none',
            'color'				: '#333333',
            'fontSize'				: '11px'
    });

    // tabela que monta o layout da janela
    var windowStructure = document.createElement("table");
    var tableBody = document.createElement("tbody");

    windowStructure.cellPadding = '0';
    windowStructure.cellSpacing = '0';
    windowStructure.border = '0';

    var tr1WindowStructure = document.createElement("tr");
    var tr2WindowStructure = document.createElement("tr");
    var tr3WindowStructure = document.createElement("tr");

    var td11WindowStructure = document.createElement("td");
    var td12WindowStructure = document.createElement("td");
    var td13WindowStructure = document.createElement("td");
    var td21WindowStructure = document.createElement("td");
    var td22WindowStructure = document.createElement("td");
    var td23WindowStructure = document.createElement("td");
    var td31WindowStructure = document.createElement("td");
    var td32WindowStructure = document.createElement("td");
    var td33WindowStructure = document.createElement("td");

    var leftUpCorner = document.createElement("div");
    $(leftUpCorner).setStyle
    ({
            'height'			: '10px',
            'width'			: '10px',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/top_left.png\')'
    });
    td11WindowStructure.appendChild(leftUpCorner);

    $(td12WindowStructure).setStyle
    ({
            'height'			: '10px',
            'width'			:  (windowWidth - 20) + 'px',
            'backgroundColor'		: '#555'
    });

    var rightUpCorner = document.createElement("div");
    $(rightUpCorner).setStyle
    ({
            'height'			: '10px',
            'width'			: '10px',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/top_right.png\')'
    });
    td13WindowStructure.appendChild(rightUpCorner);

    $(td21WindowStructure).setStyle
    ({
            'height'			: (windowHeight - 20) + 'px',
            'width'			:  '10px',
            'backgroundColor'		: '#555'
    });

    $(td22WindowStructure).setStyle
    ({
            'backgroundColor'		: '#FFFFFF'
    });

    $(td23WindowStructure).setStyle
    ({
            'height'			: (windowHeight - 20) + 'px',
            'width'			:  '10px',
            'backgroundColor'		: '#555'
    });

    $(td31WindowStructure).setStyle
    ({
            'height'			: '10px',
            'width'			: '10px',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/bottom_left.png\')'
    });

    $(td32WindowStructure).setStyle
    ({
            'height'			: '10px',
            'width'			:  (windowWidth - 20) + 'px',
            'backgroundColor'		: '#555'
    });

    $(td33WindowStructure).setStyle
    ({
            'height'			: '10px',
            'width'			: '10px',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/bottom_right.png\')'
    });

    tr1WindowStructure.appendChild(td11WindowStructure);
    tr1WindowStructure.appendChild(td12WindowStructure);
    tr1WindowStructure.appendChild(td13WindowStructure);
    tr2WindowStructure.appendChild(td21WindowStructure);
    tr2WindowStructure.appendChild(td22WindowStructure);
    tr2WindowStructure.appendChild(td23WindowStructure);
    tr3WindowStructure.appendChild(td31WindowStructure);
    tr3WindowStructure.appendChild(td32WindowStructure);
    tr3WindowStructure.appendChild(td33WindowStructure);

    tableBody.appendChild(tr1WindowStructure);
    tableBody.appendChild(tr2WindowStructure);
    tableBody.appendChild(tr3WindowStructure);

    windowStructure.appendChild(tableBody);

    modalWindow.appendChild(windowStructure);

    // monta estrutura html
    var windowHeader = document.createElement("div");
    windowHeader.setStyle
    ({
            'height'			: '38px',
            'width'			: '100%',
            'position'			: 'relative',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/header.png\')',
            'backgroundRepeat'		: 'repeat-x'
    });

    var titleDiv = document.createElement('div');
    titleDiv.setStyle
    ({
            'position'			: 'absolute',
            'top'			: '8px',
            'left'			: '10px',
            'color'			: '#FFFFFF',
            'fontSize'                  : '12px',
            'fontWeight'		: 'bold'
    });
    $(titleDiv).update(title);

    var closeButton = document.createElement('div');
    closeButton.setStyle
    ({
            'position'			: 'absolute',
            'width'			: '16px',
            'height'			: '16px',
            'top'			: '8px',
            'right'			: '5px',
            'cursor'			: 'pointer',
            'backgroundImage'		: 'url(\''+skinUrl+'adminhtml/default/default/images/iterator/window/close.png\')',
    });

    var windowBody = document.createElement("div");
    windowBody.setStyle
    ({
            'width'			: '100%',
            'position'                  : 'relative',
            'padding'			: '15px',
            'color'			: '#333333',
            'fontSize'			: '12px',
            'height'			: windowHeight + "px",
            'width'			: windowWidth + "px", 
            'overflow'			: 'auto'
    });
    $(windowBody).update(content);

    windowHeader.appendChild(titleDiv);
    windowHeader.appendChild(closeButton);
    td22WindowStructure.appendChild(windowHeader);
    td22WindowStructure.appendChild(windowBody);

    document.body.appendChild(mask);
    document.body.appendChild(modalWindow);

    $(mask).appear({ duration: 0.2, from: 0, to: 0.5 });
    $(modalWindow).appear({ duration: 0.2, from: 0, to: 1 });

    $(closeButton).observe('click', function()
    {
            document.body.removeChild(mask);
            document.body.removeChild(modalWindow);
            $$('HTML')[0].setStyle({overflow: 'auto'});
    });
}