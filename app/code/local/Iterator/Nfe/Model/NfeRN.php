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
            $retorno['msg'] = utf8_encode('O Estado do emitente da NF-e não é válido. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $aamm = date('ym');
        $cnpj = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj'));
        if(!$validarCampos->validarCnpj($cnpj)) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('O CNPJ do emitente da NF-e não é válido. Pedido: '.$order->getIncrementId());
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
            $retorno['msg'] = utf8_encode('A forma de pagamento não é válida. Pedido: '.$order->getIncrementId());
            return $retorno;
        }
        $dataHoraAtual = date("Y-m-d H:i:s");
        $dataHoraSaida = date("Y-m-d H:i:s", strtotime('+5 hours'));
        $estadoDestino = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
        if($cUF == $estadoDestino->getRegionId()) {
            $idDest = '1';
        } else  {
            $idDest = '2';
        }
        $cMunFG = Mage::getStoreConfig('nfe/emitente_opcoes/codigo_municipio');
        if(!$cMunFG) {
            $nfeRange->setNumero($novoRangeNumero-1);
            $nfeRange->save();
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('O Código do Município do emitente da NF-e não é válido. Pedido: '.$order->getIncrementId());
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
        $nfe->setMensagem(utf8_encode('Aguardando aprovação para enviar solicitação de autorização ao orgão responsável.'));
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
            $retorno['msg'] = utf8_encode('Uma ou mais informações do emitente da NF-e não são válidas. Pedido: '.$order->getIncrementId());
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
                $retorno['msg'] = utf8_encode('O CNPJ do destinatário da NF-e não é válido. Pedido: '.$order->getIncrementId());
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
                $retorno['msg'] = utf8_encode('O CPF do destinatário da NF-e não é válido. Pedido: '.$order->getIncrementId());
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
                    } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                        $cfop = '5101';
                    }
                } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) > 11) {
                    if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                        $cfop = '6102';
                    } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                        $cfop = '6101';
                    }
                } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) <= 11) {
                    if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                        $cfop = '6108';
                    } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
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
                    if($vIpi > 0) {
                        $reduzirUn = $prodIpi / $item->getQtyOrdered();
                        $nfeProduto->setVUnCom($vUnComTrib - $reduzirUn);
                        $nfeProduto->setVProd($vProd - $prodIpi);
                        $nfeProduto->setVUnTrib($vUnComTrib - $reduzirUn);
                        $nfeProduto->save();
                    }
                }
                
                $totalVProd += $vProd - $prodIpi;
                $totalVFrete += $vFrete;
                $totalVDesc += $vDesc;
                //$totalVSeg = null;
                //$totalVOutro = null;
                $totalVNf += $vProd - $vDesc + $vFrete;
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
                $infCpl .= utf8_encode('I - DOCUMENTO EMITIDO POR ME OU EPP OPTANTE PELO SIMPLES NACIONAL.  II - NÃO GERA DIREITO A CRÉDITO FISCAL DE IPI.  ');
                if($vCredICMSSN > 0) {
                    $infCpl .= utf8_encode('III - PERMITE O APROVEITAMENTO DO CRÉDITO DE ICMS NO VALOR DE '.Mage::helper('core')->currency($vCredICMSSN, true, false).' CORRESPONDENTE À ALÍQUOTA DE '.number_format($dadosNcm->getAliquotaSimples(), 2, '.', '').'%, NOS TERMOS DO ART. 23 DA LEI COMPLEMENTAR Nº 123, DE 2006.  ');
                }
            }
        }
        $infCpl .= utf8_encode('Val Aprox dos Tributos '.Mage::helper('core')->currency($totalVTotTrib, true, false).' ('.number_format($totalAliquotaIbpt / $itemComNcm, 2, '.', '').'%) Fonte: IBPT');
        $nfe->setInfInfCpl($infCpl);
        
        $nfe->save();
        
        $this->setStatusPedido($order);
        
        $retorno['status'] = 'sucesso';
        $retorno['msg'] = utf8_encode('Solicitação para emissão da NF-e gerada com sucesso.');
        return $retorno;
    }
    
    public function gerarXML($nfeId) {
        // MÉTODOS QUE SERÃO UTILIZADOS NO MOMENTO DA GERAÇÃO DO XML. BOTÃO NO FORM DE CONFIRMAÇÃO INVOCARÁ O SAVE DO CONTROLLER DA NFE QUE DEPOIS INVOCARÁ ESTES MÉTODOS.
        $nfeCriarXML = Mage::helper('nfe/NfeCriarXml');
        $this->preencherCampos($nfeCriarXML);
        $this->gerarArquivoXML($nfeCriarXML);
        // O diretório onde são salvos os XML não poderá ser manipulado e portanto não existirá como opção nas configurações, será fixo como "nfe" na raíz do Magento por exemplo e cada pacote será referenciado por: "ID_NúmeroNota"
        return true;
    }
    
    private function preencherCampos($nfeCriarXML) {
        
    }
    
    private function gerarArquivoXML($nfeCriarXML) {
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
        utf8_encode('Foi solicitada a emissão da NF-e para este pedido.<br/>
         Status: Aguardando Aprovação'));
        
        $order->save();
    }
    
    public function gerarCodigoNumerico($length=8) {
        $numero = '';
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
}
