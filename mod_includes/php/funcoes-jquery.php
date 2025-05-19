<?php

// PHP Functions

/**
 * Gera uma string de bind para prepared statements.
 * Exemplo: "campo1=:campo1, campo2=:campo2"
 *
 * @param array $fields
 * @return string
 */
function bindFields(array $fields): string
{
	$binds = [];
	foreach ($fields as $field => $data) {
		$binds[] = "$field=:$field";
	}
	return ' ' . implode(',', $binds) . ' ';
}

?>
<script>
	/* JS Functions - Clean Code & Best Practices */

	// Utilitário para adicionar/remover classes e valores
	function setFieldError(selector, hasError, errorMsg = "") {
		const borderColor = hasError ? "#F90F00" : "#AAA";
		jQuery(selector).css({
			"border": `1px solid ${borderColor}`
		});
		if (errorMsg !== "") {
			jQuery(`${selector}_erro`).html(errorMsg);
		}
	}

	// Função para marcar/desmarcar todos os checkboxes
	function toggleCheckboxes() {
		const checked = $('.todos').prop("checked");
		$('.marcar').each(function () {
			if (!$(this).prop("disabled")) {
				$(this).prop("checked", checked);
			}
		});
	}

	// Função para carregar cliente em campos específicos
	function setCliente(inputPrefix, valor, id) {
		jQuery(`#${inputPrefix}_cliente`).val(valor);
		jQuery(`#${inputPrefix}_cliente_id`).val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();
	}

	// Função para verificar extensão de arquivos
	function verificaExtensao(campo) {
		const extensoesValidas = [
			"png", "jpg", "gif", "pdf", "doc", "docx", "xls", "xlsx"
		];
		const ext = campo.value.split('.').pop().toLowerCase();
		if (!extensoesValidas.includes(ext)) {
			erro_ext++;
			jQuery(campo).css({
				"border": "1px solid #F00"
			});
			abreMask(
				'<img src=../imagens/x.png> A extensão dos arquivos devem ser (pdf, doc, docx, xls, xlsx, jpg, png, gif)<br><br>' +
				'<input value="Ok" type="button" class="close_janela" >'
			);
		} else {
			erro_ext--;
		}
	}

	// Função para abrir máscara/modal
	function abreMask(msg, altura = 90, seletor = "#janela") {
		// Remove máscara existente antes de adicionar uma nova
		jQuery('#mask').remove();
		jQuery('body').append('<div id="mask"></div>');
		jQuery('#mask').fadeIn(300);
		jQuery(seletor).html(msg).fadeIn(300).css({
			"display": "",
			"height": `${altura}px`
		});
		const popMargTop = (jQuery(seletor).outerHeight() + 24) / 2;
		const popMargLeft = (jQuery(seletor).outerWidth() + 24) / 2;
		jQuery(seletor).css({
			'margin-top': -popMargTop,
			'margin-left': -popMargLeft
		});
		// Fecha o modal ao clicar na máscara ou em elementos com a classe 'close_janela'
		jQuery('#mask, .close_janela').off('click').on('click', function () {
			jQuery('#mask').fadeOut(300, function () {
				jQuery(this).remove();
			});
			jQuery(seletor).fadeOut(300);
		});
	}

	// Função para validação de CNPJ
	function validaCNPJ(cnpj) {
		cnpj = cnpj.replace(/[^\d]+/g, '');
		if (!cnpj || cnpj.length !== 14) return false;
		if (/^(\d)\1+$/.test(cnpj)) return false;

		let tamanho = cnpj.length - 2;
		let numeros = cnpj.substring(0, tamanho);
		let digitos = cnpj.substring(tamanho);
		let soma = 0;
		let pos = tamanho - 7;
		for (let i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2) pos = 9;
		}
		let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(0)) return false;

		tamanho += 1;
		numeros = cnpj.substring(0, tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (let i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2) pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		return resultado == digitos.charAt(1);
	}

	// Função para validação de CPF
	function validaCPF(cpf) {
		cpf = cpf.replace(/[^\d]+/g, '');
		if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
		let soma = 0,
			resto;
		for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
		resto = (soma * 10) % 11;
		if (resto === 10 || resto === 11) resto = 0;
		if (resto !== parseInt(cpf.substring(9, 10))) return false;
		soma = 0;
		for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
		resto = (soma * 10) % 11;
		if (resto === 10 || resto === 11) resto = 0;
		return resto === parseInt(cpf.substring(10, 11));
	}

	// Função para formatar números
	function number_format(number, decimals = 0, dec_point = '.', thousands_sep = ',') {
		number = Number(number) || 0;
		decimals = Math.abs(decimals);
		let n = number.toFixed(decimals);
		let parts = n.split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
		return parts.join(dec_point);
	}

	// Função para substituir todas as ocorrências de um token
	function replaceAll(string, token, newtoken) {
		return string.split(token).join(newtoken);
	}

	// Função para piscar elementos
	function blink(selector) {
		jQuery(selector).fadeOut('slow', function () {
			jQuery(this).fadeIn('slow', function () {
				blink(this);
			});
		});
	}

	// Document Ready
	jQuery(function () {
		// Exemplo de uso de funções refatoradas
		blink('.piscar');

		// Exemplo: jQuery("#cli_cnpj").blur(function () { ... });
		// Refatore os eventos de validação e submit conforme necessário,
		// utilizando as funções utilitárias acima para evitar repetição de código.
		// ...
		// Por exemplo:
		// jQuery("#cli_cnpj").blur(function () {
		//     const cnpj = jQuery(this).val();
		//     if (!validaCNPJ(cnpj)) {
		//         setFieldError("#cli_cnpj", true, "CNPJ inválido");
		//     } else {
		//         setFieldError("#cli_cnpj", false);
		//     }
		// });
		// ...
	});
</script>