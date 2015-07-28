<?php

$tmpPath = __DIR__ . '/tmp';
if (!file_exists($tmpPath)) {
    mkdir($tmpPath, 0777, true);
}

$za = new ZipArchive();
$za->open($tmpPath . '/20150729000806997.zip');

for ($i = 0; $i < $za->numFiles; $i++) {
    $info = $za->statIndex($i);
    $pInfo = pathinfo(mb_convert_encoding($info['name'], 'utf8', 'big5'));
    $dashPos = strrpos($pInfo['filename'], '-');
    if (false !== $dashPos) {
        $category = substr($pInfo['filename'], 0, $dashPos);
        $year = substr($pInfo['filename'], $dashPos + 1, 3) + 1911;
        $month = substr($pInfo['filename'], $dashPos + 4, 2);
        $dataPath = __DIR__ . "/{$category}/{$year}/{$month}";
        if (!file_exists($dataPath)) {
            mkdir($dataPath, 0777, true);
            $tmpZipFile = "{$tmpPath}/{$pInfo['basename']}";
            if (!file_exists($tmpZipFile)) {
                file_put_contents($tmpZipFile, $za->getFromIndex($i));
            }
            $subZa = new ZipArchive();
            $subZa->open($tmpZipFile);
            for ($j = 0; $j < $subZa->numFiles; $j++) {
                $subInfo = $subZa->statIndex($j);
                $subPInfo = pathinfo(mb_convert_encoding($subInfo['name'], 'utf8', 'big5'));
                if (isset($subPInfo['extension']) && $subPInfo['extension'] === 'csv') {
                    if (substr($subPInfo['filename'], -4) !== 'bdmd') {
                        $subDashPos = strrpos($subPInfo['filename'], 'age');
                        $targetFile = "{$dataPath}/" . substr($subPInfo['filename'], $subDashPos + 4) . '.csv';
                    } else {
                        $targetFile = "{$dataPath}/bdmd.csv";
                    }
                    if (!file_exists($targetFile)) {
                        file_put_contents($targetFile, $subZa->getFromIndex($j));
                    }
                }
            }
        }
    } else {
        print_r($info);
        exit();
    }
}
