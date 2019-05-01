// require('leaflet')
// require('../../node_modules/leaflet/dist/leaflet.css')
// require('../../node_modules/leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css')

import 'leaflet/dist/leaflet.css';
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css'; // Re-uses images from ~leaflet package
import * as L from 'leaflet';
import 'leaflet-defaulticon-compatibility';

var mymap = L.map('mapContainer').setView([39.828175, -98.5795], 4);

L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}{r}.png', {
  subdomains: 'abcd',
  maxZoom: 19,
  minZoom: 1,
  attribution: 'Open Street Map',
}).addTo(mymap)

var formatMemberPopup = function (data) {
  return L.Util.template(
    '<strong>{preferredName} {lastName}</strong><br />{localIdentifier} / {status}<hr />{mailingAddressLine1} {mailingAddressLine2}<br />{mailingCity} {mailingState}, {mailingPostalCode}',
    data
  )
}

$.getJSON('/directory/map-data', {}, function(data) {
  $(data).each(function (i, row) {
    L.marker(L.latLng(row.mailingLatitude, row.mailingLongitude), {
      title: row.localIdentifier
    }).bindPopup(formatMemberPopup(row)).addTo(mymap)
  })
})
