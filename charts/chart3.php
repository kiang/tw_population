<?php
$data = array();
$headerDone = false;
foreach (glob(dirname(__DIR__) . '/city_diff/*/*.csv') AS $csvFile) {
    $pathParts = explode('/', $csvFile);
    $month = substr(array_pop($pathParts), 0, -4);
    $year = array_pop($pathParts);
    $key = "{$year}-{$month}";
    $csvFh = fopen($csvFile, 'r');
    fgets($csvFh, 4096);
    while ($line = fgetcsv($csvFh, 2048)) {
        if (false === $headerDone) {
            if (!isset($data[0])) {
                $data[0] = array(
                    '年月',
                );
            }
            $data[0][$line[2]] = $line[2];
        }
        if (!isset($data[$key])) {
            $data[$key] = array(
                $key
            );
        }
        $data[$key][$line[2]] = 0;
        foreach ($line AS $k => $v) {
            if ($k > 36 && $k < 137) {
                $data[$key][$line[2]] += intval($v);
            }
        }
    }
    $headerDone = true;
}
$jsonData = $counties = $towns = array();
foreach ($data AS $k1 => $v1) {
    $l1 = array(
        $k1,
    );
    $l2 = array();
    $lineCount = 0;
    foreach ($v1 AS $k2 => $v2) {
        ++$lineCount;
        if ($lineCount > 1 && $k2 !== $v2) {
            $v2 = intval($v2);
        }
        if (mb_strlen($k2, 'utf-8') === 3) {
            $l1[] = $v2;
        } else {
            $l2[] = $v2;
        }
    }
    $counties[] = $l1;
    $towns[] = $l2;
    $v1 = array_values($v1);
    if ($k1 != 0) {
        foreach ($v1 AS $k2 => $v2) {
            if ($k2 > 0) {
                $v1[$k2] = intval($v2);
            } else {
                $v1[$k2] = (string) $v2;
            }
        }
    }

    $jsonData[] = $v1;
}
$counties[0][0] = '年月';
$countyTowns = $countyKeys = array();
foreach ($towns[0] AS $k => $v) {
    if ($k > 0) {
        $county = mb_substr($v, 0, 3, 'utf-8');
        $town = mb_substr($v, 3, null, 'utf-8');
        if (!isset($countyKeys[$county])) {
            $countyKeys[$county] = array();
        }
        $countyKeys[$county][$k] = $town;
    }
}
foreach ($countyKeys AS $county => $cTowns) {
    if (!isset($countyTowns[$county])) {
        $countyTowns[$county] = array();
    }
    foreach ($towns AS $k1 => $v1) {
        $l = array($v1[0]);
        foreach ($v1 AS $k2 => $v2) {
            if (isset($cTowns[$k2])) {
                $l[] = $v2;
            }
        }
        $countyTowns[$county][] = $l;
    }
}
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>勞動人口異動量曲線圖表</title>
        <script type="text/javascript"
                src="https://www.google.com/jsapi?autoload={
                'modules':[{
                'name':'visualization',
                'version':'1',
                'packages':['corechart']
                }]
        }"></script>

        <script type="text/javascript">
                    google.setOnLoadCallback(drawChart);
                    function drawChart() {
                    var data = google.visualization.arrayToDataTable(<?php echo json_encode($jsonData); ?>);
                            var counties = google.visualization.arrayToDataTable(<?php echo json_encode($counties); ?>);
                            var towns = google.visualization.arrayToDataTable(<?php echo json_encode($towns); ?>);
                            var options = {
                            title: '縣市與鄉鎮市區勞動人口異動量曲線',
                                    curveType: 'function',
                                    legend: { position: 'bottom' }
                            };
                            var chart = new google.visualization.LineChart(document.getElementById('chart1'));
                            chart.draw(data, options);
                            options.title = '縣市勞動人口異動量曲線';
                            chart = new google.visualization.LineChart(document.getElementById('chart2'));
                            chart.draw(counties, options);
                            options.title = '鄉鎮市區勞動人口異動量曲線';
                            chart = new google.visualization.LineChart(document.getElementById('chart3'));
                            chart.draw(towns, options);
<?php
$chartNo = 3;
$countyTownsCount = count($countyTowns);
foreach ($countyTowns AS $county => $countyData) {
    $chartNo++;
    ?>
                        options.title = '<?php echo $county; ?>勞動人口異動量曲線';
                                chart = new google.visualization.LineChart(document.getElementById('chart<?php echo $chartNo; ?>'));
                                chart.draw(google.visualization.arrayToDataTable(<?php echo json_encode($countyData); ?>), options);
<?php } ?>
                    }
        </script>
    </head>
    <body>
        <div id="chart1" style="width: 80%; height: 500px; margin: auto;"></div>
        <div id="chart2" style="width: 80%; height: 500px; margin: auto;"></div>
        <div id="chart3" style="width: 80%; height: 500px; margin: auto;"></div>
        <?php for ($i = 0; $i < $countyTownsCount; $i++) { ?>
            <div id="chart<?php echo $i + 4; ?>" style="width: 80%; height: 500px; margin: auto;"></div>
        <?php } ?>
    </body>
</html>