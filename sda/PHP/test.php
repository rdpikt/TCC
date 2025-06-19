<?php
$data_nasc = date('Y-m-d', strtotime(12/02/2007));
$data_atual = date('Y-m-d');

$idade = date_diff(date_create($data_nasc), date_create($data_atual))->y;
if($idade < 18) {
    echo "idade menor que 18 anos";
}
