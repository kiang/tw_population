<?php

/*
1至99歲人口數之推估
t年x歲人口數，等於（t-1）年（x-1）歲人口數，乘以t年x歲人口存活機率

100歲以上人口數之推估
t年100歲以上人口數，等於（t-1）年99歲以上人口數，乘以t年100歲以上人口存活機率

出生數
t年15至49歲5歲年齡組之年中育齡婦女人數，乘以t年該年齡組生育率

0歲人口數
t年0歲人口數，等於t年出生數，乘以t年0歲人口存活機率
*/

$highFh = fopen(__DIR__ . '/reports/prediction/birth_high.csv', 'r');
$birthHigh = array();
fgetcsv($highFh, 2048);
while($line = fgetcsv($highFh, 2048)) {
    $birthHigh[$line[0]] = array(
        '15-19' => $line[2],
        '20-24' => $line[3],
        '25-29' => $line[4],
        '30-34' => $line[5],
        '35-39' => $line[6],
        '40-44' => $line[7],
        '45-49' => $line[8],
        'gender' => $line[9],
    );
}

$lowFh = fopen(__DIR__ . '/reports/prediction/birth_low.csv', 'r');
$birthLow = array();
fgetcsv($lowFh, 2048);
while($line = fgetcsv($lowFh, 2048)) {
    $birthLow[$line[0]] = array(
        '15-19' => $line[2],
        '20-24' => $line[3],
        '25-29' => $line[4],
        '30-34' => $line[5],
        '35-39' => $line[6],
        '40-44' => $line[7],
        '45-49' => $line[8],
        'gender' => $line[9],
    );
}

$mediumFh = fopen(__DIR__ . '/reports/prediction/birth_medium.csv', 'r');
$birthMedium = array();
fgetcsv($mediumFh, 2048);
while($line = fgetcsv($mediumFh, 2048)) {
    $birthMedium[$line[0]] = array(
        '15-19' => $line[2],
        '20-24' => $line[3],
        '25-29' => $line[4],
        '30-34' => $line[5],
        '35-39' => $line[6],
        '40-44' => $line[7],
        '45-49' => $line[8],
        'gender' => $line[9],
    );
}

$drFh = fopen(__DIR__ . '/reports/prediction/death_rate.csv', 'r');
$dr = array();
$header = false;
while($line = fgetcsv($drFh, 2048)) {
    if(false === $header) {
        $header = $line;
    } else {
        $data = array_combine($header, $line);
        $dr[$data['年齡']] = array(
            'm' => array(
                2020 => $data['2020M'],
                2025 => $data['2025M'],
                2035 => $data['2035M'],
                2045 => $data['2045M'],
                2055 => $data['2055M'],
                2065 => $data['2065M'],
            ),
            'f' => array(
                2020 => $data['2020F'],
                2025 => $data['2025F'],
                2035 => $data['2035F'],
                2045 => $data['2045F'],
                2055 => $data['2055F'],
                2065 => $data['2065F'],
            ),
        );
    }
}

$fh = fopen(__DIR__ . '/村里戶數人口數單一年齡人口數/2019/10/data.csv', 'r');
$header1 = fgetcsv($fh, 8000);
$header2 = fgetcsv($fh, 8000);
$colIndex = array();
foreach($header1 AS $k => $v) {
    $parts = explode('_', $v);
    if(isset($parts[3])) {
        if(!isset($colIndex[$parts[3]])) {
            $colIndex[$parts[3]] = array();
        }
        // gender / age / index
        $colIndex[$parts[3]][intval($parts[2])] = $k;
    }
}
$high = $medium = $low = array();
while($line = fgetcsv($fh, 8000)) {
    $y = 2019;
    $line[2] = str_replace('　', '', $line[2]);
    if(!isset($high[$line[2]])) {
        $high[$line[2]] = array(
            $y => array(),
        );
    }
    
    foreach($colIndex AS $gender => $cols) {
        if(!isset($high[$line[2]][$y][$gender])) {
            $high[$line[2]][$y][$gender] = array();
        }
        foreach($cols AS $age => $index) {
            if(!isset($high[$line[2]][$y][$gender][$age])) {
                $high[$line[2]][$y][$gender][$age] = 0;
            }
            $high[$line[2]][$y][$gender][$age] += $line[$index];
        }
    }
}
$medium = $low = $high;
foreach($high AS $area => $col1) {
    for($y = 2020; $y < 2066; $y++) {
        $py = $y - 1;
        if($y < 2025) {
            $drKey = 2020;
        } elseif($y < 2035) {
            $drKey = 2025;
        } elseif($y < 2045) {
            $drKey = 2035;
        } elseif($y < 2055) {
            $drKey = 2045;
        } elseif($y < 2065) {
            $drKey = 2055;
        } else {
            $drKey = 2065;
        }
        for($age = 1; $age <= 100; $age ++) {
            $pAge = $age - 1;
            $high[$area][$y]['m'][$age] = round($high[$area][$py]['m'][$pAge] * (1 - $dr[$age]['m'][$drKey]));
            $high[$area][$y]['f'][$age] = round($high[$area][$py]['f'][$pAge] * (1 - $dr[$age]['f'][$drKey]));
            $medium[$area][$y]['m'][$age] = round($medium[$area][$py]['m'][$pAge] * (1 - $dr[$age]['m'][$drKey]));
            $medium[$area][$y]['f'][$age] = round($medium[$area][$py]['f'][$pAge] * (1 - $dr[$age]['f'][$drKey]));
            $low[$area][$y]['m'][$age] = round($low[$area][$py]['m'][$pAge] * (1 - $dr[$age]['m'][$drKey]));
            $low[$area][$y]['f'][$age] = round($low[$area][$py]['f'][$pAge] * (1 - $dr[$age]['f'][$drKey]));
        }
        $babyHigh = $babyMedium = $babyLow = 0;
        for($age = 15; $age <= 49; $age ++) {
            if($age < 20) {
                $brKey = '15-19';
            } elseif($age < 25) {
                $brKey = '20-24';
            } elseif($age < 30) {
                $brKey = '25-29';
            } elseif($age < 35) {
                $brKey = '30-34';
            } elseif($age < 40) {
                $brKey = '35-39';
            } elseif($age < 45) {
                $brKey = '40-44';
            } else {
                $brKey = '45-49';
            }
            $babyHigh += round($high[$area][$y]['f'][$age] * ($birthHigh[$y][$brKey] / 1000));
            $babyMedium += round($medium[$area][$y]['f'][$age] * ($birthMedium[$y][$brKey] / 1000));
            $babyLow += round($low[$area][$y]['f'][$age] * ($birthLow[$y][$brKey] / 1000));
        }
        $babyHighM = round($babyHigh * ($birthHigh[$y]['gender'] / (100 + $birthHigh[$y]['gender'] )));
        $babyMediumM = round($babyMedium * ($birthMedium[$y]['gender'] / (100 + $birthMedium[$y]['gender'])));
        $babyLowM = round($babyLow * ($birthLow[$y]['gender'] / (100 + $birthLow[$y]['gender'])));
    
        $age = 0;
        $high[$area][$y]['m'][$age] = $babyHighM;
        $high[$area][$y]['f'][$age] = $babyHigh - $babyHighM;
        if($high[$area][$y]['f'][$age] < 0) {
            $high[$area][$y]['f'][$age] = 0;
        }
        $medium[$area][$y]['m'][$age] = $babyMediumM;
        $medium[$area][$y]['f'][$age] = $babyMedium - $babyMediumM;
        if($medium[$area][$y]['f'][$age] < 0) {
            $medium[$area][$y]['f'][$age] = 0;
        }
        $low[$area][$y]['m'][$age] = $babyLowM;
        $low[$area][$y]['f'][$age] = $babyLow - $babyLowM;
        if($low[$area][$y]['f'][$age] < 0) {
            $low[$area][$y]['f'][$age] = 0;
        }
    }
}

$basePath = __DIR__ . '/reports/area';
$header = false;
foreach($high AS $area => $col1) {
    $targetPath = $basePath . '/' . $area;
    if(!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
    }
    $fh = fopen($targetPath . '/high.csv', 'w');
    if(false === $header) {
        $header = array('year');
        $year = key($col1);
        $col2 = $col1[$year];
        foreach($col2 AS $gender => $col3) {
            ksort($col3);
            $keys = array_keys($col3);
            foreach($keys AS $key) {
                $header[] = $key . $gender;
            }
    }
}
    fputcsv($fh, $header);
    foreach($col1 AS $year => $col2) {
        $line = array();
        $line[] = $year;
        foreach($col2 AS $gender => $col3) {
            ksort($col3);
            $line = array_merge($line, array_values($col3));
        }
        fputcsv($fh, $line);
    }
}

foreach($medium AS $area => $col1) {
    $targetPath = $basePath . '/' . $area;
    $fh = fopen($targetPath . '/medium.csv', 'w');
    fputcsv($fh, $header);
    foreach($col1 AS $year => $col2) {
        $line = array();
        $line[] = $year;
        foreach($col2 AS $gender => $col3) {
            ksort($col3);
            $line = array_merge($line, array_values($col3));
        }
        fputcsv($fh, $line);
    }
}

foreach($low AS $area => $col1) {
    $targetPath = $basePath . '/' . $area;
    $fh = fopen($targetPath . '/low.csv', 'w');
    fputcsv($fh, $header);
    foreach($col1 AS $year => $col2) {
        $line = array();
        $line[] = $year;
        foreach($col2 AS $gender => $col3) {
            ksort($col3);
            $line = array_merge($line, array_values($col3));
        }
        fputcsv($fh, $line);
    }
}