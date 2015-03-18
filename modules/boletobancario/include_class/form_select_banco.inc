<?php

/**
 * @author Dlani
 * @copyright 2008
 */

$bancos[0]  = array( '' , 'Escolha seu Banco' );
$bancos[1]  = array( 'bb' , 'Banco do Brasil' );
$bancos[2]  = array( 'bradesco' , 'Bradesco' );
$bancos[3]  = array( 'cef' , 'Caixa Econômica Federal' );
$bancos[4]  = array( 'hsbc' , 'HSBC' );
$bancos[5]  = array( 'itau' , 'Itaú' );
$bancos[6]  = array( 'santander_banespa' , 'Santander' );

$this->_html .= '

<form action="" method="post">
<input type="hidden" name="change_bank" value="1">
<fieldset>
			<legend><img src="../img/admin/___info-ca.gif" />Banco Cobrador</legend>
			
			<label>Banco</label>			
				<div class="margin-form">
			
			<select size="1" onchange="this.form.submit();" name="banco" >';
				
					foreach ( $bancos as $a){
					
					$this->_html .=  '<option value="'.$a[0].'"';
					
					if ( $boleto_banco == $a[0]){ $this->_html .= 'selected'; };
					
					$this->_html .=  ' >'.htmlspecialchars(utf8_encode($a[1])).'</option>';
					
				};
					
$this->_html .=  '</select></div>
</fieldset>
</form>';

?>
