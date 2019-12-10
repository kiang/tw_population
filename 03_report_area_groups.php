<?php

foreach(glob(__DIR__ . '/reports/area/*/*.csv') AS $csvFile) {
    $targetFile = str_replace('/area/', '/area_group/', $csvFile);
    $p = pathinfo($targetFile);
    if(!file_exists($p['dirname'])) {
        mkdir($p['dirname'], 0777, true);
    }
    $oFh = fopen($targetFile, 'w');
    fputcsv($oFh, array('year', 'age_range', 'male', 'female', 'total'));
    $fh = fopen($csvFile, 'r');
    $header = fgetcsv($fh, 8000);
    $areaResult = array();
    while($line = fgetcsv($fh, 8000)) {
        $data = array_combine($header, $line);
        foreach($data AS $key => $val) {
            if($key === 'year') {
                $currentYear = $val;
                $areaResult[$currentYear] = array();
            } else {
                $gender = substr($key, -1);
                $age = substr($key, 0, -1);
                $idx = floor($age / 5);
                if($age < 100) {
                    $ageRange = implode('-', array($idx * 5, ($idx + 1) * 5 - 1));
                } else {
                    $ageRange = '100+';
                }
                if(!isset($areaResult[$currentYear][$ageRange])) {
                    $areaResult[$currentYear][$ageRange] = array(
                        'm' => 0,
                        'f' => 0,
                        'total' => 0,
                    );
                }
                $areaResult[$currentYear][$ageRange][$gender] += $val;
                $areaResult[$currentYear][$ageRange]['total'] += $val;
            }
        }
    }
    foreach($areaResult AS $year => $col1) {
        foreach($col1 AS $ageRange => $col2) {
            fputcsv($oFh, array_merge(array($year, $ageRange), $col2));
        }
    }
}