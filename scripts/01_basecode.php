<?php
$bastPath = dirname(__DIR__);
$page = file_get_contents('https://data.gov.tw/dataset/18681');
$key = 'http://data.moi.gov.tw/MoiOD/System/DownloadFile.aspx?DATA=';
$key2 = 'https://segisws.moi.gov.tw/STATWSSTData/OpenService';

$pos = strpos($page, '資料資源');
$posEnd = strpos($page, '提供機關', $pos);
$block = substr($page, $pos, $posEnd - $pos);
$lines = explode('data-nid="18681">', $block);
foreach($lines AS $line) {
  if(substr($line, 0, 4) === 'JSON' && strpos($line, '最小統計區')) {
    $pos = strpos($line, '<span class="ff-desc">') + 22;
    $posEnd = strpos($line, '</span>', $pos);
    $title = substr($line, $pos, $posEnd - $pos);

    $pos = strpos($line, $key);
    $posEnd = strpos($line, '"', $pos);
    $link = substr($line, $pos, $posEnd - $pos);
    $targetFile = $bastPath . '/docs/basecode/' . $title . '.json';
    if(!file_exists($targetFile)) {
      $content = shell_exec("curl '{$link}' -H 'Host: data.moi.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'");
      $pos = strpos($content, $key2);
      $posEnd = strpos($content, '"', $pos);
      $link = substr($content, $pos, $posEnd - $pos);
      $content = shell_exec("curl -k '{$link}' -H 'Host: segisws.moi.gov.tw' -H 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:57.0) Gecko/20100101 Firefox/57.0' -H 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8' -H 'Accept-Language: en-US,en;q=0.5' --compressed -H 'Connection: keep-alive' -H 'Upgrade-Insecure-Requests: 1'");
      file_put_contents($targetFile, $content);
    }
  }
}
