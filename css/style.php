<style>
@import url(http://fonts.googleapis.com/css?family=Open+Sans:300,600,700);
@import url(http://fonts.googleapis.com/css?family=Josefin+Sans:300,600,700);
/* VERDE = #81C566 */
/* AZUL  = #0F72BD */
/* CINZA = #727272 */
/* AZUL CLARO = #CBDBE8 */

/* GERAL */
html				{ }
body 				{ font-family: "Open Sans"; font-size:12px;  color: #333; background:url(../imagens/bg.jpg) repeat; margin:0;}
a					{ text-decoration:none; color:#<?php echo $ger_cor_secundaria; ?>;}
a:visited			{ text-decoration:none;}
a:hover				{ text-decoration:none; color:#<?php echo $ger_cor_primaria; ?>;}
.left				{ float:left;}
.right				{ float:right;}
div.filtro			{ float:right; margin:5px 0 10px 0;}
div#erro			{ text-align:center; margin:0 auto;font-size:12px; color:#F90F00;}
div.centro			{ width:1118px; margin:0 auto; line-height:20px; font-size:12px; background:#FFF; padding:20px; box-shadow:0px 0px 10px #333;  -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;  }
div.titulo			{ font-weight:600; font-family: "Open Sans"; border-bottom:1px dotted #CCC; width:auto; margin:0 auto; font-size:22px; color:#<?php echo $ger_cor_secundaria; ?>; text-align:left; padding:0 0 10px 10px; margin:5px 0 10px 0;}
#mask 						{ display: none; background: #000;  position: fixed; left: 0; top: 0;  z-index: 999; width: 100%; height: 100%; opacity: 0.9;}
.janela						{ color:#FFF; width:90%; text-align:center; overflow:hidden; background-color:none; padding: 30px; border:none; position:fixed; text-align:center; top: 50%; left: 50%; right: 50%; z-index: 999999;}
.titulo_unico				{ font-size:18px; border:0; color:#FFF; background:#<?php echo $ger_cor_primaria; ?>; font-weight:600; text-align:center; -moz-border-radius:5px 5px 0px 0px; -webkit-border-radius:5px 5px 0px 0px; border-radius:5px 5px 0px 0px;}
.titulo_tabela		{ font-size:13px; font-weight:600; border:0; color:#666; background:#EEE;}
.titulo_first		{ font-size:13px; font-weight:600; border:0; color:#666; background:#EEE; -moz-border-radius:2px 0px 0px 0px; -webkit-border-radius:2px 0px 0px 0px; border-radius:2px 0px 0px 0px;}
.titulo_last		{ font-size:13px; font-weight:600; border:0; color:#666; background:#EEE; -moz-border-radius:0px 2px 0px 0px; -webkit-border-radius:0px 2px 0px 0px; border-radius:0px 2px 0px 0px;}
.titulo_tabela2		{ font-size:11px; font-weight:600; border:0; color:#999; background:#FAFAFA;}
.titulo_first2		{ font-size:11px; font-weight:600; border:0; color:#999; background:#FAFAFA; -moz-border-radius:2px 0px 0px 0px; -webkit-border-radius:2px 0px 0px 0px; border-radius:2px 0px 0px 0px;}
.titulo_last2		{ font-size:11px; font-weight:600; border:0; color:#999; background:#FAFAFA; -moz-border-radius:0px 2px 0px 0px; -webkit-border-radius:0px 2px 0px 0px; border-radius:0px 2px 0px 0px;}
.borda_inferior				{ border-bottom: 1px dotted #ccc;}
.linhapar			{ background:#FAFAFA; }
.linhaimpar			{ background:#FFFFFF; }
.bordatabela		{ border: 1px solid #DADADA;  -moz-border-radius:2px 2px 0px 0px; -webkit-border-radius:2px 2px 0px 0px; border-radius:2px 2px 0px 0px;}
.bordatabela2		{ border: 1px dotted #DADADA;  -moz-border-radius:2px 2px 0px 0px; -webkit-border-radius:2px 2px 0px 0px; border-radius:2px 2px 0px 0px;}
.formtitulo					{ width:100%; font-family: "Open Sans"; font-weight:600; margin:0 auto; text-align:left; font-size:18px;  color:#666; padding:10px 0px; margin:10px 0px; border-bottom:1px dotted #CFCFCF;}
#botoes						{ width:20%; text-align:left; float:left; padding:0 0 18px 0px;}
.cor				{ -moz-border-radius:50px; -webkit-border-radius:50px; border-radius:50px; width:10px;}
.quadro				{ background:#F3F3F3; width:1090px; border:1px dotted #CCC; padding:0px 10px 10px 10px; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
.quadro_home		{ background:#FCFCFC; width:1090px; border:1px solid #CCC; padding:0px 10px 10px 10px; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
.subquadro				{ background:#F3F3F3; width:100%; border:1px dotted #CCC; padding:0px 10px 10px 10px; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
.detalhe			{ font-size:11px; color:#777;}
.preto				{ color:#000; font-weight:bold;}
.azul				{ color:#09C; font-weight:bold;}
.laranja			{ color:#F60; font-weight:bold;}
.verde				{ color:#3C9; font-weight:bold;}
.vermelho			{ color:#900; font-weight:bold;}
.mouse 				{ cursor:pointer; _cursor: hand;}
.baixa_todos		{ float:right; cursor:pointer; _cursor: hand;}


/*  TOPO  */
div#topo	{ margin:0 auto;}
div#logo	{ float:left;}
div#titulo	{ float:right; padding:70px 0 0 0;}
.linha		{ position:absolute; height:4px; width:100%; top:0px; background:#<?php echo $ger_cor_primaria; ?>;}
.topo		{ width:1160px; margin:0 auto; margin-top:20px; margin-bottom:20px; font-size:28px; font-weight:600; font-family:"Josefin Sans"; }
.logo		{ margin:0 30px 0 0; height:100px;}
div#usuario { font-size: 12px; width:1160px; text-align:right; margin:0 auto; margin-top:3px;}
div#usuario .nome  { color:#<?php echo $ger_cor_secundaria; ?>; font-weight:bold; }
div#usuario .setor { color:#<?php echo $ger_cor_secundaria; ?>; font-weight:bold; }
.system_name{font-size:17px; font-weight:600;}

/* CORES */
.vermelho			{ color:#F00;}
.laranja			{ color:#F60;}

div.situacao		{width:13px; height:15px; margin: 0 auto;  -moz-border-radius:50px; -webkit-border-radius:50px; border-radius:50px;}



/* INPUTS */
textarea					{ margin-top:3px;}
select						{ font:12px "Open Sans"; border: 1px solid #AAA; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; background:#FFF; padding: 8px 18px 8px 8px; color:#333; -webkit-appearance: none; -moz-appearance: none; appearance: none; outline:none; background:url(../imagens/seta.png) no-repeat right #FFF;}
input,textarea				{ font:12px "Open Sans"; border: 1px solid #AAA; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; background:#FFF; padding: 8px 12px; color:#333;}
input,select, textarea:focus{ outline:none;}
input[type=submit] 			{ background:#<?php echo $ger_cor_primaria; ?>; border:none; padding: 8px 15px; font-family:"Open Sans";font-size:12px; color:#FFF; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
input[type=button] 			{ background:#<?php echo $ger_cor_primaria; ?>; border:none; padding: 8px 15px; font-family:"Open Sans";font-size:12px; color:#FFF; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
input[type=button]:hover,
input[type=submit]:hover 	{ background:#<?php echo $ger_cor_secundaria; ?>; border:none; padding: 8px 15px; font-family:"Open Sans"; color:#FFF; cursor:pointer; _cursor: hand; -moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px;}
input[type=text]			{ outline:none;}
input[type=file]			{ height:20px; padding-top:6px;}
input:disabled,textarea:disabled,select:disabled	{ }
input:disabled::-webkit-input-placeholder,	textarea:disabled::-webkit-input-placeholder{ color:#CACACA;}
input:disabled::-moz-input-placeholder,		textarea:disabled::-moz-input-placeholder	{ color:#CACACA;}
input:disabled::-ms-input-placeholder,		textarea:disabled::-ms-input-placeholder	{ color:#CACACA;}

/* RODAPÉ */
div#rodape				{ width:100%; height:80px; margin:0 auto; text-align:center; margin-top:55px; background:#<?php echo $ger_cor_primaria; ?> ;}
.rodape					{ width:1160px; margin:0 auto; font-family: "Open Sans"; font-size:16px; color:#FFF; padding:0px; padding-top:30px; text-align:center;}
.versao					{ position:absolute; right:14%; margin:-20px;}
.v						{ color:#<?php echo $ger_cor_secundaria; ?>; font-size:18px;}

/* Slide */
div.container				{ margin: 0 auto; width:90%;}
h2.trigger 					{padding: 0 0 0 0px;margin: 0 0 0 0;line-height: 20px;width:100%;font-weight: normal; }
h2.trigger a 				{text-decoration: none;display: block; padding-right:0px; text-align:left; 	background: none; font-size:18px; }
.trigger 					{text-decoration: none;display: block; padding-right:10px;  text-align:left;background: none; }
h2.trigger a:hover 			{ color:#016F54;}
.toggle_container 			{ margin: 0 0 5px 22px;	padding: 0;	overflow: hidden; width: 97%; clear: both; display:none;}
.toggle_container .block 	{ padding: 0 0; }
.toggle_container .block p 	{ padding: 5px 0; margin: 5px 0;}



/* INPUTS */
input#ger_nome				{width: 335px;}
input#ger_sigla				{width: 50px;}
input#ger_cep				{width:79px;}
select#ger_uf				{width:60px;}
select#ger_municipio		{width:190px;}
input#ger_bairro			{width:152px;}
input#ger_endereco			{width:282px;}
input#ger_numero			{width:70px;}
input#ger_comp				{width:107px;}
input#ger_telefone			{width:180px;}
input#ger_email				{width:180px;}
input#ger_site				{width:180px;}
select#ger_numeracao_anual	{width:300px;}

input#dep_nome				{width:300px;}

select#usu_setor			{width:325px;}
input#usu_nome				{width:300px;}
input#usu_email				{width:300px;}
input#usu_login				{width:137px;}
input#usu_senha				{width:136px;}

input#tps_nome				{width:300px;}

select#equ_cliente			{width:328px;}
input#equ_tipo				{width:300px;}
input#equ_marca				{width:300px;}
input#equ_modelo			{width:300px;}
input#equ_num_serie			{width:83px;}
input#equ_num_pat			{width:85px;}
input#equ_nosso_num			{width:75px;}
textarea#equ_observacoes	{width:300px; height:80px;}


input#cli_nome_razao		{width:500px;}
input#cli_cnpj				{width:145px;}
div#cli_cnpj_erro			{width:185px; padding-top:10px; font-size:12px; color:#F90F00; text-align:left; margin-left:10px;}
input#cli_cep				{width:79px;}
select#cli_uf				{width:60px;}
select#cli_municipio		{width:190px;}
input#cli_bairro			{width:152px;}
input#cli_endereco			{width:282px;}
input#cli_numero			{width:70px;}
input#cli_comp				{width:107px;}
input#cli_telefone			{width:180px;}
input#cli_email				{width:180px;}
div#cli_email_erro			{width:185px; padding-top:0px; font-size:12px; color:#F90F00; text-align:left; margin-left:10px;}
input#cli_senha 			{width:180px;}

input#for_nome_razao		{width:510px;}
input#for_cnpj				{width:145px;}
div#for_cnpj_erro			{width:185px; padding-top:10px; font-size:12px; color:#F90F00; text-align:left; margin-left:10px;}
input#for_nome_mae			{width:510px;}
input#for_data_nasc			{width:185px;}
input#for_rg				{width:152px;}
input#for_cpf				{width:152px;}
input#for_pis				{width:152px;}
input#for_cep				{width:79px;}
select#for_uf				{width:60px;}
select#for_municipio		{width:190px;}
input#for_bairro			{width:152px;}
input#for_endereco			{width:282px;}
input#for_numero			{width:70px;}
input#for_comp				{width:107px;}
input#for_telefone			{width:152px;}
input#for_telefone2			{width:152px;}
input#for_telefone3			{width:152px;}
input#for_email				{width:330px;}
textarea#for_observacoes	{width:530px; height:80px;}
div#for_email_erro			{width:185px; padding-top:10px; font-size:12px; color:#F90F00; float:left; margin-left:10px;}


input#uni_nome_razao		{width:500px;}
input#uni_cnpj				{width:145px;}
div#uni_cnpj_erro			{width:185px; padding-top:10px; font-size:12px; color:#F90F00; text-align:left; margin-left:10px;}
input#uni_cep				{width:79px;}
select#uni_uf				{width:60px;}
select#uni_municipio		{width:190px;}
input#uni_bairro			{width:152px;}
input#uni_endereco			{width:282px;}
input#uni_numero			{width:70px;}
input#uni_comp				{width:107px;}
input#uni_responsavel		{width:390px;}
input#uni_telefone			{width:180px;}
input#uni_celular			{width:180px;}
input#uni_email				{width:180px;}


input#orc_cliente			{width:450px;}
input#orc_cliente_block		{width:450px;}
select#orc_tipo_servico		{width:480px;}
input#orc_tipo_servico_cliente{width:450px;}
select#orc_fornecedor		{width:140px;}
input#orc_fornecedor		{width:335px;}
input#orc_valor				{width:80px;}
input#orc_obs				{width:220px;}
textarea#orc_andamento	{width:1085px; height:120px;}
textarea#orc_observacoes	{width:1085px; height:120px;}
input#orc_avul_tipo			{width:300px;}
input#orc_avul_marca		{width:300px;}
input#orc_avul_modelo		{width:300px;}
input#orc_avul_num_serie	{width:300px;}
input#orc_telefone			{width:150px;}
select#orc_tecnico			{width:400px;}
textarea#orc_observacoes		{width:890px; height:100px;}

select#sto_status			{width:154px;}
textarea#sto_observacao		{width:450px;}


input#doc_cliente			{width:450px;}
input#doc_cliente_block		{width:450px;}
select#doc_orcamento		{width:476px;}
select#doc_tipo				{width:230px;}
select#doc_periodicidade	{width:230px;}
input#doc_data_vigencia		{width:205px;}
textarea#doc_observacoes	{width:890px; height:100px;}

input#sto_observacao		{width:700px;}

input#mal_cliente_block		{width:450px;}
input#mal_cliente			{width:450px;}
input#mai_fornecedor		{width:190px;}
select#mai_tipo_documento	{width:140px;}
input#mai_num_cheque		{width:220px;}
input#mai_valor				{width:80px;}
textarea#mal_observacoes	{width:890px; height:100px;}
input#mai_observacao		{width:300px;}

input#pre_cliente_block		{width:450px;}
input#pre_cliente			{width:450px;}
input#pre_ref_ano			{width:120px;}
input#pre_data_envio		{width:212px;}
input#pre_enviado_por		{width:212px;}
textarea#pre_observacoes	{width:890px; height:100px;}


select#fil_tipo_documento		{width:120px;}
input#fil_orc 					{width:80px;}
select#fil_periodicidade		{width:120px;}
input#fil_data_inicio 		{width:70px;}
input#fil_data_fim 			{width:70px;}


input#inf_cliente			{width:450px;}
input#inf_cliente_block		{width:450px;}
select#inf_tipo				{width:230px;}
input#inf_cidade			{width:290px;}
input#inf_proprietario		{width:290px;}
input#inf_apto				{width:48px;}
input#inf_bloco				{width:47px;}
input#inf_endereco			{width:450px;}
input#inf_email			{width:450px;}
input#inf_assunto			{width:450px;}

input#fil_nome		{width:100px;}
input#fil_proprietario		{width:100px;}
input#fil_assunto		{width:100px;}
input#fil_bloco		{width:40px;}
input#fil_apto		{width:40px;}

input#rec_assunto			{width:450px;}
select#rec_status				{width:450px;}




/* CAIXA DE SUGESTÃO */
input#campo 		{ background:none; border:none; width:461px; color: #FFF; height:10px;  -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
input#campo:hover 	{ color: #<?php echo $ger_cor_secundaria; ?>; cursor:pointer; _cursor: hand;  -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s; } 
.suggestionsBox 	{ background-color: #<?php echo $ger_cor_primaria; ?>; color: #ffffff; z-index:9; position: absolute; left: 0%; float:left; margin:0; -moz-border-radius: 5px; -webkit-border-radius: 5px; border-radius: 5px; border: 1px solid #<?php echo $ger_cor_secundaria; ?>;	width:auto;}
.suggestion			{ position: relative; float:left; z-index:2;}
.suggestionList 	{ margin: 0px; padding: 10px 2px 10px 2px;}
.suggestionList input#anu_titulo { cursor: pointer;}
.suggestion2			{ position: relative; float:left; z-index:1;}
.suggestionList2 	{ margin: 0px; padding: 10px 2px 10px 2px;}
.suggestionList2 input#anu_titulo { cursor: pointer;}


@media screen and (max-width: 1160px){ 
.linha			{ width:1160px;}
.topo			{ width:1160px;}
div#rodape		{ width:1160px;}

}
</style>