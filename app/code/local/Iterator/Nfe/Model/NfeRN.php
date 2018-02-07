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
        $vFCPUFDest = null;
        $vICMSUFDest = null;
        $vICMSUFRemet = null;
        $totalVProd = null;
        $totalVFrete = null;
        $totalVSeg = null;
        $totalVDesc = null;
        $totalVOutro = null;
        $totalVNf = null;
        $totalVTotTrib = null;
        $totalAliquotaIbpt = null;
        $retorno = array();
        
        $verificaExiste = Mage::getModel('nfe/nfe')->getCollection()
                    ->addFieldToFilter('pedido_increment_id', array('eq' => $order->getIncrementId()))
                    ->addFieldToFilter('status', array('in' => array('0','1','2','3','4', 7)))
                    ->addFieldToFilter('tp_nf', array('eq' => '1'))
                    ->getFirstItem();
        if($verificaExiste->getNfeId()) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('Uma NF-e de entrada já foi emitida para o pedido: '.$order->getIncrementId());
            return $retorno;
        }
        
        $nfe = Mage::getModel('nfe/nfe');
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        $cUF = $validarCampos->getUfEquivalente($estadoEmitente->getCode());
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
            $nfeRange->setNumero($nNF);
            $nfeRange->save();
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('A forma de pagamento não é válida. Pedido: '.$order->getIncrementId());
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
            $nfeRange->setNumero($nNF);
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
        $nfe->setVerProc('ITERATOR_NFE_1.2_MG');
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
                $nfeRange->setNumero($nNF);
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
        if($order->getCustomerId()) {
            $cliente = Mage::getModel('customer/customer')->load($order->getCustomerId());
            if($cliente->getCpfcnpj()) {
                $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $cliente->getCpfcnpj()),0,14);
            } else {
                $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $cliente->getTaxvat()),0,14); 
            }
        } else {
            if($order->getCustomerTaxvat()) {
                $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $order->getCustomerTaxvat()),0,14); 
            } else {
                $cpfCnpj = substr(eregi_replace ("[^0-9]", "", $order->getBillingAddress()->getCpfcnpj()),0,14); 
            }
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
                    $nfeRange->setNumero($nNF);
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
            if($order->getCustomerId()) {
                $razaoSocialDestinatario = $cliente->getEmpresa();
                $ieDestinatario = $cliente->getIe();
            } else {
                $razaoSocialDestinatario = $order->getCustomerFirstname();
                if($order->getCustomerNote()) {
                    $ieDestinatario = eregi_replace ("[^0-9]", "", $order->getCustomerNote());
                } else {
                    $ieDestinatario = eregi_replace ("[^0-9]", "", $order->getBillingAddress()->getIe());
                }
            }
            $nfeIdentificacaoDestinatario->setXNome($razaoSocialDestinatario);
            if($ieDestinatario && strtolower($ieDestinatario) != 'isento') {
                $nfeIdentificacaoDestinatario->setIndIeDest('1');
                $nfeIdentificacaoDestinatario->setIe($ieDestinatario);
            } else {
                $nfeIdentificacaoDestinatario->setIndIeDest('9');
            }
        } else {
            if(!$validarCampos->validarCpf($cpfCnpj)) {
                $retorno['status'] = 'erro';
                $retorno['msg'] = utf8_encode('O CPF do destinatário da NF-e não é válido. Pedido: '.$order->getIncrementId());
                try {
                    $nfeRange->setNumero($nNF);
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
        if(strpos($order->getCustomerEmail(),'extra.com.br') !== false || strpos($order->getCustomerEmail(),'walmart.com.br') !== false || strpos($order->getCustomerEmail(),'email.com.br') !== false) {
            $nfeIdentificacaoDestinatario->setEmail('');
        } else {
            $nfeIdentificacaoDestinatario->setEmail($order->getCustomerEmail());
        }
        $nfeIdentificacaoDestinatario->save();
        
        $existeMotorImpostos = Mage::getConfig()->getModuleConfig('Iterator_MotorImpostos')->is('active', 'true');
        $orderItems = $order->getAllItems();
        $nItem = 0;
        $itemComNcm = 0;
        foreach($orderItems as $item) {
            if($item->getProductType() == 'simple') {
                $cfop = null;
                $orig = null;
                $cest = null;
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
                $temIcmsDestino = null;
                $prodSt = null;
                $prodIpi = null;
                $aliquotaIbpt = null;
                $tipoBrinde = null;
                $presente = false;
                $nfeProduto = Mage::getModel('nfe/nfeproduto');
                $nItem++;
                $gtin = Mage::getModel('catalog/product')->load($item->getProductId())->getData('gtin');
                $ncm = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('ncm');
                $origem = substr(Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('origem'),0,1);
                $st = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('st');
                $unidade = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('unidade');
                $tipoMercadoria = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('tipo_mercadoria');
                if(Mage::getResourceModel('catalog/eav_attribute')->loadByCode('catalog_product', 'tipo_brinde')->getId()) {
                    $tipoBrinde = Mage::getModel('catalog/product')->load($item->getProductId())->getAttributeText('tipo_brinde');
                }
                if($tipoBrinde == 'Amostra' && $item->getPrice() == '0.000' || $tipoBrinde == 'Brinde' && $item->getPrice() == '0.000') {
                    $presente = true;
                    if($estadoEmitente->getRegionId() == $estadoDestinatario->getRegionId()) {
                        $cfop = '5910';
                    } else {
                        $cfop = '6910';
                    }
                } else {
                    if($estadoEmitente->getRegionId() == $estadoDestinatario->getRegionId()) {
                        if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                            if($st == 'Recolhido pela Empresa') {
                                $cfop = '5403';
                            } else if($st == 'Recolhido pelo Fornecedor') {
                                $cfop = '5405';
                            } else {
                                $cfop = '5102';
                            }
                        } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                            $cfop = '5101';
                        }
                    } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) > 11) {
                        if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                            if($st == 'Recolhido pela Empresa') {
                                $cfop = '6403';
                            } else if($st == 'Recolhido pelo Fornecedor') {
                                $cfop = '6404';
                            } else {
                                $cfop = '6102';
                            }
                        } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento') && $nfeIdentificacaoDestinatario->getIndIeDest() != '9') {
                            $cfop = '6101';
                        } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento') && $nfeIdentificacaoDestinatario->getIndIeDest() == '9') {
                            $cfop = '6107';
                        }
                    } else if($estadoEmitente->getRegionId() != $estadoDestinatario->getRegionId() && strlen($cpfCnpj) <= 11) {
                        if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                            if($st == 'Recolhido pela Empresa') {
                                $cfop = '6403';
                            } else if($st == 'Recolhido pelo Fornecedor') {
                                $cfop = '6404';
                            } else {
                                $cfop = '6108';
                            }
                        } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                            $cfop = '6107';
                        }
                    }
                }
                if($existeMotorImpostos && $ncm && $origem != null && $cfop) {
                    $motorCalculos = Mage::getModel('motorimpostos/motorcalculos');
                    $dadosNcm = $motorCalculos->getDadosNcm($cfop, $ncm, $origem, $nfeIdentificacaoDestinatario->getIndIeDest());
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
                        $cestModel = $motorCalculos->getDadosCest($ncm);
                        if($cestModel->getCestCodigo()) {
                            $cest = $cestModel->getCestCodigo();
                        }
                    }
                }
                $nfeProduto->setNfeId($nfeId);
                $nfeProduto->setProduto('product/'.$item->getProductId());
                $nfeProduto->setNItem($nItem);
                $nfeProduto->setCProd($item->getSku());
                $nfeProduto->setCEan($gtin);
                $nfeProduto->setNcm($ncm);
                $nfeProduto->setCest($cest);
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
                    $qtdPedido = $itemParent->getQtyOrdered();
                } else if(!$item->getParentItemId()) {
                    $xProd = $item->getName();
                    $qComTrib = $item->getQtyOrdered();
                    $vUnComTrib = $item->getPrice();
                    $vProd = $item->getPrice() * $item->getQtyOrdered();
                    $vDesc = $item->getDiscountAmount();
                    $qtdPedido = $item->getQtyOrdered();
                }
                if($presente) {
                    $vUnComTrib = '0.1';
                    $vProd = '0.1' * $qtdPedido;
                }
                $descontoDescricao = $order->getDiscountDescription();
                if(strpos($descontoDescricao, utf8_encode('Cartão de Crédito à Vista')) !== false) {
                    if($order->getShippingAmount() > 0) {
                        $fretePedido = ($vProd - $vDesc) * $order->getShippingAmount() / ($order->getGrandTotal() - $order->getShippingAmount());
                    }
                    $valorDesconto = $this->getValorDescontoCartao($descontoDescricao);
                    $vDesc += (($vProd - $vDesc + $fretePedido) * $valorDesconto) / 100;
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
                    if($nfe->getIdDest() == '2' && $nfeIdentificacaoDestinatario->getIndIeDest() == '9') {
                        $temIcmsDestino = '1';
                    }
                    $totalVTotTrib += $vTotTrib;
                    $itemComNcm++;
                }
                $nfeProduto->setVTotTrib($vTotTrib);
                $nfeProduto->setInfAdProd('Val Aprox dos Tributos '.Mage::helper('core')->currency($vTotTrib, true, false).' ('.number_format($aliquotaIbpt, 2, '.', '').'%) Fonte: IBPT');
                $nfeProduto->setTemIcms($temIcms);
                $nfeProduto->setTemPis($temPis);
                $nfeProduto->setTemCofins($temCofins);
                $nfeProduto->setTemIpi($temIpi);
                $nfeProduto->setTemIcmsDestino($temIcmsDestino);
                $nfeProduto->save();
                
                if($existeMotorImpostos && $existeDadosNcm) {
                    $impostosRetorno = $motorCalculos->setImpostosProdutoNfe($nfeProduto, $dadosNcm, $estadoEmitente->getRegionId(), $estadoDestinatario->getRegionId(), $temIcmsDestino);
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
                    if($impostosRetorno['vFCPUFDest'] > 0) {
                        $vFCPUFDest += $impostosRetorno['vFCPUFDest'];
                    }
                    if($impostosRetorno['vICMSUFDest'] > 0) {
                        $vICMSUFDest += $impostosRetorno['vICMSUFDest'];
                    }
                    if($impostosRetorno['vICMSUFRemet'] > 0) {
                        $vICMSUFRemet += $impostosRetorno['vICMSUFRemet'];
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
        $nfe->setVFcpUfDest($vFCPUFDest);
        $nfe->setVIcmsUfDest($vICMSUFDest);
        $nfe->setVIcmsUfRemet($vICMSUFRemet);
        $nfe->setVBcSt($vBCST);
        $nfe->setVSt($vST);
        $nfe->setVProd($totalVProd);
        $nfe->setVFrete($totalVFrete);
        $nfe->setVDesc($totalVDesc);
        $nfe->setVIpi($vIpi);
        $nfe->setVNf($totalVNf);
        $nfe->setVTotTrib($totalVTotTrib);
        
        $historyCollection = $order->getStatusHistoryCollection();
        $parcelaTexto = '0';
        foreach($historyCollection as $history) {
            if(strpos($history->getComment(), 'parcelas:') !== false ) {
                $parcelaTexto = $history->getComment();
            }
        }
        $parcelaArray = explode(':', $parcelaTexto);
        $parcelaQtd = $parcelaArray[1];
        if($parcelaTexto != '0') {
            if($parcelaQtd == '1') {
                $nfeCobranca = Mage::getModel('nfe/nfecobranca');
                $nfeCobranca->setNfeId($nfeId);
                $nfeCobranca->setCob_n_dup($nNF);
                $nfeCobranca->setCob_d_venc(date("Y-m-d"));
                $nfeCobranca->setCob_v_dup($totalVNf);
                $nfeCobranca->save();
            } else {
                $parcelaValor = $totalVNf / (int)$parcelaQtd;
                for($i=1; $i<=(int)$parcelaQtd; $i++) {
                    $nfeCobranca = Mage::getModel('nfe/nfecobranca');
                    $nfeCobranca->setNfeId($nfeId);
                    $nfeCobranca->setCob_n_dup($nNF.'-'.$i);
                    if($i == 1) {
                        $nfeCobranca->setCob_d_venc(date("Y-m-d"));
                    } else if($i > 1) {
                        $proximoVencimento = $i-1;
                        $nfeCobranca->setCob_d_venc(date("Y-m-d", strtotime('+'.$proximoVencimento.' month')));
                    }
                    $nfeCobranca->setCob_v_dup($parcelaValor);
                    $nfeCobranca->save();
                }
            }
        }
        
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
            if($vFCPUFDest > 0) {
                $infCpl .= utf8_encode('TOTAL DO ICMS RELATIVO AO FUNDO DE COMBATE À POBREZA PARA A UF DO DESTINATÁRIO '.Mage::helper('core')->currency($vFCPUFDest, true, false).'.  ');
            }
            if($vICMSUFDest > 0) {
                $infCpl .= utf8_encode('TOTAL DO ICMS INTERESTADUAL PARA A UF DO DESTINATÁRIO '.Mage::helper('core')->currency($vICMSUFDest, true, false).'.  ');
                $infCpl .= utf8_encode('Recolhimento DIFAL suspenso pela ADI 5464 - 02/2016.  ');
            }
        }
        $infCpl .= utf8_encode('Val Aprox dos Tributos '.Mage::helper('core')->currency($totalVTotTrib, true, false).' Fonte: IBPT');
        $nfe->setInfInfCpl($infCpl);
        
        $nfe->save();
        
        $order->setNfeNumero($nfe->getNNf());
        $order->setNfeEmissao($nfe->getDhEmi());
        $order->save();
        
        $this->setStatusPedido($order);
        
        $retorno['status'] = 'sucesso';
        $retorno['msg'] = utf8_encode('Solicitação para emissão da NF-e gerada com sucesso.');
        return $retorno;
    }
    
    public function montarNfeRetorno($order) {
        $retorno = array();
        $verificaExiste = Mage::getModel('nfe/nfe')->getCollection()
                    ->addFieldToFilter('pedido_increment_id', array('eq' => $order->getIncrementId()))
                    ->addFieldToFilter('status', array('in' => array('0','1','2','3','4', 7)))
                    ->addFieldToFilter('tp_nf', array('eq' => '0'))
                    ->getFirstItem();
        if($verificaExiste->getNfeId()) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('Uma NF-e de devolução já foi emitida para o pedido: '.$order->getIncrementId());
            return $retorno;
        }
        
        $nfeSaida = Mage::getModel('nfe/nfe')->getCollection()
                    ->addFieldToFilter('pedido_increment_id', array('eq' => $order->getIncrementId()))
                    ->addFieldToFilter('status', array('eq' => '7'))
                    ->addFieldToFilter('tp_nf', array('eq' => '1'))
                    ->getFirstItem();
        
        $estadoDestinatario = Mage::getModel('directory/region')->load($order->getShippingAddress()->getRegionId());
        $nfeSaidaId = $nfeSaida->getNfeId();
        $chaveReferenciada = substr($nfeSaida->getIdTag(),3);
        
        if(!$nfeSaida->getNfeId()) {
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('Nenhuma NF-e de saída foi emitida com sucesso para o pedido: '.$order->getIncrementId());
            return $retorno;
        }
        
        $nfeEntrada = $nfeSaida;
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        $cUF = $validarCampos->getUfEquivalente($estadoEmitente->getCode());
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
            $nfeRange->setNumero($nNF);
            $nfeRange->save();
            $retorno['status'] = 'erro';
            $retorno['msg'] = utf8_encode('A forma de pagamento não é válida. Pedido: '.$order->getIncrementId());
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
            $nfeRange->setNumero($nNF);
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
        
        $nfeEntrada->setNfeId(null);
        $nfeEntrada->setPedidoIncrementId($order->getIncrementId());
        $nfeEntrada->setNRec(null);
        $nfeEntrada->setVerAplic(null);
        $nfeEntrada->setDhRecbto(null);
        $nfeEntrada->setNProt(null);
        $nfeEntrada->setCStat(null);
        $nfeEntrada->setXMotivo(null);
        $nfeEntrada->setStatus('0');
        $nfeEntrada->setMensagem(utf8_encode('Aguardando aprovação para enviar solicitação de autorização ao orgão responsável.'));
        $nfeEntrada->setVersao('3.10');
        $nfeEntrada->setIdTag('NFe'.$chave);
        $nfeEntrada->setCUf($cUF);
        $nfeEntrada->setCNf($cNF);
        $nfeEntrada->setNatOp(utf8_encode('Devolução de venda'));
        $nfeEntrada->setIndPag($indPag);
        $nfeEntrada->setMod($mod);
        $nfeEntrada->setSerie($serie);
        $nfeEntrada->setNNf($nNF);
        $nfeEntrada->setDhEmi(str_replace(' ', 'T', $dataHoraAtual));
        $nfeEntrada->setDhSaiEnt(str_replace(' ', 'T', $dataHoraSaida));
        $nfeEntrada->setTpNf('0');
        $nfeEntrada->setIdDest($idDest);
        $nfeEntrada->setCMunFg($cMunFG);
        $nfeEntrada->setTpImp($tpImp);
        $nfeEntrada->setTpEmis($tpEmis);
        $nfeEntrada->setCDv($cDV);
        $nfeEntrada->setTpAmb($tpAmb);
        $nfeEntrada->setFinNfe('4');
        $nfeEntrada->setIndFinal('1');
        $nfeEntrada->setIndPres('2');
        $nfeEntrada->setProcEmi('0');
        $nfeEntrada->setVerProc('ITERATOR_NFE_1.0_MG');
        $nfeEntrada->setTemReferencia(1);
        $nfeEntrada->save();
        
        $nfeEntradaId = $nfeEntrada->getNfeId();
        
        $nfeIdentificacoes = Mage::getModel('nfe/nfeidentificacao')->getCollection()->addFieldToFilter('nfe_id', array('eq' => $nfeSaidaId));
        foreach($nfeIdentificacoes as $nfeIdentificacao) {
            $nfeIdentificacao->setIdentificacaoId(null);
            $nfeIdentificacao->setNfeId($nfeEntradaId);
            $nfeIdentificacao->save();
        }
        
        $nfeReferenciado = Mage::getModel('nfe/nfereferenciado');
        $nfeReferenciado->setNfeId($nfeEntradaId);
        $nfeReferenciado->setTipoDocumento('refNFe');
        $nfeReferenciado->setRefNfe($chaveReferenciada);
        $nfeReferenciado->save();
        
        $nfeProdutos = Mage::getModel('nfe/nfeproduto')->getCollection()->addFieldToFilter('nfe_id', array('eq' => $nfeSaidaId));
        $nfeIdentificacaoDestinatario = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeSaidaId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'dest'))
                    ->getFirstItem();
        $motorCalculos = Mage::getModel('motorimpostos/motorcalculos');
        foreach($nfeProdutos as $nfeProduto) {
            $nfeProdutoSaidaId = $nfeProduto->getProdutoId();
            $prdutoMagentoId = preg_replace('/[^\d]/', '', $nfeProduto->getProduto());
            $ncm = Mage::getModel('catalog/product')->load($prdutoMagentoId)->getAttributeText('ncm');
            $origem = substr(Mage::getModel('catalog/product')->load($prdutoMagentoId)->getAttributeText('origem'),0,1);
            $nfeProduto->setProdutoId(null);
            $nfeProduto->setNfeId($nfeEntradaId);
            $tipoMercadoria = Mage::getModel('catalog/product')->load($nfeProdutoSaidaId)->getAttributeText('tipo_mercadoria');
            if($estadoEmitente->getRegionId() == $estadoDestinatario->getRegionId()) {
                if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                    $cfop = '1202';
                } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                    $cfop = '1201';
                }
            } else {
                if($tipoMercadoria == utf8_encode('Adquirida ou Recebida de Terceiros')) {
                    $cfop = '2202';
                } else if($tipoMercadoria == utf8_encode('Produção do Estabelecimento')) {
                    $cfop = '2201';
                }
            }
            $nfeProduto->setCfop($cfop);
            $nfeProduto->save();
            $nfeProdutoEntradaId = $nfeProduto->getProdutoId();
            
            if($nfeProduto->getEhEspecifico() == '1') {
                $nfeProdutosEspecificos = Mage::getModel('nfe/nfeprodutoespecifico')->addFieldToFilter('produto_id', array('eq' => $nfeProdutoSaidaId));
                foreach($nfeProdutosEspecificos as $nfeProdutoEspecifico) {
                    $nfeProdutoEspecifico->setEspecificoId(null);
                    $nfeProdutoEspecifico->setProdutoId($nfeProdutoEntradaId);
                    $nfeProdutoEspecifico->save();
                }
            }
            
            $nfeProdutoImpostos = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()->addFieldToFilter('produto_id', array('eq' => $nfeProdutoSaidaId));
            foreach($nfeProdutoImpostos as $nfeProdutoImposto) {
                $dadosNcm = $motorCalculos->getDadosNcm($cfop, $ncm, $origem, $nfeIdentificacaoDestinatario->getIndIeDest());
                if($dadosNcm) {
                    $motorCalculos->setImpostosProdutoNfe($nfeProduto, $dadosNcm, $estadoEmitente->getRegionId(), $estadoDestinatario->getRegionId(), $nfeProduto->getTemIcmsDestino());
                } else {
                    $nfeProdutoImposto->setImpostoId(null);
                    $nfeProdutoImposto->setProdutoId($nfeProdutoEntradaId);
                    $nfeProdutoImposto->save();
                }
            }
            
            $nfeProdutoImportExports = Mage::getModel('nfe/nfeprodutoimportexport')->getCollection()->addFieldToFilter('produto_id', array('eq' => $nfeProdutoSaidaId));
            foreach($nfeProdutoImportExports as $nfeProdutoImportExport) {
                $nfeProdutoImportExport->setImportExportId(null);
                $nfeProdutoImportExport->setProdutoId($nfeProdutoEntradaId);
                $nfeProdutoImportExport->save();
            }
        }
        
        $this->setStatusPedidoDevolvido($order);
        
        $retorno['status'] = 'sucesso';
        $retorno['msg'] = utf8_encode('Solicitação para emissão da NF-e de devolução gerada com sucesso.');
        return $retorno;
    }
    
    public function gerarXml($nfeId) {
        $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
        $nfeHelper = Mage::helper('nfe/nfeHelper');
        $nfeCriarXML = Mage::helper('nfe/nfeCriarXML');
        $this->preencherCampos($nfe, $nfeCriarXML);
        $retornoXml = $this->gerarArquivoXML($nfe, $nfeCriarXML);
        if($retornoXml == 'sucesso') {
            $xmlNfe = $nfeHelper->getXmlNfe($nfe);
            $xmlAssinado = $nfeHelper->assinarXml($xmlNfe, 'infNFe', $nfe, 'emitir');
            if($xmlAssinado == 'sucesso') {
                $xmlNfe = $nfeHelper->getXmlNfe($nfe);
                $xmlValidado = $nfeHelper->validarXml($xmlNfe);
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
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        $nfeId = $nfe->getNfeId();
        //Numero e versÃ£o da NFe (infNFe)
        $chave = substr($nfe->getIdTag(),3);
        $versao = $nfe->getVersao();
        $resposta = $nfeCriarXML->taginfNFe($chave, $versao);

        //Dados da NFe (ide)
        $cUF = $nfe->getCUf();
        $cNF = $nfe->getCNf(); //numero aleatÃ³rio da NF
        $natOp = $nfe->getNatOp(); //natureza da operaÃ§Ã£o
        $indPag = $nfe->getIndPag(); //0=Pagamento Ã  vista; 1=Pagamento a prazo; 2=Outros
        $mod = $nfe->getMod(); //modelo da NFe 55 ou 65 essa Ãºltima NFCe
        $serie = strval(intval($nfe->getSerie())); //serie da NFe
        $nNF = strval(intval($nfe->getNNf())); // numero da NFe
        $dhEmi = $validarCampos->getHoraCerta($nfe->getDhEmi());
        $horario = Mage::getStoreConfig('nfe/nfe_opcoes/horario');
        $dhEmi = str_replace(' ', 'T', $dhEmi).$horario;  //para versÃ£o 3.00 '2014-02-03T13:22:42-3.00' nÃ£o informar para NFCe
        $dhSaiEnt = $validarCampos->getHoraCerta($nfe->getDhSaiEnt());
        $dhSaiEnt = str_replace(' ', 'T', $dhSaiEnt).$horario; //versÃ£o 2.00, 3.00 e 3.10
        $tpNF = $nfe->getTpNf();
        $idDest = $nfe->getIdDest(); //1=OperaÃ§Ã£o interna; 2=OperaÃ§Ã£o interestadual; 3=OperaÃ§Ã£o com exterior.
        $cMunFG = $nfe->getCMunFg();
        $tpImp = $nfe->getTpImp(); //0=Sem geraÃ§Ã£o de DANFE; 1=DANFE normal, Retrato; 2=DANFE normal, Paisagem;
                      //3=DANFE Simplificado; 4=DANFE NFC-e; 5=DANFE NFC-e em mensagem eletrÃ´nica
                      //(o envio de mensagem eletrÃ´nica pode ser feita de forma simultÃ¢nea com a impressÃ£o do DANFE;
                      //usar o tpImp=5 quando esta for a Ãºnica forma de disponibilizaÃ§Ã£o do DANFE).
        $tpEmis = $nfe->getTpEmis(); //1=EmissÃ£o normal (nÃ£o em contingÃªncia);
                       //2=ContingÃªncia FS-IA, com impressÃ£o do DANFE em formulÃ¡rio de seguranÃ§a;
                       //3=ContingÃªncia SCAN (Sistema de ContingÃªncia do Ambiente Nacional);
                       //4=ContingÃªncia DPEC (DeclaraÃ§Ã£o PrÃ©via da EmissÃ£o em ContingÃªncia);
                       //5=ContingÃªncia FS-DA, com impressÃ£o do DANFE em formulÃ¡rio de seguranÃ§a;
                       //6=ContingÃªncia SVC-AN (SEFAZ Virtual de ContingÃªncia do AN);
                       //7=ContingÃªncia SVC-RS (SEFAZ Virtual de ContingÃªncia do RS);
                       //9=ContingÃªncia off-line da NFC-e (as demais opÃ§Ãµes de contingÃªncia sÃ£o vÃ¡lidas tambÃ©m para a NFC-e);
                       //Nota: Para a NFC-e somente estÃ£o disponÃ­veis e sÃ£o vÃ¡lidas as opÃ§Ãµes de contingÃªncia 5 e 9.
        $cDV = $nfe->getCDv(); //digito verificador
        $tpAmb = $nfe->getTpAmb(); //1=ProduÃ§Ã£o; 2=HomologaÃ§Ã£o
        $finNFe = $nfe->getFinNfe(); //1=NF-e normal; 2=NF-e complementar; 3=NF-e de ajuste; 4=DevoluÃ§Ã£o/Retorno.
        $indFinal = $nfe->getIndFinal(); //0=NÃ£o; 1=Consumidor final;
        $indPres = $nfe->getIndPres(); //0=NÃ£o se aplica (por exemplo, Nota Fiscal complementar ou de ajuste);
                       //1=OperaÃ§Ã£o presencial;
                       //2=OperaÃ§Ã£o nÃ£o presencial, pela Internet;
                       //3=OperaÃ§Ã£o nÃ£o presencial, Teleatendimento;
                       //4=NFC-e em operaÃ§Ã£o com entrega a domicÃ­lio;
                       //9=OperaÃ§Ã£o nÃ£o presencial, outros.
        $procEmi = $nfe->getProcEmi(); //0=EmissÃ£o de NF-e com aplicativo do contribuinte;
                        //1=EmissÃ£o de NF-e avulsa pelo Fisco;
                        //2=EmissÃ£o de NF-e avulsa, pelo contribuinte com seu certificado digital, atravÃ©s do site do Fisco;
                        //3=EmissÃ£o NF-e pelo contribuinte com aplicativo fornecido pelo Fisco.
        $verProc = $nfe->getVerProc(); //versÃ£o do aplicativo emissor
        $dhCont = $nfe->getDhCont(); //entrada em contingÃªncia AAAA-MM-DDThh:mm:ssTZD
        $xJust = $nfe->getXJust(); //Justificativa da entrada em contingÃªncia
        $resposta = $nfeCriarXML->tagide($cUF, $cNF, $natOp, $indPag, $mod, $serie, $nNF, $dhEmi, $dhSaiEnt, $tpNF, $idDest, $cMunFG, $tpImp, $tpEmis, $cDV, $tpAmb, $finNFe, $indFinal, $indPres, $procEmi, $verProc, $dhCont, $xJust);
        
        // Referência
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
        //endereÃ§o do emitente
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
        
        // Destinatário
        $nfeIdentificacaoDestinatario = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                    ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                    ->addFieldToFilter('tipo_identificacao', array('eq' => 'dest'))
                    ->getFirstItem();
        //destinatÃ¡rio
        $CNPJ = $nfeIdentificacaoDestinatario->getCnpj();
        $CPF = $nfeIdentificacaoDestinatario->getCpf();
        $idEstrangeiro = $nfeIdentificacaoDestinatario->getIdEstrangeiro();
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
        //EndereÃ§o do destinatÃ¡rio
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
            //IdentificaÃ§Ã£o do local de retirada (se diferente do emitente)
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
         * Não utilizar se for igual ao desinatario
        //IdentificaÃ§Ã£o dos autorizados para fazer o download da NFe (somente versÃ£o 3.1)
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
            if($cEAN == null) {
                $cEAN = 'gtin';
            }
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
            if($cEANTrib == null) {
                $cEANTrib = 'gtin';
            }
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
            // Produto Específico
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
            
            //CEST
            $CEST = $nfeProduto->getCest();
            if($CEST) {
                $resposta = $nfeCriarXML->tagCEST($nItem, $CEST);
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
                    $vBCSTRet = $nfeProdutoImpostoIcms->getVBcstRet();
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
                    $vBCSTRet = $nfeProdutoImpostoIcms->getVBcstRet();
                    if($vBCSTRet == '0.00') {
                        $vBCSTRet = null;
                    }
                    $vICMSSTRet = $nfeProdutoImpostoIcms->getVIcmsStRet();
                    if($vICMSSTRet == '0.00') {
                        $vICMSSTRet = null;
                    }
                    $resposta = $nfeCriarXML->tagICMSSN($nItem, $orig, $csosn, $modBC, $vBC, $pRedBC, $pICMS, $vICMS, $pCredSN, $vCredICMSSN, $modBCST, $pMVAST, $pRedBCST, $vBCST, $pICMSST, $vICMSST, $vBCSTRet, $vICMSSTRet);
                }
                //ICMSPart
                //$resp = $nfe->tagICMSPart($nItem, $orig, $cst, $modBC, $vBC, $pRedBC, $pICMS, $vICMS, $modBCST, $pMVAST, $pRedBCST, $vBCST, $pICMSST, $vICMSST, $pBCOp, $ufST);
                //ICMSST
                //$resp = $nfe->tagICMSST($nItem, $orig, $cst, $vBCSTRet, $vICMSSTRet, $vBCSTDest, $vICMSSTDest);
            }
            
            // Produto ICMS UF Destino
            if($nfeProduto->getTemIcmsDestino() == '1') {
                $nfeProdutoImpostoIcmsDestino = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                        ->addFieldToFilter('tipo_imposto', array('icms_destino'))
                        ->getFirstItem();
                $vBCUFDest = $nfeProdutoImpostoIcmsDestino->getVBcUfDest();
                $pFCPUFDest = $nfeProdutoImpostoIcmsDestino->getPFcpUfDest();
                $pICMSUFDest = $nfeProdutoImpostoIcmsDestino->getPIcmsUfDest();
                $pICMSInter = $nfeProdutoImpostoIcmsDestino->getPIcmsInter();
                $pICMSInterPart = $nfeProdutoImpostoIcmsDestino->getPIcmsInterPart();
                $vFCPUFDest = $nfeProdutoImpostoIcmsDestino->getVFcpUfDest();
                $vICMSUFDest = $nfeProdutoImpostoIcmsDestino->getVIcmsUfDest();
                $vICMSUFRemet = $nfeProdutoImpostoIcmsDestino->getVIcmsUfRemet();
                $resposta = $nfeCriarXML->tagICMSUFDest($nItem, $vBCUFDest, $pFCPUFDest, $pICMSUFDest, $pICMSInter, $pICMSInterPart, $vFCPUFDest, $vICMSUFDest, $vICMSUFRemet);
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
                if($vAliqProd == '0.0000') {
                    $vAliqProd = null;
                    $qBCProd = null;
                }
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
                if($vAliqProd == '0.0000') {
                    $vAliqProd = null;
                    $qBCProd = null;
                }
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
        $vFcpUfDestTot = $nfe->getVFcpUfDest();
        $vICMSUfDestTot = $nfe->getVIcmsUfDest();
        $vICMSUfRemetTot = $nfe->getVIcmsUfRemet();
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
        $resposta = $nfeCriarXML->tagICMSTot($vBCTot, $vICMSTot, $vICMSDesonTot, $vFcpUfDestTot, $vICMSUfDestTot, $vICMSUfRemetTot, $vBCSTTot, $vSTTot, $vProdTot, $vFreteTot, $vSegTot, $vDescTot, $vIITot, $vIPITot, $vPISTot, $vCOFINSTot, $vOutroTot, $vNFTot, $vTotTribTot);
        
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
        $modFrete = $nfe->getTransModFrete(); //0=Por conta do emitente; 1=Por conta do destinatÃ¡rio/remetente; 2=Por conta de terceiros;
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
                $espVol = $volumeModel->getEsp(); //EspÃ©cie dos volumes transportados
                $marcaVol = $volumeModel->getMarca(); //Marca dos volumes transportados
                $nVol = $volumeModel->getNVol(); //NumeraÃ§Ã£o dos volume
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

        // Cobranças
        $cobrancaCollection = Mage::getModel('nfe/nfecobranca')->getCollection()
                ->addFieldToFilter('nfe_id', $nfeId);
        foreach($cobrancaCollection as $cobrancaModel) {
            $nDupCob = $cobrancaModel->getCob_n_dup();
            $dVencCob = substr($cobrancaModel->getCob_d_venc(), 0, 4).substr($cobrancaModel->getCob_d_venc(), 4, 3).substr($cobrancaModel->getCob_d_venc(), 7, 3);
            $vDupCob = $cobrancaModel->getCob_v_dup();
            $resposta = $nfeCriarXML->tagdup($nDupCob, $dVencCob, $vDupCob);
        }
        
        //informaÃ§Ãµes Adicionais
        $infAdFisco = $nfe->getInfInfAdFisco();
        $infCpl = $nfe->getInfInfCpl();
        $resposta = $nfeCriarXML->taginfAdic($infAdFisco, $infCpl);

        //observaÃ§Ãµes emitente
        $xCampoInf = $nfe->getInf_x_campo();
        $xTextoInf = $nfe->getInf_x_texto();
        if($xTextoInf) {
            $resposta = $nfeCriarXML->tagobsCont($xCampoInf, $xTextoInf);
        }

        /*
        //observaÃ§Ãµes fisco
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

        //dados exportaÃ§Ã£o
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
        $nfeHelper = Mage::helper('nfe/nfeHelper');
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
            $nfeHelper->salvarXml($xmlNfe, $caminho, $nfe->getIdTag());
            return 'sucesso';
        } else {
            header('Content-type: text/html; charset=UTF-8');
            foreach ($nfeCriarXML->erros as $err) {
                $erros = 'tag: &lt;'.$err['tag'].'&gt; ---- '.$err['desc'].'<br>';
            }
            return $erros;
        }
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
            $indPag = '2';
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
    
    private function setStatusPedidoDevolvido($order) {
        $order->setData('state', Mage_Sales_Model_Order::STATE_CLOSED);
        $order->setData('status', 'closed');
        $order->addStatusToHistory('closed', 
        utf8_encode('Foi solicitada a emissão da NF-e de devolução para este pedido.<br/>
         Status: Aguardando Aprovação'));
        
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
    
    private function getValorDescontoCartao($descontoDescricao) {
        $valorDesconto = 0;
        if(strpos($descontoDescricao, ',')) {
            $descontos = explode(',', $descontoDescricao);
            foreach($descontos as $desconto) {
                if (strpos($desconto, utf8_encode('Cartão de Crédito à Vista')) !== false) {
                    $desconto = substr($desconto, 25);
                    $valorDesconto = preg_replace('/[^\d]/', '', $desconto);
                }
            }
        } else {
            $desconto = substr($descontoDescricao, 25);
            $valorDesconto = preg_replace('/[^\d]/', '', $desconto);
        }
        return $valorDesconto;
    }
}