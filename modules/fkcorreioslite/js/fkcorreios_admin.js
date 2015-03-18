
jQuery(function() {
    $(".fkcorreios_text_cep").mask('99999-999');
});

function fkcorreiosExcluir(msg) {
	
    if (confirm(msg)) {
    	return true;
    }
    
    return false;
};

function fkcorreiosToggle(idDiv) {
	$("#" + idDiv).toggle("slow","linear");
};

function soNumeros(str){  
    var i;
    var tmp="";

    for (i=0; i < str.length; i++){  

        if (str.substr(i,1) >= "0" && str.substr(i,1) <= "9") {
            tmp = tmp + str.substr(i,1);
        }
    }

    return tmp;      
}
