<?php
//http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=F7E9A1B3-3985-434A-9195-4EB940400D59
$page = file_get_contents('https://data.gov.tw/dataset/32973');
$key = 'http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=';
$pos = strpos($page, $key);
while(false !== $pos) {
  $posEnd = strpos($page, '"', $pos);
  $link = substr($page, $pos, $posEnd - $pos);
  $pos = strpos($page, '<span class="ff-desc">', $pos) + 22;
  $posEnd = strpos($page, '</span>', $pos);
  $title = substr($page, $pos, $posEnd - $pos);

  $y = intval(substr($title, 0, 3)) + 1911;
  if($y < 2000) {
    die('something wrong');
  }
  $m = substr($title, 3, 2);
  $targetPath = __DIR__ . "/村里戶數人口數單一年齡人口數/{$y}/{$m}";
  if(!file_exists($targetPath)) {
    mkdir($targetPath, 0777, true);
  }
  $targetFile = $targetPath . "/data.csv";
  if(!file_exists($targetFile)) {
    $content = shell_exec("curl '{$link}' -H 'Host: data.moi.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'");
    file_put_contents($targetFile, $content);
  }

  $pos = strpos($page, $key, $posEnd);
}