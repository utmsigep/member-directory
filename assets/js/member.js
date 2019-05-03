/* globals Routing, $ */

import 'leaflet/dist/leaflet.css';
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css'; // Re-uses images from ~leaflet package
import * as L from 'leaflet';
import 'leaflet-defaulticon-compatibility';

$(document).ready(function() {
  var mailingLatitude = $('#mapContainer').data('mailing_latitude')
  var mailingLongitude = $('#mapContainer').data('mailing_longitude')
  
  if (!mailingLatitude || !mailingLongitude) {
    return;
  }
  
  var mymap = L.map('mapContainer').setView([mailingLatitude, mailingLongitude], 12);

  L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd',
    maxZoom: 19,
    minZoom: 1,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright" target="blank">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions" target="blank">CartoDB</a>'
  }).addTo(mymap)

  L.marker(L.latLng(mailingLatitude, mailingLongitude)).addTo(mymap)
});
