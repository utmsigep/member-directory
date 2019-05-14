/* globals Routing, $ */

import 'leaflet/dist/leaflet.css';
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css'; // Re-uses images from ~leaflet package
import * as L from 'leaflet';
import 'leaflet-defaulticon-compatibility';

// Routing
const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

var drawMap = function () {
  var mymap = L.map('mapContainer').setView([39.828175, -98.5795], 4);
  L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd',
    maxZoom: 19,
    minZoom: 1,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright" target="blank">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions" target="blank">CartoDB</a>'
  }).addTo(mymap)

  L.control.scale().addTo(mymap);

  // Search Radius Controls
  var circle = {}
  var searchResultsContainer = $('#searchResults')
  mymap.on('click', function(ev) {
      // Remove last circle drawn
      mymap.removeLayer(circle)
      // Draw circle and radius
      var radius = parseInt($('#search-radius').val(), 10)
      circle = L.circle(ev.latlng, {
          color: '#999',
          fillColor: '#000',
          fillOpacity: 0.1,
          radius: (radius * 1609.344)
      }).addTo(mymap);
      $.getJSON(Routing.generate('map_search', {latitude: ev.latlng.lat, longitude: ev.latlng.lng, radius: radius}), {}, function(data) {
          searchResultsContainer.empty()
          if (data.length > 0) {
            $(data).each(function (i, row) {
              var result = {
                fullName: row[0].preferredName + ' ' + row[0].lastName,
                localIdentifierShort: row[0].localIdentifierShort,
                distance: row.distance,
                status: row[0].status.label,
                link: Routing.generate('member', {localIdentifier: row[0].localIdentifier}),
                photoUrl: row[0].photoUrl,
                tags: formatTags(row[0])
              }
              searchResultsContainer.append(L.Util.template(
                '<div class="card mb-1"><div class="card-body"><div class="float-left w-25 mr-2"><img src="{photoUrl}" class="img-fluid" /></div><strong><a href="{link}">{fullName}</a></strong>{tags}<br />{localIdentifierShort} / {status}</div></div>', result
              ))
            })
            searchResultsContainer.append($('<p class="text-center"><strong>Records:</strong> ' + data.length + '</p>'))
          }
      })
  })
  
  // Make initial data call
  $.getJSON(Routing.generate('map_data'), {}, function(data) {
    $(data).each(function (i, row) {
      row.statusLabel = row.status.label
      L.marker(L.latLng(row.mailingLatitude, row.mailingLongitude)).bindTooltip(formatMemberTooltip(row)).bindPopup(formatMemberPopup(row)).addTo(mymap)
    })
  })
}

var formatMemberPopup = function (data) {
  data.statusLabel = data.status.label
  data.mailingAddressLine2 = data.mailingAddressLine2 ? data.mailingAddressLine2 : ''
  data.link = Routing.generate('member', {localIdentifier: data.localIdentifier})
  data.tags = formatTags(data)
  return L.Util.template(
    '<strong><a href="{link}">{preferredName} {lastName}</a></strong>{tags}<br />{localIdentifierShort} / {statusLabel}<hr />{mailingAddressLine1} {mailingAddressLine2}<br />{mailingCity} {mailingState}, {mailingPostalCode}',
    data
  )
}

var formatMemberTooltip = function (data) {
  data.statusLabel = data.status.label
  data.tags = formatTags(data)
  return L.Util.template(
    '<strong>{preferredName} {lastName}</strong>{tags}<br />{localIdentifierShort} / {statusLabel}',
    data
  )
}

var formatTags = function (data) {
  var tags = ''
  if (data.isLost) {
    tags += '<span class="badge badge-warning mr-1">Lost</span>'
  }
  if (data.isDeceased) {
    tags += '<span class="badge badge-dark mr-1">Deceased</span>'
  }
  if (data.isLocalDoNotContact) {
    tags += '<span class="badge badge-warning mr-1">Do Not Contact (National)</span>'
  }
  if (data.isLocalDoNotContact) {
    tags += '<span class="badge badge-danger mr-1">Do Not Contact (Local)</span>'
  }
  if (tags) {
    tags = '<br />' + tags
  }
  return tags
}

$(document).ready( function () {
  drawMap()
})