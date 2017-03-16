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

class Iterator_Nfe_Adminhtml_NfeController extends Mage_Adminhtml_Controller_Action {
    
    public function _construct() {
        $helper = Mage::helper('nfe');
        if(!method_exists($helper, 'checkValidationNfe')) {
            exit();
        } else {
            if(md5($_SERVER['HTTP_HOST'].'i_|*12*|_T'.$_SERVER['SERVER_ADDR']) != $helper->checkValidationNfe()) {
                exit();
            }
        }
    }
    
    public function indexAction() {
        $this->_initAction()->renderLayout();
    }
    
    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('sales/nfe/nfe')
            ->_title($this->__('Sales'))->_title($this->__('NF-e'))
            ->_addBreadcrumb($this->__('Sales'), $this->__('Sales'))
            ->_addBreadcrumb($this->__(utf8_encode('Nota Fiscal Eletrônica')), $this->__('NF-e'));
         
        return $this;
    }
    
    public function newAction() {
        $this->_forward('edit');
    }
    
    public function editAction() {
        $nfeId  = $this->getRequest()->getParam('nfe_id');
        $model = Mage::getModel('nfe/nfe');
        $referenciadoModel = Mage::getModel('nfe/nfereferenciado');
        $emitenteModel = Mage::getModel('nfe/nfeidentificacao');
        $destinatarioModel = Mage::getModel('nfe/nfeidentificacao');
        $retiradaModel = Mage::getModel('nfe/nfeidentificacao');
        $entregaModel = Mage::getModel('nfe/nfeidentificacao');
        
        if($nfeId) {
            $model->load($nfeId);
            $referenciadoModel = Mage::getModel('nfe/nfereferenciado')->getCollection()
                ->addFieldToFilter('nfe_id', $model->getNfeId())->getFirstItem();
            $emitenteModel = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                ->addFieldToFilter('nfe_id', $model->getNfeId())->addFieldToFilter('tipo_identificacao', 'emit')->getFirstItem();
            $destinatarioModel = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                ->addFieldToFilter('nfe_id', $model->getNfeId())->addFieldToFilter('tipo_identificacao', 'dest')->getFirstItem();
            $retiradaModel = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                ->addFieldToFilter('nfe_id', $model->getNfeId())->addFieldToFilter('tipo_identificacao', 'retirada')->getFirstItem();
            $entregaModel = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                ->addFieldToFilter('nfe_id', $model->getNfeId())->addFieldToFilter('tipo_identificacao', 'entrega')->getFirstItem();
            if (!$model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__(utf8_encode('Esta NF-e já não existe mais.')));
                $this->_redirect('*/*/');
                return;
            } else if($model->getStatus() != '0' && $model->getStatus() != '4') {
                Mage::getSingleton('adminhtml/session')->addError($this->__(utf8_encode('Esta NF-e não pode ser alterada.')));
                $this->_redirect('*/*/');
                return;
            }
        } else {
            $this->setEmitenteInfos($emitenteModel);
            $destinatarioModel->setTipoIdentificacao('dest');
        }
        
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        $dhEmi = $validarCampos->getHoraCerta($model->getDhEmi());
        $model->setDhEmi($dhEmi);
        $dhSaiEnt = $validarCampos->getHoraCerta($model->getDhSaiEnt());
        $model->setDhSaiEnt($dhSaiEnt);
        
        $this->_title($model->getId() ? $model->getNNf() : $this->__('Nova NF-e'));

        $data = Mage::getSingleton('adminhtml/session')->getNfeData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        
        $model->setData();
        Mage::register('nfe', $model);
        Mage::register('nfe_referenciado', $referenciadoModel);
        Mage::register('nfe_emitente', $emitenteModel);
        Mage::register('nfe_destinatario', $destinatarioModel);
        Mage::register('nfe_entrega', $entregaModel);
        Mage::register('nfe_retirada', $retiradaModel);
        
        $this->_initAction()
            ->_addBreadcrumb($nfeId ? $this->__('Editar NF-e') : $this->__('Nova NF-e'), $entradaId ? $this->__('Editar NF-e') : $this->__('Nova NF-e'))
            ->_addContent($this->getLayout()->createBlock('nfe/adminhtml_nfe_edit')->setData('action', $this->getUrl('*/*/save')))
            ->_addLeft($this->getLayout()->createBlock('nfe/adminhtml_nfe_edit_tabs'))
            ->renderLayout();
    }
    
    public function saveAction() {
        $postData = $this->getRequest()->getPost();
        $erro = false;
        $msgErro = null;
        if ($postData) {
            $validarCampos = Mage::helper('nfe/ValidarCampos');
            $model = Mage::getSingleton('nfe/nfe');
            $model->setData($postData['nfe']);
            try {
                $dhEmiOrginal = str_replace('/', '-', $postData['nfe']['dh_emi']);
                if(strlen($dhEmiOrginal) == 14) {
                    $dhEmi = substr($dhEmiOrginal,0,6).'20'.substr($dhEmiOrginal,6).':00';
                } else if(strlen($dhEmiOrginal) == 16) {
                    $dhEmi = substr($dhEmiOrginal,0,6).''.substr($dhEmiOrginal,6).':00';
                }
                $dhEmi = $validarCampos->getHoraServidor($dhEmi);
                $dhSaiEntOrginal = str_replace('/', '-', $postData['nfe']['dh_sai_ent']);
                if(strlen($dhSaiEntOrginal) == 14) {
                    $dhSaiEnt = substr($dhSaiEntOrginal,0,6).'20'.substr($dhSaiEntOrginal,6).':00';
                } else if(strlen($dhSaiEntOrginal) == 16) {
                    $dhSaiEnt = substr($dhSaiEntOrginal,0,6).''.substr($dhSaiEntOrginal,6).':00';
                }
                $dhSaiEnt = $validarCampos->getHoraServidor($dhSaiEnt);
                if(!$validarCampos->validaMinimoMaximo($postData['nfe']['nat_op'], 1, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('A natureza da operação da NF-e não é válida.');
                }
                $estadoEmitente = Mage::getModel('directory/region')->load($postData['emitente']['region_id']);
                $cUF = $validarCampos->getUfEquivalente($estadoEmitente->getCode());
                $cnpj = preg_replace('/[^\d]/', '', $postData['emitente']['cnpj']);
                if(!$validarCampos->validarCnpj($cnpj)) {
                    $erro = true;
                    $msgErro = utf8_encode('O CNPJ do emitente da NF-e não é válido.');
                }
                $ufEmitente = $validarCampos->getUfEquivalente($estadoEmitente->getCode());
                $nfeMunicipio = $validarCampos->getMunicipio($postData['emitente']['x_mun'], $ufEmitente);
                if(!$nfeMunicipio->getCodigo()) {
                    $erro = true;
                    $msgErro = utf8_encode('O Munícipio do emitente da NF-e não é válido.');
                }
                $cMunFG = $nfeMunicipio->getIbgeUf().$nfeMunicipio->getCodigo();
                $xMun = $nfeMunicipio->getNome();
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $ambiente = Mage::getStoreConfig('nfe/nfe_opcoes/ambiente');
                if($ambiente == 'producao') {
                    $tpAmb = '1';
                } else if($ambiente == 'homologacao') {
                    $tpAmb = '2';
                }
                if(!$model->getNfeId()) {
                    $nfeRange = Mage::getModel('nfe/nferange')->load('1');
                    $serie = $nfeRange->getSerie();
                    $nNF = $nfeRange->getNumero();
                    $nfeRN->setRange($nfeRange);
                    $tpEmis = Mage::getStoreConfig('nfe/nfe_opcoes/emissao');
                    $cNF = $nfeRN->gerarCodigoNumerico();
                    $mod = '55';
                    $aamm = date('ym');
                    $formatoDanfe = Mage::getStoreConfig('nfe/danfe_opcoes/formato');
                    if($formatoDanfe == 'portraite') {
                        $tpImp = '1';
                    } else if($formatoDanfe == 'landscape') {
                        $tpImp = '2';
                    }
                    $chave = $cUF . $aamm . $cnpj . $mod . $serie . $nNF . $tpEmis . $cNF;
                    $cDV = $nfeRN->calcularDV($chave);
                    $chave .= $cDV;
                    $model->setVersao('3.10');
                    $model->setIdTag('NFe'.$chave);
                    $model->setCUf($cUF);
                    $model->setCNf($cNF);
                    $model->setMod($mod);
                    $model->setSerie($serie);
                    $model->setNNf($nNF);
                    $model->setCMunFg($cMunFG);
                    $model->setTpImp($tpImp);
                    $model->setTpEmis($tpEmis);
                    $model->setCDv($cDV);
                    $model->setTpAmb($tpAmb);
                    $model->setProcEmi('0');
                    $model->setVerProc('ITERATOR_NFE_1.2_MG');
                }
                $model->setDhEmi($dhEmi);
                $model->setDhSaiEnt($dhSaiEnt);
                if($postData['nfe']['trans_cnpj']) {
                    $model->setTransCnpj(preg_replace('/[^\d]/', '', $postData['nfe']['trans_cnpj']));
                    $model->setTransCpf(null);
                }
                if($postData['nfe']['trans_cpf']) {
                    $model->setTransCpf(preg_replace('/[^\d]/', '', $postData['nfe']['trans_cpf']));
                    $model->setTransCnpj(null);
                }
                $estadoTransp = Mage::getModel('directory/region')->load($postData['nfe']['trans_region_id']);
                $ufTransp = $validarCampos->getUfEquivalente($estadoTransp->getCode());
                $nfeMunicipioTransporte = $validarCampos->getMunicipio($postData['nfe']['trans_x_mun'], $ufTransp);
                if($postData['nfe']['trans_x_mun'] != '' && !$nfeMunicipioTransporte->getCodigo()) {
                    $erro = true;
                    $msgErro = utf8_encode('O Munícipio do transportador da NF-e não é válido.');
                }
                $model->setTrans_x_mun($nfeMunicipioTransporte->getNome());
                $model->setTrans_c_munFg($nfeMunicipioTransporte->getIbgeUf().$nfeMunicipioTransporte->getCodigo());
                $model->setTransRegionId($estadoTransp->getRegionId());
                $model->setTransUf($estadoTransp->getCode());
                $estadoPlacaTransp = Mage::getModel('directory/region')->load($postData['nfe']['trans_veic_region_id']);
                $model->setTransVeicRegionId($estadoPlacaTransp->getRegionId());
                $model->setTransVeicUf($estadoPlacaTransp->getCode());
                $model->save();
                $nfeId = $model->getNfeId();
                
                $nfeIdentificacaoEmitente = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('tipo_identificacao', array('eq' => 'emit'))
                                ->getFirstItem();
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['x_nome'], 2, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O nome ou razão social do emitente da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['x_fant'], 0, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O nome fantasia do emitente da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['ie'], 1, 14)) {
                    $erro = true;
                    $msgErro = utf8_encode('A IE do emitente da NF-e não é válida.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['x_lgr'], 2, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O logradouro do endereço do emitente da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['nro'], 1, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O número do endereço do emitente da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['x_cpl'], 0, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O complemento do endereço do emitente da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['emitente']['x_bairro'], 2, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O bairro do endereço do emitante da NF-e não é válido.');
                }
                $identificacaoIdEmitente = $nfeIdentificacaoEmitente->getIdentificacaoId();
                $nfeIdentificacaoEmitente->setData($postData['emitente']);
                if($identificacaoIdEmitente) {
                    $nfeIdentificacaoEmitente->setIdentificacaoId($identificacaoIdEmitente);
                }
                $nfeIdentificacaoEmitente->setNfeId($nfeId);
                $nfeIdentificacaoEmitente->setTipoPessoa(2);
                $nfeIdentificacaoEmitente->setCnpj($cnpj);
                $nfeIdentificacaoEmitente->setCMun($cMunFG);
                $nfeIdentificacaoEmitente->setXMun($xMun);
                $nfeIdentificacaoEmitente->setRegionId($estadoEmitente->getRegionId());
                $nfeIdentificacaoEmitente->setUf($estadoEmitente->getCode());
                $nfeIdentificacaoEmitente->setCep(preg_replace('/[^\d]/', '', $postData['emitente']['cep']));
                $nfeIdentificacaoEmitente->setCPais('1058');
                $nfeIdentificacaoEmitente->setXPais('Brasil');
                $nfeIdentificacaoEmitente->setFone(preg_replace('/[^\d]/', '', $postData['emitente']['fone']));
                $nfeIdentificacaoEmitente->save();
                
                $estadoDestinatario = Mage::getModel('directory/region')->load($postData['destinatario']['region_id']);
                if(!$validarCampos->validaMinimoMaximo(utf8_encode($postData['destinatario']['x_nome'], 2, 60))) {
                    $erro = true;
                    $msgErro = utf8_encode('O nome ou razão social do destinatário da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo(utf8_encode($postData['destinatario']['x_lgr'], 2, 60))) {
                    $erro = true;
                    $msgErro = utf8_encode('O logradouro do endereço do destinatário da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo($postData['destinatario']['nro'], 1, 60)) {
                    $erro = true;
                    $msgErro = utf8_encode('O número do endereço do destinatário da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo(utf8_encode($postData['destinatario']['x_cpl'], 0, 60))) {
                    $erro = true;
                    $msgErro = utf8_encode('O complemento do endereço do destinatário da NF-e não é válido.');
                }
                if(!$validarCampos->validaMinimoMaximo(utf8_encode($postData['destinatario']['x_bairro'], 2, 60))) {
                    $erro = true;
                    $msgErro = utf8_encode('O bairro do endereço do destinatário da NF-e não é válido.');
                }
                if($postData['destinatario']['email'] != '' && !$validarCampos->validaEMail($postData['destinatario']['email'])) {
                    $erro = true;
                    $msgErro = utf8_encode('O e-mail do destinatário da NF-e não é válido.');
                }
                $ufDestinatario = $validarCampos->getUfEquivalente($estadoDestinatario->getCode());
                $nfeMunicipioDestinatario = $validarCampos->getMunicipio($postData['destinatario']['x_mun'], $ufDestinatario);
                if(!$nfeMunicipioDestinatario->getCodigo()) {
                    $erro = true;
                    $msgErro = utf8_encode('O Munícipio do destinatário da NF-e não é válido.');
                }
                $nfeIdentificacaoDestinatario = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('tipo_identificacao', array('eq' => 'dest'))
                                ->getFirstItem();
                $identificacaoIdDestinatario = $nfeIdentificacaoDestinatario->getIdentificacaoId();
                $nfeIdentificacaoDestinatario->setData($postData['destinatario']);
                if($identificacaoIdDestinatario) {
                    $nfeIdentificacaoDestinatario->setIdentificacaoId($identificacaoIdDestinatario);
                }
                $nfeIdentificacaoDestinatario->setNfeId($nfeId);
                if($postData['destinatario']['cnpj']) {
                    $nfeIdentificacaoDestinatario->setCnpj(preg_replace('/[^\d]/', '', $postData['destinatario']['cnpj']));
                    $nfeIdentificacaoDestinatario->setCpf(null);
                }
                if($postData['destinatario']['cpf']) {
                    $nfeIdentificacaoDestinatario->setCpf(preg_replace('/[^\d]/', '', $postData['destinatario']['cpf']));
                    $nfeIdentificacaoDestinatario->setCnpj(null);
                    $nfeIdentificacaoDestinatario->setIndIeDest('9');
                    $nfeIdentificacaoDestinatario->setIe(null);
                    $nfeIdentificacaoDestinatario->setIsuf(null);
                }
                if($tpAmb == '2') {
                    $nfeIdentificacaoDestinatario->setXNome('NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL');
                }
                $nfeIdentificacaoDestinatario->setCMun($nfeMunicipioDestinatario->getIbgeUf().$nfeMunicipioDestinatario->getCodigo());
                $nfeIdentificacaoDestinatario->setXMun($nfeMunicipioDestinatario->getNome());
                $nfeIdentificacaoDestinatario->setRegionId($estadoDestinatario->getRegionId());
                $nfeIdentificacaoDestinatario->setUf($estadoDestinatario->getCode());
                $nfeIdentificacaoDestinatario->setCep(preg_replace('/[^\d]/', '', $postData['destinatario']['cep']));
                $nfeIdentificacaoDestinatario->setCPais('1058');
                $nfeIdentificacaoDestinatario->setXPais('Brasil');
                $nfeIdentificacaoDestinatario->setFone(preg_replace('/[^\d]/', '', $postData['destinatario']['fone']));
                $nfeIdentificacaoDestinatario->save();
                
                if($postData['nfe']['fin_nfe'] != '1') {
                    $nfeReferenciado = Mage::getModel('nfe/nfereferenciado')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('tipo_documento', array('eq' => $postData['referenciado']['tipo_documento']))
                                ->getFirstItem();
                    $referenciadoId = $nfeReferenciado->getReferenciadoId();
                    $nfeReferenciado->setData($postData['referenciado']);
                    if($referenciadoId) {
                        $nfeReferenciado->setReferenciadoId($referenciadoId);
                    }
                    $nfeReferenciado->setNfeId($nfeId);
                    if($postData['referenciado']['tipo_documento'] == 'refNF' || $postData['referenciado']['tipo_documento'] == 'refNFP') {
                        if($postData['referenciado']['tipo_documento'] == 'refNFP') {
                            $cpf = preg_replace('/[^\d]/', '', $postData['referenciado']['cpf']);
                            $nfeReferenciado->setCpf($cpf);
                        }
                        $nfeReferenciado->setAamm(preg_replace('/[^\d]/', '', $postData['referenciado']['aamm']));
                        $cnpj = preg_replace('/[^\d]/', '', $postData['referenciado']['cnpj']);
                        $estadoReferenciado = Mage::getModel('directory/region')->load($postData['referenciado']['region_id']);
                        $referenciadoCUF = $validarCampos->getUfEquivalente($estadoReferenciado->getCode());
                        $nfeReferenciado->setCnpj($cnpj);
                        $nfeReferenciado->setCUf($referenciadoCUF);
                        if(!$estadoReferenciado->getRegionId()) {
                            $erro = true;
                            $msgErro = utf8_encode('O Estado do referenciado da NF-e não é válido.');
                        }
                    } else {
                        $nfeReferenciado->setRegionId('0');
                    }
                    $nfeReferenciado->save();
                    $model->setTemReferencia('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['tem_retirada'])) {
                    $nfeIdentificacaoRetirada = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('tipo_identificacao', array('eq' => 'retirada'))
                                ->getFirstItem();
                    $identificacaoIdRetirada = $nfeIdentificacaoRetirada->getIdentificacaoId();
                    $nfeIdentificacaoRetirada->setData($postData['retirada']);
                    if($identificacaoIdRetirada) {
                        $nfeIdentificacaoRetirada->setIdentificacaoId($identificacaoIdRetirada);
                    }
                    $nfeIdentificacaoRetirada->setNfeId($nfeId);
                    if($postData['retirada']['cnpj']) {
                        $nfeIdentificacaoRetirada->setCnpj(preg_replace('/[^\d]/', '', $postData['retirada']['cnpj']));
                        $nfeIdentificacaoDestinatario->setCpf(null);
                    }
                    if($postData['retirada']['cpf']) {
                        $nfeIdentificacaoRetirada->setCpf(preg_replace('/[^\d]/', '', $postData['retirada']['cpf']));
                        $nfeIdentificacaoDestinatario->setCnpj(null);
                    }
                    $estadoRetirada = Mage::getModel('directory/region')->load($postData['retirada']['region_id']);
                    $ufRetirada = $validarCampos->getUfEquivalente($estadoRetirada->getCode());
                    $nfeMunicipioRetirada = $validarCampos->getMunicipio($postData['retirada']['x_mun'], $ufRetirada);
                    if(!$nfeMunicipioRetirada->getCodigo()) {
                        $erro = true;
                        $msgErro = utf8_encode('O Munícipio de retirada da NF-e não é válido.');
                    }
                    $nfeIdentificacaoRetirada->setCMun($nfeMunicipioRetirada->getIbgeUf().$nfeMunicipioRetirada->getCodigo());
                    $nfeIdentificacaoRetirada->setXMun($nfeMunicipioRetirada->getNome());
                    $nfeIdentificacaoRetirada->setRegionId($estadoRetirada->getRegionId());
                    $nfeIdentificacaoRetirada->setUf($estadoRetirada->getCode());
                    $nfeIdentificacaoRetirada->setCep(preg_replace('/[^\d]/', '', $postData['retirada']['cep']));
                    $nfeIdentificacaoRetirada->setCPais('1058');
                    $nfeIdentificacaoRetirada->setXPais('Brasil');
                    $nfeIdentificacaoRetirada->setFone(preg_replace('/[^\d]/', '', $postData['retirada']['fone']));
                    $nfeIdentificacaoRetirada->save();
                    $model->setTemRetirada('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['tem_entrega'])) {
                    $nfeIdentificacaoEntrega = Mage::getModel('nfe/nfeidentificacao')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('tipo_identificacao', array('eq' => 'entrega'))
                                ->getFirstItem();
                    $identificacaoIdEntrega = $nfeIdentificacaoEntrega->getIdentificacaoId();
                    $nfeIdentificacaoEntrega->setData($postData['entrega']);
                    if($identificacaoIdEntrega) {
                        $nfeIdentificacaoEntrega->setIdentificacaoId($identificacaoIdEntrega);
                    }
                    $nfeIdentificacaoEntrega->setNfeId($nfeId);
                    if($postData['entrega']['cnpj']) {
                        $nfeIdentificacaoEntrega->setCnpj(preg_replace('/[^\d]/', '', $postData['entrega']['cnpj']));
                        $nfeIdentificacaoDestinatario->setCpf(null);
                    }
                    if($postData['entrega']['cpf']) {
                        $nfeIdentificacaoEntrega->setCpf(preg_replace('/[^\d]/', '', $postData['entrega']['cpf']));
                        $nfeIdentificacaoDestinatario->setCnpj(null);
                    }
                    $estadoEntrega = Mage::getModel('directory/region')->load($postData['entrega']['region_id']);
                    $ufEntrega = $validarCampos->getUfEquivalente($estadoEntrega->getCode());
                    $nfeMunicipioEntrega = $validarCampos->getMunicipio($postData['entrega']['x_mun'], $ufEntrega);
                    if(!$nfeMunicipioEntrega->getCodigo()) {
                        $erro = true;
                        $msgErro = utf8_encode('O Munícipio de entrega da NF-e não é válido.');
                    }
                    $nfeIdentificacaoEntrega->setCMun($nfeMunicipioEntrega->getIbgeUf().$nfeMunicipioEntrega->getCodigo());
                    $nfeIdentificacaoEntrega->setXMun($nfeMunicipioEntrega->getNome());
                    $nfeIdentificacaoEntrega->setRegionId($estadoEntrega->getRegionId());
                    $nfeIdentificacaoEntrega->setUf($estadoEntrega->getCode());
                    $nfeIdentificacaoEntrega->setCep(preg_replace('/[^\d]/', '', $postData['entrega']['cep']));
                    $nfeIdentificacaoEntrega->setCPais('1058');
                    $nfeIdentificacaoEntrega->setXPais('Brasil');
                    $nfeIdentificacaoEntrega->setFone(preg_replace('/[^\d]/', '', $postData['entrega']['fone']));
                    $nfeIdentificacaoEntrega->save();
                    $model->setTemEntrega('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['tem_importacao'])) {
                    $model->setTemImportacao('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['tem_exportacao'])) {
                    $model->setTemExportacao('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['trans_volume'])) {
                    $volumeCollection = Mage::getModel('nfe/nfetransporte')->getCollection()->addFieldToFilter('nfe_id', $nfeId)->addFieldToFilter('tipo_informacao', 'vol');
                    foreach($volumeCollection as $volumeModel) {
                        $volumeModel->delete();
                    }
                    $volumesArray = $postData['nfe']['trans_volume']['value'];
                    $volumesArrayDelete = $postData['nfe']['trans_volume']['delete'];
                    for($i=0; $i<count($volumesArray); $i++) {
                        if($volumesArray['option_'.$i] && $volumesArrayDelete['option_'.$i] != '1') {
                            $volume = Mage::getModel('nfe/nfetransporte');
                            $volume->setNfeId($nfeId);
                            $volume->setTipoInformacao('vol');
                            $volume->setQVol($volumesArray['option_'.$i]['q_vol']);
                            $volume->setEsp($volumesArray['option_'.$i]['esp']);
                            $volume->setMarca($volumesArray['option_'.$i]['marca']);
                            $volume->setNVol($volumesArray['option_'.$i]['n_vol']);
                            $volume->setPesoL($volumesArray['option_'.$i]['peso_l']);
                            $volume->setPesoB($volumesArray['option_'.$i]['peso_b']);
                            $volume->setNLacre($volumesArray['option_'.$i]['n_lacre']);
                            $volume->save();
                        }
                    }
                    $model->setTransTemVol('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['trans_lacre'])) {
                    $lacreCollection = Mage::getModel('nfe/nfetransporte')->getCollection()->addFieldToFilter('nfe_id', $nfeId)->addFieldToFilter('tipo_informacao', 'lacres');
                    foreach($lacreCollection as $lacreModel) {
                        $lacreModel->delete();
                    }
                    $lacresArray = $postData['nfe']['trans_lacre']['value'];
                    $lacresArrayDelete = $postData['nfe']['trans_lacre']['delete'];
                    for($i=0; $i<count($volumesArray); $i++) {
                        if($lacresArray['option_'.$i] && $lacresArrayDelete['option_'.$i] != '1') {
                            $lacre = Mage::getModel('nfe/nfetransporte');
                            $lacre->setNfeId($nfeId);
                            $lacre->setTipoInformacao('lacres');
                            $lacre->setNLacre($lacresArray['option_'.$i]['n_lacre']);
                            $lacre->save();
                        }
                    }
                    $model->setTransTemLacre('1');
                    $model->save();
                }
                
                if(isset($postData['nfe']['trans_reboque'])) {
                    $reboqueCollection = Mage::getModel('nfe/nfetransporte')->getCollection()->addFieldToFilter('nfe_id', $nfeId)->addFieldToFilter('tipo_informacao', 'reboque');
                    foreach($reboqueCollection as $reboqueModel) {
                        $reboqueModel->delete();
                    }
                    $reboquesArray = $postData['nfe']['trans_reboque']['value'];
                    $reboquesArrayDelete = $postData['nfe']['trans_reboque']['delete'];
                    for($i=0; $i<count($reboquesArray); $i++) {
                        if($reboquesArray['option_'.$i] && $reboquesArrayDelete['option_'.$i] != '1') {
                            $reboque = Mage::getModel('nfe/nfetransporte');
                            $reboque->setNfeId($nfeId);
                            $reboque->setTipoInformacao('reboque');
                            $reboque->setPlaca($reboquesArray['option_'.$i]['placa']);
                            $reboque->setUf($reboquesArray['option_'.$i]['uf']);
                            $reboque->setRntc($reboquesArray['option_'.$i]['rntc']);
                            $reboque->setVagao($reboquesArray['option_'.$i]['vagao']);
                            $reboque->setBalsa($reboquesArray['option_'.$i]['balsa']);
                            $reboque->save();
                        }
                    }
                    $model->setTransTemReboque('1');
                    $model->save();
                }
                
                if($postData['nfe']['cob_n_fat']) {
                    $cobrancaCollection = Mage::getModel('nfe/nfecobranca')->getCollection()->addFieldToFilter('nfe_id', $nfeId);
                    foreach($cobrancaCollection as $cobrancaModel) {
                        $cobrancaModel->delete();
                    }
                    $cobrancasArray = $postData['nfe']['cob_duplicata']['value'];
                    $cobrancasArrayDelete = $postData['nfe']['cob_duplicata']['delete'];
                    for($i=0; $i<count($cobrancasArray); $i++) {
                        if($cobrancasArray['option_'.$i] && $cobrancasArrayDelete['option_'.$i] != '1') {
                            $cobranca = Mage::getModel('nfe/nfecobranca');
                            $cobranca->setNfeId($nfeId);
                            $cobranca->setCob_n_dup($cobrancasArray['option_'.$i]['cob_n_dup']);
                            $cobDataVencimento = $cobrancasArray['option_'.$i]['cob_d_venc'];
                            $arrDataVenc = explode('/', $cobDataVencimento);
                            $cobranca->setCob_d_venc($arrDataVenc[2].'-'.$arrDataVenc[1].'-'.$arrDataVenc[0]);
                            $cobranca->setCob_v_dup($cobrancasArray['option_'.$i]['cob_v_dup']);
                            $cobranca->save();
                        }
                    }
                }
                
                $itensArray = $postData['itens']['value'];
                $itensArrayDelete = $postData['itens']['delete'];
                $nItem = 0;
                for($i=0; $i<count($itensArray); $i++) {
                    if($itensArray['option_'.$i]['produto'] && $itensArrayDelete['option_'.$i] != '1') {
                        $nfeProduto = Mage::getModel('nfe/nfeproduto')->getCollection()
                                ->addFieldToFilter('nfe_id', array('eq' => $nfeId))
                                ->addFieldToFilter('produto', array('eq' => $itensArray['option_'.$i]['produto']));
                        if($nfeProduto->count() > 1) {
                            if(isset($produtoExiste[$itensArray['option_'.$i]['produto']])) {
                                $nfeProduto = $nfeProduto->getLastItem();
                            } else {
                                $nfeProduto = $nfeProduto->getFirstItem();
                                $produtoExiste[$itensArray['option_'.$i]['produto']] = true;
                            }
                        } else {
                            $nfeProduto = $nfeProduto->getFirstItem();
                        }
                        /*
                        if($itensArray['option_'.$i]['c_ean'] == '' || $itensArray['option_'.$i]['c_ean'] == '0') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem GTIN e portanto está NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['c_ean_trib' || $itensArray['option_'.$i]['c_ean_trib']] == '0') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem GTIN Tributação e portanto está NF-e não é válida.');
                        }
                         */
                        if($itensArray['option_'.$i]['ncm'] == '' || $itensArray['option_'.$i]['ncm'] == '0') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem NCM e portanto está NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['cfop'] == '' || $itensArray['option_'.$i]['cfop'] == '0') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem CFOP Tributação e portanto está NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['u_com'] == '') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem Unidade Comercial e portanto está NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['u_trib'] == '') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem Unidade Tributável e portanto está NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['q_com'] == 0) {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e possuem quantidade igual a zero e portanto está NF-e não é válida.');
                        }
                        $nItem++;
                        $productId = preg_replace('/[^\d]/', '', $itensArray['option_'.$i]['produto']);
                        $produto = Mage::getModel('catalog/product')->load($productId);
                        $nfeProduto->setNfeId($nfeId);
                        $nfeProduto->setProduto($itensArray['option_'.$i]['produto']);
                        $nfeProduto->setNItem($nItem);
                        $nfeProduto->setCProd($produto->getSku());
                        $nfeProduto->setCEan($itensArray['option_'.$i]['c_ean']);
                        $nfeProduto->setNcm($itensArray['option_'.$i]['ncm']);
                        $nfeProduto->setNve($itensArray['option_'.$i]['nve']);
                        $nfeProduto->setExtipi($itensArray['option_'.$i]['extipi']);
                        $nfeProduto->setCest($itensArray['option_'.$i]['cest']);
                        $nfeProduto->setCfop($itensArray['option_'.$i]['cfop']);
                        $nfeProduto->setUCom($itensArray['option_'.$i]['u_com']);
                        $nfeProduto->setXProd($itensArray['option_'.$i]['x_prod']);
                        $nfeProduto->setQCom($itensArray['option_'.$i]['q_com']);
                        $nfeProduto->setVUnCom($itensArray['option_'.$i]['v_un_com']);
                        $nfeProduto->setVProd($itensArray['option_'.$i]['v_prod']);
                        $nfeProduto->setCEanTrib($itensArray['option_'.$i]['c_ean_trib']);
                        $nfeProduto->setUTrib($itensArray['option_'.$i]['u_trib']);
                        $nfeProduto->setQTrib($itensArray['option_'.$i]['q_trib']);
                        $nfeProduto->setVUnTrib($itensArray['option_'.$i]['v_un_trib']);
                        $nfeProduto->setVFrete($itensArray['option_'.$i]['v_frete']);
                        $nfeProduto->setVSeg($itensArray['option_'.$i]['v_seg']);
                        $nfeProduto->setVDesc($itensArray['option_'.$i]['v_desc']);
                        $nfeProduto->setVOutro($itensArray['option_'.$i]['v_outro']);
                        if(isset($itensArray['option_'.$i]['ind_tot'])) {
                            $nfeProduto->setIndTot('1');
                        } else {
                            $nfeProduto->setIndTot(null);
                        }
                        $nfeProduto->setXPed($postData['nfe']['pedido_increment_id']);
                        $nfeProduto->setNItemPed($nItem);
                        $nfeProduto->setVTotTrib($itensArray['option_'.$i]['v_tot_trib']);
                        $nfeProduto->setPDevol($itensArray['option_'.$i]['p_devol']);
                        $nfeProduto->setVIpiDevol($itensArray['option_'.$i]['v_ipi_devol']);
                        $nfeProduto->setInfAdProd($itensArray['option_'.$i]['inf_ad_prod']);
                        $nfeProduto->save();
                        $produtoId = $nfeProduto->getProdutoId();
                        if($itensArray['option_'.$i]['produto_especifico'] != '') {
                            $nfeProduto->setEhEspecifico('1');
                            if($itensArray['option_'.$i]['produto_especifico'] == '1') {
                                $nfeProdutoEspecifico = Mage::getModel('nfe/nfeprodutoespecifico')->getCollection()
                                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                        ->addFieldToFilter('tipo_especifico', array('eq' => 'veicProd'))
                                        ->getFirstItem();
                                $nfeProdutoEspecifico->setProdutoId($produtoId);
                                $nfeProdutoEspecifico->setTipoEspecifico('veicProd');
                                $nfeProdutoEspecifico->setTpOp($itensArray['option_'.$i]['tp_op']);
                                $nfeProdutoEspecifico->setChassi($itensArray['option_'.$i]['chassi']);
                                $nfeProdutoEspecifico->setCCor($itensArray['option_'.$i]['c_cor']);
                                $nfeProdutoEspecifico->setXCor($itensArray['option_'.$i]['x_cor']);
                                $nfeProdutoEspecifico->setPot($itensArray['option_'.$i]['pot']);
                                $nfeProdutoEspecifico->setCilin($itensArray['option_'.$i]['cilin']);
                                $nfeProdutoEspecifico->setPesoL($itensArray['option_'.$i]['peso_l']);
                                $nfeProdutoEspecifico->setPesoB($itensArray['option_'.$i]['peso_b']);
                                $nfeProdutoEspecifico->setNSerie($itensArray['option_'.$i]['n_serie']);
                                $nfeProdutoEspecifico->setTpComb($itensArray['option_'.$i]['tp_comb']);
                                $nfeProdutoEspecifico->setNMotor($itensArray['option_'.$i]['n_motor']);
                                $nfeProdutoEspecifico->setCmt($itensArray['option_'.$i]['cmt']);
                                $nfeProdutoEspecifico->setDist($itensArray['option_'.$i]['dist']);
                                $nfeProdutoEspecifico->setAnoMod($itensArray['option_'.$i]['ano_mod']);
                                $nfeProdutoEspecifico->setAnoFab($itensArray['option_'.$i]['ano_fab']);
                                $nfeProdutoEspecifico->setTpPint($itensArray['option_'.$i]['tp_pint']);
                                $nfeProdutoEspecifico->setTpVeic($itensArray['option_'.$i]['tp_veic']);
                                $nfeProdutoEspecifico->setEspVeic($itensArray['option_'.$i]['esp_veic']);
                                $nfeProdutoEspecifico->setVin($itensArray['option_'.$i]['vin']);
                                $nfeProdutoEspecifico->setCondVeic($itensArray['option_'.$i]['cond_veic']);
                                $nfeProdutoEspecifico->setCMod($itensArray['option_'.$i]['c_mod']);
                                $nfeProdutoEspecifico->setCCorDenatran($itensArray['option_'.$i]['c_cor_denatran']);
                                $nfeProdutoEspecifico->setLota($itensArray['option_'.$i]['lota']);
                                $nfeProdutoEspecifico->setTpRest($itensArray['option_'.$i]['tp_rest']);
                                $nfeProdutoEspecifico->save();
                            }
                            if($itensArray['option_'.$i]['produto_especifico'] == '2') {
                                $nfeProdutoEspecifico = Mage::getModel('nfe/nfeprodutoespecifico')->getCollection()
                                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                        ->addFieldToFilter('tipo_especifico', array('eq' => 'med'))
                                        ->getFirstItem();
                                $nfeProdutoEspecifico->setProdutoId($produtoId);
                                $nfeProdutoEspecifico->setTipoEspecifico('med');
                                $nfeProdutoEspecifico->setNLote($itensArray['option_'.$i]['n_lote']);
                                $nfeProdutoEspecifico->setQLote($itensArray['option_'.$i]['q_lote']);
                                $nfeProdutoEspecifico->setDFab($itensArray['option_'.$i]['d_fab']);
                                $nfeProdutoEspecifico->setDVal($itensArray['option_'.$i]['d_val']);
                                $nfeProdutoEspecifico->setNLote($itensArray['option_'.$i]['n_lote']);
                                $nfeProdutoEspecifico->setVPmc($itensArray['option_'.$i]['v_pmc']);
                                $nfeProdutoEspecifico->save();
                            }
                            if($itensArray['option_'.$i]['produto_especifico'] == '3') {
                                $nfeProdutoEspecifico = Mage::getModel('nfe/nfeprodutoespecifico')->getCollection()
                                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                        ->addFieldToFilter('tipo_especifico', array('eq' => 'arma'))
                                        ->getFirstItem();
                                $nfeProdutoEspecifico->setProdutoId($produtoId);
                                $nfeProdutoEspecifico->setTipoEspecifico('arma');
                                $nfeProdutoEspecifico->setTpArma($itensArray['option_'.$i]['tp_arma']);
                                $nfeProdutoEspecifico->setNSerie($itensArray['option_'.$i]['arma_n_serie']);
                                $nfeProdutoEspecifico->setNCano($itensArray['option_'.$i]['n_cano']);
                                $nfeProdutoEspecifico->setDesc($itensArray['option_'.$i]['desc']);
                                $nfeProdutoEspecifico->save();
                            }
                            if($itensArray['option_'.$i]['produto_especifico'] == '4') {
                                $nfeProdutoEspecifico = Mage::getModel('nfe/nfeprodutoespecifico')->getCollection()
                                        ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                        ->addFieldToFilter('tipo_especifico', array('eq' => 'comb'))
                                        ->getFirstItem();
                                $nfeProdutoEspecifico->setProdutoId($produtoId);
                                $nfeProdutoEspecifico->setTipoEspecifico('comb');
                                $nfeProdutoEspecifico->setCProdAnp($itensArray['option_'.$i]['c_prod_anp']);
                                $nfeProdutoEspecifico->setPMixGn($itensArray['option_'.$i]['p_mix_gn']);
                                $nfeProdutoEspecifico->setCodif($itensArray['option_'.$i]['codif']);
                                $nfeProdutoEspecifico->setQTemp($itensArray['option_'.$i]['q_temp']);
                                $nfeProdutoEspecifico->setUfCons($itensArray['option_'.$i]['uf_cons']);
                                $nfeProdutoEspecifico->setQBcProd($itensArray['option_'.$i]['q_bc_prod']);
                                $nfeProdutoEspecifico->setVAliqProd($itensArray['option_'.$i]['v_aliq_prod']);
                                $nfeProdutoEspecifico->setVCide($itensArray['option_'.$i]['v_cide']);
                                $nfeProdutoEspecifico->save();
                            }
                        }
                        if($itensArray['option_'.$i]['operacao'] == '1' && $itensArray['option_'.$i]['cst'] != '' || $itensArray['option_'.$i]['operacao'] == '1' && $itensArray['option_'.$i]['cso_sn'] != '') {
                            $nfeProduto->setTemIcms('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('icms'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('icms');
                            if($itensArray['option_'.$i]['cst'] != '') {
                                $nfeProdutoImposto->setCst($itensArray['option_'.$i]['cst']);
                            }
                            if($itensArray['option_'.$i]['cso_sn'] != '') {
                                $nfeProdutoImposto->setCsoSn($itensArray['option_'.$i]['cso_sn']);
                            }
                            $nfeProdutoImposto->setOrig($itensArray['option_'.$i]['orig']);
                            $nfeProdutoImposto->setModBc($itensArray['option_'.$i]['mod_bc']);
                            $nfeProdutoImposto->setVBc($itensArray['option_'.$i]['v_bc']);
                            $nfeProdutoImposto->setPIcms($itensArray['option_'.$i]['p_icms']);
                            $nfeProdutoImposto->setVIcms($itensArray['option_'.$i]['v_icms']);
                            $nfeProdutoImposto->setPRedBc($itensArray['option_'.$i]['p_red_bc']);
                            $nfeProdutoImposto->setPMvaSt($itensArray['option_'.$i]['p_mva_st']);
                            $nfeProdutoImposto->setModBcSt($itensArray['option_'.$i]['mod_bc_st']);
                            $nfeProdutoImposto->setVBcSt($itensArray['option_'.$i]['v_bc_st']);
                            $nfeProdutoImposto->setPIcmsSt($itensArray['option_'.$i]['p_icms_st']);
                            $nfeProdutoImposto->setVIcmsSt($itensArray['option_'.$i]['v_icms_st']);
                            $nfeProdutoImposto->setPRedBcSt($itensArray['option_'.$i]['p_red_bc_st']);
                            $nfeProdutoImposto->setVIcmsDeson($itensArray['option_'.$i]['v_icms_deson']);
                            $nfeProdutoImposto->setMotDesIcms($itensArray['option_'.$i]['mot_des_icms']);
                            $nfeProdutoImposto->setVIcmsOp($itensArray['option_'.$i]['v_icms_op']);
                            $nfeProdutoImposto->setPDif($itensArray['option_'.$i]['p_dif']);
                            $nfeProdutoImposto->setVIcmsDif($itensArray['option_'.$i]['v_icms_dif']);
                            $nfeProdutoImposto->setVBcstRet($itensArray['option_'.$i]['v_bcst_ret']);
                            $nfeProdutoImposto->setVIcmsStRet($itensArray['option_'.$i]['v_icms_st_ret']);
                            $nfeProdutoImposto->setPBcOp($itensArray['option_'.$i]['p_bc_op']);
                            $nfeProdutoImposto->setUfSt($itensArray['option_'.$i]['uf_st']);
                            $nfeProdutoImposto->setVBcStDest($itensArray['option_'.$i]['v_bc_st_dest']);
                            $nfeProdutoImposto->setVIcmsStDest($itensArray['option_'.$i]['v_icms_st_dest']);
                            $nfeProdutoImposto->setPCredSn($itensArray['option_'.$i]['p_cred_sn']);
                            $nfeProdutoImposto->setVCredIcmsSn($itensArray['option_'.$i]['v_cred_icms_sn']);
                            $nfeProdutoImposto->save();
                        } else if($itensArray['option_'.$i]['operacao'] == '1' && $itensArray['option_'.$i]['cst'] == '' || $itensArray['option_'.$i]['operacao'] == '1' && $itensArray['option_'.$i]['cso_sn'] == '') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais itens desta NF-e não possuem CST/CSOSN e portanto está NF-e não é válida.');
                        }
                        if($model->getIdDest() == '2' && $nfeIdentificacaoDestinatario->getIndIeDest() == '9') {
                            $nfeProduto->setTemIcmsDestino('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('icms_destino'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('icms_destino');
                            $nfeProdutoImposto->setVBcUfDest($itensArray['option_'.$i]['v_bc_uf_dest']);
                            $nfeProdutoImposto->setPFcpUfDest($itensArray['option_'.$i]['p_fcp_uf_dest']);
                            $nfeProdutoImposto->setPIcmsUfDest($itensArray['option_'.$i]['p_icms_uf_dest']);
                            $nfeProdutoImposto->setPIcmsInter($itensArray['option_'.$i]['p_icms_inter']);
                            $nfeProdutoImposto->setPIcmsInterPart($itensArray['option_'.$i]['p_icms_inter_part']);
                            $nfeProdutoImposto->setVFcpUfDest($itensArray['option_'.$i]['v_fcp_uf_dest']);
                            $nfeProdutoImposto->setVIcmsUfDest($itensArray['option_'.$i]['v_icms_uf_dest']);
                            $nfeProdutoImposto->setVIcmsUfRemet($itensArray['option_'.$i]['v_icms_uf_remet']);
                            $nfeProdutoImposto->save();
                        } else {
                            $nfeProduto->setTemIcmsDestino(null);
                        }
                        if($itensArray['option_'.$i]['pis_cst'] != '') {
                            $nfeProduto->setTemPis('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('pis'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('pis');
                            $nfeProdutoImposto->setCst($itensArray['option_'.$i]['pis_cst']);
                            $nfeProdutoImposto->setVBc($itensArray['option_'.$i]['pis_v_bc']);
                            $nfeProdutoImposto->setPPis($itensArray['option_'.$i]['p_pis']);
                            $nfeProdutoImposto->setVAliqProd($itensArray['option_'.$i]['pis_v_aliq_prod']);
                            $nfeProdutoImposto->setQBcProd($itensArray['option_'.$i]['pis_q_bc_prod']);
                            $nfeProdutoImposto->setVPis($itensArray['option_'.$i]['v_pis']);
                            $nfeProdutoImposto->save();
                        } else {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais produtos não possuem CST de PIS e sendo assim a NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['cofins_cst'] != '') {
                            $nfeProduto->setTemCofins('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('cofins'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('cofins');
                            $nfeProdutoImposto->setCst($itensArray['option_'.$i]['cofins_cst']);
                            $nfeProdutoImposto->setVBc($itensArray['option_'.$i]['cofins_v_bc']);
                            $nfeProdutoImposto->setPCofins($itensArray['option_'.$i]['p_cofins']);
                            $nfeProdutoImposto->setVAliqProd($itensArray['option_'.$i]['cofins_v_aliq_prod']);
                            $nfeProdutoImposto->setQBcProd($itensArray['option_'.$i]['cofins_q_bc_prod']);
                            $nfeProdutoImposto->setVCofins($itensArray['option_'.$i]['v_cofins']);
                            $nfeProdutoImposto->save();
                        } else {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais produtos não possuem CST de COFINS e sendo assim a NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['ipi_cst'] != '') {
                            $nfeProduto->setTemIpi('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('ipi'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('ipi');
                            $nfeProdutoImposto->setCst($itensArray['option_'.$i]['ipi_cst']);
                            $nfeProdutoImposto->setClEnq($itensArray['option_'.$i]['cl_enq']);
                            $nfeProdutoImposto->setCEnq($itensArray['option_'.$i]['c_enq']);
                            $nfeProdutoImposto->setCSelo($itensArray['option_'.$i]['c_selo']);
                            $nfeProdutoImposto->setQSelo($itensArray['option_'.$i]['q_selo']);
                            $nfeProdutoImposto->setCnpjProd(preg_replace('/[^\d]/', '', $itensArray['option_'.$i]['cnpj_prod']));
                            $nfeProdutoImposto->setVBc($itensArray['option_'.$i]['ipi_v_bc']);
                            $nfeProdutoImposto->setPIpi($itensArray['option_'.$i]['p_ipi']);
                            $nfeProdutoImposto->setQUnid($itensArray['option_'.$i]['q_unid']);
                            $nfeProdutoImposto->setVUnid($itensArray['option_'.$i]['v_unid']);
                            $nfeProdutoImposto->setVIpi($itensArray['option_'.$i]['v_ipi']);
                            $nfeProdutoImposto->save();
                        } else if($itensArray['option_'.$i]['ipi_cst'] != '' && $itensArray['option_'.$i]['operacao'] == '1') {
                            $erro = true;
                            $msgErro = utf8_encode('Um ou mais produtos não possuem CST de IPI e sendo assim a NF-e não é válida.');
                        }
                        if($itensArray['option_'.$i]['operacao'] == '2') {
                            $nfeProduto->setTemIssqn('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('issqn'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('issqn');
                            $nfeProdutoImposto->setVBc($itensArray['option_'.$i]['issqn_v_bc']);
                            $nfeProdutoImposto->setVAliq($itensArray['option_'.$i]['v_aliq']);
                            $nfeProdutoImposto->setVIssqn($itensArray['option_'.$i]['v_issqn']);
                            if($itensArray['option_'.$i]['municipio_issqn'] != '') {
                                $issqnMunicipio = $validarCampos->getMunicipio($itensArray['option_'.$i]['municipio_issqn'], 'n');
                                if(!$issqnMunicipio->getCodigo()) {
                                    $erro = true;
                                    $msgErro = utf8_encode('O Munícipio de ocorrência da ISSQN da NF-e não é válido.');
                                }
                                $nfeProdutoImposto->setMunicipioIssqn($issqnMunicipio->getNome());
                                $nfeProdutoImposto->setCMunFg($issqnMunicipio->getIbgeUf().$issqnMunicipio->getCodigo());
                            }
                            $nfeProdutoImposto->setCListServ($itensArray['option_'.$i]['c_list_serv']);
                            $nfeProdutoImposto->setVDeducao($itensArray['option_'.$i]['v_deducao']);
                            $nfeProdutoImposto->setVOutro($itensArray['option_'.$i]['issqn_v_outro']);
                            $nfeProdutoImposto->setVDescIncond($itensArray['option_'.$i]['v_desc_incond']);
                            $nfeProdutoImposto->setVDescCond($itensArray['option_'.$i]['v_desc_cond']);
                            $nfeProdutoImposto->setVDescIncond($itensArray['option_'.$i]['v_desc_incond']);
                            $nfeProdutoImposto->setVIssRet($itensArray['option_'.$i]['v_iss_ret']);
                            $nfeProdutoImposto->setIndIss($itensArray['option_'.$i]['ind_iss']);
                            $nfeProdutoImposto->setCServico($itensArray['option_'.$i]['c_servico']);
                            if($itensArray['option_'.$i]['municipio_incidencia'] != '') {
                                $incidenciaMunicipio = $validarCampos->getMunicipio($itensArray['option_'.$i]['municipio_incidencia'], 'n');
                                if(!$incidenciaMunicipio->getCodigo()) {
                                    $erro = true;
                                    $msgErro = utf8_encode('O Munícipio de incidência da ISSQN da NF-e não é válido.');
                                }
                                $nfeProdutoImposto->setMunicipioIncidencia($incidenciaMunicipio->getNome());
                                $nfeProdutoImposto->setCMun($incidenciaMunicipio->getIbgeUf().$incidenciaMunicipio->getCodigo());
                            }
                            $nfeProdutoImposto->setCPais('1058');
                            $nfeProdutoImposto->setNProcesso($itensArray['option_'.$i]['n_processo']);
                            $nfeProdutoImposto->setIndIncentivo($itensArray['option_'.$i]['ind_incentivo']);
                            $nfeProdutoImposto->save();
                        }
                        if(isset($postData['nfe']['tem_exportacao'])) {
                            $nfeProdutoExportacao = Mage::getModel('nfe/nfeprodutoimportexport')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_operacao', array('exportacao'))
                                    ->getFirstItem();
                            $nfeProdutoExportacao->setProdutoId($produtoId);
                            $nfeProdutoExportacao->setTipoOperacao('exportacao');
                            $nfeProdutoExportacao->setNDraw($itensArray['option_'.$i]['n_draw']);
                            $nfeProdutoExportacao->setNRe($itensArray['option_'.$i]['n_re']);
                            $nfeProdutoExportacao->setChNfe($itensArray['option_'.$i]['ch_nfe']);
                            $nfeProdutoExportacao->setQExport($itensArray['option_'.$i]['q_export']);
                            $nfeProdutoExportacao->save();
                        }
                        if(isset($postData['nfe']['tem_importacao'])) {
                            $nfeProduto->setTemDi('1');
                            $nfeProduto->setTemIi('1');
                            $nfeProdutoImposto = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_imposto', array('ii'))
                                    ->getFirstItem();
                            $nfeProdutoImposto->setProdutoId($produtoId);
                            $nfeProdutoImposto->setTipoImposto('ii');
                            $nfeProdutoImposto->setIi_v_bc($itensArray['option_'.$i]['ii_v_bc']);
                            $nfeProdutoImposto->setVDespAdu($itensArray['option_'.$i]['v_desp_adu']);
                            $nfeProdutoImposto->setVII($itensArray['option_'.$i]['v_II']);
                            $nfeProdutoImposto->setVIof($itensArray['option_'.$i]['v_iof']);
                            $nfeProdutoImposto->save();
                            $nfeProdutoImportacao = Mage::getModel('nfe/nfeprodutoimportexport')->getCollection()
                                    ->addFieldToFilter('produto_id', array('eq' => $produtoId))
                                    ->addFieldToFilter('tipo_operacao', array('importacao'))
                                    ->getFirstItem();
                            $nfeProdutoImportacao->setProdutoId($produtoId);
                            $nfeProdutoImportacao->setTipoOperacao('importacao');
                            $nfeProdutoImportacao->setNDi($itensArray['option_'.$i]['n_di']);
                            $nfeProdutoImportacao->setDDi($itensArray['option_'.$i]['d_di']);
                            $nfeProdutoImportacao->setXLocDesemb($itensArray['option_'.$i]['x_loc_desemb']);
                            $nfeProdutoImportacao->setUfDesemb($itensArray['option_'.$i]['uf_desemb']);
                            $nfeProdutoImportacao->setDDesemb($itensArray['option_'.$i]['d_desemb']);
                            $nfeProdutoImportacao->setTpViaTransp($itensArray['option_'.$i]['tp_via_transp']);
                            $nfeProdutoImportacao->setAfrmm($itensArray['option_'.$i]['v_afrmm']);
                            $nfeProdutoImportacao->setTpIntermedio($itensArray['option_'.$i]['tp_intermedio']);
                            $nfeProdutoImportacao->setCnpj(preg_replace('/[^\d]/', '', $itensArray['option_'.$i]['cnpj']));
                            $nfeProdutoImportacao->setUfTerceiro($itensArray['option_'.$i]['uf_terceiro']);
                            $nfeProdutoImportacao->setCExportador($itensArray['option_'.$i]['c_exportador']);
                            $nfeProdutoImportacao->setNAdicao($itensArray['option_'.$i]['n_adicao']);
                            $nfeProdutoImportacao->setNSeqAdic($itensArray['option_'.$i]['n_seq_adic']);
                            $nfeProdutoImportacao->setCFabricante($itensArray['option_'.$i]['c_fabricante']);
                            $nfeProdutoImportacao->setVDescDi($itensArray['option_'.$i]['v_desc_di']);
                            $nfeProdutoImportacao->setNDraw($itensArray['option_'.$i]['n_draw']);
                            $nfeProdutoImportacao->save();
                        }
                        $nfeProduto->save();
                        $produtosMovimento[] = $itensArray['option_'.$i]['produto'];
                    }
                }
                if($nItem == 0) {
                    $erro = true;
                    $msgErro = utf8_encode('Não foram constatadas a presença de itens e portanto está NF-e não é válida.');
                }
                $nfeRN->confirmarItensNfe($nfeId, $produtosMovimento);
                if(!$erro) {
                    $xmlGerado = $nfeRN->gerarXml($nfeId);
                    if($xmlGerado == 'sucesso') {
                        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('A NF-e foi salva com sucesso.'));
                        $model->setStatus('1');
                        $model->setMensagem(utf8_encode('Aguardando envio ao orgão responsável.'));
                        $model->save();
                    } else {
                        Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto o XML desta NF-e era gerado. '.$xmlGerado));
                        $model->setStatus('0');
                        $model->setMensagem($xmlGerado);
                        $model->save();
                        $this->_redirect('*/*/');
                        return;
                    }
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                    return;
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era salva. '.$msgErro));
                    $model->setStatus('0');
                    $model->setMensagem($msgErro);
                    $model->save();
                    $this->_redirect('*/*/');
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era salva. '.$e->getMessage()));
            }
            Mage::getSingleton('adminhtml/session')->setNfeData($postData);
            $this->_redirectReferer();
        }
    }
    
    public function gerarNfeAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            try {
                $order = Mage::getModel('sales/order')->load($orderId);
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $retorno = $nfeRN->montarNfe($order);
                if($retorno['status'] == 'sucesso') {
                    $this->_getSession()->addSuccess($this->__($retorno['msg']));
                } else if($retorno['status'] == 'erro') {
                    $this->_getSession()->addError($this->__($retorno['msg']));
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Pedido com NF-e n&atilde;o gerada.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
    }
    
    public function corrigirAction() {
        $nfeId  = $this->getRequest()->getParam('nfe_id');
        $modelNfe = Mage::getModel('nfe/nfe');
        $model = Mage::getModel('nfe/nfecce');
        
        if($nfeId) {
            $modelNfe = $modelNfe->load($nfeId);
            $model = Mage::getModel('nfe/nfecce')->getCollection()
                ->addFieldToFilter('nfe_id', $modelNfe->getNfeId())->getFirstItem();
            if(!$model->getId()) {
                $model->setNfeId($nfeId);
            }
            if(!$modelNfe->getId()) {
                Mage::getSingleton('adminhtml/session')->addError($this->__(utf8_encode('Esta NF-e já não existe mais.')));
                $this->_redirect('*/*/');
                return;
            }
        }
        $nSeqEvento = $model->getNSeqEvento();
        if(!$nSeqEvento) {
            $model->setNSeqEvento(1);
        } else {
            $model->setNSeqEvento($nSeqEvento+1);
        }
        
        $this->_title($model->getId() ? $model->getNSeqEvento() : $this->__('Nova CC-e'));

        $data = Mage::getSingleton('adminhtml/session')->getCceData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('nfe/nfecce', $model);
        
        $this->_initAction()
            ->_addBreadcrumb($nfeId ? $this->__('Editar CC-e') : $this->__('Nova CC-e'), $model->getId() ? $this->__('Editar CC-e') : $this->__('Nova CC-e'))
            ->_addContent($this->getLayout()->createBlock('nfe/adminhtml_nfe_cce')->setData('action', $this->getUrl('*/*/saveCorrigir')))
            ->renderLayout();
    }
    
    public function saveCorrigirAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            $model = Mage::getSingleton('nfe/nfecce');
            $model->setData($postData);
            try {
                $model->save();
                $nfe = Mage::getModel('nfe/nfe');
                $nfe->load($model->getNfeId());
                $nfeHelper = Mage::helper('nfe/nfeHelper');
                $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
                $aRetorno = array();
                $xmlCorrigido = $nfeHelper->envCCe(substr($nfe->getIdTag(),3), $model->getXCorrecao(), $model->getNSeqEvento(), $nfe->getTpAmb(), $aRetorno, $estadoEmitente->getCode(), $nfe->getCUf(), preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj')));
                if($xmlCorrigido['retorno'] == 'sucesso') {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__(utf8_encode('CC-e da NF-e foi salva com sucesso.')));
                    $nfeHelper->salvarXmlCorrigido($xmlCorrigido['xml'], $nfe);
                    $nfeHelper->gerarDacce($xmlCorrigido['xml'], $nfe, 'F');
                    $nfeHelper->setCorrigido($nfe);
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era corrigida.'));
                    $nfe->setMensagem(utf8_encode('Houve erro na correção da NF-e. Erro: '.utf8_decode($xmlCorrigido['retorno'])));
                    $nfe->save();
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto a CC-e da NF-e era salva.'));
            }
            Mage::getSingleton('adminhtml/session')->setCceData($postData);
            $this->_redirectReferer();
        }
    }
    
    public function massGerarNfeAction() {
        $orderIds = $this->getRequest()->getPost('order_ids', array());
        $countNfeOrder = 0;
        $countNonNfeOrder = 0;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getStatus() == 'processing' || $order->getStatus() == 'nfe_cancelada' || $order->getStatus() == 'nfe_retirada' || $order->getStatus() == 'nfe_denegada') {
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $retorno = $nfeRN->montarNfe($order);
                if($retorno['status'] == 'sucesso') {
                    $countNfeOrder++;
                } else if($retorno['status'] == 'erro') {
                    $this->_getSession()->addError($this->__($retorno['msg']));
                    $countNonNfeOrder++;
                }
            } else {
                $countNonNfeOrder++;
            }
        }
        if ($countNonNfeOrder) {
            if ($countNfeOrder) {
                $this->_getSession()->addError($this->__('%s solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e n&atilde;o gerada(s).', $countNonNfeOrder));
            } else {
                $this->_getSession()->addError($this->__('Solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e n&atilde;o gerada(s).'));
            }
        }
        if ($countNfeOrder) {
            $this->_getSession()->addSuccess($this->__('%s solicita&ccedil;&otilde;es de pedido(s) para emiss&atilde;o de NF-e gerada(s) com sucesso.', $countNfeOrder));
        }
        $this->_redirect('*/sales_order/');
    }
    
    public function gerarNfeDevolucaoAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        if ($orderId) {
            try {
                $order = Mage::getModel('sales/order')->load($orderId);
                $nfeRN = Mage::getModel('nfe/nfeRN');
                $retorno = $nfeRN->montarNfeRetorno($order);
                if($retorno['status'] == 'sucesso') {
                    $this->_getSession()->addSuccess($this->__($retorno['msg']));
                } else if($retorno['status'] == 'erro') {
                    $this->_getSession()->addError($this->__($retorno['msg']));
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Pedido com NF-e n&atilde;o gerada.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
    }
    
    public function editRangeAction() {
        $model = Mage::getModel('nfe/nferange')->load('1');
        $this->_title($model->getId() ? $model->getCodigo() : $this->__(utf8_encode('Gerenciar Range da NF-e')));
        $data = Mage::getSingleton('adminhtml/session')->getNfeRange(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('nfe_range', $model);
        
        $this->_initAction()
            ->_addBreadcrumb($this->__(utf8_encode('Gerenciar Range da NF-e')))
            ->_addContent($this->getLayout()->createBlock('nfe/adminhtml_nfe_range')->setData('action', $this->getUrl('*/*/saveRange')))
            ->renderLayout();
    }
    
    public function saveRangeAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                $model = Mage::getModel('nfe/nferange');
                $model->setRangeId('1');
                $model->setNumero($postData['numero']);
                $model->setSerie($postData['serie']);
                $model->setValorInicio('1');
                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__(utf8_encode('Range da NF-e adicionada com sucesso.')));
                $this->_redirect('*/*/');
                return;
            }
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto a Range da NF-e era salva.'));
            }
            Mage::getSingleton('adminhtml/session')->setAliquotaSn($postData);
            $this->_redirectReferer();
        }
    }
    
    public function editEnviarAction() {
        $model = Mage::getModel('nfe/nfe');
        $data = Mage::getSingleton('adminhtml/session')->getNfeEnviar(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        Mage::register('nfe_enviar', $model);
        
        $this->_initAction()
            ->_addBreadcrumb($this->__(utf8_encode('Enviar NF-e do Mês')))
            ->_addContent($this->getLayout()->createBlock('nfe/adminhtml_nfe_enviar')->setData('action', $this->getUrl('*/*/saveEnviar')))
            ->renderLayout();
    }
    
    public function saveEnviarAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                $arquivosXml = array();
                $diaInicial = '01';
                $diaFinal = '31';
                $mes = $postData['mes'];
                $ano = $postData['ano'];
                $nfeCollection = Mage::getModel('nfe/nfe')->getCollection();
                $nfeCollection->addFieldToFilter('dh_recbto', array('date' => true, 'from' => $ano.$mes.$diaInicial.' 00:00:00'));
                $nfeCollection->addFieldToFilter('dh_recbto', array('date' => true, 'to' => $ano.$mes.$diaFinal.' 23:59:59'));
                foreach($nfeCollection as $nfe) {
                    if($nfe->getTpNf() == '0') {
                        $tipo = 'entrada';
                    } else {
                        $tipo = 'saida';
                    }
                    $arquivosXml[] = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS . $nfe->getIdTag() . '.xml';
                    $nfeCce = Mage::getModel('nfe/nfecce')->getCollection()->addFieldToFilter('nfe_id', $nfe->getNfeId())->getFirstItem();
                    if($nfeCce->getCceId()) {
                        $arquivosXml[] = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . 'corrigido' . DS . str_replace('NF', 'CC', $nfe->getIdTag()) . '.xml';
                    }
                }
                $nfeCollectionInutilizados = Mage::getModel('nfe/nfe')->getCollection();
                $nfeCollectionInutilizados->addFieldToFilter('status', array('eq' => '9'));
                $nfeCollectionInutilizados->addFieldToFilter('dh_emi', array('date' => true, 'from' => $ano.$mes.$diaInicial.' 00:00:00'));
                $nfeCollectionInutilizados->addFieldToFilter('dh_emi', array('date' => true, 'to' => $ano.$mes.$diaFinal.' 23:59:59'));
                foreach($nfeCollectionInutilizados as $nfeInutilizado) {
                    $arquivosXml[] = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . 'inutilizado' . DS . $nfeInutilizado->getIdTag() . '.xml';
                }
                $arquivoZip = Mage::getBaseDir(). DS . 'nfe' . DS . 'zip' . DS . 'NFeMes' . '.zip';
                $this->createZipFile($arquivosXml, $arquivoZip, true);
                $this->sendMailAttachedZip($arquivoZip, $postData['mes'], $postData['ano'], $postData['email']);
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__(utf8_encode('O e-mail com as NF-e do Mês foi enviado com sucesso para o seguinte destinatário: '.$postData['email'])));
                $this->_redirect('*/*/');
                return;
            } 
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('O E-mail com as NF-e n&atilde;o pode ser enviado.'));
                Mage::logException($e);
            }
            Mage::getSingleton('adminhtml/session')->setAliquotaSn($postData);
            $this->_redirectReferer();
        }
    }
    
    public function buscarProdutoAction() {
        $produtoId = (string) $this->getRequest()->getParam('produto');
        $product = Mage::getModel('catalog/product')->load($produtoId);
        $result = array();
        $result['x_prod'] = $product->getName();
        $result['gtin'] = ($product->getData('gtin') ? $product->getData('gtin') : '');
        $result['ncm'] = ($product->getAttributeText('ncm') ? $product->getAttributeText('ncm') : '');
        $result['unidade'] = ($product->getAttributeText('unidade') ? $product->getAttributeText('unidade') : '');
        
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function retirarAction() {
        $nfeId = $this->getRequest()->getParam('nfe_id');
        $nfe = Mage::getModel('nfe/nfe');
        $nfe->load($nfeId);
        $nfeHelper = Mage::helper('nfe/nfeHelper');
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        $aRetorno = array();
        $xmlInutilizado = $nfeHelper->inutNF(date('y'), strval(intval($nfe->getSerie())), strval(intval($nfe->getNNf())), strval(intval($nfe->getNNf())), utf8_encode('Número inutilizado por erro de operação'), $nfe->getTpAmb(), $aRetorno, $estadoEmitente->getCode(), $nfe->getCUf(), $nfe->getMod(), preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj')));
        if($xmlInutilizado['retorno'] == 'sucesso') {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('A NF-e foi inutilizada com sucesso.'));
            $nfeHelper->salvarXmlInutilizado($xmlInutilizado['xml'], $nfe);
            $nfeHelper->setRetirado($nfe);
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era inutilizada.'));
            $nfe->setMensagem(utf8_encode('Houve erro na inutilização do número. Erro: '.utf8_decode($xmlInutilizado['retorno'])));
            $nfe->save();
        }
        $this->_redirect('*/*/');
        return;
    }
    
    public function cancelAction() {
        $nfeId = $this->getRequest()->getParam('nfe_id');
        $nfe = Mage::getModel('nfe/nfe');
        $nfe->load($nfeId);
        $nfeHelper = Mage::helper('nfe/nfeHelper');
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        $aRetorno = array();
        $cancelado = $nfeHelper->cancelEvent(substr($nfe->getIdTag(),3),$nfe->getNProt(),utf8_encode('Nota Fiscal cancelada por erro de operação'), $nfe->getTpAmb(), $aRetorno, $estadoEmitente->getCode(), $nfe->getCUf(), preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj')));
        if($cancelado['retorno'] == 'sucesso') {
            $nfe->setVerAplic($cancelado['infProt']['verAplic']);
            $nfe->setDhRecbto($cancelado['infProt']['dhRecbto']);
            $nfe->setNProt($cancelado['infProt']['nProt']);
            //$nfe->setDigVal($cancelado['infProt']['digVal']);
            $nfe->setCStat($cancelado['infProt']['cStat']);
            $nfe->setXMotivo($cancelado['infProt']['xMotivo']);
            $xmlNfe = $nfeHelper->getXmlNfe($nfe);
            $xmlProtocolado = $nfeHelper->addProt($xmlNfe, $cancelado['infProt'], $nfe->getVersao(), 'retEvento');
            if($xmlProtocolado['retorno'] == 'sucesso') {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('A NF-e foi cancelada com sucesso.'));
                if($nfe->getTpNf() == '0') {
                    $tipo = 'entrada';
                } else {
                    $tipo = 'saida';
                }
                $caminho = Mage::getBaseDir(). DS . 'nfe' . DS . 'xml' . DS . $tipo . DS;
                $nfeHelper->salvarXml($xmlProtocolado['xml'], $caminho, $nfe->getIdTag());
                $nfeHelper->gerarDanfe($xmlProtocolado['xml'], $nfe, 'F');
                $nfeHelper->setCancelado($nfe);
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era cancelada.'));
                $nfe->setStatus('5');
                $nfe->setMensagem(utf8_encode('Aguardando correção para envio ao orgão responsável. Erro: '.utf8_decode($xmlProtocolado['retorno'])));
                $nfe->save();
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Um erro ocorreu enquanto esta NF-e era cancelada.'));
            $nfe->setStatus('5');
            $nfe->setMensagem(utf8_encode('Aguardando correção para envio ao orgão responsável. Erro: '.utf8_decode($cancelado['retorno'])));
            $nfe->save();
        }
        $this->_redirect('*/*/');
        return;
    }
    
    public function imprimirAction() {
        $nfeHelper = Mage::helper('nfe/nfeHelper');
        $nfeId = $this->getRequest()->getParam('nfe_id');
        $nfeIds = $this->getRequest()->getParam('nfe_ids');
        if($nfeId) {
            $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
            $xmlNfe = $nfeHelper->getXmlNfe($nfe);
            $sXml = $nfeHelper->xmlString($xmlNfe);
            $nfeHelper->gerarDanfe($sXml, $nfe, 'I');
        } else if($nfeIds) {
            $nfeArray = explode(',', $nfeIds);
            $nfeHelper->gerarDanfes($nfeArray);
        }
    }
    
    public function massImprimirAction() {
        $nfeIds = $this->getRequest()->getParam('nfe_id');
        $countNfeId = 0;
        $countNonNfeId = 0;
        if (!is_array($nfeIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('nfe')->__('Por favor selecione o(s) item(s).'));
        } else {
            try {
                $nfeArray = array();
                $nfeModel = Mage::getModel('nfe/nfe');
                foreach ($nfeIds as $nfeId) {
                    $nfeModel->load($nfeId);
                    if($nfeModel->getStatus() == '6' || $nfeModel->getStatus() == '7') {
                        $nfeArray[] = $nfeId;
                        $countNfeId++;
                    } else {
                        $countNonNfeId++;
                    }
                }
                if ($countNonNfeId) {
                    if ($countNfeId) {
                        //$this->_getSession()->addError($this->__('%s NF-e(s) n&atilde;o impressa(s).', $countNonNfeId));
                    } else {
                        $this->_getSession()->addError($this->__('NF-e(s) n&atilde;o impressa(s).'));
                    }
                }
                if ($countNfeId) {
                    //$this->_getSession()->addSuccess($this->__('%s NF-e(s) impressa(s) com sucesso.', $countNfeId));
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        if($nfeArray) {
            $nfeIdsVirgulas = implode(',', $nfeArray);
            $this->_redirect('*/nfe/imprimir', array('nfe_ids'=>$nfeIdsVirgulas));
        } else {
            $this->_redirect('*/*/');
        }
    }
    
    public function ReportAction() {
        $model = Mage::getModel('nfe/nfe');
        $data = Mage::getSingleton('adminhtml/session')->getNfeData(true);
        if (!empty($data)) {
            $model->setData($data);
        }  
        Mage::register('nfe_data', $model);
        $this->_initAction()
            ->_setActiveMenu('report')
            ->_title($this->__('sales'))->_title($this->__('NF-e'))->_title($this->__(utf8_encode('Relatório de Notas Fiscais Eletrônicas (NF-e)')))
            ->_addBreadcrumb($this->__(utf8_encode('Novo Relatório de Notas Fiscais Eletrônicas (NF-e)')))
            ->_addContent($this->getLayout()->createBlock('nfe/adminhtml_nfe_report')->setData('action', $this->getUrl('*/*/gerarRelatorio')))
            ->renderLayout();
    }
    
    public function gerarRelatorioAction() {
        $postData = $this->getRequest()->getPost();
        if ($postData) {
            try {
                Mage::getSingleton('adminhtml/session')->setNfePost($postData);
                echo '<script type="text/javascript">window.location.replace("'.Mage::helper('adminhtml')->getUrl('*/nfe/report/').'");</script>';
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__(utf8_encode('O relatório de NF-e foi gerado com sucesso.')));
            }  
            catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($this->__(utf8_encode('Um erro ocorreu enquanto este relatório de NF-e era gerado.')));
            }
            if($postData['dh_recbto_desde']) {
                $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dhRecbtoDesde = Mage::app()->getLocale()->date($postData['dh_recbto_desde'], $format);
                $postData['dh_recbto_desde'] = Mage::getModel('core/date')->gmtDate(null, $dhRecbtoDesde->getTimestamp());
            }
            if($postData['dh_recbto_ate']) {
                $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dhRecbtoAte = Mage::app()->getLocale()->date($postData['dh_recbto_ate'], $format);
                $postData['dh_recbto_ate'] = Mage::getModel('core/date')->gmtDate(null, $dhRecbtoAte->getTimestamp());
            }
            Mage::getSingleton('adminhtml/session')->setNfeData($postData);
        }
    }
    
    public function abrirRelatorioAction() {
        // Foi necessário pelo fato de alguns navegadores modernos possuirem bloqueios contra redirecionamentos com javascript feitos de forma automatizada, ou seja, sem ação do usuário.
        // Este tempo de espera foi setado para que não ocorra do método imprimirRelatorio serja carregado antes que o gerarRelatorio que é o responsável por receber os dados do formulário.
        echo utf8_encode('<div style="margin:60px auto;text-align:center;"><img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'adminhtml/default/default/images/iterator/report/logo.png'.'"/></div><div style="margin:30px auto;text-align:center;font-size:32px;">Aguarde enquanto seu relatório está sendo processado...</div>');
        echo '<script type="text/javascript">setTimeout(function(){window.location.replace("'.Mage::helper('adminhtml')->getUrl('*/nfe/imprimirRelatorio/').'")}, 1500);</script>';
    }
    
    public function imprimirRelatorioAction() {
        $nfeModel = Mage::getModel('nfe/nfe');
        $data = Mage::getSingleton('adminhtml/session')->getNfePost();
        
        if (!empty($data)) {
            $nfeModel->setData($data);
            $nfeCollection = Mage::getModel('nfe/nfe')->getCollection();
            if($nfeModel->getTpNf() != '') {
                $nfeCollection->addFieldToFilter('main_table.tp_nf', array('eq' => $nfeModel->getTpNf()));
            }
            if($nfeModel->getStatus() != '') {
                $nfeCollection->addFieldToFilter('main_table.status', array('eq' => $nfeModel->getStatus()));
            }
            if($nfeModel->getNatOp() != '') {
                $nfeCollection->addFieldToFilter('main_table.nat_op', array('eq' => $nfeModel->getNatOp()));
            }
            if($data['dh_recbto_desde']) {
                $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dhRecbtoDesde = Mage::app()->getLocale()->date($data['dh_recbto_desde'], $format);
                $dhRecbtoDesdeData = substr(Mage::getModel('core/date')->gmtDate(null, $dhRecbtoDesde->getTimestamp()), 0, 10);
                $nfeCollection->addFieldToFilter('main_table.dh_recbto', array('date' => true, 'from' => $dhRecbtoDesdeData.' 00:00:00'));
            }
            if($data['dh_recbto_ate']) {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $dhRecbtoAte = Mage::app()->getLocale()->date($data['dh_recbto_ate'], $format);
                $dhRecbtoAteData = substr(Mage::getModel('core/date')->gmtDate(null, $dhRecbtoAte->getTimestamp()), 0, 10);
                $nfeCollection->addFieldToFilter('main_table.dh_recbto', array('date' => true, 'to' => $dhRecbtoAteData.' 23:59:59'));
            }
            
            $nfeCollection->setOrder($data['ordenar'], $data['posicao']);
            
            $nfePdf = Mage::helper('nfe/pdf_emitidas');
            $nfePdf->render($nfeCollection);
            
            Mage::getSingleton('adminhtml/session')->unsNfePost();
        }
    }
    
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('sales/nfe');
    }
    
    public function exportCsvAction() {
        $fileName   = 'nfe.csv';
        $content    = $this->getLayout()->createBlock('nfe/adminhtml_nfe_grid')
            ->getCsv();
 
        $this->_sendUploadResponse($fileName, $content);
    }
 
    public function exportXmlAction() {
        $fileName   = 'nfe.xml';
        $content    = $this->getLayout()->createBlock('nfe/adminhtml_nfe_grid')
            ->getXml();
 
        $this->_sendUploadResponse($fileName, $content);
    }
    
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
    
    public function consultarNfeAction() {
        $nfeId = $this->getRequest()->getParam('nfe_id');
        $nfe = Mage::getModel('nfe/nfe')->load($nfeId);
        $nfeProdutosCollection = Mage::getModel('nfe/nfeproduto')->getCollection()->addFieldToFilter('nfe_id', array('eq' => $nfeId));
        $nfeCce = Mage::getModel('nfe/nfecce')->getCollection()->addFieldToFilter('nfe_id', $nfeId)->getFirstItem();
        $html .= '<ul style="border:1px solid #333; width:793px; margin:0 auto; padding:0 0 10px;">';
        $html .= '<li style="background:#ccc; text-align:center; margin-bottom:5px;"><strong>Chave de Acesso: </strong>#'.$nfe->getIdTag().'</li>';
        $html .= utf8_encode('
            <li>
                <strong style="margin:0 50px 0 15px;">Pedido</strong>
                <strong style="margin-right:68px;">Data da Emissão</strong>
                <strong style="margin-right:68px;">Data de Saída/Entrada</strong>
                <strong style="margin-right:68px;">Modelo NF</strong> 
                <strong style="margin-right:68px;">Série NF</strong> 
                <strong>Número NF</strong>
            </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:65px; margin-right:25px; margin-left:15px; text-align:left;">'.$nfe->getPedidoIncrementId().'</div>
                    <div style="float:left; width:100px; margin-right:80px; text-align:center;">'.Mage::helper('core')->formatDate($nfe->getDhEmi(), 'short').'</div>
                    <div style="float:left; width:100px; margin-right:35px; text-align:center;">'.Mage::helper('core')->formatDate($nfe->getDhSaiEnt(), 'short').'</div>
                    <div style="float:left; width:85px; margin-right:25px; text-align:right;">'.$nfe->getMod().'</div>
                    <div style="float:left; width:95px; margin-right:25px; text-align:right;">'.$nfe->getSerie().'</div>
                    <div style="float:left; width:125px; text-align:right;">'.$nfe->getNNf().'</div>
                  </li>';
        $html .= utf8_encode('
                <li style="margin-top:5px;">
                    <strong style="margin:0 75px 0 15px;">Base ICMS</strong>
                    <strong style="margin-right:80px;">Valor ICMS</strong> 
                    <strong style="margin-right:80px;">Base ICMS Subst.</strong>
                    <strong style="margin-right:81px;">Valor ICMS Subst.</strong>
                    <strong>Valor Total Produtos</strong> 
                </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:60px; margin-right:20px; margin-left:15px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVBc(), true, false).'</div>
                    <div style="float:left; width:118px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVIcms(), true, false).'</div>
                    <div style="float:left; width:162px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVBcSt(), true, false).'</div>
                    <div style="float:left; width:163px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVSt(), true, false).'</div>
                    <div style="float:left; width:177px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVProd(), true, false).'</div>
                  </li>';
        $html .= utf8_encode('
                <li style="margin-top:5px;">
                    <strong style="margin:0 70px 0 15px;">Valor Frete</strong> 
                    <strong style="margin-right:70px;">Valor Seguro</strong>
                    <strong style="margin-right:71px;">Desconto</strong> 
                    <strong style="margin-right:71px;">Outras Desp.</strong> 
                    <strong style="margin-right:71px;">Valor IPI</strong>
                    <strong>Valor Total Nota</strong>
                </li>');
        $html .= '<li style="margin:0; overflow:hidden;">
                    <div style="float:left; width:60px; margin-right:20px; margin-left:15px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVFrete(), true, false).'</div>
                    <div style="float:left; width:126px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVSeg(), true, false).'</div>
                    <div style="float:left; width:107px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVDesc(), true, false).'</div>
                    <div style="float:left; width:125px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVOutro(), true, false).'</div>
                    <div style="float:left; width:102px; margin-right:20px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVIpi(), true, false).'</div>
                    <div style="float:left; width:140px; text-align:right;">'.Mage::helper('core')->currency($nfe->getVNf(), true, false).'</div>
                  </li>';
        $html .= '</ul>';
        $html .= '<div style="padding:30px 0;">';
        $html .= '<ul>';
        $html .= '<li><strong>Itens da NF-e:</strong></li>';
        $html .= utf8_encode('
                <li style="background:#ccc; border:1px solid #333;">
                    <strong style="margin:0 180px 0 70px;">Produto</strong> 
                    <strong style="margin-right:50px;">Quantidade</strong> 
                    <strong style="margin-right:73px;">Valor Unitário</strong> 
                    <strong style="margin-right:67px;">Desconto</strong> 
                    <strong style="margin-right:67px;">Valor Total</strong> 
                    <strong style="margin-right:67px;">Valor ICMS</strong> 
                    <strong>Valor IPI</strong>
                </li>');
        foreach($nfeProdutosCollection as $nfeProduto) {
            $valorIcms = 0;
            $valorIpi = 0;
            if($nfeProduto->getTemIcms()) {
                $produtoImpostoIcms = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                    ->addFieldToFilter('produto_id', $nfeProduto->getProdutoId())->addFieldToFilter('tipo_imposto', 'icms')->getFirstItem();
                $valorIcms = $produtoImpostoIcms->getVIcms();
            }
            if($nfeProduto->getTemIpi()) {
                $produtoImpostoIpi = Mage::getModel('nfe/nfeprodutoimposto')->getCollection()
                    ->addFieldToFilter('produto_id', $nfeProduto->getProdutoId())->addFieldToFilter('tipo_imposto', 'ipi')->getFirstItem();
                $valorIpi = $produtoImpostoIpi->getVIpi();
            }
            $html .= '<li style="border:1px solid #333; margin:0; overflow:hidden;">
                        <div style="float:left; width:280px; margin-right:10px; text-align:left;">'.$nfeProduto->getXProd().'</div>
                        <div style="float:left; width:45px; margin-right:55px; text-align:right;">'.$nfeProduto->getQTrib().'</div>
                        <div style="float:left; width:100px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVUnTrib(), true, false).'</div>
                        <div style="float:left; width:120px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVDesc(), true, false).'</div>
                        <div style="float:left; width:120px; margin-right:10px; text-align:right;">'.Mage::helper('core')->currency($nfeProduto->getVProd(), true, false).'</div>
                        <div style="float:left; width:120px; text-align:right;">'.Mage::helper('core')->currency($valorIcms, true, false).'</div>
                        <div style="float:left; width:118px; text-align:right;">'.Mage::helper('core')->currency($valorIpi, true, false).'</div>
                      </li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        if($nfe->getStatus() == '6' || $nfe->getStatus() == '7') {
            $nfeHelper = Mage::helper('nfe/nfeHelper');
            $downloadsDetalhes = $nfeHelper->getDownloads($nfe, '');
            $html .= '<div style="margin:20px 0 60px 0;">';
            $html .= utf8_encode('<button style="float:right;" type="button" class="go" onclick="javascript:window.location.replace(\''.$downloadsDetalhes['pdf_url'].'\');"><span>Download da DANFE</span></button>');
            $html .= utf8_encode('<button style="float:right; margin-right:10px;" type="button" class="go" onclick="javascript:window.location.replace(\''.$downloadsDetalhes['xml_url'].'\');"><span>Download do XML da NF-e</span></button>');
            $html .= '</div>';
        } else if($nfe->getStatus() == '9') {
            $nfeHelper = Mage::helper('nfe/nfeHelper');
            $downloadsDetalhes = $nfeHelper->getDownloads($nfe, 'inutilizado');
            $html .= '<div style="margin:20px 0 60px 0;">';
            $html .= utf8_encode('<button style="float:right;" type="button" class="go" onclick="javascript:window.location.replace(\''.$downloadsDetalhes['xml_url'].'\');"><span>Download do XML da NF-e</span></button>');
            $html .= '</div>';
        }
        if($nfeCce->getCceId()) {
            $nfeHelper = Mage::helper('nfe/nfeHelper');
            $downloadsDetalhes = $nfeHelper->getDownloads($nfe, 'corrigido');
            $html .= '<div style="margin:20px 0 60px 0;">';
            $html .= utf8_encode('<button style="float:right;" type="button" class="go" onclick="javascript:window.location.replace(\''.$downloadsDetalhes['pdf_url'].'\');"><span>Download da DACCE</span></button>');
            $html .= utf8_encode('<button style="float:right; margin-right:10px;" type="button" class="go" onclick="javascript:window.location.replace(\''.$downloadsDetalhes['xml_url'].'\');"><span>Download do XML da CC-e</span></button>');
            $html .= '</div>';
        }
        
        $this->getResponse()->setBody($html);
    }
    
    public function municipiosSearchAction() {
        $query = $this->getRequest()->getParam('query', '');
        $municipios = Mage::getModel('nfe/nfemunicipio')->getCollection()->addFieldToFilter('nome', array('like'=>$query));
        if($municipios->getSize() <> 1) {
            $municipios = Mage::getModel('nfe/nfemunicipio')->getCollection()->addFieldToFilter('nome', array('like'=>$query.'%'));
        }
        
        $resultado = array();
        foreach($municipios as $municipio) {
            $resultado[] = array(
                'id' => $municipio->getMunicipioId(),
                'type' => $municipio->getMunicipioId(),
                'name' => $municipio->getNome(),
                'description' => utf8_encode('Código IBGE: '.$municipio->getIbgeUf().$municipio->getCodigo())
            );
        }
        $totalCount = sizeof($resultado);
        
        $block = $this->getLayout()->createBlock('adminhtml/template')
            ->setTemplate('iterator_nfe/autocomplete.phtml')
            ->assign('municipios', $resultado);
        
        $this->getResponse()->setBody($block->toHtml());
    }
    
    public function validarMunicipioAction() {
        $municipio = $this->getRequest()->getParam('municipio');
        $validarCampos = Mage::helper('nfe/ValidarCampos');
        $result = array();
        $nfeMunicipio = $validarCampos->getMunicipio($municipio, 'n');
        if(!$nfeMunicipio->getCodigo()) {
            $result['resultado'] = 'false';
        } else {
            $result['resultado'] = 'true';
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    private function setEmitenteInfos($nfeIdentificacaoEmitente) {
        $cnpj = preg_replace('/[^\d]/', '', Mage::getStoreConfig('nfe/emitente_opcoes/cnpj'));
        $cMunFG = Mage::getStoreConfig('nfe/emitente_opcoes/codigo_municipio');
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
        $estadoEmitente = Mage::getModel('directory/region')->load(Mage::getStoreConfig('nfe/emitente_opcoes/region_id'));
        
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
    }
    
    private function createZipFile($files = array(), $destination = '', $overwrite = false) {
        if (file_exists($destination) && $overwrite === false) { return false; }
	if (count($files)) {
            $zip = new ZipArchive();
            if ($zip->open($destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
                return false;
            }
            foreach($files as $file) {
                if(file_exists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
            return true;
	} else {
            return false;
	}
    }
    
    private function sendMailAttachedZip($arquivoZip, $mes, $ano, $destinatario) {
        $mailTemplate = Mage::getModel('core/email_template');
        $mailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
        $mailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
        $mailTemplate->setTemplateSubject(utf8_encode('Notas Fiscais Eletrônicas da empresa '.Mage::getStoreConfig('nfe/emitente_opcoes/razao')));
        $mailTemplate->setTemplateText(utf8_encode('Olá, <br/><br/>Segue em anexo arquivo compactado contendo as <b>Notas Fiscais Eletrônicas (NF-e)</b> emitidas pela empresa <b>'.Mage::getStoreConfig('nfe/emitente_opcoes/razao').'</b> no Mês '.$mes.' do ano de '.$ano.'.<br/><br/>Esta é uma mensagem automática. <br/>Por favor não responda este e-mail,<br/>Obrigado.'));
        $mailTemplate->getMail()->createAttachment(file_get_contents($arquivoZip), Zend_Mime::TYPE_OCTETSTREAM, Zend_Mime::DISPOSITION_ATTACHMENT, Zend_Mime::ENCODING_BASE64, 'NFeMes.zip');
        $mailTemplate->send($destinatario, $destinatario);
        
        $user = Mage::getSingleton('admin/session');
        $mailTemplateAdmin = Mage::getModel('core/email_template');
        $mailTemplateAdmin->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name'));
        $mailTemplateAdmin->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
        $mailTemplateAdmin->setTemplateSubject(utf8_encode('Envio de Notas Fiscais Eletrônicas da empresa '.Mage::getStoreConfig('nfe/emitente_opcoes/razao')));
        $mailTemplateAdmin->setTemplateText(utf8_encode('Olá, <br/><br/>O usuário administrativo <b>'.$user->getUser()->getUsername().'</b> pertencente ao ID <b>'.$user->getUser()->getUserId().'</b> fez o envio de Notas Fiscais Eletrônicas (NF-e) emitidas pela empresa '.Mage::getStoreConfig('nfe/emitente_opcoes/razao').' no Mês '.$mes.' do ano  de '.$ano.', para o desinatário com o seguinte endereço de e-mail: <b>'.$destinatario.'</b><br/><br/>Esta é uma mensagem automática. <br/>Por favor não responda este e-mail,<br/>Obrigado.'));
        $mailTemplateAdmin->send(Mage::getStoreConfig('trans_email/ident_general/email'), Mage::getStoreConfig('trans_email/ident_general/email'));
    }
}

?>
