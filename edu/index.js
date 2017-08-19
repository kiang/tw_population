var edu, levelId;
var layerStyle = new ol.style.Style({
    stroke: false,
    fill: false
});
var projection = ol.proj.get('EPSG:3857');
var projectionExtent = projection.getExtent();
var size = ol.extent.getWidth(projectionExtent) / 256;
var resolutions = new Array(20);
var matrixIds = new Array(20);
for (var z = 0; z < 20; ++z) {
    // generate resolutions and matrixIds arrays for this WMTS
    resolutions[z] = size / Math.pow(2, z);
    matrixIds[z] = z;
}
var popup = new ol.Overlay.Popup();

var mapLayers = [new ol.layer.Tile({
        source: new ol.source.WMTS({
            matrixSet: 'EPSG:3857',
            format: 'image/png',
            url: 'http://maps.nlsc.gov.tw/S_Maps/wmts',
            layer: 'EMAP',
            tileGrid: new ol.tilegrid.WMTS({
                origin: ol.extent.getTopLeft(projectionExtent),
                resolutions: resolutions,
                matrixIds: matrixIds
            }),
            style: 'default',
            wrapX: true,
            attributions: '<a href="http://maps.nlsc.gov.tw/" target="_blank">國土測繪圖資服務雲</a>'
        }),
        opacity: 0.6
    })];

var zoneLayer = new ol.layer.Vector({
    source: new ol.source.Vector({
        url: '20150401.json',
        format: new ol.format.TopoJSON()
    }),
    style: layerStyle
});
zoneLayer.setProperties({'id': 'zoneLayer'});

mapLayers.push(zoneLayer);
var map = new ol.Map({
    layers: mapLayers,
    target: 'map',
    controls: ol.control.defaults({
        attributionOptions: /** @type {olx.control.AttributionOptions} */ ({
            collapsible: false
        })
    }),
    view: new ol.View({
        center: ol.proj.fromLonLat([120.4782261982077, 24.085695695162585]),
        zoom: 9
    })
});
map.addOverlay(popup);
map.on('singleclick', onLayerClick);

function onLayerClick(e) {
    var message = '', p;
    map.forEachFeatureAtPixel(e.pixel, function (feature) {
        p = feature.getProperties();
    });
    if(p['C_Name']) {
      var vid = p['VILLAGE_ID'];
      message += '<h3>' + p['C_Name'] + p['T_Name'] + p['V_Name'] + '</h3>';
      if(edu[vid]) {
        for(k in edu[vid]) {
          message += k + ': ' + edu[vid][k] + '<br />';
        }
      }
      popup.show(e.coordinate, message);
      map.getView().setCenter(e.coordinate);
    }
}

var styleCache = {};
var styleFunction = function(feature) {
  var vid = feature.get('VILLAGE_ID');
  var rangeLevel = 1;
  if(edu[vid]) {
    if(edu[vid][levelId] > levelRange[levelId].v4) {
      rangeLevel = 5;
    } else if(edu[vid][levelId] > levelRange[levelId].v3) {
      rangeLevel = 4;
    } else if(edu[vid][levelId] > levelRange[levelId].v2) {
      rangeLevel = 3;
    } else if(edu[vid][levelId] > levelRange[levelId].v1) {
      rangeLevel = 2;
    } else if(edu[vid][levelId] == 0) {
      rangeLevel = 0;
    }
  }
  var style = styleCache[rangeLevel];
  if (!style) {
    var fillColor = 'rgba(128, 0, 0, 0.3)';
    switch(rangeLevel) {
      case 0:
        fillColor = 'rgba(255, 255, 255, 0.3)';
      break;
      case 1:
        fillColor = 'rgba(0, 0, 0, 0.3)';
      break;
      case 2:
        fillColor = 'rgba(0, 0, 255, 1)';
      break;
      case 3:
        fillColor = 'rgba(128, 0, 0, 1)';
      break;
      case 4:
        fillColor = 'rgba(255, 255, 0, 1)';
      break;
      case 5:
        fillColor = 'rgba(255, 0, 0, 1)';
      break;
    }
    style = new ol.style.Style({
      stroke: new ol.style.Stroke({
        color: 'rgba(255, 255, 255, 0.8)',
        width: 1
      }),
      fill: new ol.style.Fill({
        color: fillColor
      })
    });
    styleCache[rangeLevel] = style;
  }
  return style;
};

var levelRange = {};

$.getJSON('edu.json', {}, function(r) {
  edu = r;
  //to calculate the range for each item
  for(vid in edu) {
    for(level in edu[vid]) {
      if(!levelRange[level]) {
        levelRange[level] = {
          max: 0,
          min: 100000
        }
      }
      if(edu[vid][level] > levelRange[level].max) {
        levelRange[level].max = edu[vid][level];
      }
      if(edu[vid][level] < levelRange[level].min) {
        levelRange[level].min = edu[vid][level];
      }
    }
  }
  for(k in levelRange) {
    var levelStep = Math.round((levelRange[k].max - levelRange[k].min) / 5, 0);
    levelRange[k].v1 = levelStep;
    levelRange[k].v2 = levelStep * 2;
    levelRange[k].v3 = levelStep * 3;
    levelRange[k].v4 = levelStep * 4;
  }
  var firstKey = Object.keys(edu)[0];
  var pKeys = Object.keys(edu[firstKey]);

  var block = $('#levelButtons');
  for(k in pKeys) {
    block.append('<a href="#" class="btn btn-default levelSwitch" data-id="' + pKeys[k] + '">' + pKeys[k] + '</a>');
  }
  $('a.levelSwitch').click(function() {
    $('a.levelSwitch').removeClass('btn-primary');
    $(this).addClass('btn-primary');
    levelId = $(this).attr('data-id');
    var levelRangeBlock = $('#levelRange');
    var message = '';
    message += 'min: ' + levelRange[levelId].min + ' &nbsp; ';
    message += 'v1: ' + levelRange[levelId].v1 + ' &nbsp; ';
    message += 'v2: ' + levelRange[levelId].v2 + ' &nbsp; ';
    message += 'v3: ' + levelRange[levelId].v3 + ' &nbsp; ';
    message += 'v4: ' + levelRange[levelId].v4 + ' &nbsp; ';
    message += 'max: ' + levelRange[levelId].max + ' &nbsp; ';
    levelRangeBlock.html(message);
    map.getLayers().forEach(function(layer) {
      if(layer.get('id') === 'zoneLayer') {
        layer.setStyle(styleFunction);
      }
    });
    return false;
  });

})
