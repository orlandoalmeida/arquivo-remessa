<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'Empresa.php';
require_once 'vendor/autoloader.php';
use \CnabPHP\Remessa;

$empresa = new Empresa;
$empresa->set_cnpjcpf('399.982.498-05');
$empresa->set_nome('Orlando E Almeida');

$doc = $empresa->get_cnpjcpf();
$empresa_nome = $empresa->get_nome();
$tipo = 1;
if(strlen($doc) > 14){
    $tipo = 2;
}

$vencimento = date('Y-m-d', strtotime('+7 days') );
$conta = '12345';
$conta_digito = '1';
$agencia = '0001';
$agencia_digito = '1';

$n_arquivo = rand(0, 777); // sequencial do arquivo um numero novo para cada arquivo gerado
$codigo_beneficiario = '123456'; //código fornecido pelo banco

$banco = '756';
$cnab = 'cnab240';


$arquivo = new Remessa($banco, $cnab,array(
    'nome_empresa' => $empresa_nome, // seu nome de empresa
    'tipo_inscricao'  => $tipo, // 1 para cpf, 2 cnpj 
    'numero_inscricao' => $doc, // seu cpf ou cnpj completo
    'agencia'       => $agencia, // agencia sem o digito verificador 
    'agencia_dv'    => $agencia_digito, // somente o digito verificador da agencia 
    'conta'         => $conta, // número da conta
    'conta_dv'     => $conta_digito, // digito da conta
    'numero_sequencial_arquivo'     => $n_arquivo, // sequencial do arquivo um numero novo para cada arquivo gerado
    'codigo_beneficiario'     => $codigo_beneficiario, // codigo fornecido pelo banco
    /* SICOB ------------------------------------------------------------- */
    'codigo_beneficiario_dv'=> $codigo_beneficiario, // codigo fornecido pelo banco
));


$lote  = $arquivo->addLote(array('tipo_servico'=> '1')); // tipo_servico  = 1 para cobrança registrada, 2 para sem registro

$lote->inserirDetalhe(array(
        //Registro 3P Dados do Boleto
        'nosso_numero'      => '1800001', // numero sequencial de boleto
        //'nosso_numero_dv'   =>    1, // pode ser informado ou calculado pelo sistema
        'parcela'           =>  '01',
        'modalidade'        =>  '1',
        'tipo_formulario'   =>  '4',
        'codigo_carteira'   =>  '1', // codigo da carteira
        'carteira'          =>  '1', // codigo da carteira
        'seu_numero'        =>  "DEV180001",// se nao informado usarei o nosso numero
        'data_vencimento'   => $vencimento, // informar a data neste formato
        'valor'             =>  '50.00', // Valor do boleto como float valido em php
        'cod_emissao_boleto'=>  '2', // tipo de emissao do boleto informar 2 para emissao pelo beneficiario e 1 para emissao pelo banco
        'especie_titulo'    =>  "DM", // informar dm e sera convertido para codigo em qualquer laytou conferir em especie.php
        'data_emissao'      => date('Y-m-d'), // informar a data neste formato
        'codigo_juros'      =>  '2', // Taxa por mês,
        'data_juros'        =>  date('Y-m-d', strtotime('+12 days') ), // data dos juros, mesma do vencimento
        'vlr_juros'         =>  '0000000000001.00', // Valor do juros/mora informa 1% e o sistema recalcula a 0,03% por 
        'protestar'         =>  '1', // 1 = Protestar com (Prazo) dias, 3 = Devolver após (Prazo) dias
        'prazo_protesto'    =>  '90', // Informar o numero de dias apos o vencimento para iniciar o protesto
        'identificacao_contrato'    =>  "Contrato 32156",

        // Registro 3Q [PAGADOR]
        'tipo_inscricao'    => '1', //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
        'numero_inscricao'  => '638.035.884-64',//cpf ou ncpj do pagador
        'nome_pagador'      => "Rafael Clares", // O Pagador é o cliente, preste atenção nos campos abaixo
        'endereco_pagador'  => 'Rua Esquerda, 42',
        'bairro_pagador'    => 'Bairro Queluz',
        'cep_pagador'       => '11045-400', // com hífem
        'cidade_pagador'    => 'Praia Grande',
        'uf_pagador'        => 'SP',

        'codigo_multa'      =>  '2', // Taxa por mês
        'data_multa'        =>  date('Y-m-d', strtotime('+12 days') ), // data dos juros, mesma do vencimento
        'vlr_multa'         =>  '0000000000002.00', // Valor do juros de 2% ao mês

        // Registro 3S3 Mensagens a serem impressas
        'mensagem_sc_1'     => "Após venc. Mora 0,03%/dia e Multa 2,00%",
        'mensagem_sc_2'     => "Não conceder desconto",
        'mensagem_sc_3'     => "Sujeito a protesto após o vencimento",
        'mensagem_sc_4'     => "VelvetTux Soluções em Sistemas <('')",

));

$remessa = utf8_decode($arquivo->getText()); // observar a header do seu php para não gerar comflitos de codificação de 
salva_arquivo($remessa);
pre($remessa);

function pre($data = null, $exit = 0){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    $exit == 1 ? exit : '' ;
}

function salva_arquivo($remessa = '', $file_name = ''){

    if(is_dir('arquivos')){
        @chmod('arquivos', 777);
        @system('chmod 777 arquivos/');
    }else{
        @mkdir('arquivos');
        @chmod('arquivos', 777);
        @system('chmod 777 arquivos/');
    }
    $data = date('d-m-Y-H-i-s');
    if(!empty($file_name)){
        $nome_arquivo = 'arquivos/remessa-' . $data . '-' . $file_name;
    }else{
        $rand = rand(0, 42);
        $nome_arquivo = "arquivos/remessa-$rand-$data.crm";
    }
    $arquivo = fopen($nome_arquivo, 'a+');
    if ($arquivo == false) die('Não foi possível criar o arquivo.');
    fwrite($arquivo, $remessa);
    fclose($arquivo);
}
