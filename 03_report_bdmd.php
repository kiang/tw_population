<?php

$pool = [];
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
        if(!isset($pool[$y])) {
            $pool[$y] = [];
        }
        if(!isset($pool[$y][$m])) {
            $pool[$y][$m] = 0;
        }
        $pool[$y][$m] += $data['死亡數'];
    }
}
$keys = array_keys($pool[2015]);
$fh = fopen(__DIR__ . '/reports/death.csv', 'w');
fputcsv($fh, array_merge(['year'], $keys));
foreach($pool AS $y => $l1) {
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