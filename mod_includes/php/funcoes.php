<?php
//########## FUNCAO PROXIMO DIA UTIL ###########
function getDayOfWeek($timestamp){
  	$date = getdate($timestamp);
    $diaSemana = $date['weekday'];
    if(preg_match('/(sunday|domingo)/mi',$diaSemana))
        $diaSemana = 'Domingo';
    else if(preg_match('/(monday|segunda)/mi',$diaSemana))
        $diaSemana = 'Segunda';
    else if(preg_match('/(tuesday|terça)/mi',$diaSemana))
        $diaSemana = 'Terça';
    else if(preg_match('/(wednesday|quarta)/mi',$diaSemana))
        $diaSemana = 'Quarta';
    else if(preg_match('/(thursday|quinta)/mi',$diaSemana))
        $diaSemana = 'Quinta';
    else if(preg_match('/(friday|sexta)/mi',$diaSemana))
        $diaSemana = 'Sexta';
    else if(preg_match('/(saturday|sábado)/mi',$diaSemana))
        $diaSemana = 'Sábado';
         
    return $diaSemana;
}

function diaUtil($data){
    while(true){
        if(getDayOfWeek($data) == 'Sábado'){
 
            $data = $data + (86400 * 2);
            return diaUtil($data);
             
        }else if(getDayOfWeek($data) == 'Domingo'){
             
            $data = $data + (86400 * 1);
            return diaUtil($data);
             
        }
		else
		{
            return $data;
        }
             
    }
}
//############# FIM FUNCAO PROXIMO DIA UTIL ###############
?>