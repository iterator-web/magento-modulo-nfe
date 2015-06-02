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

class Iterator_Nfe_Model_NfeRN extends Mage_Core_Model_Abstract {
    
    public function montarNfe($order) {
        $vBC = null;
        $vICMS = null;
        $vBCST = null;
        $vST = null;
        $vIpi = null;
        $vPis = null;
        $vCofins = null;
        $vCredICMSSN = null;
        $totalVProd = null;
        $totalVFrete = null;
        $totalVSeg = null;
        $totalVDesc = null;
        $totalVOutro = null;
        $totalVNf = null;
        $totalVTotTrib = null;
        $totalAliquotaIbpt = null;
        $retorno = array();
        $nfe = Mage::getModel('nfe/nfe');
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        $cUF = $validarCampos->getUfEquivalente($estadoEmitente->getRegionId());
        if(!$cUF) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('O Estado do emitente da NF-e n�o � v�lido. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $aamm = date('ym');
        $cnpj = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj'));
        if(!$validarCampos->validarCnpj($cnpj)) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('O CNPJ do emitente da NF-e n�o � v�lido. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $mod = '55';
        $nfeRange = Mage::getModel('nfe/nferange')->load('1');
        $serie = $nfeRange->getSerie();
        $nNF = $nfeRange->getNumero();
        $this->setRange($nfeRange);
        $tpEmis = Mage::getStoreConfig('nfe/nfe_opcoes/emissao');
        $cNF = $this->gerarCodigoNumerico();
        $chave = $cUF . $aamm . $cnpj . $mod . $serie . $nNF . $tpEmis . $cNF;
        $cDV = $this->calcularDV($chave);
        $chave .= $cDV;
        $indPag = $this->getFormaPagamento($order);
        if($indPag == null) {
            $nfeRange->setNumero($novoRangeNumero-1);
            $nfeRange->save();
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('A forma de pagamento n�o � v�lida. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $dataHoraAtual = date("Y-m-d H:i:s");
        $dataHoraSaida = date("Y-m-d H:i:s", strtotime('+5 hours'));
        $estadoDestino = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
        if($estadoEmitente->getRegionId() == $estadoDestino->getRegionId()) {
            $idDest = '1';
        } else  {
            $idDest = '2';
        }
        $cMunFG = Mage::getStoreConfig('nfe/emitente_opcoes/codigo_municipio');
        if(!$cMunFG) {
            $nfeRange->setNumero($novoRangeNumero-1);
            $nfeRange->save();
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('O C�digo do Munic�pio do emitente da NF-e n�o � v�lido. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $formatoDanfe = Mage::getStoreConfig('nfe/danfe_opcoes/formato');
        if($formatoDanfe == 'portraite') {
            $tpImp = '1';
        } else if($formatoDanfe == 'landscape') {
            $tpImp = '2';
        }
        $ambiente = Mage::getStoreConfig('nfe/nfe_opcoes/ambiente');
        if($ambiente == 'producao') {
            $tpAmb = '1';
        } else if($ambiente == 'homologacao') {
            $tpAmb = '2';
        }
        
        $nfe->setPedidoIncrementId($order->getIncrementId());
        $nfe->setStatus('0');
        $nfe->setMensagem(utf8_encode('Aguardando aprova��o para enviar solicita��o de autoriza��o ao org�o respons�vel.'));
        $nfe->setVersao('3.10');
        $nfe->setIdTag('NFe'.$chave);
        $nfe->setCUf($cUF);
        $nfe->setCNf($cNF);
        $nfe->setNatOp('Venda de Mercadoria');
        $nfe->setIndPag($indPag);
        $nfe->setMod($mod);
        $nfe->setSerie($serie);
        $nfe->setNNf($nNF);
        $nfe->setDhEmi(str_replace(' ', 'T', $dataHoraAtual));
        $nfe->setDhSaiEnt(str_replace(' ', 'T', $dataHoraSaida));
        $nfe->setTpNf('1');
        $nfe->setIdDest($idDest);
        $nfe->setCMunFg($cMunFG);
        $nfe->setTpImp($tpImp);
        $nfe->setTpEmis($tpEmis);
        $nfe->setCDv($cDV);
        $nfe->setTpAmb($tpAmb);
        $nfe->setFinNfe('1');
        $nfe->setIndFinal('1');
        $nfe->setIndPres('2');
        $nfe->setProcEmi('0');
        $nfe->setVerProc('1.0.0');
        $nfe->save();
        
        $nfeIdentificacaoEmitente = Mage::getModel('nfe/nfeidentificacao');
        $nfeId = $nfe->getNfeId();
        $crt = Mage::getStoreConfig('nfe/emitente_opcoes/crt');
        $razaoSocial = Mage::getStoreConfig('nfe/emitente_opcoes/razao');
        $nomeFantasia = Mage::getStoreConfig('nfe/emitente_opcoes/fantasia');
        $logradouro = Mage::getStoreConfig('nfe/emitente_opcoes/logradouro');
        $numero = Mage::getStoreConfig('nfe/emitente_opcoes/numero');
        $complemento = Mage::getStoreConfig('nfe/emitente_opcoes/complemento');
        $bairro = Mage::getStoreConfig('nfe/emitente_opcoes/bairro');
        $cep = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cep'));
        $telefone = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/fone'));
        $ie = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/ie'));
        $nomeMunicipio = Mage::getStoreConfig('nfe/emitente_opcoes/nome_municipio');
        if(!$razaoSocial || !$nomeFantasia || !$ie || !$logradouro || !$numero || !$bairro || !$cep || !$telefone || !$ie || !$nomeMunicipio) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('Uma ou mais informa��es do emitente da NF-e n�o s�o v�lidas. Pedido: '.$order->getIncrementId());
            try {
                $nfeRange->setNumero($novoRangeNumero-1);
                $nfeRange->save();
                $nfe->delete();
                $resource = Mage::getSingleton('core/resource');
                $writeConnection = $resource->getConnection('core_write');
                $table = $resource->getTableName('nfe/nfe');
                $autoIncrement = (int)$nfeId;
                $query = "ALTER TABLE {$table} AUTO_INCREMENT = {$autoIncrement}";
                $writeConnection->query($query);
            } catch (Exception $e) {
                $retorno['msg'] = $e;
            }
            return $retorno;
        }
        
        $nfeIdentificacaoEmitente->setNfeId($nfeId);
        $nfeIdentificacaoEmitente->setTipoIdentificacao('emit');
        $nfeIdentificacaoEmitente->setTipoPessoa(2);
        $nfeIdentificacaoEmitente->setCnpj($cnpj);
        $nfeIdentificacaoEmitente->setXNome($razaoSocial);
        $nfeIdentificacaoEmitente->setXFant($nomeFantasia);
        $nfeIdentificacaoEmitente->setXLgr($logradouro);
        $nfeIdentificacaoEmitente->setNro($numero);
        $nfeIdentificacaoEmitente->setXCpl($complemento);
        $nfeIdentificacaoEmitente->setXBairro($bairro);
        $nfeIdentificacaoEmitente->setCMun($cMunFG);
        $nfeIdentificacaoEmitente->setXMun($nomeMunicipio);
        $nfeIdentificacaoEmitente->setRegionId($estadoEmitente->getRegionId());
        $nfeIdentificacaoEmitente->setUf($estadoEmitente->getCode());
        $nfeIdentificacaoEmitente->setCep($cep);
        $nfeIdentificacaoEmitente->setCPais('1058');
        $nfeIdentificacaoEmitente->setXPais('Brasil');
        $nfeIdentificacaoEmitente->setFone($telefone);
        $nfeIdentificacaoEmitente->setIe($ie);
        $nfeIdentificacaoEmitente->setCrt($crt);
        $nfeIdentificacaoEmitente->save();      
        
        $nfeIdentificacaoDestinatario = Mage::getModel('nfe/nfeidentificacao');
        $cliente = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if($cliente->getCpfcnpj()) {
            $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $cliente->getCpfcnpj()),0,14);
        } else {
            $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $cliente->getTaxvat()),0,14); 
        }
        $cidade = str_replace(array('\'','&'), array(' ','e'), $order->getShippingAddress()->getCity());
        $estadoDestinatario = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
        $nfeMunicipio = Mage::getModel('nfe/nfemunicipio')->getCollection()->addfieldToFilter('nome', array('like' => $cidade))->getFirstItem();
        
        $nfeIdentificacaoDestinatario->setNfeId($nfeId);
        $nfeIdentificacaoDestinatario->setTipoIdentificacao('dest');
        if(strlen($cpfCnpj) > 11) {
            if(!$validarCampos->validarCnpj($cpfCnpj)) {
                $retorno['status'] = 'erro';
                $retorno['msg'] = utf8_encode('O CNPJ do destinat�rio da NF-e n�o � v�lido. Pedido: '.$order->getIncrementId());
                try {
                    $nfeRange->setNumero($novoRangeNumero-1);
                    $nfeRange->save();
                    $nfeIdentificacaoEmitente->delete();
                    $nfe->delete();
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $table = $resource->getTableName('nfe/nfe');
                    $autoIncrement = (int)$nfeId;
                    $query = "ALTER TABLE {$table} AUTO_INCREMENT = {$autoIncrement}";
                    $writeConnection->query($query);
                } catch (Exception $e) {
                    $retorno['msg'] = $e;
                }
                return $retorno;
            }
            $nfeIdentificacaoDestinatario->setTipoPessoa(2);
            $nfeIdentificacaoDestinatario->setCnpj($cpfCnpj);
            $nfeIdentificacaoDestinatario->setXNome($cliente->getRazaosocial());
            if($cliente->getIe()) {
                $nfeIdentificacaoDestinatario->setIndIeDest('1');
                $nfeIdentificacaoDestinatario->setIe($cliente->getIe());
            } else {
                $nfeIdentificacaoDestinatario->setIndIeDest('9');
            }
        } else {
            if(!$validarCampos->validarCpf($cpfCnpj)) {
                $retorno['status'] = 'erro';
                $retorno['msg'] = utf8_encode('O CPF do destinat�rio da NF-e n�o � v�lido. Pedido: '.$order->getIncrementId());
                try {
                    $nfeRange->setNumero($novoRangeNumero-1);
                    $nfeRange->save();
                    $nfeIdentificacaoEmitente->delete();
                    $nfe->delete();
                    $resource = Mage::getSingleton('core/resource');
                    $writeConnection = $resource->getConnection('core_write');
                    $table = $resource->getTableName('nfe/nfe');
                    $autoIncrement = (int)$nfeId;
                    $query = "ALTER TABLE {$table} AUTO_INCREMENT = {$autoIncrement}";
                    $writeConnection->query($query);
                } catch (Exception $e) {
                    $retorno['msg'] = $e;
                }
                return $retorno;
            }
            $nfeIdentificacaoDestinatario->setTipoPessoa(1);
            $nfeIdentificacaoDestinatario->setCpf($cpfCnpj);
            $nfeIdentificacaoDestinatario->setXNome($order->getShippingAddress()->getFirstname().' '.$order->getShippingAddress()->getLastname());
            $nfeIdentificacaoDestinatario->setIndIeDest('9');
        }
        if($tpAmb == '2') {
            $nfeIdentificacaoDestinatario->setXNome('NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL');
        }
        $nfeIdentificacaoDestinatario->setXLgr($order->getShippingAddress()->getStreet(1));
        $nfeIdentificacaoDestinatario->setNro($order->getShippingAddress()->getStreet(2));
        $nfeIdentificacaoDestinatario->setXCpl($order->getShippingAddress()->getStreet(3));
        $nfeIdentificacaoDestinatario->setXBairro($order->getShippingAddress()->getStreet(4));
        if($nfeMunicipio->getCodigo()) {
            $nfeIdentificacaoDestinatario->setCMun($nfeMunicipio->getIbgeUf().$nfeMunicipio->getCodigo());
            $nfeIdentificacaoDestinatario->setXMun($nfeMunicipio->getNome());
        }
        $nfeIdentificacaoDestinatario->setRegionId($estadoDestinatario->getRegionId());
        $nfeIdentificacaoDestinatario->setUf($estadoDestinatario->getCode());
        $nfeIdentificacaoDestinatario->setCep(preg_replace('/[^\d]/', '', $order->getShippingAddress()->getPostcode()));
        $nfeIdentificacaoDestinatario->setCPais('1058');
        $nfeIdentificacaoDestinatario->setXPais('Brasil');
        $nfeIdentificacaoDestinatario->setFone(preg_replace('/[^\d]/', '', $order->getShippingAddress()->getTelephone()));
        $nfeIdentificacaoDestinatario->setEmail($order->getCustomerEmail());
        $nfeIdentificacaoDestinatario->save();
        
        $existeMotorImpostos = Mage::getConfig()->getModuleConfig('Iterator_MotorImpostos')->is('active', 'true');
        $orderItems = $order->getAllItems();
        $nItem = 0;
        $itemComNcm = 0;
        foreach($orderItems as $item) {
            if($item->getProductType() == 'simple') {
                $cfop = null;
                $orig = null;
                $cstCsosn = null;
                $modBc = null;
                $modBcSt = null;
                $ipiCst = null;
                $pisCofinsCst = null;
                $exTipi = null;
                $existeDadosNcm = false;
                $temIcms = null;
                $temPis = null;
                $temCofins = null;
                $temIpi = null;
                $prodSt = null;
                $prodIpi = null;
                $aliquotaIbpt = null;
                $nfeProduto = Mage::getModel('nfe/nfeproduto');
                $nItem++;
                $gtin = Mage::getModel('catalog/product')->load($item->getProductId())->getData('gtin');
                $ncm = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('ncm');
                $unidade = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('unidade');
                $tipoMercadoria = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('tipo_mercadoria');
                if($estadoEmitente->getRegionId() == $estadoDestinatario->getRegionId()) {
                    if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                        $cfop = '5102';
                    } else if($tipoMercadoria == utf8_encode('Produ��o do Estabelecimento')) {
                        $cfop = '5101';
                    }
                } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) > 11) {
                    if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                        $cfop = '6102';
                    } else if($tipoMercadoria == utf8_encode('Produ��o do Estabelecimento')) {
                        $cfop = '6101';
                    }
                } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) <= 11) {
                    if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                        $cfop = '6108';
                    } else if($tipoMercadoria == utf8_encode('Produ��o do Estabelecimento')) {
                        $cfop = '6107';
                    }
                }
                if($existeMotorImpostos && $ncm && $cfop) {
                    $motorCalculos = Mage::getModel('motorimpostos/motorcalculos');
                    $dadosNcm = $motorCalculos->getDadosNcm($cfop, $ncm);
                    if($dadosNcm) {
                        $orig = $dadosNcm->getIcmsOrigem();
                        $cstCsosn = $dadosNcm->getIcmsCst();
                        $modBc = $dadosNcm->getIcmsModBc();
                        $modBcSt = $dadosNcm->getIcmsModBcSt();
                        $ipiCst = $dadosNcm->getIpiCst();
                        $pisCofinsCst = $dadosNcm->getPisCofinsCst();
                        $exTipi = $dadosNcm->getExTipi();
                        $aliquotaIbpt = $dadosNcm->getAliquotaIbpt();
                        $existeDadosNcm = true;
                    }
                }
                $nfeProduto->setNfeId($nfeId);
                $nfeProduto->setProduto('product/'.$item->getProductId());
                $nfeProduto->setNItem($nItem);
                $nfeProduto->setCProd($item->getSku());
                $nfeProduto->setCEan($gtin);
                $nfeProduto->setNcm($ncm);
                $nfeProduto->setExtipi($exTipi);
                $nfeProduto->setCfop($cfop);
                $nfeProduto->setUCom($unidade);
                if($item->getParentItemId()) {
                    $itemParent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
                    $xProd = $itemParent->getName();
                    $qComTrib = $itemParent->getQtyOrdered();
                    $vUnComTrib = $itemParent->getPrice();
                    $vProd = $itemParent->getPrice() * $itemParent->getQtyOrdered();
                    $vDesc = $itemParent->getDiscountAmount();
                } else if(!$item->getParentItemId()) {
                    $xProd = $item->getName();
                    $qComTrib = $item->getQtyOrdered();
                    $vUnComTrib = $item->getPrice();
                    $vProd = $item->getPrice() * $item->getQtyOrdered();
                    $vDesc = $item->getDiscountAmount();
                }
                $existeRewards = Mage::getConfig()->getModuleConfig('Magestore_Affiliateplus')->is('active', 'true');
                if($existeRewards) {
                    if($order->getRewardpointsDiscount() > 0) {
                        $porcentagemDesc = ($order->getRewardpointsDiscount() / $order->getSubtotal()) * 100;
                        $vDesc += ($vProd * $porcentagemDesc) / 100;
                    }
                }
                if($order->getShippingAmount() > 0) {
                    $vFrete = ($vProd - $vDesc) * $order->getShippingAmount() / ($order->getGrandTotal() - $order->getShippingAmount());
                }
                $nfeProduto->setXProd($xProd);
                $nfeProduto->setQCom($qComTrib);
                $nfeProduto->setVUnCom($vUnComTrib);
                $nfeProduto->setVProd($vProd);
                $nfeProduto->setCEanTrib($gtin);
                $nfeProduto->setUTrib($unidade);
                $nfeProduto->setQTrib($qComTrib);
                $nfeProduto->setVUnTrib($vUnComTrib);
                $nfeProduto->setVFrete($vFrete);
                $nfeProduto->setVDesc($vDesc);
                $nfeProduto->setIndTot('1');
                $nfeProduto->setXPed($order->getIncrementId());
                $nfeProduto->setNItemPed($nItem);
                $vTotTrib = null;
                if($existeMotorImpostos && $existeDadosNcm) {
                    $vTotTrib = $motorCalculos->impostosAproximados($dadosNcm, $vProd);
                    $temIcms = '1';
                    $temPis = '1';
                    $temCofins = '1';
                    $temIpi = '1';
                    $totalVTotTrib += $vTotTrib;
                    $itemComNcm++;
                }
                $nfeProduto->setVTotTrib($vTotTrib);
                $nfeProduto->setInfAdProd('Val Aprox dos Tributos '.Mage::helper('core')->currency($vTotTrib, true, false).' ('.number_format($aliquotaIbpt, 2, '.', '').'%) Fonte: IBPT');
                $nfeProduto->setTemIcms($temIcms);
                $nfeProduto->setTemPis($temPis);
                $nfeProduto->setTemCofins($temCofins);
                $nfeProduto->setTemIpi($temIpi);
                $nfeProduto->save();
                
                if($existeMotorImpostos && $existeDadosNcm) {
                    $impostosRetorno = $motorCalculos->setImpostosProdutoNfe($nfeProduto, $dadosNcm, $estadoEmitente->getRegionId(), $estadoDestinatario->getRegionId());
                    if($impostosRetorno['vBC'] > 0) {
                        $vBC += $impostosRetorno['vBC'];
                    }
                    if($impostosRetorno['vICMS'] > 0) {
                        $vICMS += $impostosRetorno['vICMS'];
                    }
                    if($impostosRetorno['vBCST'] > 0) {
                        $vBCST += $impostosRetorno['vBCST'];
                    }
                    if($impostosRetorno['vST'] > 0) {
                        $prodSt = $impostosRetorno['vST'];
                        $vST += $impostosRetorno['vST'];
                    }
                    if($impostosRetorno['vIPI'] > 0) {
                        $prodIpi = $impostosRetorno['vIPI'];
                        $vIpi += $impostosRetorno['vIPI'];
                    }
                    if($impostosRetorno['vCredICMSSN'] > 0) {
                        $vCredICMSSN += $impostosRetorno['vCredICMSSN'];
                    }
                    //$vPis = '';
                    //$vCofins = '';
                    if($vST > 0) {
                        $vProd -= $prodSt;
                        $reduzirUnSt = $prodSt / $item->getQtyOrdered();
                        $vUnComTrib -= $reduzirUnSt;
                        $nfeProduto->setVUnCom($vUnComTrib);
                        $nfeProduto->setVProd($vProd);
                        $nfeProduto->setVUnTrib($vUnComTrib);
                        $nfeProduto->save();
                    }
                    if($vIpi > 0) {
                        $vProd -= $prodIpi;
                        $reduzirUn = $prodIpi / $item->getQtyOrdered();
                        $vUnComTrib -= $reduzirUn;
                        $nfeProduto->setVUnCom($vUnComTrib);
                        $nfeProduto->setVProd($vProd);
                        $nfeProduto->setVUnTrib($vUnComTrib);
                        $nfeProduto->save();
                    }
                }
                
                $totalVProd += $vProd;
                $totalVFrete += $vFrete;
                $totalVDesc += $vDesc;
                //$totalVSeg = null;
                //$totalVOutro = null;
                $totalVNf += $vProd - $vDesc + $vFrete + $prodIpi + $prodSt;
                $totalAliquotaIbpt += $aliquotaIbpt;
            }
        }
        $nfe->setVBc($vBC);
        $nfe->setVIcms($vICMS);
        $nfe->setVBcSt($vBCST);
        $nfe->setVSt($vST);
        $nfe->setVProd($totalVProd);
        $nfe->setVFrete($totalVFrete);
        $nfe->setVDesc($totalVDesc);
        $nfe->setVIpi($vIpi);
        $nfe->setVNf($totalVNf);
        $nfe->setVTotTrib($totalVTotTrib);
        
        $nfe->setTransModFrete(0);
        if(strpos($order->getShippingDescription(), 'Correios') !== false) {
            $nfe->setTransTipoPessoa(2);
            $nfe->setTransCnpj(preg_replace('/[^\d]/', '', Mage::getStoreConfig('carriers/pedroteixeira_correios/cnpj')));
            $nfe->setTrans_x_nome(Mage::getStoreConfig('carriers/pedroteixeira_correios/razao'));
            $nfe->setTransIe(preg_replace('/[^\d]/', '', Mage::getStoreConfig('carriers/pedroteixeira_correios/ie')));
            $nfe->setTrans_x_ender(Mage::getStoreConfig('carriers/pedroteixeira_correios/endereco'));
            $nfe->setTrans_x_mun(Mage::getStoreConfig('carriers/pedroteixeira_correios/municipio'));
            $estadoTransp = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
            $nfe->setTransRegionId($estadoTransp->getRegionId());
            $nfe->setTransUf($estadoTransp->getCode());
        }
        $nfe->setTransTemVol(1);
        $nfeTransporteVol = Mage::getModel('nfe/nfetransporte');
        $nfeTransporteVol->setNfeId($nfeId);
        $nfeTransporteVol->setTipoInformacao('vol');
        $nfeTransporteVol->setQVol(1);
        $nfeTransporteVol->setEsp(Mage::getStoreConfig('carriers/pedroteixeira_correios/embalagem'));
        $nfeTransporteVol->setPesoL($order->getWeight());
        $nfeTransporteVol->setPesoB($order->getWeight());
        $nfeTransporteVol->save();
        
        $infCpl = null;
        if($existeMotorImpostos) {
            $regimeTributacao = Mage::getStoreConfig('tax/empresa/regimetributacao');
            if($regimeTributacao == 2) {
                $infCpl .= utf8_encode('I - DOCUMENTO EMITIDO POR ME OU EPP OPTANTE PELO SIMPLES NACIONAL.  II - N�O GERA DIREITO A CR�DITO FISCAL DE IPI.  ');
                if($vCredICMSSN > 0) {
                    $infCpl .= utf8_encode('III - PERMITE O APROVEITAMENTO DO CR�DITO DE ICMS NO VALOR DE '.Mage::helper('core')->currency($vCredICMSSN, true, false).' CORRESPONDENTE � AL�QUOTA DE '.number_format($dadosNcm->getAliquotaSimples(), 2, '.', '').'%, NOS TERMOS DO ART. 23 DA LEI COMPLEMENTAR N� 123, DE 2006.  ');
                }
            }
        }
        $infCpl .= utf8_encode('Val Aprox dos Tributos '.Mage::helper('core')->currency($totalVTotTrib, true, false).' ('.number_format($totalAliquotaIbpt / $itemComNcm, 2, '.', '').'%) Fonte: IBPT');
        $nfe->setInfInfCpl($infCpl);
        
        $nfe->save();
        
        $this->setStatusPedido($order);
        
        $retorno['status'] = 'sucesso';
        $retorno['msg'] = utf8_encode('Solicita��o para emiss�o da NF-e gerada com sucesso.');
        return $retorno;
    }
    
    public function gerarXML($nfeId) {
        $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
        $nfeHelper = Mage::Helper('nfe/nfeHelper');
        $nfeCriarXML = Mage::helper('nfe/NfeCriarXml');
        $this->preencherCampos($nfe, $nfeCriarXML);
        $retornoXml = $this->gerarArquivoXML($nfe, $nfeCriarXML);
        if($retornoXml == 'sucesso') {
            $xmlNfe = $nfeHelper->getXmlNfe($nfe);
            $xmlAssinado = $this->assinarXml($xmlNfe, 'infNFe', $nfe);
            if($xmlAssinado == 'sucesso') {
                $xmlNfe = $nfeHelper->getXmlNfe($nfe);
                $xmlValidado = $this->validarXml($xmlNfe);
                if($xmlValidado == 'sucesso') {
                    return 'sucesso';
                } else {
                    return $xmlValidado;
                }
            } else {
                return $xmlAssinado;
            }
        } else {
            return $retornoXml;
        }
    }
    
    private function preencherCampos($nfe, $nfeCriarXML) {
        $nfeId = $nfe->getNfeId();
        //Numero e versão da NFe (infNFe)
        $chave = substr($nfe->getIdTag(),3);
        $versao = $nfe->getVersao();
        $resposta = $nfeCriarXML->taginfNFe($chave, $versao);

        //Dados da NFe (ide)
        $cUF = $nfe->getCUf();
        $cNF = $nfe->getCNf(); //numero aleatório da NF
        $natOp = $nfe->getNatOp(); //natureza da operação
        $indPag = $nfe->getIndPag(); //0=Pagamento à vista; 1=Pagamento a prazo; 2=Outros
        $mod = $nfe->getMod(); //modelo da NFe 55 ou 65 essa última NFCe
        $serie = strval(intval($nfe->getSerie())); //serie da NFe
        $nNF = strval(intval($nfe->getNNf())); // numero da NFe
        $dhEmi = str_replace(' ', 'T', $nfe->getDhEmi()).'-03:00';  //para versão 3.00 '2014-02-03T13:22:42-3.00' não informar para NFCe
        $dhSaiEnt = str_replace(' ', 'T', $nfe->getDhSaiEnt()).'-03:00'; //versão 2.00, 3.00 e 3.10
        $tpNF = $nfe->getTpNf();
        $idDest = $nfe->getIdDest(); //1=Operação interna; 2=Operação interestadual; 3=Operação com exterior.
        $cMunFG = $nfe->getCMunFg();
        $tpImp = $nfe->getTpImp(); //0=Sem geração de DANFE; 1=DANFE normal, Retrato; 2=DANFE normal, Paisagem;
                      //3=DANFE Simplificado; 4=DANFE NFC-e; 5=DANFE NFC-e em mensagem eletrônica
                      //(o envio de mensagem eletrônica pode ser feita de forma simultânea com a impressão do DANFE;
                      //usar o tpImp=5 quando esta for a única forma de disponibilização do DANFE).
        $tpEmis = $nfe->getTpEmis(); //1=Emissão normal (não em contingência);
                       //2=Contingência FS-IA, com impressão do DANFE em formulário de segurança;
                       //3=Contingência SCAN (Sistema de Contingência do Ambiente Nacional);
                       //4=Contingência DPEC (Declaração Prévia da Emissão em Contingência);
                       //5=Contingência FS-DA, com impressão do DANFE em formulário de segurança;
                       //6=Contingência SVC-AN (SEFAZ Virtual de Contingência do AN);
                       //7=Contingência SVC-RS (SEFAZ Virtual de Contingência do RS);
                       //9=Contingência off-line da NFC-e (as demais opções de contingência são válidas também para a NFC-e);
                       //Nota: Para a NFC-e somente estão disponíveis e são válidas as opções de contingência 5 e 9.
        $cDV = $nfe->getCDv(); //digito verificador
        $tpAmb = $nfe->getTpAmb(); //1=Produção; 2=Homologação
        $finNFe = $nfe->getFinNfe(); //1=NF-e normal; 2=NF-e complementar; 3=NF-e de ajuste; 4=Devolução/Retorno.
        $indFinal = $nfe->getIndFinal(); //0=Não; 1=Consumidor final;
        $indPres = $nfe->getIndPres(); //0=Não se aplica (por exemplo, Nota Fiscal complementar ou de ajuste);
                       //1=Operação presencial;
                       //2=Operação não presencial, pela Internet;
                       //3=Operação não presencial, Teleatendimento;
                       //4=NFC-e em operação com entrega a domicílio;
                       //9=Operação não presencial, outros.
        $procEmi = $nfe->getProcEmi(); //0=Emissão de NF-e com aplicativo do contribuinte;
                        //1=Emissão de NF-e avulsa pelo Fisco;
                        //2=Emissão de NF-e avulsa, pelo contribuinte com seu certificado digital, através do site do Fisco;
                        //3=Emissão NF-e pelo contribuinte com aplicativo fornecido pelo Fisco.
        $verProc = $nfe->getVerProc(); //versão do aplicativo emissor
        $dhCont = $nfe->getDhCont(); //entrada em contingência AAAA-MM-DDThh:mm:ssTZD
        $xJust = $nfe->getXJust(); //Justificativa da entrada em contingência
        $resposta = $nfeCriarXML->tagide($cUF, $cNF, $natOp, $indPag, $mod, $serie, $nNF, $dhEmi, $dhSaiEnt, $tpNF, $idDest, $cMunFG, $tpImp, $tpEmis, $cDV, $tpAmb, $finNFe, $indFinal, $indPres, $procEmi, $verProc, $dhCont, $xJust);
        
        // Refer�ncia
        if($nfe->getTemReferencia() == '1') {
            $nfeReferenciado = Mage::getModel('nfe/nfereferenciado')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->getFirstItem();
            if($nfeReferenciado->getTipoDocumento() == 'refNFe') {
                //refNFe NFe referenciada  
                $refNFe = $nfeReferenciado->getRefNfe();
                $resposta = $nfeCriarXML->tagrefNFe($refNFe);
            } else if($nfeReferenciado->getTipoDocumento() == 'refNF') {
                //refNF Nota Fiscal 1A referenciada
                $cUF = $nfeReferenciado->getCUf();
                $AAMM = $nfeReferenciado->getAamm();
                $CNPJ = $nfeReferenciado->getCnpj();
                $mod = $nfeReferenciado->getMod();
                $serie = $nfeReferenciado->getSerie();
                $nNF = $nfeReferenciado->getNNf();
                $resposta = $nfeCriarXML->tagrefNF($cUF, $AAMM, $CNPJ, $mod, $serie, $nNF);
            } else if($nfeReferenciado->getTipoDocumento() == 'refNFP') {
                //NFPref Nota Fiscal Produtor Rural referenciada
                $cUF = $nfeReferenciado->getCUf();
                $AAMM = $nfeReferenciado->getAamm();
                $CNPJ = $nfeReferenciado->getCnpj();
                $CPF = $nfeReferenciado->getCpf();
                $IE = $nfeReferenciado->getIe();
                $mod = $nfeReferenciado->getMod();
                $serie = $nfeReferenciado->getSerie();
                $nNF = $nfeReferenciado->getNNf();
                $resposta = $nfeCriarXML->tagrefNFP($cUF, $AAMM, $CNPJ, $CPF, $IE, $mod, $serie, $nNF);
            } else if($nfeReferenciado->getTipoDocumento() == 'refECF') {
                //ECFref ECF referenciada
                $mod = $nfeReferenciado->getMod();
                $nECF = $nfeReferenciado->getNEcf();
                $nCOO = $nfeReferenciado->getCoo();
                $resposta = $nfeCriarXML->tagrefECF($mod, $nECF, $nCOO);
            }
        }

        // Emitente
        $nfeIdentificacaoEmitente = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'emit'))
                    ->getFirstItem();
        //Dados do emitente
        $CNPJ = $nfeIdentificacaoEmitente->getCnpj();
        $CPF = $nfeIdentificacaoEmitente->getCpf();
        $xNome = $nfeIdentificacaoEmitente->getXNome();
        $xFant = $nfeIdentificacaoEmitente->getXFant();
        $IE = $nfeIdentificacaoEmitente->getIe();
        $IEST = $nfeIdentificacaoEmitente->getIest();
        $IM = $nfeIdentificacaoEmitente->getIm();
        $CNAE = $nfeIdentificacaoEmitente->getCnae();
        $CRT = $nfeIdentificacaoEmitente->getCrt();
        $resposta = $nfeCriarXML->tagemit($CNPJ, $CPF, $xNome, $xFant, $IE, $IEST, $IM, $CNAE, $CRT);
        //endereço do emitente
        $xLgr = $nfeIdentificacaoEmitente->getXLgr();
        $nro = $nfeIdentificacaoEmitente->getNro();
        $xCpl = $nfeIdentificacaoEmitente->getXCpl();
        $xBairro = $nfeIdentificacaoEmitente->getXBairro();
        $cMun = $nfeIdentificacaoEmitente->getCMun();
        $xMun = $nfeIdentificacaoEmitente->getXMun();
        $UF = $nfeIdentificacaoEmitente->getUf();
        $CEP = $nfeIdentificacaoEmitente->getCep();
        $cPais = $nfeIdentificacaoEmitente->getCPais();
        $xPais = $nfeIdentificacaoEmitente->getXPais();
        $fone = $nfeIdentificacaoEmitente->getFone();
        $resposta = $nfeCriarXML->tagenderEmit($xLgr, $nro, $xCpl, $xBairro, $cMun, $xMun, $UF, $CEP, $cPais, $xPais, $fone);
        
        // Destinat�rio
        $nfeIdentificacaoDestinatario = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'dest'))
                    ->getFirstItem();
        //destinatário
        $CNPJ = $nfeIdentificacaoDestinatario->getCnpj();
        $CPF = $nfeIdentificacaoDestinatario->getCpf();
        $idEstrangeiro = $nfeIdentificacaoDestinatario->getIdEstrangeiro;
        $xNome = $nfeIdentificacaoDestinatario->getXNome();
        $indIEDest = $nfeIdentificacaoDestinatario->getIndIeDest();
        $IE = $nfeIdentificacaoDestinatario->getIe();
        $ISUF = null;
        if($nfe->getTransCfop() != '0') {
            $ISUF = $nfeIdentificacaoDestinatario->getIsuf();
        }
        $IM = $nfeIdentificacaoDestinatario->getIm();
        $email = $nfeIdentificacaoDestinatario->getEmail();
        $resposta = $nfeCriarXML->tagdest($CNPJ, $CPF, $idEstrangeiro, $xNome, $indIEDest, $IE, $ISUF, $IM, $email);
        //Endereço do destinatário
        $xLgr = $nfeIdentificacaoDestinatario->getXLgr();
        $nro = $nfeIdentificacaoDestinatario->getNro();
        $xCpl = $nfeIdentificacaoDestinatario->getXCpl();
        $xBairro = $nfeIdentificacaoDestinatario->getXBairro();
        $cMun = $nfeIdentificacaoDestinatario->getCMun();
        $xMun = $nfeIdentificacaoDestinatario->getXMun();
        $UF = $nfeIdentificacaoDestinatario->getUf();
        $CEP = $nfeIdentificacaoDestinatario->getCep();
        $cPais = $nfeIdentificacaoDestinatario->getCPais();
        $xPais = $nfeIdentificacaoDestinatario->getXPais();
        $fone = $nfeIdentificacaoDestinatario->getFone();
        $resposta = $nfeCriarXML->tagenderDest($xLgr, $nro, $xCpl, $xBairro, $cMun, $xMun, $UF, $CEP, $cPais, $xPais, $fone);
        
        // Retirada
        if($nfe->getTemRetirada() == '1') {
            $nfeIdentificacaoRetirada = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'retirada'))
                    ->getFirstItem();
            //Identificação do local de retirada (se diferente do emitente)
            $CNPJ = $nfeIdentificacaoRetirada->getCnpj();
            $CPF = $nfeIdentificacaoRetirada->getCpf();
            $xLgr = $nfeIdentificacaoRetirada->getXLgr();
            $nro = $nfeIdentificacaoRetirada->getNro();
            $xCpl = $nfeIdentificacaoRetirada->getXCpl();
            $xBairro = $nfeIdentificacaoRetirada->getXBairro();
            $cMun = $nfeIdentificacaoRetirada->getCMun();
            $xMun = $nfeIdentificacaoRetirada->getXMun();
            $UF = $nfeIdentificacaoRetirada->getUf();
            $resposta = $nfeCriarXML->tagretirada($CNPJ, $CPF, $xLgr, $nro, $xCpl, $xBairro, $cMun, $xMun, $UF);
        }
        
        // Entrega
        if($nfe->getTemEntrega() == '1') {
            $nfeIdentificacaoEntrega = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'entrega'))
                    ->getFirstItem();
            $CNPJ = $nfeIdentificacaoEntrega->getCnpj();
            $CPF = $nfeIdentificacaoEntrega->getCpf();
            $xLgr = $nfeIdentificacaoEntrega->getXLgr();
            $nro = $nfeIdentificacaoEntrega->getNro();
            $xCpl = $nfeIdentificacaoEntrega->getXCpl();
            $xBairro = $nfeIdentificacaoEntrega->getXBairro();
            $cMun = $nfeIdentificacaoEntrega->getCMun();
            $xMun = $nfeIdentificacaoEntrega->getXMun();
            $UF = $nfeIdentificacaoEntrega->getUf();
            $resposta = $nfeCriarXML->tagentrega($CNPJ, $CPF, $xLgr, $nro, $xCpl, $xBairro, $cMun, $xMun, $UF);
        }
        
        /*
         * N�o utilizar se for igual ao desinatario
        //Identificação dos autorizados para fazer o download da NFe (somente versão 3.1)
        $aAut = array('11111111111111','2222222','33333333333333');
        foreach ($aAut as $aut) {
            if (strlen($aut) == 14) {
                $resp = $nfeCriarXML->tagautXML($aut);
            } else {
                $resp = $nfeCriarXML->tagautXML('', $aut);
            }
        }
         * 
         */
        
        // Produtos
        $nfeProdutos = Mage::getModel('nfe/nfeproduto')->getCollection()
                ->addFieldToFilter('nfe_id', array('eq' => $nfeId));
        foreach($nfeProdutos as $nfeProduto) {
            $EXTIPI = null;
            $vFrete = null;
            $vSeg = null;
            $vDesc = null;
            $vOutro = null;
            $produtoId = $nfeProduto->getProdutoId();
            $nItem = $nfeProduto->getNItem();
            $cProd = $nfeProduto->getCProd();
            $cEAN = $nfeProduto->getCEan();
            $xProd = $nfeProduto->getXProd();
            $NCM = $nfeProduto->getNcm();
            $NVE = $nfeProduto->getNve();
            if($nfeProduto->getExtipi() != '000') {
                $EXTIPI = $nfeProduto->getExtipi();
            }
            $CFOP = $nfeProduto->getCfop();
            $uCom = $nfeProduto->getUCom();
            $qCom = $nfeProduto->getQCom();
            $vUnCom = $nfeProduto->getVUnCom();
            $vProd = $nfeProduto->getVProd();
            $cEANTrib = $nfeProduto->getCEanTrib();
            $uTrib = $nfeProduto->getUTrib();
            $qTrib = $nfeProduto->getQTrib();
            $vUnTrib = $nfeProduto->getVUnTrib();
            if($nfeProduto->getVFrete() != '0.00') {
                $vFrete = $nfeProduto->getVFrete();
            }
            if($nfeProduto->getVSeg() != '0.00') {
                $vSeg = $nfeProduto->getVSeg();
            }
            if($nfeProduto->getVDesc() != '0.00') {
                $vDesc = $nfeProduto->getVDesc();
            }
            if($nfeProduto->getVOutro() != '0.00') {
                $vOutro = $nfeProduto->getVOutro();
            }
            $indTot = $nfeProduto->getIndTot();
            $xPed = $nfeProduto->getXPed();
            $nItemPed = $nfeProduto->getNItemPed();
            $nFCI = '';
            $resposta = $nfeCriarXML->tagprod($nItem, $cProd, $cEAN, $xProd, $NCM, $NVE, $EXTIPI, $CFOP, $uCom, $qCom, $vUnCom, $vProd, $cEANTrib, $uTrib, $qTrib, $vUnTrib, $vFrete, $vSeg, $vDesc, $vOutro, $indTot, $xPed, $nItemPed, $nFCI);
            // Produto Espec�fico
            if($nfeProduto->getEhEspecifico() == '1') {
                $nfeProdutoEspecifico = Mage::getModel('nfe/nfeprodutoespecifico')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->getFirstItem();
                if($nfeProdutoEspecifico->getTipoEspecifico() == 'veicProd') {
                    $tpOp = $nfeProdutoEspecifico->getTpOp();
                    $chassi = $nfeProdutoEspecifico->getChassi();
                    $cCor = $nfeProdutoEspecifico->getCCor();
                    $xCor = $nfeProdutoEspecifico->getXCor();
                    $pot = $nfeProdutoEspecifico->getPot();
                    $cilin = $nfeProdutoEspecifico->getCilin();
                    $pesoL = $nfeProdutoEspecifico->getPesoL();
                    $pesoB = $nfeProdutoEspecifico->getPesoB();
                    $nSerie = $nfeProdutoEspecifico->getNSerie();
                    $tpComb = $nfeProdutoEspecifico->getTpComb();
                    $nMotor = $nfeProdutoEspecifico->getNMotor();
                    $cmt = $nfeProdutoEspecifico->getCmt();
                    $dist = $nfeProdutoEspecifico->getDist();
                    $anoMod = $nfeProdutoEspecifico->getAnoMod();
                    $anoFab = $nfeProdutoEspecifico->getAnoFab();
                    $tpPint = $nfeProdutoEspecifico->getTpPint();
                    $tpVeic = $nfeProdutoEspecifico->getTpVeic();
                    $espVeic = $nfeProdutoEspecifico->getEspVeic();
                    $VIN = $nfeProdutoEspecifico->getVin();
                    $condVeic = $nfeProdutoEspecifico->getCondVeic();
                    $cMod = $nfeProdutoEspecifico->getCMod();
                    $cCorDENATRAN = $nfeProdutoEspecifico->getCCorDenatran();
                    $lota = $nfeProdutoEspecifico->getLota();
                    $tpRest = $nfeProdutoEspecifico->getTpRest();
                    $resposta = $nfeCriarXML->tagveicProd($nItem, $tpOp, $chassi, $cCor, $xCor, $pot, $cilin, $pesoL, $pesoB, $nSerie, $tpComb, $nMotor, $cmt, $dist, $anoMod, $anoFab, $tpPint, $tpVeic, $espVeic, $VIN, $condVeic, $cMod, $cCorDENATRAN, $lota, $tpRest);
                } else if($nfeProdutoEspecifico->getTipoEspecifico() == 'med') {
                    $nLote = $nfeProdutoEspecifico->getNLote();
                    $qLote = $nfeProdutoEspecifico->getQLote();
                    $dFab = $nfeProdutoEspecifico->getDFab();
                    $dVal = $nfeProdutoEspecifico->getDVal();
                    $vPMC = $nfeProdutoEspecifico->getVPmc();
                    $resposta = $nfeCriarXML->tagmed($nItem, $nLote, $qLote, $dFab, $dVal, $vPMC);
                } else if($nfeProdutoEspecifico->getTipoEspecifico() == 'arma') {
                    $tpArma = $nfeProdutoEspecifico->getTpArma();
                    $nSerie = $nfeProdutoEspecifico->getNSerie();
                    $nCano = $nfeProdutoEspecifico->getNCano();
                    $descr = $nfeProdutoEspecifico->getDesc();
                    $resposta = $nfeCriarXML->tagarma($nItem, $tpArma, $nSerie, $nCano, $descr);
                } else if($nfeProdutoEspecifico->getTipoEspecifico() == 'comb') {
                    $cProdANP = $nfeProdutoEspecifico->getCProdAnp();
                    $pMixGN = $nfeProdutoEspecifico->getPMixGn();
                    $codif = $nfeProdutoEspecifico->getCodif();
                    $qTemp = $nfeProdutoEspecifico->getqTemp();
                    $ufCons = $nfeProdutoEspecifico->getufCons();
                    $qBCProd = $nfeProdutoEspecifico->getQBcProd();
                    $vAliqProd = $nfeProdutoEspecifico->getVAliqProd();
                    $vCIDE = $nfeProdutoEspecifico->getVCide();
                    $resposta = $nfeCriarXML->tagarma($nItem, $cProdANP, $pMixGN, $codif, $qTemp, $ufCons, $qBCProd, $vAliqProd, $vCIDE);
                }
            }
            
            if($nfe->getTemImportacao() == '1') {
                $nfeProdutoImportacao = Mage::getModel('nfe/nfeprodutoimportexport')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_operacao', array('importacao'))
                        ->getFirstItem();
                //DI
                $nDI = $nfeProdutoImportacao->getNDi();
                $dDI = $nfeProdutoImportacao->getDDi();
                $xLocDesemb = $nfeProdutoImportacao->getXLocDesemb();
                $UFDesemb = $nfeProdutoImportacao->getUfDesemb();
                $dDesemb = $nfeProdutoImportacao->getDDesemb();
                $tpViaTransp = $nfeProdutoImportacao->getTpViaTransp();
                $vAFRMM = $nfeProdutoImportacao->getVAfrmm();
                $tpIntermedio = $nfeProdutoImportacao->getTpIntermedio();
                $CNPJ = $nfeProdutoImportacao->getCnpj();
                $UFTerceiro = $nfeProdutoImportacao->getUfTerceiro();
                $cExportador = $nfeProdutoImportacao->getCExportador();
                $resposta = $nfeCriarXML->tagDI($nItem, $nDI, $dDI, $xLocDesemb, $UFDesemb, $dDesemb, $tpViaTransp, $vAFRMM, $tpIntermedio, $CNPJ, $UFTerceiro, $cExportador);
                //adi
                $nDI = $nfeProdutoImportacao->getNDi();
                $nAdicao = $nfeProdutoImportacao->getNAdicao();
                $nSeqAdicC = $nfeProdutoImportacao->getNSeqAdic();
                $cFabricante = $nfeProdutoImportacao->getCFabricante();
                $vDescDI = $nfeProdutoImportacao->getVDescDi();
                $nDraw = $nfeProdutoImportacao->getNDraw();
                $resposta = $nfeCriarXML->tagadi($nItem, $nDI, $nAdicao, $nSeqAdicC, $cFabricante, $vDescDI, $nDraw);
                //II
                $nfeProdutoImpostoIi = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('ii'))
                        ->getFirstItem();
                $vBC = $nfeProdutoImpostoIi->getVBc();
                $vDespAdu = $nfeProdutoImpostoIi->getVDespAdu();
                $vII = $nfeProdutoImpostoIi->getVII();
                $vIOF = $nfeProdutoImpostoIi->getVIof();
                $resposta = $nfeCriarXML->tagII($nItem, $vBC, $vDespAdu, $vII, $vIOF);
            }
            
            if($nfe->getTemExportacao() == '1') {
                $nfeProdutoExportacao = Mage::getModel('nfe/nfeprodutoimportexport')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_operacao', array('exportacao'))
                        ->getFirstItem();
                //detExport
                $nDraw = $nfeProdutoExportacao->getNDraw();
                $exportInd = '1';
                $nRE = $nfeProdutoExportacao->getNRe();
                $chNFe = $nfeProdutoExportacao->getChNfe();
                $qExport = $nfeProdutoExportacao->getQExport();
                $resposta = $nfeCriarXML->tagdetExport($nItem, $nDraw, $exportInd, $nRE, $chNFe, $qExport);
            }
            
            //imposto
            $vTotTrib = $nfeProduto->getVTotTrib();
            $resposta = $nfeCriarXML->tagimposto($nItem, $vTotTrib);
            
            // Produto ICMS
            if($nfeProduto->getTemIcms() == '1') {
                $nfeProdutoImpostoIcms = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('icms'))
                        ->getFirstItem();
                if($nfeProdutoImpostoIcms->getCst()) {
                    $orig = $nfeProdutoImpostoIcms->getOrig();
                    $cst = $nfeProdutoImpostoIcms->getCst();
                    $modBC = $nfeProdutoImpostoIcms->getModBc();
                    $pRedBC = $nfeProdutoImpostoIcms->getPRedBc();
                    $vBC = $nfeProdutoImpostoIcms->getVBc();
                    $pICMS = $nfeProdutoImpostoIcms->getPIcms();
                    $vICMS = $nfeProdutoImpostoIcms->getVIcms();
                    $vICMSDeson = $nfeProdutoImpostoIcms->getVIcmsDeson();
                    $motDesICMS = $nfeProdutoImpostoIcms->getMotDesIcms();
                    $modBCST = $nfeProdutoImpostoIcms->getModBcSt();
                    $pMVAST = $nfeProdutoImpostoIcms->getPMvaSt();
                    $pRedBCST = $nfeProdutoImpostoIcms->getPRedBcSt();
                    $vBCST = $nfeProdutoImpostoIcms->getVBcSt();
                    $pICMSST = $nfeProdutoImpostoIcms->getPIcmsSt();
                    $vICMSST = $nfeProdutoImpostoIcms->getVIcmsSt();
                    $pDif = $nfeProdutoImpostoIcms->getPDif();
                    $vICMSDif = $nfeProdutoImpostoIcms->getVIcmsDif();
                    $vICMSOp = $nfeProdutoImpostoIcms->getVIcmsOp();
                    $vBCSTRet = $nfeProdutoImpostoIcms->getVbcstRet();
                    $vICMSSTRet = $nfeProdutoImpostoIcms->getVIcmsStRet();
                    $resposta = $nfeCriarXML->tagICMS($nItem, $orig, $cst, $modBC, $pRedBC, $vBC, $pICMS, $vICMS, $vICMSDeson, $motDesICMS, $modBCST, $pMVAST, $pRedBCST, $vBCST, $pICMSST, $vICMSST, $pDif, $vICMSDif, $vICMSOp, $vBCSTRet, $vICMSSTRet);
                } else if($nfeProdutoImpostoIcms->getCsoSn()) {
                    $orig = $nfeProdutoImpostoIcms->getOrig();
                    $csosn = $nfeProdutoImpostoIcms->getCsoSn();
                    $modBC = $nfeProdutoImpostoIcms->getModBc();
                    $pRedBC = $nfeProdutoImpostoIcms->getPRedBc();
                    $vBC = $nfeProdutoImpostoIcms->getVBc();
                    $pICMS = $nfeProdutoImpostoIcms->getPIcms();
                    $vICMS = $nfeProdutoImpostoIcms->getVIcms();
                    $pCredSN = $nfeProdutoImpostoIcms->getPCredSn();
                    $vCredICMSSN = $nfeProdutoImpostoIcms->getVCredIcmsSn();
                    $modBCST = $nfeProdutoImpostoIcms->getModBcSt();
                    $pMVAST = $nfeProdutoImpostoIcms->getPMvaSt();
                    $pRedBCST = $nfeProdutoImpostoIcms->getPRedBcSt();
                    $vBCST = $nfeProdutoImpostoIcms->getVBcSt();
                    $pICMSST = $nfeProdutoImpostoIcms->getPIcmsSt();
                    $vICMSST = $nfeProdutoImpostoIcms->getVIcmsSt();
                    $vBCSTRet = $nfeProdutoImpostoIcms->getVbcstRet();
                    $vICMSSTRet = $nfeProdutoImpostoIcms->getVIcmsStRet(); 
                    $resposta = $nfeCriarXML->tagICMSSN($nItem, $orig, $csosn, $modBC, $vBC, $pRedBC, $pICMS, $vICMS, $pCredSN, $vCredICMSSN, $modBCST, $pMVAST, $pRedBCST, $vBCST, $pICMSST, $vICMSST, $vBCSTRet, $vICMSSTRet);
                }
                //ICMSPart
                //$resp = $nfe->tagICMSPart($nItem, $orig, $cst, $modBC, $vBC, $pRedBC, $pICMS, $vICMS, $modBCST, $pMVAST, $pRedBCST, $vBCST, $pICMSST, $vICMSST, $pBCOp, $ufST);
                //ICMSST
                //$resp = $nfe->tagICMSST($nItem, $orig, $cst, $vBCSTRet, $vICMSSTRet, $vBCSTDest, $vICMSSTDest);
            }
            
            // Produto PIS
            if($nfeProduto->getTemPis() == '1') {
                $nfeProdutoImpostoPis = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('pis'))
                        ->getFirstItem();
                $pisCst = $nfeProdutoImpostoPis->getCst();
                if(strlen($pisCst) == 1) {
                    $pisCst = '0'.$nfeProdutoImpostoPis->getCst();
                }
                $cst = $pisCst;
                $vBC = $nfeProdutoImpostoPis->getVBc();
                $pPIS = $nfeProdutoImpostoPis->getPPis();
                $vPIS = $nfeProdutoImpostoPis->getVPis();
                $qBCProd = $nfeProdutoImpostoPis->getQBcProd();
                $vAliqProd = $nfeProdutoImpostoPis->getVAliqProd();
                $resposta = $nfeCriarXML->tagPIS($nItem, $cst, $vBC, $pPIS, $vPIS, $qBCProd, $vAliqProd);
            }
            //PISST
            //$resp = $nfe->tagPISST($nItem, $vBC, $pPIS, $qBCProd, $vAliqProd, $vPIS);
            
            // Produto COFINS
            if($nfeProduto->getTemCofins() == '1') {
                $nfeProdutoImpostoCofins = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('cofins'))
                        ->getFirstItem();
                $cofinsCst = $nfeProdutoImpostoCofins->getCst();
                if(strlen($cofinsCst) == 1) {
                    $cofinsCst = '0'.$nfeProdutoImpostoCofins->getCst();
                }
                $cst = $cofinsCst;
                $vBC = $nfeProdutoImpostoCofins->getVBc();
                $pCOFINS = $nfeProdutoImpostoCofins->getPCofins();
                $vCOFINS = $nfeProdutoImpostoCofins->getVCofins();
                $qBCProd = $nfeProdutoImpostoCofins->getQBcProd();
                $vAliqProd = $nfeProdutoImpostoCofins->getVAliqProd();
                $resposta = $nfeCriarXML->tagCOFINS($nItem, $cst, $vBC, $pCOFINS, $vCOFINS, $qBCProd, $vAliqProd);
            }
            //COFINSST
            //$resp = $nfe->tagCOFINSST($nItem, $vBC, $pCOFINS, $qBCProd, $vAliqProd, $vCOFINS);
            
            // Produto IPI
            if($nfeProduto->getTemIpi() == '1') {
                $nfeProdutoImpostoIpi = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('ipi'))
                        ->getFirstItem();
                $ipiCst = $nfeProdutoImpostoIpi->getCst();
                if(strlen($ipiCst) == 1) {
                    $ipiCst = '0'.$nfeProdutoImpostoIpi->getCst();
                }
                $vBC = null;
                $pIPI = null;
                $qUnid = null;
                $vUnid = null;
                $cst = $ipiCst;
                $clEnq = $nfeProdutoImpostoIpi->getClEnq();
                $cnpjProd = $nfeProdutoImpostoIpi->getCnpjProd();
                $cSelo = $nfeProdutoImpostoIpi->getCSelo();
                $qSelo = $nfeProdutoImpostoIpi->getQSelo();
                $cEnq = $nfeProdutoImpostoIpi->getCEnq();
                if($nfeProdutoImpostoIpi->getVBc()) {
                    $vBC = $nfeProdutoImpostoIpi->getVBc();
                }
                if($nfeProdutoImpostoIpi->getPIpi()) {
                    $pIPI = $nfeProdutoImpostoIpi->getPIpi();
                }
                if($nfeProdutoImpostoIpi->getQUnid() != '0.000') {
                    $qUnid = $nfeProdutoImpostoIpi->getQUnid();
                }
                if($nfeProdutoImpostoIpi->getVUnid() != '0.000') {
                    $vUnid = $nfeProdutoImpostoIpi->getVUnid();
                }
                $vIPI = $nfeProdutoImpostoIpi->getVIpi();
                $resposta = $nfeCriarXML->tagIPI($nItem, $cst, $clEnq, $cnpjProd, $cSelo, $qSelo, $cEnq, $vBC, $pIPI, $qUnid, $vUnid, $vIPI);
            }
            
            // Produto ISSQN
            if($nfeProduto->getTemIssqn() == '1') {
                $nfeProdutoImpostoIssqn = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('issqn'))
                        ->getFirstItem();
                $vBC = $nfeProdutoImpostoIssqn->getVBc();
                $vAliq = $nfeProdutoImpostoIssqn->getVAliq();
                $vISSQN = $nfeProdutoImpostoIssqn->getVIssqn();
                $cMunFG = $nfeProdutoImpostoIssqn->getCMunFg();
                $cListServ = $nfeProdutoImpostoIssqn->getCListServ();
                $vDeducao = $nfeProdutoImpostoIssqn->getVDeducao();
                $vOutro = $nfeProdutoImpostoIssqn->getVOutro();
                $vDescIncond = $nfeProdutoImpostoIssqn->getVDescIncond();
                $vDescCond = $nfeProdutoImpostoIssqn->getVDescCond();
                $vISSRet = $nfeProdutoImpostoIssqn->getVIssRet();
                $indISS = $nfeProdutoImpostoIssqn->getIndIss();
                $cServico = $nfeProdutoImpostoIssqn->getCServico();
                $cMun = $nfeProdutoImpostoIssqn->getCMun();
                $cPais = $nfeProdutoImpostoIssqn->getCPais();
                $nProcesso = $nfeProdutoImpostoIssqn->getNProcesso();
                $indIncentivo = $nfeProdutoImpostoIssqn->getIndIncentivo();
                $resposta = $nfeCriarXML->tagISSQN($nItem, $vBC, $vAliq, $vISSQN, $cMunFG, $cListServ, $vDeducao, $vOutro, $vDescIncond, $vDescCond, $vISSRet, $indISS, $cServico, $cMun, $cPais, $nProcesso, $indIncentivo);
            }
            
            $pDevol = $nfeProduto->getPDevol();
            $vIPIDevol = $nfeProduto->getVIpiDevol();
            if($pDevol && $vIPIDevol) {
               $resposta = $nfeCriarXML->tagimpostoDevol($nItem, $pDevol, $vIPIDevol); 
            }
            
            $texto = $nfeProduto->getInfAdProd();
            $resposta = $nfeCriarXML->taginfAdProd($nItem, $texto );
        }
        
        //ICMSTot
        $vBCTot = $nfe->getVBc(); 
        $vICMSTot = $nfe->getVIcms(); 
        $vICMSDesonTot = $nfe->getVIcmsDeson(); 
        $vBCSTTot = $nfe->getVBcSt(); 
        $vSTTot = $nfe->getVSt(); 
        $vProdTot = $nfe->getVProd(); 
        $vFreteTot = $nfe->getVFrete();
        $vSegTot = $nfe->getVSeg(); 
        $vDescTot = $nfe->getVDesc(); 
        $vIITot = $nfe->getVLl(); 
        $vIPITot = $nfe->getVIpi(); 
        $vPISTot = $nfe->getVPis();
        $vCOFINSTot = $nfe->getVCofins(); 
        $vOutroTot = $nfe->getVOutro(); 
        $vNFTot = $nfe->getVNf();
        $vTotTribTot = $nfe->getVTotTrib();
        $resposta = $nfeCriarXML->tagICMSTot($vBCTot, $vICMSTot, $vICMSDesonTot, $vBCSTTot, $vSTTot, $vProdTot, $vFreteTot, $vSegTot, $vDescTot, $vIITot, $vIPITot, $vPISTot, $vCOFINSTot, $vOutroTot, $vNFTot, $vTotTribTot);
        
        //ISSQNTot
        $vServIss = $nfe->getVServ(); 
        $vBCIss = $nfe->getVBcIss(); 
        $vISS = $nfe->getVIss(); 
        $vPISIss = $nfe->getVPisIss(); 
        $vCOFINSIss = $nfe->getVCofinsIss(); 
        $dCompetIss = $nfe->getDCompet(); 
        $vDeducaoIss = $nfe->getVDeducao(); 
        $vOutroIss = ''; 
        $vDescIncondIss = $nfe->getVDescIncond(); 
        $vDescCondIss = $nfe->getVDescCond(); 
        $vISSRetIss = $nfe->getVIssRet(); 
        $cRegTribIss = $nfe->getCRegTrib(); 
        if($dCompetIss) {
            $resposta = $nfeCriarXML->tagISSQNTot($vServIss, $vBCIss, $vISS, $vPISIss, $vCOFINSIss, $dCompetIss, $vDeducaoIss, $vOutroIss, $vDescIncondIss, $vDescCondIss, $vISSRetIss, $cRegTribIss);
        }
        
        //retTrib
        $vRetPIS = null;
        $vRetCOFINS = null;
        $vRetCSLL = null;
        $vBCIRRF = null;
        $vIRRF = null;
        $vBCRetPrev = null;
        $vRetPrev = null;
        $temRetencao = false;
        if($nfe->getVRetPis() != '0.00') {
            $vRetPIS = $nfe->getVRetPis();
            $temRetencao = true;
        }
        if($nfe->getVRetCofins() != '0.00') {
            $vRetCOFINS = $nfe->getVRetCofins(); 
            $temRetencao = true;
        }
        if($nfe->getVRetCsll() != '0.00') {
            $vRetCSLL = $nfe->getVRetCsll();
            $temRetencao = true;
        }
        if($nfe->getVBcIrrf() != '0.00') {
            $vBCIRRF = $nfe->getVBcIrrf(); 
            $temRetencao = true;
        }
        if($nfe->getVIrrf() != '0.00') {
            $vIRRF = $nfe->getVIrrf();
            $temRetencao = true;
        }
        if($nfe->getVBcRetPrev() != '0.00') {
            $vBCRetPrev = $nfe->getVBcRetPrev(); 
            $temRetencao = true;
        }
        if($nfe->getVRetPrev() != '0.00') {
            $vRetPrev = $nfe->getVRetPrev();
            $temRetencao = true;
        }
        if($temRetencao) {
            $resposta = $nfeCriarXML->tagretTrib($vRetPIS, $vRetCOFINS, $vRetCSLL, $vBCIRRF, $vIRRF, $vBCRetPrev, $vRetPrev);
        }
        
        //frete
        $modFrete = $nfe->getTransModFrete(); //0=Por conta do emitente; 1=Por conta do destinatário/remetente; 2=Por conta de terceiros;
        $resposta = $nfeCriarXML->tagtransp($modFrete);

        //transportadora
        $CNPJTrans = $nfe->getTransCnpj();
        $CPFTrans = $nfe->getTransCpf();
        $xNomeTrans = $nfe->getTrans_x_nome();
        $IETrans = $nfe->getTransIe();
        $xEnderTrans = $nfe->getTrans_x_ender();
        $xMunTrans = $nfe->getTrans_x_mun();
        $UFTrans = $nfe->getTransUf();
        $resposta = $nfeCriarXML->tagtransporta($CNPJTrans, $CPFTrans, $xNomeTrans, $IETrans, $xEnderTrans, $xMunTrans, $UFTrans);

        //valores retidos para transporte
        $vServTrans = null;
        $vBCRetTrans = null;
        $pICMSRetTrans = null;
        $vICMSRetTrans = null;
        $CFOPTrans = null;
        $cMunFGTrans = null;
        $temRetencaoTransp = false;
        if($nfe->getTrans_v_serv() != '0.00') {
            $vServTrans = $nfe->getTrans_v_serv();
            $temRetencaoTransp = true;
        }
        if($nfe->getTrans_v_bcRet() != '0.00') {
            $vBCRetTrans = $nfe->getTrans_v_bcRet();
            $temRetencaoTransp = true;
        }
        if($nfe->getTrans_p_icmsRet() != '0.0000') {
            $pICMSRetTrans = $nfe->getTrans_p_icmsRet();
            $temRetencaoTransp = true;
        }
        if($nfe->getTrans_v_icmsRet() != '0.00') {
            $vICMSRetTrans = $nfe->getTrans_v_icmsRet();
            $temRetencaoTransp = true;
        }
        if($nfe->getTransCfop() != '0') {
            $CFOPTrans = $nfe->getTransCfop();
            $temRetencaoTransp = true;
        }
        if($temRetencaoTransp) {
            $cMunFGTrans = $nfe->getTrans_c_munFg();
            $resposta = $nfeCriarXML->tagretTransp($vServTrans, $vBCRetTrans, $pICMSRetTrans, $vICMSRetTrans, $CFOPTrans, $cMunFGTrans);
        }
        
        //Dados dos veiculos de transporte
        $placaTrans =  $nfe->getTransPlaca();
        $UFVeic = $nfe->getTransVeicUf();
        $RNTCTrans = $nfe->getTransRntc();
        if($placaTrans && $UFVeic) {
           $resposta = $nfeCriarXML->tagveicTransp($placaTrans, $UFVeic, $RNTCTrans); 
        }

        // Reboques Transporte
        if($nfe->getTransTemReboque() == '1') {
            $reboqueCollection = Mage::getModel('nfe/nfetransporte')->getCollection()
                    ->addFieldToFilter('nfe_id', $nfeId)
                    ->addFieldToFilter('tipo_informacao', 'reboque');
            foreach($reboqueCollection as $reboqueModel) {
                $placaReb = $reboqueModel->getPlaca();
                $UFReb = $reboqueModel->getUf();
                $RNTCReb = $reboqueModel->getRntc();
                $vagaoReb = $reboqueModel->getVagao();
                $balsaReb = $reboqueModel->getBalsa();
                $resposta = $nfeCriarXML->tagreboque($placaReb, $UFReb, $RNTCReb, $vagaoReb, $balsaReb);
            }
        }
        
        // Volumes Transporte
        if($nfe->getTransTemVol() == '1') {
            $volumeCollection = Mage::getModel('nfe/nfetransporte')->getCollection()
                    ->addFieldToFilter('nfe_id', $nfeId)
                    ->addFieldToFilter('tipo_informacao', 'vol');
            foreach($volumeCollection as $volumeModel) {
                $lacresVol = array();
                $qVol = $volumeModel->getQVol(); //Quantidade de volumes transportados
                $espVol = $volumeModel->getEsp(); //Espécie dos volumes transportados
                $marcaVol = $volumeModel->getMarca(); //Marca dos volumes transportados
                $nVol = $volumeModel->getNVol(); //Numeração dos volume
                $pesoLVol = $volumeModel->getPesoL();
                $pesoBVol = $volumeModel->getPesoB();
                if($volumeModel->getNLacre()) {
                    $lacresVol = $volumeModel->getNLacre();
                }
                $resposta = $nfeCriarXML->tagvol($qVol, $espVol, $marcaVol, $nVol, $pesoLVol, $pesoBVol, $lacresVol);
            }
        }
        
        /*
        // Lacres Transporte
        $lacreCollection = Mage::getModel('nfe/nfetransporte')->getCollection()
                ->addFieldToFilter('nfe_id', $nfeId)
                ->addFieldToFilter('tipo_informacao', 'lacres');
        foreach($lacreCollection as $lacreModel) {
            
         */
        
        //dados da fatura
        $nFatCob = null;
        $vOrigCob = null;
        $vDescCob = null;
        $vLiqCob = null;
        $temFatura = false;
        if($nfe->getCob_n_fat()) {
            $nFatCob = $nfe->getCob_n_fat();
            $temFatura = true;
        }
        if($nfe->getCob_v_orig() != '0.00') {
            $vOrigCob = $nfe->getCob_v_orig();
            $temFatura = true;
        }
        if($nfe->getCob_v_desc() != '0.00') {
            $vDescCob = $nfe->getCob_v_desc();
            $temFatura = true;
        }
        if($nfe->getCob_v_liq() != '0.00') {
            $vLiqCob = $nfe->getCob_v_liq();
            $temFatura = true;
        }
        if($temFatura) {
            $resposta = $nfeCriarXML->tagfat($nFatCob, $vOrigCob, $vDescCob, $vLiqCob);
        }

        // Cobran�as
        $cobrancaCollection = Mage::getModel('nfe/nfecobranca')->getCollection()
                ->addFieldToFilter('nfe_id', $nfeId);
        foreach($cobrancaCollection as $cobrancaModel) {
            $nDupCob = $cobrancaModel->getCob_n_dup();
            $dVencCob = substr($cobrancaModel->getCob_d_venc(), 0, 4).substr($cobrancaModel->getCob_d_venc(), 7, 3).substr($cobrancaModel->getCob_d_venc(), 4, 3);
            $vDupCob = $cobrancaModel->getCob_v_dup();
            $resposta = $nfeCriarXML->tagdup($nDupCob, $dVencCob, $vDupCob);
        }
        
        //informações Adicionais
        $infAdFisco = $nfe->getInfInfAdFisco();
        $infCpl = $nfe->getInfInfCpl();
        $resposta = $nfeCriarXML->taginfAdic($infAdFisco, $infCpl);

        //observações emitente
        $xCampoInf = $nfe->getInf_x_campo();
        $xTextoInf = $nfe->getInf_x_texto();
        if($xTextoInf) {
            $resposta = $nfeCriarXML->tagobsCont($xCampoInf, $xTextoInf);
        }

        /*
        //observações fisco
        $xCampo = $obs[0];
        $xTexto = $obs[1];
        //$resp = $nfe->tagobsFisco($xCampo, $xTexto);
         */

        //Dados do processo
        $nProcInf = $nfe->getInf_n_proc();
        $indProcInf = $nfe->getInfIndProc();
        if($nProcInf && $indProcInf) {
           $resposta = $nfeCriarXML->tagprocRef($nProcInf, $indProcInf); 
        }

        //dados exportação
        if($nfe->getTemExportacao() == '1') {
            $UFSaidaPais = $nfe->getExpUfSaidaPais();
            $xLocExporta = $nfe->getExp_x_locExporta();
            $xLocDespacho = $nfe->getExp_x_locDespacho();
            $resposta = $nfeCriarXML->tagexporta($UFSaidaPais, $xLocExporta, $xLocDespacho);
        }
        
        //dados de compras
        if($nfe->getTpNf() == '0') {
            $xNEmpComp = $nfe->getComp_x_n_emp();
            $xPedComp = $nfe->getComp_x_ped();
            $xContComp = $nfe->getComp_x_cont();
            $resposta = $nfeCriarXML->tagcompra($xNEmpComp, $xPedComp, $xContComp);
        }
    }
    
    private function gerarArquivoXML($nfe, $nfeCriarXML) {
        $resposta = $nfeCriarXML->montaNFe();
        if ($resposta) {
            if($nfe->getTpNf() == '0') {
                $tipo = 'entrada';
            } else {
                $tipo = 'saida';
            }
            $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS;
            header('Content-type: text/xml; charset=UTF-8');
            $xmlNfe = $nfeCriarXML->getXML();
            $this->salvarXml($xmlNfe, $caminho, $nfe->getIdTag());
            return 'sucesso';
        } else {
            header('Content-type: text/html; charset=UTF-8');
            foreach ($nfeCriarXML->erros as $err) {
                $erros = 'tag: &lt;'.$err['tag'].'&gt; ---- '.$err['desc'].'<br>';
            }
            return $erros;
        }
    }
    
    private function salvarXml($xmlNfe, $caminho, $idTag) {
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->preservWhiteSpace = false; //elimina espaços em branco
        $doc->formatOutput = false;
        $doc->loadXML($xmlNfe, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
        $doc->save($caminho.$idTag.'.xml');
    }
    
    private function getFormaPagamento($order) {
        $indPag = null;
        if($order->getPayment()->getMethodInstance()->getCode() == 'Maxima_Cielo_Cc') {
            if((int)$order->getPayment()->getAdditionalInformation('Cielo_installments') > 1) {
                $indPag = '1';
            } else {
                $indPag = '0';
            }
        } else {
            $indPag = '0';
        }
        
        return $indPag;
    }
    
    private function setStatusPedido($order) {
        $order->setData('state', Mage_Sales_Model_Order::STATE_PROCESSING);
        $order->setData('status', 'nfe_aguardando');
        $order->addStatusToHistory('nfe_aguardando', 
        utf8_encode('Foi solicitada a emiss�o da NF-e para este pedido.<br/>
         Status: Aguardando Aprova��o'));
        
        $order->save();
    }
    
    public function gerarCodigoNumerico($length=8) {
        $numero = null;
        for ($x=0;$x<$length;$x++){
            $numero .= rand(0,9);
        }
        return $numero;
    }
    
    public function calcularDV($chave43) {
        $multiplicadores = array(2,3,4,5,6,7,8,9);
        $i = 42;
        while ($i >= 0) {
            for ($m=0; $m<count($multiplicadores) && $i>=0; $m++) {
                $soma_ponderada+= $chave43[$i] * $multiplicadores[$m];
                $i--;
            }
        }
        $resto = $soma_ponderada % 11;
        if ($resto == '0' || $resto == '1') {
            return 0;
        } else {
            return (11 - $resto);
       }
    }
    
    public function setRange($nfeRange) {
        $serie = $nfeRange->getSerie();
        $nNF = $nfeRange->getNumero();
        $novoRangeNumero = $nNF + 1;
        if($novoRangeNumero > 999999999) {
            $nfeRange->setSerie($serie+1);
        }
        $nfeRange->setNumero($novoRangeNumero);
        $nfeRange->save();
    }
    
    public function confirmarItensNfe($nfeId, $produtosMovimento) {
        $produtosNfe = Mage::getModel('nfe/nfeproduto')->getCollection()->addFieldToFilter('nfe_id', array('eq' => $nfeId));
        foreach($produtosNfe as $produtoNfe) {
            $foiMovimentado = false;
            foreach($produtosMovimento as $produtoMovimentado) {
                if($produtoNfe->getProduto() == $produtoMovimentado) {
                    $foiMovimentado = true;
                }
            }
            if(!$foiMovimentado) {
                $produtoNfe->delete();
            }
        }
    }
    
    /**
     * M�todo pertence a classe ToolsNFePHP.class.php do projeto NFE-PHP
     * signXML
     * Assinador TOTALMENTE baseado em PHP para arquivos XML
     * este assinador somente utiliza comandos nativos do PHP para assinar
     * os arquivos XML
     *
     * @name signXML
     * @param  mixed $docxml Path para o arquivo xml ou String contendo o arquivo XML a ser assinado
     * @param  string $tagid TAG do XML que devera ser assinada
     * @return mixed false se houve erro ou string com o XML assinado
     */
    private function assinarXml($docxml, $tagid = '', $nfe) {
        $msg = 'sucesso';
        try {
            $nfeHelper = Mage::Helper('nfe/nfeHelper');
            $certificado = $nfeHelper->pLoadCerts();
            if($certificado['retorno'] != 'sucesso') {
                return $certificado['retorno'];
            }
            if ($tagid == '') {
                $msg = "Uma tag deve ser indicada para que seja assinada!!";
                return $msg;
            }
            if ($docxml == '') {
                $msg = "Um xml deve ser passado para que seja assinado!!";
                return $msg;
            }
            if (! is_file($certificado['priKey'])) {
                $msg = "Arquivo da chave privada parece invalido, verifique!!";
                return $msg;
            }
            if (is_file($docxml)) {
                $xml = file_get_contents($docxml);
            } else {
                $xml = $docxml;
            }
            //obter a chave privada para a assinatura
            //modificado para permitir a leitura de arquivos maiores
            //que o normal que é cerca de 2kBytes.
            if (! $filep = fopen($certificado['priKey'], "r")) {
                $msg = "Erro ao ler arquivo da chave privada!!";
                return $msg;
            }
            $priv_key = '';
            while (! feof($filep)) {
                $priv_key .= fread($filep, 8192);
            }
            fclose($filep);
            $pkeyid = openssl_get_privatekey($priv_key);
            //limpeza do xml com a retirada dos CR, LF e TAB
            $order = array("\r\n", "\n", "\r", "\t");
            $replace = '';
            $xml = str_replace($order, $replace, $xml);
            // Habilita a manipulaçao de erros da libxml
            libxml_use_internal_errors(true);
            //limpa erros anteriores que possam estar em memória
            libxml_clear_errors();
            //carrega o documento DOM
            $xmldoc = new DOMDocument('1.0', 'utf-8');
            $xmldoc->preservWhiteSpace = false; //elimina espaços em branco
            $xmldoc->formatOutput = false;
            //é muito importante deixar ativadas as opçoes para limpar os espacos em branco
            //e as tags vazias
            if ($xmldoc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG)) {
                $root = $xmldoc->documentElement;
            } else {
                $msg = "Erro ao carregar XML, provavel erro na passagem do parâmetro docxml ou no próprio xml!!";
                $errors = libxml_get_errors();
                if (!empty($errors)) {
                    $countI = 1;
                    foreach ($errors as $error) {
                        $msg .= "\n  [$countI]-".trim($error->message);
                        $countI++;
                    }
                    libxml_clear_errors();
                }
                return $msg;
            }
            //extrair a tag com os dados a serem assinados
            $node = $xmldoc->getElementsByTagName($tagid)->item(0);
            if (!isset($node)) {
                $msg = "A tag < $tagid > não existe no XML!!";
                return $msg;
            }
            //extrai o atributo ID com o numero da NFe de 44 digitos
            $Id = $node->getAttribute("Id");
            //extrai e canoniza os dados da tag para uma string
            $dados = $node->C14N(false, false, null, null);
            //calcular o hash dos dados
            $hashValue = hash('sha1', $dados, true);
            //converte o valor para base64 para serem colocados no xml
            $digValue = base64_encode($hashValue);
            //monta a tag da assinatura digital
            $Signature = $xmldoc->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
            $root->appendChild($Signature);
            $SignedInfo = $xmldoc->createElement('SignedInfo');
            $Signature->appendChild($SignedInfo);
            //estabelece o método de canonização
            $newNode = $xmldoc->createElement('CanonicalizationMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            //estabelece o método de assinatura
            $newNode = $xmldoc->createElement('SignatureMethod');
            $SignedInfo->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            //indica a referencia da assinatura
            $Reference = $xmldoc->createElement('Reference');
            $SignedInfo->appendChild($Reference);
            $Reference->setAttribute('URI', '#'.$Id);
            //estabelece as tranformações
            $Transforms = $xmldoc->createElement('Transforms');
            $Reference->appendChild($Transforms);
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            $newNode = $xmldoc->createElement('Transform');
            $Transforms->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            //estabelece o método de calculo do hash
            $newNode = $xmldoc->createElement('DigestMethod');
            $Reference->appendChild($newNode);
            $newNode->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            //carrega o valor do hash
            $newNode = $xmldoc->createElement('DigestValue', $digValue);
            $Reference->appendChild($newNode);
            //extrai e canoniza os dados a serem assinados para uma string
            $dados = $SignedInfo->C14N(false, false, null, null);
            //inicializa a variavel que irá receber a assinatura
            $signature = '';
            //executa a assinatura digital usando o resource da chave privada
            openssl_sign($dados, $signature, $pkeyid);
            //codifica assinatura para o padrão base64
            $signatureValue = base64_encode($signature);
            //insere o valor da assinatura digtal
            $newNode = $xmldoc->createElement('SignatureValue', $signatureValue);
            $Signature->appendChild($newNode);
            //insere a chave publica usada para conferencia da assinatura digital
            $KeyInfo = $xmldoc->createElement('KeyInfo');
            $Signature->appendChild($KeyInfo);
            //X509Data
            $X509Data = $xmldoc->createElement('X509Data');
            $KeyInfo->appendChild($X509Data);
            //carrega o certificado sem as tags de inicio e fim
            $cert = $nfeHelper->pCleanCerts($certificado['pubKey']);
            //X509Certificate
            $newNode = $xmldoc->createElement('X509Certificate', $cert);
            $X509Data->appendChild($newNode);
            //grava em uma string o objeto DOM
            $xml = $xmldoc->saveXML();
            //libera a chave privada da memoria
            openssl_free_key($pkeyid);
            // Salva o XML
            if($nfe->getTpNf() == '0') {
                $tipo = 'entrada';
            } else {
                $tipo = 'saida';
            }
            $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS;
            $this->salvarXml($xml, $caminho, $nfe->getIdTag());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return 'sucesso';
    } //fim signXML
    
    /**
     * M�todo pertence a classe ToolsNFePHP.class.php do projeto NFE-PHP
     * validXML
     * Verifica o xml com base no xsd
     * Esta função pode validar qualquer arquivo xml do sistema de NFe
     * Há um bug no libxml2 para versões anteriores a 2.7.3
     * que causa um falso erro na validação da NFe devido ao
     * uso de uma marcação no arquivo tiposBasico_v1.02.xsd
     * onde se le {0 , } substituir por *
     * A validação não deve ser feita após a inclusão do protocolo !!!
     * Caso seja passado uma NFe ainda não assinada a falta da assinatura será desconsiderada.
     * @name validXML
     * @author Roberto L. Machado <linux.rlm at gmail dot com>
     * @param    string  $xml  string contendo o arquivo xml a ser validado ou seu path
     * @param    string  $xsdfile Path completo para o arquivo xsd
     * @param    array   $aError Variável passada como referencia irá conter as mensagens de erro se houverem
     * @return   boolean
     */
    private function validarXml($xml = '', $xsdFile = '', &$aError = array()) {
        try {
            $flagOK = true;
            // Habilita a manipulaçao de erros da libxml
            libxml_use_internal_errors(true);
            //limpar erros anteriores que possam estar em memória
            libxml_clear_errors();
            //verifica se foi passado o xml
            if (strlen($xml)==0) {
                $msg = 'Você deve passar o conteudo do xml assinado como parâmetro '
                       .'ou o caminho completo até o arquivo.';
                return $msg;
            }
            // instancia novo objeto DOM
            $dom = new DOMDocument('1.0', 'utf-8');
            $dom->preserveWhiteSpace = false; //elimina espaços em branco
            $dom->formatOutput = false;
            // carrega o xml tanto pelo string contento o xml como por um path
            if (is_file($xml)) {
                $dom->load($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            } else {
                $dom->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOEMPTYTAG);
            }
            // pega a assinatura
            $Signature = $dom->getElementsByTagName('Signature')->item(0);
            //recupera os erros da libxml
            $errors = libxml_get_errors();
            if (!empty($errors)) {
                //o dado passado como $docXml não é um xml
                $msg = 'O dado informado não é um XML ou não foi encontrado. '
                        . 'Você deve passar o conteudo de um arquivo xml assinado como parâmetro.';
                return $msg;
            }
            if ($xsdFile=='') {
                if (is_file($xml)) {
                    $contents = file_get_contents($xml);
                } else {
                    $contents = $xml;
                }
                $sxml = simplexml_load_string($contents);
                $nome = $sxml->getName();
                $sxml = null;
                //determinar qual o arquivo de schema válido
                //buscar o nome do scheme
                switch ($nome) {
                    case 'evento':
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $tpEvento = $node->getElementsByTagName('tpEvento')->item(0)->nodeValue;
                        switch ($tpEvento) {
                            case '110110':
                                //carta de correção
                                $xsdFile = "CCe_v$ver.xsd";
                                break;
                            default:
                                $xsdFile = "";
                                break;
                        }
                        break;
                    case 'envEvento':
                        //obtem o node com a versão
                        $node = $dom->getElementsByTagName('evento')->item(0);
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $tpEvento = $node->getElementsByTagName('tpEvento')->item(0)->nodeValue;
                        switch ($tpEvento) {
                            case '110110':
                                //carta de correção
                                $xsdFile = "envCCe_v$ver.xsd";
                                break;
                            default:
                                $xsdFile = "envEvento_v$ver.xsd";
                                break;
                        }
                        break;
                    case 'NFe':
                        //obtem o node com a versão
                        $node = $dom->getElementsByTagName('infNFe')->item(0);
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = "nfe_v$ver.xsd";
                        break;
                    case 'nfeProc':
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = "procNFe_v$ver.xsd";
                        break;
                    default:
                        //obtem o node com a versão
                        $node = $dom->documentElement;
                        //obtem a versão do layout
                        $ver = trim($node->getAttribute("versao"));
                        $xsdFile = $nome."_v".$ver.".xsd";
                        break;
                }
                $schemeVersion = 'PL_008e';
                $diretorio = Mage::getBaseDir(). DS . 'nfe' . DS . 'schemes' . DS . $schemeVersion . DS;
                $aFile = $diretorio.$xsdFile;
                if (empty($aFile) || empty($aFile[0])) {
                    $msg = "Erro na localização do schema xsd.\n";
                    return $msg;
                } else {
                    $xsdFile = $aFile;
                }
            }
            //limpa erros anteriores
            libxml_clear_errors();
            // valida o xml com o xsd
            if (!$dom->schemaValidate($xsdFile)) {
                /**
                 * Se não foi possível validar, você pode capturar
                 * todos os erros em um array
                 * Cada elemento do array $arrayErrors
                 * será um objeto do tipo LibXmlError
                 */
                // carrega os erros em um array
                $aIntErrors = libxml_get_errors();
                $flagOK = false;
                if (!isset($Signature)) {
                    // remove o erro de falta de assinatura
                    foreach ($aIntErrors as $k => $intError) {
                        if (strpos($intError->message, '( {http://www.w3.org/2000/09/xmldsig#}Signature )') !== false) {
                            // remove o erro da assinatura, se tiver outro meio melhor (atravez dos erros de codigo) e alguem souber como tratar por eles, por favor contribua...
                            unset($aIntErrors[$k]);
                        }
                    }
                    reset($aIntErrors);
                    $flagOK = true;
                }//fim teste Signature
                $msg = '';
                foreach ($aIntErrors as $intError) {
                    $flagOK = false;
                    $en = array("{http://www.portalfiscal.inf.br/nfe}"
                                ,"[facet 'pattern']"
                                ,"The value"
                                ,"is not accepted by the pattern"
                                ,"has a length of"
                                ,"[facet 'minLength']"
                                ,"this underruns the allowed minimum length of"
                                ,"[facet 'maxLength']"
                                ,"this exceeds the allowed maximum length of"
                                ,"Element"
                                ,"attribute"
                                ,"is not a valid value of the local atomic type"
                                ,"is not a valid value of the atomic type"
                                ,"Missing child element(s). Expected is"
                                ,"The document has no document element"
                                ,"[facet 'enumeration']"
                                ,"one of"
                                ,"failed to load external entity"
                                ,"Failed to locate the main schema resource at"
                                ,"This element is not expected. Expected is"
                                ,"is not an element of the set");

                    $pt = array(""
                                ,"[Erro 'Layout']"
                                ,"O valor"
                                ,"não é aceito para o padrão."
                                ,"tem o tamanho"
                                ,"[Erro 'Tam. Min']"
                                ,"deve ter o tamanho mínimo de"
                                ,"[Erro 'Tam. Max']"
                                ,"Tamanho máximo permitido"
                                ,"Elemento"
                                ,"Atributo"
                                ,"não é um valor válido"
                                ,"não é um valor válido"
                                ,"Elemento filho faltando. Era esperado"
                                ,"Falta uma tag no documento"
                                ,"[Erro 'Conteúdo']"
                                ,"um de"
                                ,"falha ao carregar entidade externa"
                                ,"Falha ao tentar localizar o schema principal em"
                                ,"Este elemento não é esperado. Esperado é"
                                ,"não é um dos seguintes possiveis");

                    switch ($intError->level) {
                        case LIBXML_ERR_WARNING:
                            $aError[] = " Atençao $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                        case LIBXML_ERR_ERROR:
                            $aError[] = " Erro $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                        case LIBXML_ERR_FATAL:
                            $aError[] = " Erro Fatal $intError->code: ".str_replace($en, $pt, $intError->message);
                            break;
                    }
                    $msg .= str_replace($en, $pt, $intError->message);
                }
            } else {
                $flagOK = true;
            }
            if (!$flagOK) {
                return $msg;
            }
        } catch (nfephpException $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return false;
        }
        return 'sucesso';
    } //fim validXML
}