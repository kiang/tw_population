<?php
$basePath = dirname(__DIR__);
$result = [];
for ($y = 2018; $y <= 2021; $y++) {
    $fh = fopen($basePath . "/population/{$y}/03/data.csv", 'r');
    $header = fgetcsv($fh, 4096);
    fgetcsv($fh, 4096);
    while ($line = fgetcsv($fh, 4096)) {
        $line[2] = str_replace('　', '', $line[2]);
        switch ($line[2]) {
            case '高雄市三民一':
            case '高雄市三民二':
                $line[2] = '高雄市三民區';
                break;
            case '高雄市鳳山一':
            case '高雄市鳳山二':
                $line[2] = '高雄市鳳山區';
                break;
        }
        $line[2] = mb_substr($line[2], 0, 3, 'utf-8');
        if (!isset($result[$line[2]])) {
            $result[$line[2]] = [];
        }
        if (!isset($result[$line[2]][$y])) {
            $result[$line[2]][$y] = 0;
        }
        for($i = 8; $i <= 37; $i++) {
            $result[$line[2]][$y] += $line[$i];
        }
    }
}
$oFh = fopen(__DIR__ . '/children_cities.csv', 'w');
fputcsv($oFh, ['area', '2018', '2019', '2020', '2021', 'diff']);
foreach ($result as $area => $years) {
    fputcsv($oFh, [$area, $years['2018'], $years['2019'], $years['2020'], $years['2021'], $years['2021'] - $years['2018']]);
}
