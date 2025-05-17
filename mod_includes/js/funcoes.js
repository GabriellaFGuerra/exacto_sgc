//----------------------------------------MASCARAS-----------------------------------------------//

function mascaraData(inputData, e)
{
	var data = inputData.value;
	if(data.length == 10)
	{
		return false;
	}
	else
	{
		if(document.all) // Internet Explorer
		var tecla = event.keyCode;
		else //Outros Browsers
		var tecla = e.which;
		
		if(tecla >= 48 && tecla < 58)
		{ // numeros de 0 a 9 e "/"
			var data = inputData.value;
			if (data.length == 2 || data.length == 5){
			data += '/';
			inputData.value = data;
		}
		}
		else if(tecla == 8 || tecla == 0) // Backspace, Delete e setas direcionais(para mover o cursor, apenas para FF)
		return true;
		else
		return false;
	}
}

function mascaraCEP(campoCEP)
{
	var cep = campoCEP.value;
    if (cep.length == 5)
    {
    	cep = cep + '-';
        campoCEP.value = cep;
	    return true;     
		alert('aa');         
    }
}

function mascaraRG(campoRG)
{
	var reg = campoRG.value;
    if (reg.length == 2)
    {
    	reg = reg + '.';
        campoRG.value = reg;
	    return true;              
    }
    if (reg.length == 6)
    {
        reg = reg + '.';
        campoRG.value = reg;
        return true;
    }
	 if (reg.length == 10)
    {
        reg = reg + '-';
        campoRG.value = reg;
        return true;
    }
}

function mascaraCPF(campoCPF)
{
	var cpf = campoCPF.value;
    if (cpf.length == 3)
    {
    	cpf = cpf + '.';
        campoCPF.value = cpf;
	    return true;              
    }
    if (cpf.length == 7)
    {
        cpf = cpf + '.';
        campoCPF.value = cpf;
        return true;
    }
	 if (cpf.length == 11)
    {
        cpf = cpf + '-';
        campoCPF.value = cpf;
        return true;
    }
}
function mascaraCNPJ(campoCNPJ)
{
	var cnpj = campoCNPJ.value;
    if (cnpj.length == 2)
    {
    	cnpj = cnpj + '.';
        campoCNPJ.value = cnpj;
	    return true;              
    }
    if (cnpj.length == 6)
    {
        cnpj = cnpj + '.';
        campoCNPJ.value = cnpj;
        return true;
    }
	if (cnpj.length == 10)
    {
        cnpj = cnpj + '/';
        campoCNPJ.value = cnpj;
        return true;
    }
	 if (cnpj.length == 15)
    {
        cnpj = cnpj + '-';
        campoCNPJ.value = cnpj;
        return true;
    }
}
/// INICIO MASCARA TELEFONE ///
function mascara(o,f){
    v_obj=o
    v_fun=f
    setTimeout("execmascara()",1)
}
function execmascara(){
    v_obj.value=v_fun(v_obj.value)
}
function mtel(v){
    v=v.replace(/\D/g,"");             //Remove tudo o que não é dígito
    v=v.replace(/^(\d{2})(\d)/g,"($1) $2"); //Coloca parênteses em volta dos dois primeiros dígitos
    v=v.replace(/(\d)(\d{4})$/,"$1-$2");    //Coloca hífen entre o quarto e o quinto dígitos
    return v;
}
function id( el ){
	return document.getElementById( el );
}
function mascaraTELEFONE(campo)
{		
	mascara( campo, mtel );
}

/// FIM MASCARA TELEFONE ///

function SomenteNumero(e)
{
	var tecla=(window.event)?event.keyCode:e.which;
	if((tecla > 47 && tecla < 58))
		return true;
		else
		{
			if (tecla != 8)
				return false;
			else
			return true;
		}
}
function SomenteNumeroRG(e)
{
	var tecla=(window.event)?event.keyCode:e.which;
	if((tecla > 47 && tecla < 58 || tecla == 88 || tecla == 120))
		return true;
		else
		{
			if (tecla != 8)
				return false;
			else
			return true;
		}
}
function SomenteNumeroCEL(inputData, e)
{
	var campo = inputData.value;
	if(campo.length == 15)
	{
		return false;
	}
	else
	{
		var tecla=(window.event)?event.keyCode:e.which;
		if((tecla > 47 && tecla < 58 ) || tecla == 32)
		return true;
		else
		{
			if (tecla != 8)
				return false;
			else
			return true;
		}
	}
}
function MascaraMoeda(objTextBox, SeparadorMilesimo, SeparadorDecimal, e){

    var sep = 0;
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '0123456789';
    var aux = aux2 = '';
    var whichCode = (window.Event) ? e.which : e.keyCode;
    if (whichCode == 13) return true;
    key = String.fromCharCode(whichCode); // Valor para o código da Chave
    if (strCheck.indexOf(key) == -1) return false; // Chave inválida
    len = objTextBox.value.length;
    for(i = 0; i < len; i++)
        if ((objTextBox.value.charAt(i) != '0') && (objTextBox.value.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(objTextBox.value.charAt(i))!=-1) aux += objTextBox.value.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) objTextBox.value = '';
    if (len == 1) objTextBox.value = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) objTextBox.value = '0'+ SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += SeparadorMilesimo;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        objTextBox.value = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
        objTextBox.value += aux2.charAt(i);
        objTextBox.value += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return false;
}
//----------------------------------------FIM MASCARAS-----------------------------------------------//



//--------------------------------------FUNCOÇES GERAIS----------------------------------------------//

function limpar(campo) 
{
	campo.value = '';
}

function voltar() 
{
	window.history.back();
}
//------------------------------------FIM FUNCOÇES GERAIS--------------------------------------------//



function abrir(URL) {

  var width = 920;
  var height = 500;

  var left = 99;
  var top = 99;

  window.open(URL,'janela', 'width='+width+', height='+height+', top='+top+', left='+left+', scrollbars=yes, status=no, toolbar=no, location=no, directories=no, menubar=no, resizable=no, fullscreen=no');

}

function aumentarZoom() 
{
	if (window.parent.document.body.style.zoom != undefined && window.parent.document.body.style.zoom != 0)
	{
		window.parent.document.body.style.zoom *= 1.05
	}
	else
	{
		window.parent.document.body.style.zoom = 1.05
	}
}

function zerarZoom()
{
	window.parent.document.body.style.zoom = 1
}

function diminuirZoom()
{
	if (window.parent.document.body.style.zoom != undefined && window.parent.document.body.style.zoom != 0)
	{
		window.parent.document.body.style.zoom /= 1.05
	}
	else
	{
		window.parent.document.body.style.zoom = 1 / 1.05
	}
}
