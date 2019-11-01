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
$agencia = '1234';
$agencia_digito = '1';

$n_arquivo = rand(0, 777); // sequencial do arquivo um numero novo para cada arquivo gerado
$codigo_beneficiario = '123456'; //código fornecido pelo banco

$banco = '756';
$cnab = 'cnab240';


// dados da empresa
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

$lote  = $arquivo->addLote(array('tipo_servico'=> 1)); // tipo_servico  = 1 para cobrança registrada, 2 para sem registro

// dados do cliente
for ($i = 1; $i <= 1; $i++){

	$lote->inserirDetalhe(array(

    'codigo_movimento' => 1, //1 = Entrada de título, para outras opçoes ver nota explicativa C004 manual Cnab_SIGCB na pasta docs
    'nosso_numero'      => $i, // numero sequencial de boleto
    'seu_numero'        => $i,// se nao informado usarei o nosso numero 
    
    /* campos necessarios somente para itau e siccob,  não precisa comentar se for outro layout    */
    'carteira_banco'    => '42', // codigo da carteira ex: 109,RG esse vai o nome da carteira no banco
    'cod_carteira'      => '42', // I para a maioria ddas carteiras do itau
    'codigo_carteira' 	=> '42',

    'parcela' 			=>	'01',
    'modalidade'		=>	'1',
    'tipo_formulario'	=>	'4',
    /*----------------------------------------------------------------------------------------    */

    'especie_titulo'    => "DM", // informar dm e sera convertido para codigo em qualquer laytou conferir em especie.php
    'valor'             => 100.00, // Valor do boleto como float valido em php
    'emissao_boleto'    => 2, // tipo de emissao do boleto informar 2 para emissao pelo beneficiario e 1 para emissao pelo banco
    'protestar'         => 3, // 1 = Protestar com (Prazo) dias, 3 = Devolver após (Prazo) dias
    'prazo_protesto'    => 5, // Informar o numero de dias apos o vencimento para iniciar o protesto
    'nome_pagador'      => "Rafael Clares", // O Pagador é o cliente, preste atenção nos campos abaixo
    'tipo_inscricao'    => 1, //campo fixo, escreva '1' se for pessoa fisica, 2 se for pessoa juridica
    'numero_inscricao'  => '030.671.490-68',//cpf ou ncpj do pagador
    'endereco_pagador'  => 'Rua dos developers sl 103',
    'bairro_pagador'    => 'Boqueirão',
    'cep_pagador'       => '12345-123', // com hífem
    'cidade_pagador'    => 'Praia Grande',
    'uf_pagador'        => 'SP',
    'data_vencimento'   => $vencimento, // informar a data neste formato
    'data_emissao'      => date('Y-m-d'), // informar a data neste formato
    'vlr_juros'         => 0.15, // Valor do juros de 1 dia'
    'data_desconto'     => date('Y-m-d'), // informar a data neste formato
    'vlr_desconto'      => '0', // Valor do desconto
    'baixar'            => 1, // codigo para indicar o tipo de baixa '1' (Baixar/ Devolver) ou '2' (Não Baixar / Não Devolver)
    'prazo_baixa'       => 90, // prazo de dias para o cliente pagar após o vencimento
    'mensagem'          => '',
    'email_pagador'     => 'rogerio@ciatec.net', // data da multa
    'data_multa'        => '2019-10-31', // informar a data neste formato, // data da multa
    'vlr_multa'         => 30.00, // valor da multa
    
    // campos necessários somente para o sicoob
    'taxa_multa'         => 30.00, // taxa de multa em percentual
    'taxa_juros'         => 30.00, // taxa de juros em percentual
)); 	
}  

$remessa = $arquivo->getText();
$file_name = $arquivo->getFileName();

salva_arquivo($remessa, $file_name);
pre($remessa, 1);

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