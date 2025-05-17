<?php
//########## FUNÇÃO PRÓXIMO DIA ÚTIL ###########
function getDayOfWeek(int $timestamp): string
{
    $diasSemana = [
        'Sunday' => 'Domingo',
        'Monday' => 'Segunda',
        'Tuesday' => 'Terça',
        'Wednesday' => 'Quarta',
        'Thursday' => 'Quinta',
        'Friday' => 'Sexta',
        'Saturday' => 'Sábado'
    ];

    return $diasSemana[date('l', $timestamp)] ?? 'Indefinido';
}

function diaUtil(int $data): int
{
    while (true) {
        $diaSemana = getDayOfWeek($data);

        if ($diaSemana === 'Sábado') {
            $data += 86400 * 2; // Avança dois dias
        } elseif ($diaSemana === 'Domingo') {
            $data += 86400; // Avança um dia
        } else {
            return $data;
        }
    }
}
//############# FIM FUNÇÃO PRÓXIMO DIA ÚTIL ###############
?>