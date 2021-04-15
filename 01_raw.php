<?php
//http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=F7E9A1B3-3985-434A-9195-4EB940400D59
// old: https://data.gov.tw/dataset/32973

$poolFile = __DIR__ . '/pool.csv';
$fh = fopen($poolFile, 'a+');
$pool = [];
fseek($fh, 0);
while ($line = fgetcsv($fh, 1024)) {
  if (isset($line[2])) {
    $pool[$line[0]] = $line[1] . $line[2];
  }
}

$page = file_get_contents('https://data.gov.tw/dataset/77132');
$pos = strpos($page, '<script data-n-head="ssr" type="application/ld+json">');
$pos = strpos($page, '[{', $pos);
$posEnd = strpos($page, '</script>', $pos);
$json = json_decode(substr($page, $pos, $posEnd - $pos), true);
foreach ($json[1]['distribution'] as $line) {
  if ($line['encodingFormat'] === 'CSV' && !isset($pool[$line['contentUrl']])) {
    $content = shell_exec("curl '{$line['contentUrl']}' -H 'Host: data.moi.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'");
    $pos = strpos($content, "\n");
    $pos = strpos($content, "\n", $pos + 1);
    $ym = substr($content, $pos + 1, 5);
    $y = intval(substr($ym, 0, 3)) + 1911;
    if ($y > 1911) {
      $m = substr($ym, 3, 2);
      $targetPath = __DIR__ . "/population/{$y}/{$m}";
      if (!file_exists($targetPath)) {
        mkdir($targetPath, 0777, true);
      }
      $targetFile = $targetPath . '/data.csv';
      file_put_contents($targetFile, $content);
      fputcsv($fh, [$line['contentUrl'], $y, $m]);
      $pool[$line['contentUrl']] = $y . $m;
    }
  }
}