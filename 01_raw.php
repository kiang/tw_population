<?php
//http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=F7E9A1B3-3985-434A-9195-4EB940400D59
// old: https://data.gov.tw/dataset/32973
$page = file_get_contents('https://data.gov.tw/dataset/77132');
$key = 'http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=';
$logFile = __DIR__ . '/raw.log';
$logs = '';
if(file_exists($logFile)) {
  $logs = file_get_contents($logFile);
}
$fh = fopen($logFile, 'a+');
$pos = strpos($page, $key);
while(false !== $pos) {
  $posEnd = strpos($page, '"', $pos);
  $link = substr($page, $pos, $posEnd - $pos);
  $posEnd = strpos($page, '</span>', $posEnd);
  if(false === strpos($logs, $link)) {
    fputs($fh, $link);
    $logs .= $link . "\n";
    $content = shell_exec("curl '{$link}' -H 'Host: data.moi.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'");
    $cPos = strpos($content, "\n");
    $cPos = strpos($content, "\n", $cPos + 1);
    $y = intval(substr($content, $cPos+1, 3)) + 1911;
    if($y < 2000) {
      die('something wrong');
    }
    $m = substr($content, $cPos + 4, 2);
    $targetPath = __DIR__ . "/村里戶數人口數單一年齡人口數/{$y}/{$m}";
    if(!file_exists($targetPath)) {
      mkdir($targetPath, 0777, true);
    }
    $targetFile = $targetPath . "/data.csv";
    file_put_contents($targetFile, $content);
  }

  $pos = strpos($page, $key, $posEnd);
}

fclose($fh);
