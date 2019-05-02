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
  attribution: '&copy; <a href="http://www.openstreetmap.org/copyright" target="blank">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions" target="blank">CartoDB</a>'
}).addTo(mymap)

var formatMemberPopup = function (data) {
  data.statusLabel = data.status.label
  data.mailingAddressLine2 = data.mailingAddressLine2 ? data.mailingAddressLine2 : ''
  return L.Util.template(
    '<strong>{preferredName} {lastName}</strong><br />{localIdentifier} / {statusLabel}<hr />{mailingAddressLine1} {mailingAddressLine2}<br />{mailingCity} {mailingState}, {mailingPostalCode}',
    data
  )
}

var formatMemberTooltip = function (data) {
  data.statusLabel = data.status.label
  return L.Util.template(
    '<strong>{preferredName} {lastName}</strong><br />{localIdentifier} / {statusLabel}',
    data
  )
}

$.getJSON('/directory/map-data', {}, function(data) {
  $(data).each(function (i, row) {
    row.statusLabel = row.status.label
    L.marker(L.latLng(row.mailingLatitude, row.mailingLongitude)).bindTooltip(formatMemberTooltip(row)).bindPopup(formatMemberPopup(row)).addTo(mymap)
  })
})
