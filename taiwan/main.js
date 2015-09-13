$.ajaxSetup({async: false});

var map, cunliData = [];

$.get('cunli/2015/07.csv', function (data) {
    var lines = $.csv.toArrays(data);
    for (k in lines) {
        if (k > 0) {
            cunliData[lines[k][1]] = lines[k];
        }
    }
});
function initialize() {

    /*map setting*/
    $('#map-canvas').height(window.outerHeight / 2.2);

    map = new google.maps.Map(document.getElementById('map-canvas'), {
        zoom: 12,
        center: {lat: 23.00, lng: 120.30}
    });

    $.getJSON('taiwan/cunli.json', function (data) {
        cunli = map.data.addGeoJson(topojson.feature(data, data.objects.cunli));
    });

    cunli.forEach(function (value) {
        var key = value.getProperty('VILLAGE_ID');
        if(cunliData[key] && cunliData[key][5] !== 0) {
            value.setProperty('num', cunliData[key][10] / cunliData[key][5]);
        }
    });

    map.data.setStyle(function (feature) {
        color = ColorBar(feature.getProperty('num'));
        return {
            fillColor: color,
            fillOpacity: 0.6,
            strokeColor: 'gray',
            strokeWeight: 1
        }
    });

    map.data.addListener('mouseover', function (event) {
        var Cunli = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        var p = event.feature.getProperty('num') * 100;
        if(isNaN(p)) {
            p = 0;
        }
        map.data.revertStyle();
        map.data.overrideStyle(event.feature, {fillColor: 'white'});
        $('#content').html('<div>' + Cunli + ' ：' + p + ' %</div>').removeClass('text-muted');
    });

    map.data.addListener('mouseout', function (event) {
        map.data.revertStyle();
        $('#content').html('在地圖上滑動或點選以顯示數據').addClass('text-muted');
    });

    map.data.addListener('click', function (event) {
        var Cunli = event.feature.getProperty('VILLAGE_ID');
        var CunliTitle = event.feature.getProperty('C_Name') + event.feature.getProperty('T_Name') + event.feature.getProperty('V_Name');
        var profile = '<table class="table table-bordered">';
        profile += '<tr><td colspan="2" align="center"><h3>' + CunliTitle + '</h3></td></tr>';
        if(cunliData[Cunli]) {
            profile += '<tr><th>年月</th><td>' + cunliData[Cunli][0] + '</td></tr>';
            profile += '<tr><th>戶數</th><td>' + cunliData[Cunli][4] + '</td></tr>';
            profile += '<tr><th>人口數</th><td>' + cunliData[Cunli][5] + '</td></tr>';
            profile += '<tr><th>男性</th><td>' + cunliData[Cunli][6] + '</td></tr>';
            profile += '<tr><th>女性</th><td>' + cunliData[Cunli][7] + '</td></tr>';
            profile += '<tr><th>< 15</th><td>' + cunliData[Cunli][8] + '</td></tr>';
            profile += '<tr><th>15 - 65</th><td>' + cunliData[Cunli][9] + '</td></tr>';
            profile += '<tr><th>&gt; 65</th><td>' + cunliData[Cunli][10] + '</td></tr>';
            profile += '<tr><th>&gt; 65 比率</th><td>' + (event.feature.getProperty('num') * 100) + ' %</td></tr>';
        }
        profile += '</table>';
        $('#cunliProfile').html(profile);
    });
}

google.maps.event.addDomListener(window, 'load', initialize);