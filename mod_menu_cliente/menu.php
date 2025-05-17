<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php include("../mod_menu_cliente/style_menu.php"); ?>
<script>
jQuery(document).ready(function()
{
	jQuery(".menu li").hover(function() 
	{
		jQuery(this).addClass("over");
	},
	function() 
	{
		jQuery(this).removeClass("over");
	}
	);
});
</script>
</head>
 
<body>
<div class="containermenu bodytext">
    <div class="textomenu"> 
    	<ul class="menu">   
            <li class="top">
            	<a href="admin.php?pagina=admin<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-home.png" border="0" /><br /> Início</a>
            </li>
   		</ul> 
        <ul class="menu">   
            <li class="top" >
            	<a href="novo_orcamento.php?pagina=novo_orcamento<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-registrar.png" border="0" valign="top" /><br /> Novo Orçamento</a>
            </li>
   		</ul>  
        <ul class="menu">   
            <li class="top" >
            	<a href="consultar_orcamento.php?pagina=consultar_orcamento<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-consultar.png" border="0" valign="top" /><br /> Consultar Orçamento</a>
            </li>
   		</ul>
        <ul class="menu">   
            <li class="top" >
            	<a href="consultar_documento.php?pagina=consultar_documento<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-consultar-doc.png" border="0" valign="top" /><br /> Consultar Documentos</a>
            </li>
   		</ul>
        <ul class="menu">   
            <li class="top" >
            	<a href="consultar_infracoes.php?pagina=consultar_infracoes<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-consultar-infracoes.png" border="0" valign="top" /><br /> Consultar Infrações</a>
            </li>
   		</ul>  
        <ul class="menu">   
            <li class="toplast">
            	<a onclick="
                	abreMask(
                    'Deseja realmente sair do sistema?<br><br>'+
                    '<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'logout.php?pagina=logout\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
                    '<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
                " class="top_link" target="_parent"><img src="../imagens/icon-sair.png" border="0" valign="top" /><br /> Sair<!--[if gte IE 7]><!--></a><!--<![endif]--><!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>
   		</ul> 
    </div>    
</div>

<div id='usuario'>
	Bem-vindo <span class='nome'><?php echo $n;?></span> 
</div>
<div id='janela' class='janela' style='display:none;'> </div>
