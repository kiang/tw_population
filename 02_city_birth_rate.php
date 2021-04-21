<?php

$result = [
    '全國' => [],
];
$replaces = array(
    ' ' => '',
    '　' => '',
    '高雄市三民一' => '高雄市三民區',
    '高雄市三民二' => '高雄市三民區',
    '高雄市鳳山一' => '高雄市鳳山區',
    '高雄市鳳山二' => '高雄市鳳山區',
    '苗栗縣頭份鎮' => '苗栗縣頭份市',
    '彰化縣員林鎮' => '彰化縣員林市',
);
foreach(glob(__DIR__ . '/bdmd/*/*/data.csv') AS $bdmdFile) {
    $fh = fopen($bdmdFile, 'r');
    $header = fgetcsv($fh, 2048);
    if(false === strpos($header[0], '統計年月')) {
        $header = fgetcsv($fh, 2048);
    }
    $header[0] = '統計年月';
    while($line = fgetcsv($fh, 2048)) {
        $data = array_combine($header, $line);
        $y = intval(substr($data['統計年月'], 0, 3)) + 1911;
        if($y < 2015 || $y > 2020) {
            continue;
        }
        $data['區域別'] = strtr($data['區域別'], $replaces);
        if(!isset($result[$data['區域別']])) {
            $result[$data['區域別']] = [];
        }
        if(!isset($result[$data['區域別']][$y])) {
            $result[$data['區域別']][$y] = [
                'birth' => 0,
                'death' => 0,
                'population' => 0,
            ];
        }
        $result[$data['區域別']][$y]['birth'] += $data['出生數'];
        $result[$data['區域別']][$y]['death'] += $data['死亡數'];

        $city = mb_substr($data['區域別'], 0, 3, 'utf-8');
        if(!isset($result[$city])) {
            $result[$city] = [];
        }
        if(!isset($result[$city][$y])) {
            $result[$city][$y] = [
                'birth' => 0,
                'death' => 0,
                'population' => 0,
            ];
        }
        $result[$city][$y]['birth'] += $data['出生數'];
        $result[$city][$y]['death'] += $data['死亡數'];

        $city = '全國';
        if(!isset($result[$city][$y])) {
            $result[$city][$y] = [
                'birth' => 0,
                'death' => 0,
                'population' => 0,
            ];
        }
        $result[$city][$y]['birth'] += $data['出生數'];
        $result[$city][$y]['death'] += $data['死亡數'];
    }
}

foreach(glob(__DIR__ . '/cunli/*/06.csv') AS $csvFile) {
    $fh = fopen($csvFile, 'r');
    $header = fgetcsv($fh, 2048);
    while($line = fgetcsv($fh, 2048)) {
        $data = array_combine($header, $line);
        $y = substr($data['年月'], 0, 4);
        if($y < 2015 || $y > 2020) {
            continue;
        }
        $data['區域'] = strtr($data['區域'], $replaces);
        $result[$data['區域']][$y]['population'] += $data['人口'];
        $city = mb_substr($data['區域'], 0, 3, 'utf-8');
        $result[$city][$y]['population'] += $data['人口'];
        $city = '全國';
        $result[$city][$y]['population'] += $data['人口'];
    }
}

$targetPath = __DIR__ . '/city_birth_rate';
if(!file_exists($targetPath)) {
    mkdir($targetPath, 0777);
}
$oFh = [];
$allFh = fopen($targetPath . '/all.csv', 'w');
ksort($result);
$headerDone = false;
foreach($result AS $city => $lv1) {
    ksort($lv1);
    if(false === $headerDone) {
        $headerDone = true;
        $header = array_merge(['區域'], array_keys($lv1));
        fputcsv($allFh, $header);
    }
    $line = [$city];
    foreach($lv1 AS $y => $data) {
        if(!isset($oFh[$y])) {
            $oFh[$y] = fopen($targetPath . '/' . $y . '.csv', 'w');
            fputcsv($oFh[$y], ['區域', '年出生數', '年死亡數', '年中人口(6月底)', '粗出生率', '粗死亡率']);
        }
        $birthRate = $deathRate = 0;
        if($data['population'] > 0) {
            $birthRate = round($data['birth'] / $data['population'] * 1000, 2);
            $deathRate = round($data['death'] / $data['population'] * 1000, 2);
        } else {
            echo $city;
            print_r($data);
        }
        $line[] = $birthRate;
        
        fputcsv($oFh[$y], [$city, $data['birth'], $data['death'], $data['population'], $birthRate, $deathRate]);
    }
    fputcsv($allFh, $line);
}