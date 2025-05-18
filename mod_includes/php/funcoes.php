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

function diaUtil(int $timestamp): int
{
    while (true) {
        $diaSemana = getDayOfWeek($timestamp);

        if ($diaSemana === 'Sábado') {
            $timestamp += 86400 * 2; // Avança dois dias
            continue;
        }

        if ($diaSemana === 'Domingo') {
            $timestamp += 86400; // Avança um dia
            continue;
        }

        return $timestamp;
    }
}
//############# FIM FUNÇÃO PRÓXIMO DIA ÚTIL ###############