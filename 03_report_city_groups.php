<?php

foreach(glob(__DIR__ . '/reports/city/*/*.csv') AS $csvFile) {
    $targetFile = str_replace('/city/', '/city_group/', $csvFile);
    $p = pathinfo($targetFile);
    if(!file_exists($p['dirname'])) {
        mkdir($p['dirname'], 0777, true);
    }
    $tFh = fopen($p['dirname'] . '/years.csv', 'w');
    $oFh = fopen($targetFile, 'w');
    fputcsv($tFh, array('year', 'male', 'female', 'total'));
    fputcsv($oFh, array('year', 'age_range', 'male', 'female', 'total'));
    $fh = fopen($csvFile, 'r');
    $header = fgetcsv($fh, 8000);
    $cityResult = array();
    while($line = fgetcsv($fh, 8000)) {
        $data = array_combine($header, $line);
        foreach($data AS $key => $val) {
            if($key === 'year') {
                $currentYear = $val;
                $cityResult[$currentYear] = array();
            } else {
                $gender = substr($key, -1);
                $age = substr($key, 0, -1);
                $idx = floor($age / 5);
                if($age < 100) {
                    $ageRange = implode('-', array($idx * 5, ($idx + 1) * 5 - 1));
                } else {
                    $ageRange = '100+';
                }
                if(!isset($cityResult[$currentYear][$ageRange])) {
                    $cityResult[$currentYear][$ageRange] = array(
                        'm' => 0,
                        'f' => 0,
                        'total' => 0,
                    );
                }
                $cityResult[$currentYear][$ageRange][$gender] += $val;
                $cityResult[$currentYear][$ageRange]['total'] += $val;
            }
        }
    }
    foreach($cityResult AS $year => $col1) {
        $male = $female = $total = 0;
        foreach($col1 AS $ageRange => $col2) {
            $male += $col2['m'];
            $female += $col2['f'];
            $total += $col2['total'];
            fputcsv($oFh, array_merge(array($year, $ageRange), $col2));
        }
        fputcsv($tFh, array($year, $male, $female, $total));
    }
}