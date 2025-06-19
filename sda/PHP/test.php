<?php

function isLessThan18(string $birthdate): bool
{
    $today = new DateTime();
    $birthDateObj = DateTime::createFromFormat('Y-m-d', $birthdate);

    if (!$birthDateObj) {
        return false; // Data inválida
    }

    $age = $birthDateObj->diff($today)->y;

    return $age < 18;
}
$dataNascimento = "2006-06-20"; // Exemplo de data de nascimento
if (isLessThan18($dataNascimento)) {
    echo "A pessoa é menor de 18 anos.";
} else {
    echo "A pessoa não é menor de 18 anos.";
}