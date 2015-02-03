<?php

$previous = false;
$keyMap = array(
    '桃園縣桃園市' => '桃園市桃園區',
    '桃園縣中壢市' => '桃園市中壢區',
    '桃園縣大溪鎮' => '桃園市大溪區',
    '桃園縣楊梅市' => '桃園市楊梅區',
    '桃園縣蘆竹市' => '桃園市蘆竹區',
    '桃園縣大園鄉' => '桃園市大園區',
    '桃園縣龜山鄉' => '桃園市龜山區',
    '桃園縣八德市' => '桃園市八德區',
    '桃園縣龍潭鄉' => '桃園市龍潭區',
    '桃園縣平鎮市' => '桃園市平鎮區',
    '桃園縣新屋鄉' => '桃園市新屋區',
    '桃園縣觀音鄉' => '桃園市觀音區',
    '桃園縣復興鄉' => '桃園市復興區',
    '桃園縣' => '桃園市',
);

foreach (glob(__DIR__ . '/city/*/*.csv') AS $csvFile) {
    $pathParts = explode('/', $csvFile);
    $month = substr(array_pop($pathParts), 0, -4);
    $year = array_pop($pathParts);

    $targetPath = __DIR__ . "/city_diff/{$year}";
    if (!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
    }
    $current = array();
    
    $csvFh = fopen($csvFile, 'r');
    if (false !== $previous) {
        $fh = fopen("{$targetPath}/{$month}.csv", 'w');
        fputcsv($fh, fgetcsv($csvFh, 4096));
    } else {
        fgets($csvFh, 4096);
    }

    while ($line = fgetcsv($csvFh, 4096)) {
        if (isset($keyMap[$line[2]])) {
            $line[2] = $keyMap[$line[2]];
        }
        $current[$line[2]] = $line;
    }
    if (false !== $previous) {
        foreach ($current AS $k => $v) {
            foreach ($v AS $idx => $val) {
                if ($idx > 2) {
                    $previous[$k][$idx] = $val - $previous[$k][$idx];
                }
            }
            fputcsv($fh, $previous[$k]);
        }
    }
    $previous = $current;
}