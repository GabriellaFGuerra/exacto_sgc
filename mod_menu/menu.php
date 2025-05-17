<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php include("../mod_menu/style_menu.php"); ?>
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
            <li class="top" id='admin'>
            	<a href="admin.php?pagina=admin<?php echo $autenticacao;?>" class="top_link" target="_parent"><img src="../imagens/icon-home.png" border="0" /></a>
            </li>
   		</ul> 
        <?php
			$sql = "SELECT * FROM admin_setores_permissoes
					LEFT JOIN admin_submodulos ON admin_submodulos.sub_id = admin_setores_permissoes.sep_submodulo
					INNER JOIN admin_modulos ON admin_modulos.mod_id = admin_setores_permissoes.sep_modulo 
					INNER JOIN ( admin_setores 
						INNER JOIN admin_usuarios 
						ON admin_usuarios.usu_setor = admin_setores.set_id )
					ON admin_setores.set_id = admin_setores_permissoes.sep_setor
					WHERE sep_setor = '".$_SESSION['setor']."' 
					GROUP BY mod_id  
					ORDER BY mod_ordem ASC
					";
			$query = mysql_query($sql,$conexao);
			$rows = mysql_num_rows($query);
			if($rows > 0)
			{
				while($row = mysql_fetch_array($query))
				{
					echo "
					<ul class='menu'>  
						<li class='top'>
							<a href='".$row['mod_link']."' class='top_link' target='_parent'>".$row['mod_nome']."<!--[if gte IE 7]><!--></a><!--<![endif]-->
							<ul class='sub'>
							";
							$sql_sub = "SELECT * FROM admin_setores_permissoes
										INNER JOIN ( admin_submodulos 
											INNER JOIN admin_modulos 
											ON admin_modulos.mod_id = admin_submodulos.sub_modulo )
										ON admin_submodulos.sub_id = admin_setores_permissoes.sep_submodulo
										INNER JOIN ( admin_setores 
											INNER JOIN admin_usuarios 
											ON admin_usuarios.usu_setor = admin_setores.set_id )
										ON admin_setores.set_id = admin_setores_permissoes.sep_setor
										WHERE sep_setor = '".$_SESSION['setor']."' AND mod_id = '".$row['mod_id']."'
										GROUP BY sub_id  
										ORDER BY sub_id ASC
									";
							
							$query_sub = mysql_query($sql_sub,$conexao);
							$rows_sub = mysql_num_rows($query_sub);
							if($rows_sub > 0)
							{
								while($row_sub = mysql_fetch_array($query_sub))
								{
									echo "
									<li class='top'><a href='".$row_sub['sub_link'].".php?pagina=".$row_sub['sub_link']."$autenticacao' target='_parent'>&raquo; ".$row_sub['sub_nome']."</a></li>
									";
								}
							}
							echo "
							</ul>
						</li>
					</ul>      
					";
				}
			}
             ?>     
        <ul class="menu">   
            <li class="toplast">
            	<a onclick="
                	abreMask(
                    'Deseja realmente sair do sistema?<br><br>'+
                    '<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'logout.php?pagina=logout\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
                    '<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
                " class="top_link" target="_parent">Sair<!--[if gte IE 7]><!--></a><!--<![endif]--><!--[if lte IE 6]></td></tr></table></a><![endif]-->
            </li>
   		</ul> 
    </div>    
</div>

<div id='usuario'>
	Bem-vindo <span class='nome'><?php echo $n;?></span> | <span class='setor'><?php echo $_SESSION['setor_nome'];?></span>
</div>
<div id='janela' class='janela' style='display:none;'> </div>
