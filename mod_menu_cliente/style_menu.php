<style>
@import url(http://fonts.googleapis.com/css?family=Source+Sans+Pro:300);
/* Gerais */
.bodytext 					{ font-family:"Source Sans Pro"; font-size: 15px; background:none;}
.containermenu				{ margin:0 auto;width:100%;background:#<?php echo $ger_cor_primaria; ?>;height:76px;text-align:center;border:none; vertical-align:middle;}
.textomenu					{ margin:0 auto;padding:0;width:1160px;height:76px;text-align:center;}
.menu :hover ul :hover ul,
.menu :hover ul :hover ul :hover ul,
.menu :hover ul :hover ul :hover ul :hover ul,
.menu :hover ul :hover ul :hover ul :hover ul :hover ul { left:0px; top:-4px; background: #fff; padding:3px; white-space:nowrap; width:90px; z-index:400; height:auto;}

/* Menu - Links */
.menu 						{ padding:0; margin:0; list-style:none; height:46px; background:none; position:relative; z-index:500; display:inline;}
.menu li.top 				{ display:block; float:left; text-align:center; border: none; border-right:1px solid #CCC; padding:0;}
.menu li.toplast			{ display:block; float:left; text-align:left; border-right: none; padding:0;  -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.menu li a.top_link 		{ display:block; float:left; height:76px; line-height:46px; color:#FFF; text-decoration:none; padding:0; cursor:pointer; padding:0 39px;  -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.menu li a.top_link:hover 	{ color:#FFF; background:#<?php echo $ger_cor_secundaria; ?>;  -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.menu li:hover > a.top_link { color:#FFF; padding:0 39px;}

.menu img			{ padding: 13px 0 0 0; }

/* Menu */
.menu table 		{ width:0; height:0; position:absolute; top:0; left:0;}
.menu a:hover 		{ visibility:visible; position:relative; z-index:200;}
.menu li:hover 		{ position:relative; z-index:200;}
.menu ul, 
.menu :hover ul ul, 
.menu :hover ul :hover ul ul,
.menu :hover ul :hover ul :hover ul ul,
.menu :hover ul :hover ul :hover ul :hover ul ul { position:absolute; left:-9999px; top:-9999px; width:0; height:0; margin:0; padding:0; list-style:none;}

/* Submenu */
.menu :hover ul.sub 				{left:0; top:46px;  background:#<?php echo $ger_cor_secundaria; ?>;padding:10px 20px 20px 20px; border:none; white-space:nowrap; width:185px; height:auto; z-index:300; }
.menu :hover ul.sub li 				{display:block; height:22px; position:relative; float:left; width:160px; font-weight:normal;border: none; }
.menu :hover ul.sub li a 			{display:block; height:20px; width:185px; line-height:16px; text-indent:10px; color:#EEE; text-decoration:none; padding:7px 0 0 0; -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.menu :hover ul.sub li a:hover 		{color:#<?php echo $ger_cor_primaria; ?>; font-weight:bold; padding:7px 0 0 10px; -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.menu :hover ul.sub li a.fly:hover 	{color:#fff;}
.menu :hover ul li:hover > a.fly 	{color:#fff;}

.over		{ background:#<?php echo $ger_cor_secundaria; ?>; -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}
.out		{ background:#<?php echo $ger_cor_primaria; ?>; -webkit-transition-duration: 0.5s;-moz-transition-duration: 0.5s;transition-duration: 0.5s;}


@media screen and (max-width: 1160px){ 
.containermenu		{ width:1160px;}
}
</style>