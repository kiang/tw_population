<?php

$old = __DIR__ . '/population/2018/12/data.csv';
$new = __DIR__ . '/population/2021/08/data.csv';

$fh = fopen($old, 'r');
$pool = [];
fgetcsv($fh, 8000);
fgetcsv($fh, 8000);
/**
    [0] => 統計年月
    [1] => 區域別代碼
    [2] => 區域別
    [3] => 村里
    [4] => 戶數
    [5] => 人口數
    [6] => 人口數-男
    [7] => 人口數-女
*/
$pairs = [
    '　' => '',
    '三民一' => '三民區',
    '三民二' => '三民區',
    '鳳山一' => '鳳山區',
    '鳳山二' => '鳳山區',
    '安南區顯宮里' => '安南區媽祖宮里',
    '仁愛鄉合作村' => '仁愛鄉德鹿谷村',
];
while($line = fgetcsv($fh, 8000)) {
    $key = $line[2] . $line[3];
    $key = strtr($key, $pairs);
    $pool[$key] = [
        '2018' => $line[5],
        '2021' => 0,
        'diff' => 0,
        'rate' => 0.0,
    ];
}

$fh = fopen($new, 'r');
fgetcsv($fh, 8000);
fgetcsv($fh, 8000);

while($line = fgetcsv($fh, 8000)) {
    $key = $line[2] . $line[3];
    $key = strtr($key, $pairs);
    if(isset($pool[$key])) {
        $pool[$key]['2021'] = $line[5];
        $pool[$key]['diff'] = $pool[$key]['2021'] - $pool[$key]['2018'];
        $pool[$key]['rate'] = round($pool[$key]['diff'] / $pool[$key]['2018'], 3);
    }
}

function cmp($a, $b) {
    if ($a['diff'] == $b['diff']) {
        return 0;
    }
    return ($a['diff'] < $b['diff']) ? -1 : 1;
}

uasort($pool, 'cmp');

$oFh = fopen(__DIR__ . '/reports/2019/population.csv', 'w');
fputcsv($oFh, ['cunli', '2018.12', '2021.08', 'diff', 'rate']);
foreach($pool AS $k => $v) {
    fputcsv($oFh, [$k, $v['2018'], $v['2021'], $v['diff'], $v['rate']]);
}