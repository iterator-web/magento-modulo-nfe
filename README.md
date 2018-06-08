# Magento NF-e
Módulo que integra o Magento ao sistema da SEFAZ permitindo a emissão de NF-e diretamente na loja virtual.

## Descrição
Este módulo foi desenvolvido e utilizado em produção entre 2015 e 2018 em lojas Magento utilizando versão 1.6.x e 1.9.x.

## Desenvolvimento
Foi criado um módulo que gerencia todos os processos envolvidos na NF-e diretamente no Magento, sem a necessidade de
utilização de nenhum recurso externo ao Magento. A única exigência, é que como o projeto original contempla além da 
emissão de NF-e, também o cálculo automático dos impostos, a instalação de um segundo módulo desenvolvido para esta 
finalidade de automatização de impostos e que também está publico e pode ser encontrado aqui: https://github.com/iterator-web/magento-modulo-motorimpostos

A parte de comunicação com o Webservice do SEFAZ, algumas regras, bem como a validação e gerenciamento de certificado digital, foram desenvolvidas com base no projeto NFePHP: https://github.com/nfephp-org/nfephp

## Repositório Público
Este projeto foi desenvolvido originalmente em um reposítório privado e agora após mais de 3 anos de uso em produção, tornou-se público.

## Necessita Atualização
O projeto em repositório privado foi descontinuado, por este motivo optou-se por torna-lo público, pois como ele foi desenvolvido para a versão 3.10 da NF-e, necessita ser atualizado para a versão 4.0 e no momento não será feito por não haver disponibilidade de tempo para dedicar à este desenvolvimento específico. Portanto necessita de colaboradores para que realizem as tarefas necessárias para adequação do projeto a versão 4.0 e todos são bem vindos.
