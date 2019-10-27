<?php 
/**
 * Orlando Almeida 
 */
class Empresa
{
	private $nome;
	private $cnpjcpf;
	function __construct()
	{
	}
	
	public function set_nome($nome = ''){
		$this->nome = $nome;
	}

	public function set_cnpjcpf($cnpjcpf = ''){
		$this->cnpjcpf = $cnpjcpf;
	}

	public function get_nome(){
		return $this->nome;
	}

	public function get_cnpjcpf(){
		return $this->cnpjcpf;
	}
}