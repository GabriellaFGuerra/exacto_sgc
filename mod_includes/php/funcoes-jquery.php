<?php

function bindFields($fields)
{
	end($fields);
	$lastField = key($fields);

	$bindString = ' ';
	foreach ($fields as $field => $data) {
		$bindString .= $field . '=:' . $field;
		$bindString .= ($field === $lastField ? ' ' : ',');
	}
	return $bindString;
}
?>
<script language="javascript">
	function darBaixa(mal_id) {

		document.form_malote_gerenciar.action = "malote_gerenciar.php?pagina=editar_malote_gerenciar&action=baixa_malote&mal_id=" + mal_id + "&<?php echo $autenticacao; ?>";
		document.form_malote_gerenciar.submit();
	}


	function marcardesmarcar() {
		if ($('.todos').prop("checked")) {
			$('.marcar').each(
				function () {
					if ($(this).prop("disabled")) {
					}
					else {
						$(this).prop("checked", true);
					}
				}
			);
		}
		else {
			$('.marcar').each(
				function () {
					$(this).prop("checked", false);
				}
			);
		}
	}
	erro_ext = "";
	function carregaCliente(valor, id) {

		jQuery("#orc_cliente").val('');
		jQuery("#orc_cliente").val(valor);
		jQuery("#orc_cliente_id").val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();

	}
	function carregaClienteDoc(val, id) {

		jQuery("#doc_cliente").val('');
		jQuery("#doc_cliente").val(val);
		jQuery("#doc_cliente_id").val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();


		jQuery.post("../mod_includes/php/procura_orcamento.php",
			{
				busca: id

			},
			function (valor) // Carrega o resultado acima para o campo catadm
			{
				jQuery("#doc_orcamento").html(valor);
			});

	}
	function carregaClienteMal(valor, id) {

		jQuery("#mal_cliente").val('');
		jQuery("#mal_cliente").val(valor);
		jQuery("#mal_cliente_id").val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();

	}
	function carregaClienteInf(valor, id) {

		jQuery("#inf_cliente").val('');
		jQuery("#inf_cliente").val(valor);
		jQuery("#inf_cliente_id").val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();

	}
	function carregaClientePrestacao(valor, id) {

		jQuery("#pre_cliente").val('');
		jQuery("#pre_cliente").val(valor);
		jQuery("#pre_cliente_id").val(id);
		jQuery("#autoSuggestionsList").html("");
		jQuery('#suggestions').hide();

	}
	function carregaFornecedor(val) {

		jQuery("select[name=orc_fornecedor][0]").html('<option value="">Carregando...</option>');
		jQuery('select[name^="orc_fornecedor"]').each(function () {
			jQuery(this).html('<option value="">Carregando...</option>');
		});
		jQuery.post("../mod_includes/php/procura_fornecedor.php",
			{
				busca: val
			},
			function (valor) // Carrega o resultado acima para o campo
			{
				jQuery('select[name^="orc_fornecedor"]').each(function () {
					jQuery(this).html(valor);
				});

			});
	}
	function verificaExtensao(campo) {
		var ext = campo.value.substring(campo.value.lastIndexOf('.') + 1);
		if (ext !== "png" && ext !== "jpg" && ext !== "gif" && ext !== "JPG" && ext !== "PNG" && ext !== "GIF" && ext !== "pdf" && ext !== "PDF" && ext !== "doc" && ext !== "DOC" && ext !== "docx" && ext !== "DOCX" && ext !== "xls" && ext !== "XLS" && ext !== "xlsx" && ext !== "XLSX") {
			erro_ext++;
			jQuery(campo).css({ "border": "1px solid #F00" });
			abreMask(
				'<img src=../imagens/x.png> A extensão dos arquivos devem ser (pdf, doc, docx, xls, xlsx, jpg, png, gif)<br><br>' +
				'<input value="Ok" type="button" class="close_janela" >');
		}
		else {
			erro_ext--;
		}
	}
	function enviaAprovacao() {
		orc_aprovacao = 0;
		if (jQuery("#sto_fornecedor_aprovado").val() == '') {
			orc_aprovacao++;
			jQuery("#sto_fornecedor_aprovado").css({ "border": "1px solid #F90F00" });
		}
		else {
			jQuery("#sto_fornecedor_aprovado").css({ "border": "1px solid #AAA" });
		}
		if (orc_aprovacao == 0) {
			jQuery("#form_aprovar_orcamento").submit();
		}
		else {
			jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
		}
	}
	function enviaReprovacao() {
		orc_reprovacao = 0;
		if (orc_reprovacao == 0) {
			jQuery("#form_reprovar_orcamento").submit();
		}
		else {
			jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
		}
	}


	jQuery(document).ready(function () {
		jQuery("#for_autonomo").change(function () {
			if (jQuery("#for_autonomo").is(":checked")) {
				jQuery("#for_nome_mae").show();
				jQuery("#for_data_nasc").show();
				jQuery("#for_rg").show();
				jQuery("#for_cpf").show();
				jQuery("#for_pis").show();
			}
			else {
				jQuery("#for_nome_mae").hide();
				jQuery("#for_data_nasc").hide();
				jQuery("#for_rg").hide();
				jQuery("#for_cpf").hide();
				jQuery("#for_pis").hide();
				jQuery("#for_nome_mae").val("");
				jQuery("#for_data_nasc").val("");
				jQuery("#for_rg").val("");
				jQuery("#for_cpf").val("");
				jQuery("#for_pis").val("");
			}
		});
		jQuery("#orc_cliente").click(function () {
			jQuery("#orc_cliente").val("");
			jQuery("#orc_cliente_id").val("");
		});
		jQuery("#mal_cliente").click(function () {
			jQuery("#mal_cliente").val("");
			jQuery("#mal_cliente_id").val("");
		});
		jQuery("#pre_cliente").click(function () {
			jQuery("#pre_cliente").val("");
			jQuery("#pre_cliente_id").val("");
		});

		jQuery("div.conteudo").hide();
		jQuery("div.status").show();

		jQuery('div.status .subtitle').on('click', function () {
			jQuery(this).parent().find('div.conteudo').slideToggle('slow');
		});
		/*jQuery("#bt_tramitacao").click(function()
		{
			
			jQuery(".quadro").animate({
				opacity: 0,
				"display": 'none',
				"margin-right": '+300'
			  }, 500 );
		});*/
		jQuery('input.close_janela, .ui-dialog-titlebar-close').live('click', function () {
			jQuery('#mask , .janela').fadeOut(100, function () {
				jQuery('.janela').fadeOut(100, function () {
					jQuery('#mask').remove();
					jQuery('body').css({ 'overflow': 'visible' });
				});
			});
			return false;
		});


		/*----------- VERIFICAÇÃO FORMULÁRIO --------------*/

		/// PARAMETROS GERAIS ///
		var ger_cep = 0;
		var ger_uf = 0;
		var ger_municipio = 0;
		var ger_bairro = 0;
		var ger_endereco = 0;
		jQuery("#ger_cep").blur(function () {
			if (jQuery("#ger_cep").val() == "") {
				ger_cep++;
				jQuery("#ger_cep").css({ "border": "1px solid #F90F00" });
				jQuery('#ger_cep_erro').html("Digite o CEP");
			}
			else {
				ger_cep = 0;
				jQuery("#ger_cep").css({ "border": "1px solid #AAA" });
				jQuery('#ger_cep_erro').html("&nbsp;");
			}
			/* CARREGA UF */
			jQuery("select[name=ger_uf]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "uf"
				},
				function (valor) // Carrega o resultado acima para o campo
				{

					jQuery("select[name=ger_uf]").html(valor);
					ger_cep = 0;
					jQuery("#ger_cep").css({ "border": "1px solid #999" });
					jQuery('#ger_cep_erro').html("&nbsp;");
				});

			/* CARREGA MUNICIPIO */
			jQuery("select[name=ger_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "municipio"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("select[name=ger_municipio]").html(valor);
					ger_cep = 0;
					jQuery("#ger_cep").css({ "border": "1px solid #999" });
					jQuery('#ger_cep_erro').html("&nbsp;");
				});

			/* CARREGA BAIRRO */
			jQuery("input[name=ger_bairro]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "bairro"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=ger_bairro]").val(valor);
					ger_cep = 0;
					jQuery("#ger_cep").css({ "border": "1px solid #999" });
					jQuery('#ger_cep_erro').html("&nbsp;");
				});

			/* CARREGA RUA */
			jQuery("input[name=ger_endereco]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "endereco"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=ger_endereco]").val(valor);
					ger_cep = 0;
					jQuery("#ger_cep").css({ "border": "1px solid #999" });
					jQuery('#ger_cep_erro').html("&nbsp;");
				});
		});
		jQuery("select[name=ger_uf]").change(function () {
			if (jQuery("#ger_uf").val() == "" || jQuery("#ger_uf").val() == "Carregando...") {
				ger_uf++;
			}
			jQuery("select[name=ger_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_uf.php",
				{
					uf: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					jQuery("select[name=ger_municipio]").html(valor);
				});
		});


		/* USUARIOS */
		var usu_login = 0;
		jQuery("#usu_login").blur(function () {
			if (jQuery("#usu_login").val() == "") {
				//usu_login++;
				//jQuery("#usu_login").css({"border" : "1px solid #F00"});
			}
			else {
				jQuery.post("../mod_includes/php/verifica_login_usuario.php",
					{
						login: jQuery("#usu_login").val(),
						usu_id: jQuery("#usu_id").val()
					},
					function (valor) // Carrega o resultado acima para o campo
					{
						if (valor == 'true') {
							usu_login++;
							jQuery("#usu_login").css({ "border": "1px solid #F00" });
							jQuery('#erro').html("Este login já está cadastrado. Por favor escolha outro.");
						}
						else {
							usu_login = 0;
							jQuery("#usu_login").css({ "border": "1px solid #999" });
							jQuery('#erro').html("&nbsp;");
						}
					});
			}
		});

		jQuery("#bt_admin_usuarios").click(function () {
			admin_usuarios = 0;
			if (jQuery("#usu_setor").val() == '') {
				admin_usuarios++;
				jQuery("#usu_setor").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#usu_setor").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#usu_nome").val() == '') {
				admin_usuarios++;
				jQuery("#usu_nome").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#usu_nome").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#usu_login").val() == '') {
				admin_usuarios++;
				jQuery("#usu_login").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#usu_login").css({ "border": "1px solid #AAA" });
			}
			if (admin_usuarios == 0 && usu_login == 0) {
				jQuery("#form_admin_usuarios").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});

		/* CLIENTE */
		var cli_cnpj = 0;
		jQuery("#cli_cnpj").blur(function () {
			if (!validaCNPJ(jQuery("#cli_cnpj").val())) {
				cli_cnpj++;
				jQuery("#cli_cnpj").css({ "border": "1px solid #F00" });
				jQuery('#cli_cnpj_erro').html("CNPJ inválido");
			}
			else {
				jQuery.post("../mod_includes/php/verifica_cnpj.php",
					{
						cnpj: jQuery("#cli_cnpj").val(),
						cli_id: jQuery("#cli_id").val()
					},
					function (valor) // Carrega o resultado acima para o campo
					{
						if (valor == 'true') {
							cli_cnpj++;
							jQuery("#cli_cnpj").css({ "border": "1px solid #999" });
							jQuery('#cli_cnpj_erro').html("CNPJ já cadastrado no sistema");
						}
						else {
							cli_cnpj = 0;
							jQuery("#cli_cnpj").css({ "border": "1px solid #AAA" });
							jQuery('#cli_cnpj_erro').html("&nbsp;");
						}
					}
				)
			};
		});

		var cli_cep = 0;
		var cli_uf = 0;
		var cli_municipio = 0;
		var cli_bairro = 0;
		var cli_endereco = 0;
		jQuery("#cli_cep").blur(function () {
			if (jQuery("#cli_cep").val() == "") {
				cli_cep++;
				jQuery("#cli_cep").css({ "border": "1px solid #F90F00" });
			}
			else {
				cli_cep = 0;
				jQuery("#cli_cep").css({ "border": "1px solid #AAA" });
			}
			/* CARREGA UF */
			jQuery("select[name=cli_uf]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "uf"
				},
				function (valor) // Carrega o resultado acima para o campo
				{

					jQuery("select[name=cli_uf]").html(valor);
					cli_cep = 0;
					jQuery("#cli_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA MUNICIPIO */
			jQuery("select[name=cli_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "municipio"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("select[name=cli_municipio]").html(valor);
					cli_cep = 0;
					jQuery("#cli_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA BAIRRO */
			jQuery("input[name=cli_bairro]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "bairro"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=cli_bairro]").val(valor);
					cli_cep = 0;
					jQuery("#cli_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA RUA */
			jQuery("input[name=cli_endereco]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "endereco"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=cli_endereco]").val(valor);
					cli_cep = 0;
					jQuery("#cli_cep").css({ "border": "1px solid #999" });
				});
		});
		jQuery("select[name=cli_uf]").change(function () {
			if (jQuery("#cli_uf").val() == "" || jQuery("#cli_uf").val() == "Carregando...") {
				cli_uf++;
			}
			jQuery("select[name=cli_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_uf.php",
				{
					uf: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					jQuery("select[name=cli_municipio]").html(valor);
				});
		});

		var cli_email = 0;
		jQuery("#cli_email").blur(function () {
			if (jQuery("#cli_email").val() == "") {
				//cli_email++;
				//jQuery("#cli_email").css({"border" : "1px solid #F00"});
			}
			else {
				jQuery.post("../mod_includes/php/verifica_email_cliente.php",
					{
						email: jQuery("#cli_email").val(),
						cli_id: jQuery("#cli_id").val()
					},
					function (valor) // Carrega o resultado acima para o campo
					{
						if (valor == 'true') {
							cli_email++;
							jQuery("#cli_email").css({ "border": "1px solid #F00" });
							jQuery('#cli_email_erro').html("Este email já está cadastrado.");
						}
						else {
							cli_email = 0;
							jQuery("#cli_email").css({ "border": "1px solid #999" });
							jQuery('#cli_email_erro').html("&nbsp;");
						}
					});
			}
		});

		jQuery("#bt_cadastro_clientes").click(function () {
			cadastro_clientes = 0;
			if (jQuery("#cli_cnpj").val() == '') {
				cadastro_clientes++;
				jQuery("#cli_cnpj").css({ "border": "1px solid #F90F00" });
				jQuery('#cli_cnpj_erro').html("Digite o CNPJ");
			}
			else if (!validaCNPJ(jQuery("#cli_cnpj").val())) {
				cadastro_clientes++;
				jQuery("#cli_cnpj").css({ "border": "1px solid #F00" });
				jQuery('#cli_cnpj_erro').html("CNPJ Inválido");
			}
			else {
				jQuery.post("../mod_includes/php/verifica_cnpj.php",
					{
						cnpj: jQuery("#cli_cnpj").val(),
						cli_id: jQuery("#cli_id").val()
					},
					function (valor) // Carrega o resultado acima para o campo
					{
						if (valor == 'true') {
							cadastro_clientes++;
							jQuery("#cli_cnpj").css({ "border": "1px solid #F00" });
							jQuery('#cli_cnpj_erro').html("CNPJ já cadastrado no sistema");
						}
						else {
							jQuery("#cli_cnpj").css({ "border": "1px solid #AAA" });
							jQuery('#cli_cnpj_erro').html("&nbsp;");
						}
					});
			}

			if (jQuery("#cli_nome_razao").val() == '') {
				cadastro_clientes++;
				jQuery("#cli_nome_razao").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_nome_razao").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#cli_cep").val() == "") {
				cadastro_clientes++;
				jQuery("#cli_cep").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_cep").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#cli_uf").val() == "") {
				cadastro_clientes++;
				jQuery("#cli_uf").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_uf").css({ "border": "1px solid #AAA" });
			}

			if (jQuery("#cli_municipio").val() == "") {
				cadastro_clientes++;
				jQuery("#cli_municipio").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_municipio").css({ "border": "1px solid #AAA" });
			}

			if (jQuery("#cli_bairro").val() == "" || jQuery("#cli_bairro").val() == "Carregando...") {
				cadastro_clientes++;
				jQuery("#cli_bairro").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_bairro").css({ "border": "1px solid #AAA" });
			}

			if (jQuery("#cli_endereco").val() == "" || jQuery("#cli_endereco").val() == "Carregando...") {
				cadastro_clientes++;
				jQuery("#cli_endereco").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_endereco").css({ "border": "1px solid #AAA" });
			}

			if (jQuery("#cli_numero").val() == '') {
				cadastro_clientes++;
				jQuery("#cli_numero").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_numero").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#cli_email").val() == '') {
				cadastro_clientes++;
				jQuery("#cli_email").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_email").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#cli_senha").val() == '') {
				cadastro_clientes++;
				jQuery("#cli_senha").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#cli_senha").css({ "border": "1px solid #AAA" });
			}
			if (cadastro_clientes == 0 && cli_email == 0 && cli_cnpj == 0) {
				jQuery("#form_cadastro_clientes").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});


		/* FORNECEDOR */
		var for_cnpj = 0;
		jQuery("#for_cnpj").blur(function () {
			if (!validaCNPJ(jQuery("#for_cnpj").val())) {
				for_cnpj++;
				jQuery("#for_cnpj").css({ "border": "1px solid #F00" });
				jQuery('#for_cnpj_erro').html("CNPJ inválido");
			}
			else {
				jQuery.post("../mod_includes/php/verifica_cnpj_fornecedor.php",
					{
						cnpj: jQuery("#for_cnpj").val(),
						for_id: jQuery("#for_id").val()
					},
					function (valor) // Carrega o resultado acima para o campo
					{
						if (valor == 'true') {
							for_cnpj++;
							jQuery("#for_cnpj").css({ "border": "1px solid #999" });
							jQuery('#for_cnpj_erro').html("CNPJ já cadastrado no sistema");
						}
						else {
							for_cnpj = 0;
							jQuery("#for_cnpj").css({ "border": "1px solid #AAA" });
							jQuery('#for_cnpj_erro').html("&nbsp;");
						}
					}
				)
			};
		});

		var for_cep = 0;
		var for_uf = 0;
		var for_municipio = 0;
		var for_bairro = 0;
		var for_endereco = 0;
		jQuery("#for_cep").blur(function () {
			/* CARREGA UF */
			jQuery("select[name=for_uf]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "uf"
				},
				function (valor) // Carrega o resultado acima para o campo
				{

					jQuery("select[name=for_uf]").html(valor);
					for_cep = 0;
					jQuery("#for_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA MUNICIPIO */
			jQuery("select[name=for_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "municipio"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("select[name=for_municipio]").html(valor);
					for_cep = 0;
					jQuery("#for_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA BAIRRO */
			jQuery("input[name=for_bairro]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "bairro"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=for_bairro]").val(valor);
					for_cep = 0;
					jQuery("#for_cep").css({ "border": "1px solid #999" });
				});

			/* CARREGA RUA */
			jQuery("input[name=for_endereco]").val('Carregando...');
			jQuery.post("../mod_includes/php/procura_cep.php",
				{
					cep: jQuery(this).val(),
					up: "endereco"
				},
				function (valor) // Carrega o resultado acima para o campo
				{
					jQuery("input[name=for_endereco]").val(valor);
					for_cep = 0;
					jQuery("#for_cep").css({ "border": "1px solid #999" });
				});
		});
		jQuery("select[name=for_uf]").change(function () {
			if (jQuery("#for_uf").val() == "" || jQuery("#for_uf").val() == "Carregando...") {
				for_uf++;
			}
			jQuery("select[name=for_municipio]").html('<option value="">Carregando...</option>');
			jQuery.post("../mod_includes/php/procura_uf.php",
				{
					uf: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					jQuery("select[name=for_municipio]").html(valor);
				});
		});


		jQuery("#bt_cadastro_fornecedores").click(function () {
			cadastro_fornecedores = 0;
			/*if(jQuery("#for_cnpj").val() == '')
			{
				cadastro_fornecedores++;
				jQuery("#for_cnpj").css({"border" : "1px solid #F90F00"});
				jQuery('#for_cnpj_erro').html("Digite o CNPJ");	
			}
			else if(!validaCNPJ(jQuery("#for_cnpj").val()))
			{
				cadastro_fornecedores++;
				jQuery("#for_cnpj").css({"border" : "1px solid #F00"});
				jQuery('#for_cnpj_erro').html("CNPJ Inválido");
			}
			else
			{
				jQuery.post("../mod_includes/php/verifica_cnpj_fornecedor.php",
				{
					cnpj:jQuery("#for_cnpj").val(),
					for_id:jQuery("#for_id").val()
				},
				function(valor) // Carrega o resultado acima para o campo
				{
					if(valor == 'true')
					{
						cadastro_fornecedores++;
						jQuery("#for_cnpj").css({"border" : "1px solid #F00"});
						jQuery('#for_cnpj_erro').html("CNPJ já cadastrado no sistema");
					}
					else
					{
						jQuery("#for_cnpj").css({"border" : "1px solid #AAA"});
						jQuery('#for_cnpj_erro').html("&nbsp;");
					}
				});
			}*/

			if (jQuery("#for_nome_razao").val() == '') {
				cadastro_fornecedores++;
				jQuery("#for_nome_razao").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#for_nome_razao").css({ "border": "1px solid #AAA" });
			}
			/*if(jQuery("#for_cep").val() == "")
			{
				cadastro_fornecedores++;
				jQuery("#for_cep").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_cep").css({"border" : "1px solid #AAA"});
			}
			if(jQuery("#for_uf").val() == "")
			{
				cadastro_fornecedores++;
				jQuery("#for_uf").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_uf").css({"border" : "1px solid #AAA"});
			}
		
			if(jQuery("#for_municipio").val() == "")
			{
				cadastro_fornecedores++;
				jQuery("#for_municipio").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_municipio").css({"border" : "1px solid #AAA"});
			}
		
			if(jQuery("#for_bairro").val() == "" || jQuery("#for_bairro").val() == "Carregando...")
			{
				cadastro_fornecedores++;
				jQuery("#for_bairro").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_bairro").css({"border" : "1px solid #AAA"});
			}
		
			if(jQuery("#for_endereco").val() == "" || jQuery("#for_endereco").val() == "Carregando...")
			{
				cadastro_fornecedores++;
				jQuery("#for_endereco").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_endereco").css({"border" : "1px solid #AAA"});
			}
			
			if(jQuery("#for_numero").val() == '')
			{
				cadastro_fornecedores++;
				jQuery("#for_numero").css({"border" : "1px solid #F90F00"});
			}
			else
			{
				jQuery("#for_numero").css({"border" : "1px solid #AAA"});
			}*/
			if (jQuery("#for_email").val() == '') {
				cadastro_fornecedores++;
				jQuery("#for_email").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#for_email").css({ "border": "1px solid #AAA" });
			}

			if (cadastro_fornecedores == 0) {
				jQuery("#form_cadastro_fornecedores").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});

		/* ORÇAMENTOS */
		jQuery("input[name=orc_cliente]").keyup(function () {
			jQuery.post("../mod_includes/php/procura_cliente.php",
				{
					busca: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					if (jQuery("#orc_cliente").val() != "") {
						jQuery('#suggestions').show();
						jQuery("#autoSuggestionsList").html(valor);
					}
					else {

						jQuery("#autoSuggestionsList").html("");
						jQuery('#suggestions').hide();

					}
				});
		});

		jQuery("#bt_orcamento_gerenciar").click(function () {
			orcamento_gerenciar = 0;
			if (jQuery("#orc_cliente_id").val() == '') {
				orcamento_gerenciar++;
				jQuery("#orc_cliente").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#orc_cliente").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#orc_tipo_servico").val() == "") {
				orcamento_gerenciar++;
				jQuery("#orc_tipo_servico").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#orc_tipo_servico").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#calculado").is(":checked")) {
				if (jQuery("#orc_fornecedor").val() == "") {
					orcamento_gerenciar++;
					jQuery("#orc_fornecedor").css({ "border": "1px solid #F90F00" });
				}
				else {
					jQuery("#orc_fornecedor").css({ "border": "1px solid #AAA" });
				}
				if (jQuery("#orc_valor").val() == "") {
					orcamento_gerenciar++;
					jQuery("#orc_valor").css({ "border": "1px solid #F90F00" });
				}
				else {
					jQuery("#orc_valor").css({ "border": "1px solid #AAA" });
				}

			}

			if (orcamento_gerenciar == 0) {
				jQuery("#form_orcamento_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});


		/* MALOTE */
		jQuery("input[name=mal_cliente]").keyup(function () {
			jQuery.post("../mod_includes/php/procura_cliente_malote.php",
				{
					busca: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					if (jQuery("#mal_cliente").val() != "") {
						jQuery('#suggestions').show();
						jQuery("#autoSuggestionsList").html(valor);
					}
					else {

						jQuery("#autoSuggestionsList").html("");
						jQuery('#suggestions').hide();

					}
				});
		});

		jQuery("#bt_malote_gerenciar").click(function () {
			malote_gerenciar = 0;
			if (jQuery("#mal_fornecedor").val() == "") {
				malote_gerenciar++;
				jQuery("#mal_fornecedor").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#mal_fornecedor").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#mal_tipo_documento").val() == "") {
				malote_gerenciar++;
				jQuery("#mal_tipo_documento").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#mal_tipo_documento").css({ "border": "1px solid #AAA" });
			}
			if (malote_gerenciar == 0) {
				jQuery("#form_malote_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});


		jQuery("#bt_cadastro_orcamentos").click(function () {
			cadastro_orcamentos = 0;
			if (jQuery("#orc_tipo_servico").val() == '') {
				cadastro_orcamentos++;
				jQuery("#orc_tipo_servico").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#orc_tipo_servico").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#orc_observacoes").val() == "") {
				cadastro_orcamentos++;
				jQuery("#orc_observacoes").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#orc_observacoes").css({ "border": "1px solid #AAA" });
			}
			if (cadastro_orcamentos == 0) {
				jQuery("#form_cadastro_orcamentos").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});


		/* DOCUMENTOS */
		jQuery("input[name=doc_cliente]").keyup(function () {
			jQuery.post("../mod_includes/php/procura_cliente_documento.php",
				{
					busca: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					if (jQuery("#doc_cliente").val() != "") {
						jQuery('#suggestions').show();
						jQuery("#autoSuggestionsList").html(valor);
					}
					else {

						jQuery("#autoSuggestionsList").html("");
						jQuery('#suggestions').hide();

					}
				});
		});

		jQuery("#bt_documento_gerenciar").click(function () {
			documento_gerenciar = 0;
			if (jQuery("#doc_cliente_id").val() == '') {
				documento_gerenciar++;
				jQuery("#doc_cliente").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#doc_cliente").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#doc_tipo").val() == '') {
				documento_gerenciar++;
				jQuery("#doc_tipo").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#doc_tipo").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#doc_data_vigencia").val() == '') {
				documento_gerenciar++;
				jQuery("#doc_data_vigencia").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#doc_data_vigencia").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#doc_periodicidade").val() == '') {
				documento_gerenciar++;
				jQuery("#doc_periodicidade").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#doc_periodicidade").css({ "border": "1px solid #AAA" });
			}
			if (documento_gerenciar == 0) {
				jQuery("#form_documento_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});

		/* PRESTACAO CONTAS */
		jQuery("input[name=pre_cliente]").keyup(function () {
			jQuery.post("../mod_includes/php/procura_cliente_prestacao.php",
				{
					busca: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					if (jQuery("#pre_cliente").val() != "") {
						jQuery('#suggestions').show();
						jQuery("#autoSuggestionsList").html(valor);
					}
					else {

						jQuery("#autoSuggestionsList").html("");
						jQuery('#suggestions').hide();

					}
				});
		});

		jQuery("#bt_prestacao_gerenciar").click(function () {
			prestacao_gerenciar = 0;
			if (jQuery("#pre_cliente_id").val() == '') {
				prestacao_gerenciar++;
				jQuery("#pre_cliente").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#pre_cliente").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#pre_ref_mes").val() == "") {
				prestacao_gerenciar++;
				jQuery("#pre_ref_mes").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#pre_ref_mes").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#pre_ref_ano").val() == "") {
				prestacao_gerenciar++;
				jQuery("#pre_ref_ano").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#pre_ref_ano").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#pre_data_envio").val() == "") {
				prestacao_gerenciar++;
				jQuery("#pre_data_envio").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#pre_data_envio").css({ "border": "1px solid #AAA" });
			}
			if (prestacao_gerenciar == 0) {
				jQuery("#form_prestacao_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});


		/* INFRACOES */
		jQuery("input[name=inf_cliente]").keyup(function () {
			jQuery.post("../mod_includes/php/procura_cliente_infracoes.php",
				{
					busca: jQuery(this).val()

				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					if (jQuery("#inf_cliente").val() != "") {
						jQuery('#suggestions').show();
						jQuery("#autoSuggestionsList").html(valor);
					}
					else {

						jQuery("#autoSuggestionsList").html("");
						jQuery('#suggestions').hide();

					}
				});
		});



		jQuery("#bt_infracoes_gerenciar").click(function () {
			infracoes_gerenciar = 0;
			if (jQuery("#inf_cliente_id").val() == '') {
				infracoes_gerenciar++;
				jQuery("#inf_cliente").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#inf_cliente").css({ "border": "1px solid #AAA" });
			}
			if (jQuery("#inf_tipo").val() == '') {
				infracoes_gerenciar++;
				jQuery("#inf_tipo").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#inf_tipo").css({ "border": "1px solid #AAA" });
			}
			if (infracoes_gerenciar == 0) {
				jQuery("#form_infracoes_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});

		jQuery("#bt_recurso_gerenciar").click(function () {
			recurso_gerenciar = 0;
			if (jQuery("#rec_assunto").val() == '') {
				recurso_gerenciar++;
				jQuery("#rec_assunto").css({ "border": "1px solid #F90F00" });
			}
			else {
				jQuery("#rec_assunto").css({ "border": "1px solid #AAA" });
			}

			if (recurso_gerenciar == 0) {
				jQuery("#form_recurso_gerenciar").submit();
			}
			else {
				jQuery('#erro').html("Por favor verifique os campos obrigatórios em vermelho");
			}
		});
	});

	/* --------- FUNCOES GERAIS  ------------ */
	// ADICIONAR/REMOVER CAMPOS DINAMICAMENTE

	$(function () {
		var scntDiv = $('#p_scents');
		var i = $('#p_scents p').size() + 1;
		$('#addScnt').live('click', function () {
			//jQuery(this).next().next().val("aaa");

			var variaveljs = jQuery("#orc_tipo_servico").val();
			var total = 0;
			$('<p><label for="itens"><select name="orc_fornecedor[]" id="orc_fornecedor">' +
				'<option value="">Fornecedor</option>' +
				'</select> ' +
				'<input type="text" id="orc_valor" size="12" name="orc_valor[]" value="" placeholder="Valor (em R$)" onkeypress="return MascaraMoeda(this,\'.\',\',\',event);" /> ' +
				'<input type="text" id="orc_obs" name="orc_obs[]" value="" placeholder="Observação" /> ' +
				'<input name="orc_anexo[]" id="orc_anexo" type="file" onchange="verificaExtensao(this);"> &nbsp; <input type="button" id="addScnt" value="Adicionar"> &nbsp; <input type="button" id="remScnt" value="X"></label></p>').appendTo(scntDiv);
			jQuery.post("../mod_includes/php/carrega_fornecedor.php",
				{
					tipo_servico: jQuery("#orc_tipo_servico").val()
				},
				function (valor) // Carrega o resultado acima para o campo catadm
				{
					//alert(valor);
					jQuery('select[name^="orc_fornecedor"]').each(function () {
						if (jQuery(this).val() == '') {
							jQuery(this).html(valor);
						}
					});


					//jQuery(this).val();


				});

			i++;
			return false;
		});

		$('#remScnt').live('click', function () {
			var total = 0;
			if (i > 1) {
				$(this).parents('p').remove();
				i--;

			}
			return false;
		});
	});

	$(function () {
		var scntDiv = $('#p_scents_malote');
		var x = $('div.bloco_fornecedores').size() + 1;
		$(document).on('click', '#addScnt_malote', function () {
			//var variaveljs = jQuery("#orc_tipo_servico").val(); 
			var total = 0;
			$('<div class="bloco_fornecedores">' +
				'<p><label for="itens"><input name="fornecedores[' + x + '][mai_fornecedor]" id="mai_fornecedor" placeholder="Fornecedor"> ' +
				'<select name="fornecedores[' + x + '][mai_tipo_documento]" id="mai_tipo_documento">' +
				'<option value="">Tipo Documento</option>' +
				'<option value="Boleto">Boleto</option>' +
				'<option value="Guia">Guia</option>' +
				'<option value="Depósito">Depósito</option>' +
				'<option value="Reembolso">Reembolso</option>' +
				'<option value="Carteira">Carteira</option>' +
				'<option value="Cheque sem retorno">Cheque sem retorno</option>' +
				'<option value="O.P.">O.P.</option>' +
				'<option value="O.P. Agendada">O.P. Agendada</option>' +
				'<option value="Outros">Outros</option>' +
				'</select> ' +
				'<input type="text" id="mai_num_cheque" name="fornecedores[' + x + '][mai_num_cheque]" value="" placeholder="N° Cheque" /> ' +
				'<input type="text" id="mai_valor" size="12" name="fornecedores[' + x + '][mai_valor]" value="" placeholder="Valor (em R$)" onkeypress="return MascaraMoeda(this,\'.\',\',\',event);" /> ' +
				'<input type="text" id="mai_data_vencimento" name="fornecedores[' + x + '][mai_data_vencimento]" value="" placeholder="Data Vencimento" onkeypress="return mascaraData(this,event);" /> ' +
				'&nbsp; <input type="button" id="addScnt_malote" value="Adicionar"> &nbsp; <input type="button" id="remScnt_malote" value="X"></label></div>').appendTo(scntDiv);
			x++;
			return false;
		});

		$(document).on('click', '#remScnt_malote', function () {
			var total = 0;
			if (x > 1) {
				$(this).parents('div.bloco_fornecedores').remove();
				//i--;
				//x--;

			}
			return false;
		});
	});




	function link_mask(url) {
		document.location.href = url;
	}

	function abreMask(msg) {
		jQuery('body').append('<div id="mask"></div>');
		jQuery('#mask').fadeIn(300);
		jQuery('#janela').html(msg);
		jQuery("#janela").fadeIn(300);
		jQuery('#janela').css({ "display": "" });
		jQuery('#janela').css({ "height": "90px" });
		//jQuery('body').css({'overflow':'hidden'});

		var popMargTopJanela = (jQuery("#janela").height() + 24) / 2;
		var popMargLeftJanela = (jQuery("#janela").width() + 24) / 2;

		jQuery("#janela").css({
			'margin-top': -popMargTopJanela,
			'margin-left': -popMargLeftJanela
		});
	}

	function abreMaskAcao(msg) {
		jQuery('body').append('<div id="mask"></div>');
		jQuery('#mask').fadeIn(300);
		jQuery('#janela').html(msg);
		jQuery("#janela").fadeIn(300);
		jQuery('#janela').css({ "display": "" });
		jQuery('#janela').css({ "height": "170px" });
		//jQuery('body').css({'overflow':'hidden'});

		var popMargTopJanela = (jQuery("#janela").height() + 24) / 2;
		var popMargLeftJanela = (jQuery("#janela").width() + 24) / 2;

		jQuery("#janela").css({
			'margin-top': -popMargTopJanela,
			'margin-left': -popMargLeftJanela
		});
	}

	function abreMaskSenha(msg) {
		jQuery('body').append('<div id="mask"></div>');
		jQuery('#mask').fadeIn(300);
		jQuery('#janela_senha').html(msg);
		jQuery("#janela_senha").fadeIn(300);
		jQuery('#janela_senha').css({ "display": "" });
		jQuery('#janela_senha').css({ "height": "200px" });
		//jQuery('body').css({'overflow':'hidden'});

		var popMargTopJanela = (jQuery("#janela_senha").height() + 24) / 2;
		var popMargLeftJanela = (jQuery("#janela_senha").width() + 24) / 2;

		jQuery("#janela_senha").css({
			'margin-top': -popMargTopJanela,
			'margin-left': -popMargLeftJanela
		});

	}


	function PrintDiv(div) {
		$('#' + div).show().printElement();
	}

	function submitenter(myfield, e) {
		var keycode;
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
		if (keycode == 13) {
			jQuery("#form_newsletter").submit();
			return false;
		}
		else
			return true;
	}

	function sleep(milliseconds) {
		setTimeout(function () {
			var start = new Date().getTime();
			while ((new Date().getTime() - start) < milliseconds) {
				// Do nothing
			}
		}, 0);
	}

	function blink(selector) {
		jQuery(selector).fadeOut('slow', function () {
			jQuery(this).fadeIn('slow', function () {
				blink(this);
			});
		});
	}
	blink('.piscar');

	function validaCPF(cpf) {
		cpf = cpf.replace(".", "");
		cpf = cpf.replace(".", "");
		cpf = cpf.replace("-", "");

		var numeros, digitos, soma, i, resultado, digitos_iguais;
		digitos_iguais = 1;
		if (cpf.length < 11)
			return false;
		for (i = 0; i < cpf.length - 1; i++)
			if (cpf.charAt(i) != cpf.charAt(i + 1)) {
				digitos_iguais = 0;
				break;
			}
		if (!digitos_iguais) {
			numeros = cpf.substring(0, 9);
			digitos = cpf.substring(9);
			soma = 0;
			for (i = 10; i > 1; i--)
				soma += numeros.charAt(10 - i) * i;
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(0))
				return false;
			numeros = cpf.substring(0, 10);
			soma = 0;
			for (i = 11; i > 1; i--)
				soma += numeros.charAt(11 - i) * i;
			resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
			if (resultado != digitos.charAt(1))
				return false;
			return true;
		}
		else
			return false;
	}

	function validaCNPJ(cnpj) {
		//cpf = cpf.replace(".", "");
		//cpf = cpf.replace(".", "");
		//cpf = cpf.replace("-", "");

		cnpj = cnpj.replace(/[^\d]+/g, '');

		if (cnpj == '') return false;

		if (cnpj.length != 14)
			return false;

		// Elimina CNPJs invalidos conhecidos
		if (cnpj == "00000000000000" ||
			cnpj == "11111111111111" ||
			cnpj == "22222222222222" ||
			cnpj == "33333333333333" ||
			cnpj == "44444444444444" ||
			cnpj == "55555555555555" ||
			cnpj == "66666666666666" ||
			cnpj == "77777777777777" ||
			cnpj == "88888888888888" ||
			cnpj == "99999999999999")
			return false;

		// Valida DVs
		tamanho = cnpj.length - 2
		numeros = cnpj.substring(0, tamanho);
		digitos = cnpj.substring(tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
				pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(0))
			return false;

		tamanho = tamanho + 1;
		numeros = cnpj.substring(0, tamanho);
		soma = 0;
		pos = tamanho - 7;
		for (i = tamanho; i >= 1; i--) {
			soma += numeros.charAt(tamanho - i) * pos--;
			if (pos < 2)
				pos = 9;
		}
		resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
		if (resultado != digitos.charAt(1))
			return false;

		return true;
	}

	function validaRG(numero) {
		numero = numero.replace(".", "");
		numero = numero.replace(".", "");
		numero = numero.replace("-", "");
		/*
		##  Igor Carvalho de Escobar
		##    www.webtutoriais.com
		##   Java Script Developer
		*/
		var numero = numero.split("");
		tamanho = numero.length;
		vetor = new Array(tamanho);

		if (tamanho >= 1) {
			vetor[0] = parseInt(numero[0]) * 2;
		}
		if (tamanho >= 2) {
			vetor[1] = parseInt(numero[1]) * 3;
		}
		if (tamanho >= 3) {
			vetor[2] = parseInt(numero[2]) * 4;
		}
		if (tamanho >= 4) {
			vetor[3] = parseInt(numero[3]) * 5;
		}
		if (tamanho >= 5) {
			vetor[4] = parseInt(numero[4]) * 6;
		}
		if (tamanho >= 6) {
			vetor[5] = parseInt(numero[5]) * 7;
		}
		if (tamanho >= 7) {
			vetor[6] = parseInt(numero[6]) * 8;
		}
		if (tamanho >= 8) {
			vetor[7] = parseInt(numero[7]) * 9;
		}
		if (tamanho >= 9) {
			if (numero[8] == 'x') {
				vetor[8] = 10 * 100;
			}
			else {
				vetor[8] = parseInt(numero[8]) * 100;
			}
		}

		total = 0;

		if (tamanho >= 1) {
			total += vetor[0];
		}
		if (tamanho >= 2) {
			total += vetor[1];
		}
		if (tamanho >= 3) {
			total += vetor[2];
		}
		if (tamanho >= 4) {
			total += vetor[3];
		}
		if (tamanho >= 5) {
			total += vetor[4];
		}
		if (tamanho >= 6) {
			total += vetor[5];
		}
		if (tamanho >= 7) {
			total += vetor[6];
		}
		if (tamanho >= 8) {
			total += vetor[7];
		}
		if (tamanho >= 9) {
			total += vetor[8];
		}

		alert(total);
		resto = total % 11;
		if (resto != 0) {
			return false;
		}
		else {
			return true;
		}
	}

	function number_format(number, decimals, dec_point, thousands_sep) {
		// %        nota 1: Para 1000.55 retorna com precisão 1 no FF/Opera é 1,000.5, mas no IE é 1,000.6
		// *     exemplo 1: number_format(1234.56);
		// *     retorno 1: '1,235'
		// *     exemplo 2: number_format(1234.56, 2, ',', ' ');
		// *     retorno 2: '1 234,56'
		// *     exemplo 3: number_format(1234.5678, 2, '.', '');
		// *     retorno 3: '1234.57'
		// *     exemplo 4: number_format(67, 2, ',', '.');
		// *     retorno 4: '67,00'
		// *     exemplo 5: number_format(1000);
		// *     retorno 5: '1,000'
		// *     exemplo 6: number_format(67.311, 2);
		// *     retorno 6: '67.31'

		var n = number, prec = decimals;
		n = !isFinite(+n) ? 0 : +n;
		prec = !isFinite(+prec) ? 0 : Math.abs(prec);
		var sep = (typeof thousands_sep == "undefined") ? ',' : thousands_sep;
		var dec = (typeof dec_point == "undefined") ? '.' : dec_point;

		var s = (prec > 0) ? n.toFixed(prec) : Math.round(n).toFixed(prec); //fix for IE parseFloat(0.55).toFixed(0) = 0;

		var abs = Math.abs(n).toFixed(prec);
		var _, i;

		if (abs >= 1000) {
			_ = abs.split(/\D/);
			i = _[0].length % 3 || 3;

			_[0] = s.slice(0, i + (n < 0)) +
				_[0].slice(i).replace(/(\d{3})/g, sep + '$1');

			s = _.join(dec);
		} else {
			s = s.replace('.', dec);
		}

		return s;
	}
	function replaceAll(string, token, newtoken) {
		while (string.indexOf(token) != -1) {
			string = string.replace(token, newtoken);
		}
		return string;
	}
</script>