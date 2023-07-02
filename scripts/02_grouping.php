<?php
$bastPath = dirname(__DIR__);

$target = array(
    'year' => '2015',
    'month' => '11',
);

$fh = fopen($bastPath . '/cunli_code.csv', 'r');
/*
 * Array
  (
  [0] => 縣市代碼
  [1] => 縣市名稱
  [2] => 區里代碼
  [3] => 區鄉鎮名稱
  [4] => 村里代碼
  [5] => 村里名稱
  )
 */
fgetcsv($fh, 2048);
$codes = $cityCodeCheck = $area2City = $code2name = array();
while ($line = fgetcsv($fh, 2048)) {
    if (!isset($cityCodeCheck[$line[0]])) {
        $cityCodeCheck[$line[0]] = true;
        $codes[$line[1]] = str_pad($line[0], 5, '0', STR_PAD_RIGHT);
        $code2name[$codes[$line[1]]] = $line[1];
    }
    if (!isset($codes[$line[1] . $line[3]])) {
        $codes[$line[1] . $line[3]] = $line[2];
        $area2City[$line[2]] = $codes[$line[1]];
    }
}
fclose($fh);

$replaces = array(
    '彰化縣員林鎮' => '彰化縣員林市',
    ' ' => '',
    '臺北市' => '台北市',
    '臺中市' => '台中市',
    '臺南市' => '台南市',
    '高雄市三民一' => '高雄市三民區',
    '高雄市三民二' => '高雄市三民區',
    '高雄市鳳山一' => '高雄市鳳山區',
    '高雄市鳳山二' => '高雄市鳳山區',
    '苗栗縣頭份市' => '苗栗縣頭份鎮',
);

$stack = array();

foreach (glob($bastPath . "/docs/population/{$target['year']}/{$target['month']}/*.csv") AS $csvFile) {
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
            } else {
                $line[1] = strtr($line[1], $replaces);
            }

            if (!isset($codes[$line[1]])) {
                echo "{$line[1]} 找不到代碼\n";
                exit();
            }

            $areaCode = $codes[$line[1]];
            $cityCode = $area2City[$areaCode];

            if (!isset($stack[$year])) {
                $stack[$year] = array();
            }
            if (!isset($stack[$year][$month])) {
                $stack[$year][$month] = array();
            }
            if (!isset($stack[$year][$month][$cityCode])) {
                $stack[$year][$month][$cityCode] = array(
                    'stack' => array(
                        0 => "{$year}-{$month}",
                        1 => $cityCode,
                        2 => $code2name[$cityCode],
                    ),
                );
            }
            if (!isset($stack[$year][$month][$cityCode][$areaCode])) {
                $stack[$year][$month][$cityCode][$areaCode] = array(
                    0 => "{$year}-{$month}",
                    1 => $areaCode,
                    2 => $line[1],
                );
            }
        } else {
            print_r($line);
            exit();
        }
        foreach ($line AS $k => $v) {
            if ($k > 2) {
                if (!isset($stack[$year][$month][$cityCode][$areaCode][$k])) {
                    $stack[$year][$month][$cityCode][$areaCode][$k] = 0;
                }
                if (!isset($stack[$year][$month][$cityCode]['stack'][$k])) {
                    $stack[$year][$month][$cityCode]['stack'][$k] = 0;
                }
                $stack[$year][$month][$cityCode]['stack'][$k] += $v;
                $stack[$year][$month][$cityCode][$areaCode][$k] += $v;
            }
        }
    }
}

$header[0] = '統計年月';
$header[1] = '代碼';
$header[2] = '區域';
unset($header[209]);

foreach ($stack AS $year => $yData) {
    $targetPath = $bastPath . "/docs/city/{$year}";
    if (!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
    }
    foreach ($yData AS $month => $mData) {
        $fh = fopen("{$targetPath}/{$month}.csv", 'w');
        fputcsv($fh, $header);
        foreach ($mData AS $cityCode => $lines) {
            foreach ($lines AS $line) {
                unset($line[209]);
                fputcsv($fh, $line);
            }
        }
        fclose($fh);
    }
}