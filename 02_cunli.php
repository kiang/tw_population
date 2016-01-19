<?php

$fh = fopen(__DIR__ . '/villages.csv', 'r');
/*
 * Array
  (
  [0] => ivid
  [1] => id
  [2] => name
  [3] => town
  [4] => county
  [5] => vid
  [6] => icid
  [7] => itid
  )
 */
fgetcsv($fh, 2048);
$codes = array();
while ($line = fgetcsv($fh, 2048)) {
    if (!isset($codes[$line[1] . $line[3] . $line[5]])) {
        $codes[$line[4] . $line[3] . $line[2]] = $line[1];
    }
    if ($line[4] === '桃園縣') {
        $replacedKey = '桃園市' . mb_substr($line[3], 0, -1) . '區' . mb_substr($line[2], 0, -1, 'utf-8') . '里';
        $codes[$replacedKey] = $line[1];
    }
    if ($line[3] === '員林鎮') {
        $replacedKey = $line[4] . '員林市' . $line[2];
        $codes[$replacedKey] = $line[1];
    }
}
fclose($fh);

$replaces = array(
    ' ' => '',
    '高雄市三民一' => '高雄市三民區',
    '高雄市三民二' => '高雄市三民區',
    '高雄市鳳山一' => '高雄市鳳山區',
    '高雄市鳳山二' => '高雄市鳳山區',
    '苗栗縣頭份市' => '苗栗縣頭份鎮',
);

$stack = array(
    '彰化縣彰化市下廍里' => '10007010-002',
    '彰化縣彰化市南瑶里' => '10007010-039',
    '彰化縣彰化市寶廍里' => '10007010-059',
    '彰化縣彰化市磚󿾨里' => '10007010-030',
    '彰化縣埔鹽鄉瓦󿾨村' => '10007140-010',
    '彰化縣埔心鄉南舘村' => '10007150-013',
    '彰化縣埔心鄉埤脚村' => '10007150-020',
    '彰化縣埔心鄉新舘村' => '10007150-014',
    '彰化縣埔心鄉舊舘村' => '10007150-012',
    '彰化縣二水鄉上豊村' => '10007180-002',
    '南投縣竹山鎮硘󿾨里' => '10008040-005',
    '雲林縣麥寮鄉瓦󿾨村' => '10009130-003',
    '雲林縣元長鄉瓦󿾨村' => '10009170-018',
    '雲林縣四湖鄉󿿀子村' => '10009180-016',
    '雲林縣四湖鄉󿿀東村' => '10009180-021',
    '嘉義縣中埔鄉塩舘村' => '10010130-002',
    '嘉義縣中埔鄉石硦村' => '10010130-014',
    '嘉義縣竹崎鄉文峯村' => '10010140-018',
    '屏東縣新園鄉瓦󿾨村' => '10013170-001',
    '澎湖縣馬公市󼱹裡里' => '10016010-031',
    '嘉義市西區磚󿾨里' => '10020020-018',
    '新北市中和區瓦󿾨里' => '65000030-017',
    '新北市樹林區󿾵寮里' => '65000070-007',
    '臺中市大安區龜売里' => '66000220-004',
    '臺南市西港區檨林里' => '67000140-004',
    '臺南市安南區公󻕯里' => '67000350-024',
    '臺南市安南區󻕯南里' => '67000350-003',
    '新北市中和區灰󿾨里' => '65000030-054',
    '雲林縣麥寮鄉中興村' => '10009130-008',
    '新竹市東區關新里' => '10018010-054',
    '新竹市北區中雅里' => '10018020-045',
    '高雄市杉林區大愛里' => '64000340-008',
);

$fhPool = array();
$missingPool = array();
foreach (glob(__DIR__ . '/村里戶數人口數單一年齡人口數/*/*/*.csv') AS $csvFile) {
    $csvFh = fopen($csvFile, 'r');
    $header = fgetcsv($csvFh, 4096);
    while ($line = fgetcsv($csvFh, 4096)) {
        if (strlen($line[0]) === 5) {
            /*
             * Array
              (
              [0] => 10304
              [1] => 連江縣南竿鄉
              [2] => 仁愛村
             */
            $year = substr($line[0], 0, 3) + 1911;
            $month = substr($line[0], 3);
            if (false !== strpos($line[1], '桃園縣')) {
                $line[1] = mb_substr($line[1], 0, 2, 'utf-8') . '市' . mb_substr($line[1], 3, -1, 'utf-8') . '區';
                $line[2] = mb_substr($line[2], 0, -1, 'utf-8') . '里';
            } else {
                $line[1] = strtr($line[1], $replaces);
            }

            if (isset($codes[$line[1] . $line[2]])) {
                $cunliCode = $codes[$line[1] . $line[2]];
            } elseif (isset($stack["{$line[1]}{$line[2]}"])) {
                $cunliCode = $stack["{$line[1]}{$line[2]}"];
            } else {
                if (!isset($missingPool["{$line[1]}{$line[2]}"])) {
                    $missingPool["{$line[1]}{$line[2]}"] = true;
                    echo "'{$line[1]}{$line[2]}' => '',\n";
                }
            }
        } else {
            print_r($line);
            exit();
        }
        $ym = "{$year}-{$month}";
        if (!isset($fhPool[$ym])) {
            if (!file_exists(__DIR__ . "/cunli/{$year}")) {
                mkdir(__DIR__ . "/cunli/{$year}", 0777, true);
            }
            $fhPool[$ym] = fopen(__DIR__ . "/cunli/{$year}/{$month}.csv", 'w');
            fputcsv($fhPool[$ym], array(
                'ym' => '年月',
                'code' => '村里代碼',
                'area' => '區域',
                'cunli' => '村里',
                'family' => '戶數',
                'population' => '人口',
                'm' => '男性人口',
                'f' => '女性人口',
                '<15' => '未滿15歲',
                '15-65' => '15-65歲',
                '>65' => '超過65歲',
                '>20' => '超過20歲',
                '18-19' => '18-19歲',
            ));
        }
        $dataLine = array(
            'ym' => $ym,
            'code' => $cunliCode,
            'area' => $line[1],
            'cunli' => $line[2],
            'family' => $line[3],
            'population' => $line[4],
            'm' => $line[5],
            'f' => $line[6],
            '<15' => 0,
            '15-65' => 0,
            '>65' => 0,
            '>20' => 0,
            '18-19' => 0,
        );
        foreach ($line AS $k => $v) {
            if ($k < 7) {
                continue;
            } elseif ($k < 37) {
                // < 15
                $dataLine['<15'] += $v;
            } elseif ($k < 139) {
                // 15 ~ 65
                $dataLine['15-65'] += $v;
            } else {
                // > 65
                $dataLine['>65'] += $v;
            }
            if (in_array($k, array(43, 44, 45, 46))) {
                $dataLine['18-19'] += $v;
            }
            if ($k > 46) {
                $dataLine['>20'] += $v;
            }
        }
        fputcsv($fhPool[$ym], $dataLine);
    }
}
