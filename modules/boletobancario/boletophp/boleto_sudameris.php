<?php
// +----------------------------------------------------------------------+
// | BoletoPhp - Vers�o Beta                                              |
// +----------------------------------------------------------------------+
// | Este arquivo est� dispon�vel sob a Licen�a GPL dispon�vel pela Web   |
// | em http://pt.wikipedia.org/wiki/GNU_General_Public_License           |
// | Voc� deve ter recebido uma c�pia da GNU Public License junto com     |
// | esse pacote; se n�o, escreva para:                                   |
// |                                                                      |
// | Free Software Foundation, Inc.                                       |
// | 59 Temple Place - Suite 330                                          |
// | Boston, MA 02111-1307, USA.                                          |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Originado do Projeto BBBoletoFree que tiveram colabora��es de Daniel |
// | William Schultz e Leandro Maniezo que por sua vez foi derivado do	  |
// | PHPBoleto de Jo�o Prado Maia e Pablo Martins F. Costa				  |
// | 																	  |
// | Se vc quer colaborar, nos ajude a desenvolver p/ os demais bancos :-)|
// | Acesse o site do Projeto BoletoPhp: www.boletophp.com.br             |
// +----------------------------------------------------------------------+

// +----------------------------------------------------------------------+
// | Equipe Coordena��o Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
// | Desenvolvimento Boleto Sudameris: Fl�vio Yutaka Nakamura             |
// +----------------------------------------------------------------------+


// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Nonononono";
$dadosboleto["demonstrativo2"] = "Mensalidade referente a nonon nonooon nononon<br>Taxa banc�ria - R$ ".$taxa_boleto;
$dadosboleto["demonstrativo3"] = "BoletoPhp - http://www.boletophp.com.br";

// INSTRU��ES PARA O CAIXA
$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% ap�s o vencimento";
$dadosboleto["instrucoes2"] = "- Receber at� 10 dias ap�s o vencimento";
$dadosboleto["instrucoes3"] = "- Em caso de d�vidas entre em contato conosco: xxxx@xxxx.com.br";
$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto BoletoPhp - www.boletophp.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "";
$dadosboleto["valor_unitario"] = "";
$dadosboleto["aceite"] = "";		
$dadosboleto["uso_banco"] = ""; 	
$dadosboleto["especie"] = "R$";

// Esp�cie do Titulo
/*
DM	Duplicata Mercantil
DMI	Duplicata Mercantil p/ Indica��o
DS	Duplicata de Servi�o
DSI	Duplicata de Servi�o p/ Indica��o
DR	Duplicata Rural
LC	Letra de C�mbio
NCC Nota de Cr�dito Comercial
NCE Nota de Cr�dito a Exporta��o
NCI Nota de Cr�dito Industrial
NCR Nota de Cr�dito Rural
NP	Nota Promiss�ria
NPR	Nota Promiss�ria Rural
TM	Triplicata Mercantil
TS	Triplicata de Servi�o
NS	Nota de Seguro
RC	Recibo
FAT	Fatura
ND	Nota de D�bito
AP	Ap�lice de Seguro
ME	Mensalidade Escolar
PC	Parcela de Cons�rcio
*/
$dadosboleto["especie_doc"] = "DM";


// ---------------------- DADOS FIXOS DE CONFIGURA��O DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - SUDAMERIS
$dadosboleto["agencia"]       = "0501";		// N�mero da agencia, sem digito
$dadosboleto["conta"]         = "6703255";	// N�mero da conta, sem digito
$dadosboleto["carteira"]      = "57";		// Deve possuir conv�nio - Carteira 57 (Sem Registro) ou 20 (Com Registro)

?>
