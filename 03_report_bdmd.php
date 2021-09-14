<?php

$death = $birth = [];
foreach(glob(__DIR__ . '/bdmd/*/*/*.csv') AS $csvFile) {
    $fh = fopen($csvFile, 'r');
    $head = fgetcsv($fh, 2048);
    if(false === strpos($head[0], '統計年月')) {
        $head = fgetcsv($fh, 2048);
    }
    $head[0] = '統計年月';
    while($line = fgetcsv($fh, 2048)) {
        $data = array_combine($head, $line);
        $y = substr($data['統計年月'], 0, 3) + 1911;
        $m = substr($data['統計年月'], 3);
        if(!isset($death[$y])) {
            $death[$y] = [];
            $birth[$y] = [];
        }
        if(!isset($death[$y][$m])) {
            $death[$y][$m] = 0;
            $birth[$y][$m] = 0;
        }
        $death[$y][$m] += $data['死亡數'];
        $birth[$y][$m] += $data['出生數'];
    }
}
$keys = array_keys($death[2015]);
$fh = fopen(__DIR__ . '/reports/death.csv', 'w');
fputcsv($fh, array_merge(['year'], $keys));
foreach($death AS $y => $l1) {
    $line = [$y];
    foreach($keys AS $key) {
        if(isset($l1[$key])) {
            $line[] = $l1[$key];
        } else {
            $line[] = '';
        }
    }
    fputcsv($fh, $line);
}
$fh = fopen(__DIR__ . '/reports/birth.csv', 'w');
fputcsv($fh, array_merge(['year'], $keys));
foreach($birth AS $y => $l1) {
    $line = [$y];
    foreach($keys AS $key) {
        if(isset($l1[$key])) {
            $line[] = $l1[$key];
        } else {
            $line[] = '';
        }
    }
    fputcsv($fh, $line);
}