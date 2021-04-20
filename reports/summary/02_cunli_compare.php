<?php
$basePath = dirname(dirname(__DIR__));
$result = [];
for ($y = 2019; $y <= 2021; $y++) {
    $fh = fopen($basePath . "/population/{$y}/03/data.csv", 'r');
    $header = fgetcsv($fh, 4096);
    fgetcsv($fh, 4096);
    while ($line = fgetcsv($fh, 4096)) {
        $line[2] = trim(str_replace('　', '', $line[2]));
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
        $cunli = $line[2] . $line[3];
        if(!isset($result[$cunli])) {
            $result[$cunli] = [
                'city' => mb_substr($line[2], 0, 3),
                'cunli' => $line[2] . $line[3],
                'adult' => [],
                'children' => [],
            ];
        }
        $result[$cunli]['adult'][$y] = 0;
        $result[$cunli]['children'][$y] = 0;
        for($i = 8; $i <= 33; $i++) {
            $result[$cunli]['children'][$y] += $line[$i];
        }
        for($i = 38; $i <= 129; $i++) {
            $result[$cunli]['adult'][$y] += $line[$i];
        }
    }
}

$targetPath = __DIR__ . '/02_cunli';
if(!file_exists($targetPath)) {
    mkdir($targetPath, 0777);
}
$fh = [];
foreach($result AS $lv1) {
    if(!isset($fh[$lv1['city']])) {
        $fh[$lv1['city']] = fopen($targetPath . '/' . $lv1['city'] . '.csv', 'w');
        fputcsv($fh[$lv1['city']], ['cunli', 'adult2019', 'chilren2019', 'adult2020', 'chilren2020', 'adult2021', 'chilren2021', 'adult_diff', 'children_diff']);
    }
    fputcsv($fh[$lv1['city']], [
        $lv1['cunli'],
        isset($lv1['adult']['2019']) ? $lv1['adult']['2019'] : '',
        isset($lv1['children']['2019']) ? $lv1['children']['2019'] : '',
        isset($lv1['adult']['2020']) ? $lv1['adult']['2020'] : '',
        isset($lv1['children']['2020']) ? $lv1['children']['2020'] : '',
        isset($lv1['adult']['2021']) ? $lv1['adult']['2021'] : '',
        isset($lv1['children']['2021']) ? $lv1['children']['2021'] : '',
        (isset($lv1['adult']['2021']) && isset($lv1['adult']['2019'])) ? ($lv1['adult']['2021'] - $lv1['adult']['2019']) : '',
        (isset($lv1['children']['2021']) && isset($lv1['children']['2019'])) ? ($lv1['children']['2021'] - $lv1['children']['2019']) : ''
    ]);
}