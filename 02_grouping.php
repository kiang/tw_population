<?php

foreach (glob(__DIR__ . '/村里戶數人口數單一年齡人口數/*/*/*.csv') AS $csvFile) {
    $pathParts = explode('/', $csvFile);
    $cityCode = array_pop($pathParts);
    $cityCode = substr($cityCode, 0, -4);
    $month = array_pop($pathParts);
    $year = array_pop($pathParts);

    $targetPath = __DIR__ . "/city/{$year}";
    if (!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
    }
    $csvFh = fopen($csvFile, 'r');
    $areaStack = array();
    $cityStack = array(
        0 => "{$year}-{$month}",
        1 => $cityCode,
        2 => 'all',
    );
    $header = fgetcsv($csvFh, 2048);
    $header1 = fgetcsv($csvFh, 2048);
    if (!file_exists("{$targetPath}/{$month}.csv")) {
        $fh = fopen("{$targetPath}/{$month}.csv", 'w');
        $header[1] = '縣市代碼';
        $header[2] = '區域';
        unset($header[206]);
        $header = array_merge($header, $header1);
        fputcsv($fh, $header);
    } else {
        $fh = fopen("{$targetPath}/{$month}.csv", "a");
    }
    while ($line = fgetcsv($csvFh, 2048)) {
        foreach ($line AS $k => $v) {
            if ($k > 2) {
                if (!isset($areaStack[$line[1]])) {
                    if ($cityStack[2] === 'all') {
                        $cityStack[2] = mb_substr($line[1], 0, 3, 'utf-8');
                    }
                    $areaStack[$line[1]] = array(
                        0 => "{$year}-{$month}",
                        1 => $cityCode,
                        2 => $line[1],
                    );
                }
                if (!isset($areaStack[$line[1]][$k])) {
                    $areaStack[$line[1]][$k] = 0;
                }
                if (!isset($cityStack[$k])) {
                    $cityStack[$k] = 0;
                }
                $areaStack[$line[1]][$k] += $v;
                $cityStack[$k] += $v;
            }
        }
    }
    foreach ($areaStack AS $area) {
        fputcsv($fh, $area);
    }
    fputcsv($fh, $cityStack);
    fclose($fh);
}