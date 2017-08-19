<?php
$cunliMap = $result = array();
$json = json_decode(file_get_contents('20150401.json'), true);
foreach($json['objects']['20150401']['geometries'] AS $f) {
  $zone = "{$f['properties']['C_Name']}{$f['properties']['T_Name']}";
  if(!isset($cunliMap[$zone])) {
    $cunliMap[$zone] = array();
  }
  $cunliMap[$zone][$f['properties']['V_Name']] = $f['properties']['VILLAGE_ID'];
}

$fh = fopen(__DIR__ . '/opendata105Y050.csv', 'r');
fgetcsv($fh, 2048);
$header = fgetcsv($fh, 2048);
while($line = fgetcsv($fh, 2048)) {
  $line = array_combine($header, $line);
  $line['區域別'] = str_replace('　', '', $line['區域別']);
  switch($line['區域別']) {
    case '高雄市三民一':
    case '高雄市三民二':
    $line['區域別'] = '高雄市三民區';
    break;
    case '高雄市鳳山一':
    case '高雄市鳳山二':
    $line['區域別'] = '高雄市鳳山區';
    break;
    case '苗栗縣頭份市':
    $line['區域別'] = '苗栗縣頭份鎮';
    break;
    case '彰化縣員林市':
    $line['區域別'] = '彰化縣員林鎮';
    break;
  }
  $cunli = "{$line['區域別']}{$line['村里名稱']}";
  switch($cunli) {
    case '新北市板橋區公舘里':
    $cunliCode = '6500100-021';
    break;
    case '新北市中和區瓦󿾨里':
    $cunliCode = '6500300-017';
    break;
    case '新北市中和區灰󿾨里':
    $cunliCode = '6500300-054';
    break;
    case '新北市永和區新廍里':
    $cunliCode = '6500400-045';
    break;
    case '新北市新店區五峯里':
    $cunliCode = '6500600-041';
    break;
    case '新北市樹林區󿾵寮里':
    $cunliCode = '6500700-007';
    break;
    case '新北市三峽區永舘里':
    $cunliCode = '6500900-006';
    break;
    case '新北市瑞芳區爪峯里':
    $cunliCode = '6501200-007';
    break;
    case '新北市瑞芳區濓新里':
    $cunliCode = '6501200-027';
    break;
    case '新北市瑞芳區濓洞里':
    $cunliCode = '6501200-028';
    break;
    case '新北市土城區峯廷里':
    $cunliCode = '6501300-017';
    break;
    case '新北市坪林區石𥕢里':
    $cunliCode = '6502000-004';
    break;
    case '新北市萬里區崁脚里':
    $cunliCode = '6502800-008';
    break;
    case '臺北市信義區富台里':
    $cunliCode = '6300200-019';
    break;
    case '臺北市萬華區糖廍里':
    $cunliCode = '6300700-015';
    break;
    case '桃園市大園區菓林里':
    $cunliCode = '6800600-011';
    break;
    case '桃園市新屋區槺榔里':
    $cunliCode = '6801100-020';
    break;
    case '臺中市西區公舘里':
    $cunliCode = '6600400-009';
    break;
    case '臺中市西區双龍里':
    $cunliCode = '6600400-024';
    break;
    case '臺中市北屯區廍子里':
    $cunliCode = '6600800-013';
    break;
    case '臺中市清水區槺榔里':
    $cunliCode = '6601200-016';
    break;
    case '臺中市外埔區廍子里':
    $cunliCode = '6602100-011';
    break;
    case '臺中市大安區龜売里':
    $cunliCode = '6602200-004';
    break;
    case '臺中市大肚區蔗廍里':
    $cunliCode = '6602400-016';
    break;
    case '臺南市新營區舊廍里':
    $cunliCode = '6700100-022';
    break;
    case '臺南市後壁區後廍里':
    $cunliCode = '6700500-016';
    break;
    case '臺南市麻豆區晋江里':
    $cunliCode = '6700700-004';
    break;
    case '臺南市麻豆區寮廍里':
    $cunliCode = '6700700-015';
    break;
    case '臺南市官田區南廍里':
    $cunliCode = '6701000-008';
    break;
    case '臺南市佳里區頂廍里':
    $cunliCode = '6701200-014';
    break;
    case '臺南市西港區檨林里':
    $cunliCode = '6701400-004';
    break;
    case '臺南市七股區塩埕里':
    $cunliCode = '6701500-007';
    break;
    case '臺南市七股區槺榔里':
    $cunliCode = '6701500-020';
    break;
    case '臺南市新化區山脚里':
    $cunliCode = '6701800-016';
    break;
    case '臺南市新化區𦰡拔里':
    $cunliCode = '6701800-018';
    break;
    case '臺南市山上區玉峯里':
    $cunliCode = '6702200-005';
    break;
    case '臺南市龍崎區石𥕢里':
    $cunliCode = '6703000-008';
    break;
    case '臺南市永康區塩行里':
    $cunliCode = '6703100-010';
    break;
    case '臺南市永康區塩洲里':
    $cunliCode = '6703100-029';
    break;
    case '臺南市安南區󻕯南里':
    $cunliCode = '6703500-003';
    break;
    case '臺南市安南區塩田里':
    $cunliCode = '6703500-019';
    break;
    case '臺南市安南區公󻕯里':
    $cunliCode = '6703500-024';
    break;
    case '高雄市左營區廍北里':
    $cunliCode = '6400300-027';
    break;
    case '高雄市左營區廍南里':
    $cunliCode = '6400300-028';
    break;
    case '高雄市鳥松區坔埔里':
    $cunliCode = '6401800-004';
    break;
    case '高雄市阿蓮區峯山里':
    $cunliCode = '6402300-003';
    break;
    case '高雄市湖內區公舘里':
    $cunliCode = '6402500-004';
    break;
    case '新竹縣竹東鎮上舘里':
    $cunliCode = '1000402-006';
    break;
    case '新竹縣竹東鎮鷄林里':
    $cunliCode = '1000402-012';
    break;
    case '新竹縣北埔鄉水磜村':
    $cunliCode = '1000409-004';
    break;
    case '苗栗縣苑裡鎮山脚里':
    $cunliCode = '1000502-014';
    break;
    case '苗栗縣苑裡鎮上舘里':
    $cunliCode = '1000502-021';
    break;
    case '苗栗縣竹南鎮公舘里':
    $cunliCode = '1000504-018';
    break;
    case '苗栗縣三義鄉双湖村':
    $cunliCode = '1000513-002';
    break;
    case '苗栗縣三義鄉双潭村':
    $cunliCode = '1000513-003';
    break;
    case '彰化縣彰化市下廍里':
    $cunliCode = '1000701-002';
    break;
    case '彰化縣彰化市磚󿾨里':
    $cunliCode = '1000701-030';
    break;
    case '彰化縣彰化市南瑶里':
    $cunliCode = '1000701-039';
    break;
    case '彰化縣彰化市寶廍里':
    $cunliCode = '1000701-059';
    break;
    case '彰化縣員林鎮大峯里':
    $cunliCode = '1000710-030';
    break;
    case '彰化縣埔鹽鄉廍子村':
    $cunliCode = '1000714-003';
    break;
    case '彰化縣埔鹽鄉瓦󿾨村':
    $cunliCode = '1000714-010';
    break;
    case '彰化縣埔心鄉舊舘村':
    $cunliCode = '1000715-012';
    break;
    case '彰化縣埔心鄉南舘村':
    $cunliCode = '1000715-013';
    break;
    case '彰化縣埔心鄉新舘村':
    $cunliCode = '1000715-014';
    break;
    case '彰化縣埔心鄉埤脚村':
    $cunliCode = '1000715-020';
    break;
    case '彰化縣芳苑鄉頂廍村':
    $cunliCode = '1000723-013';
    break;
    case '南投縣竹山鎮硘󿾨里':
    $cunliCode = '1000804-005';
    break;
    case '南投縣名間鄉廍下村':
    $cunliCode = '1000806-012';
    break;
    case '南投縣仁愛鄉都達村':
    continue;
    break;
    case '雲林縣斗六市崙峯里':
    $cunliCode = '1000901-018';
    break;
    case '雲林縣西螺鎮公舘里':
    $cunliCode = '1000904-027';
    break;
    case '雲林縣北港鎮公舘里':
    $cunliCode = '1000906-011';
    break;
    case '雲林縣麥寮鄉瓦󿾨村':
    $cunliCode = '1000913-003';
    break;
    case '雲林縣元長鄉瓦󿾨村':
    $cunliCode = '1000917-018';
    break;
    case '雲林縣四湖鄉󿿀子村':
    $cunliCode = '1000918-016';
    break;
    case '雲林縣四湖鄉󿿀東村':
    $cunliCode = '1000918-021';
    break;
    case '雲林縣水林鄉𣐤埔村':
    $cunliCode = '1000920-021';
    break;
    case '嘉義縣朴子市双溪里':
    $cunliCode = '1001002-012';
    break;
    case '嘉義縣民雄鄉双福村':
    $cunliCode = '1001005-023';
    break;
    case '嘉義縣中埔鄉塩舘村':
    $cunliCode = '1001013-002';
    break;
    case '嘉義縣中埔鄉石硦村':
    $cunliCode = '1001013-014';
    break;
    case '嘉義縣竹崎鄉文峯村':
    $cunliCode = '1001014-018';
    break;
    case '嘉義縣梅山鄉双溪村':
    $cunliCode = '1001015-010';
    break;
    case '嘉義縣梅山鄉瑞峯村':
    $cunliCode = '1001015-015';
    break;
    case '屏東縣東港鎮下廍里':
    $cunliCode = '1001303-017';
    break;
    case '屏東縣萬丹鄉厦北村':
    $cunliCode = '1001305-012';
    break;
    case '屏東縣萬丹鄉厦南村':
    $cunliCode = '1001305-013';
    break;
    case '屏東縣里港鄉三廍村':
    $cunliCode = '1001309-012';
    break;
    case '屏東縣新園鄉瓦󿾨村':
    $cunliCode = '1001317-001';
    break;
    case '屏東縣林邊鄉崎峯村':
    $cunliCode = '1001319-007';
    break;
    case '屏東縣滿州鄉响林村':
    $cunliCode = '1001324-005';
    break;
    case '屏東縣瑪家鄉凉山村':
    $cunliCode = '1001328-003';
    break;
    case '臺東縣關山鎮里壠里':
    $cunliCode = '1001403-005';
    break;
    case '臺東縣綠島鄉公舘村':
    $cunliCode = '1001411-001';
    break;
    case '臺東縣達仁鄉台坂村':
    $cunliCode = '1001415-001';
    break;
    case '臺東縣達仁鄉土坂村':
    $cunliCode = '1001415-002';
    break;
    case '澎湖縣馬公市󼱹裡里':
    $cunliCode = '1001601-031';
    break;
    case '澎湖縣湖西鄉菓葉村':
    $cunliCode = '1001602-022';
    break;
    case '嘉義市西區磚󿾨里':
    $cunliCode = '1002002-018';
    break;
    case '金門縣金城鎮西門里':
    case '金門縣金城鎮南門里':
    case '金門縣金城鎮北門里':
    continue;
    break;
    case '連江縣北竿鄉坂里村':
    $cunliCode = '0900702-005';
    break;
    default:
    if(!isset($cunliMap[$line['區域別']][$line['村里名稱']])) {
      print_r($cunli);
      ksort($cunliMap[$line['區域別']]);
      print_r($cunliMap[$line['區域別']]);
      exit();
    }
    $cunliCode = $cunliMap[$line['區域別']][$line['村里名稱']];
  }
  foreach($line AS $k => $v) {
    $parts = explode('_', $k);
    if(count($parts) === 3) {
      if(!isset($result[$cunliCode])) {
        $result[$cunliCode] = array();
      }
      if(!isset($result[$cunliCode][$parts[1]])) {
        $result[$cunliCode][$parts[1]] = 0;
      }
      $result[$cunliCode][$parts[1]] += intval($v);
    }
  }
}

file_put_contents(__DIR__ . '/edu.json', json_encode($result));
