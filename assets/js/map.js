/* globals Routing, $ */

import 'leaflet/dist/leaflet.css';
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css'; // Re-uses images from ~leaflet package
import * as L from 'leaflet';
import 'leaflet-defaulticon-compatibility';

// Routing
const routes = require('../../public/js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

var markerIcon = L.Icon.extend({
  options: {
    iconUrl:       require('../images/marker-icon.svg'),
    shadowUrl:     require('../images/marker-shadow.png'),
    iconSize:    [12, 20],
    iconAnchor:  [6, 20],
    popupAnchor: [1, -16],
    tooltipAnchor: [8, -24],
    shadowSize:  [20, 20]
  }
});

var defaultIcon = new markerIcon();
var undergraduateIcon = new markerIcon({ iconUrl: require('../images/marker-icon-undergraduate.svg'), });

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
          $('#searchResultCount').html(0)
          if (data.length > 0) {
            $('#searchResultCount').html(data.length)
            $(data).each(function (i, row) {
              var result = {
                fullName: row[0].preferredName + ' ' + row[0].lastName,
                localIdentifierShort: row[0].localIdentifierShort,
                distance: row.distance,
                classYear: row[0].classYear,
                status: row[0].status.label,
                link: Routing.generate('member', {localIdentifier: row[0].localIdentifier}),
                photoUrl: row[0].photoUrl,
                tags: formatTags(row[0])
              }
              searchResultsContainer.append(L.Util.template(
                '<div class="card mb-1"><div class="card-body"><div class="float-left w-25 mr-2"><img src="{photoUrl}" class="img-fluid" /></div><strong><a href="{link}">{fullName}</a> ({classYear})</strong>{tags}<br />{localIdentifierShort} / {status}</div></div>', result
              ))
            })
          }
      })
  })
  
  // Make initial data call
  $.getJSON(Routing.generate('map_data'), {}, function(data) {
    $(data).each(function (i, row) {
      var marker = L.marker(L.latLng(row.mailingLatitude, row.mailingLongitude))
      if (row.status.label == 'Undergraduate') {
        marker.setIcon(undergraduateIcon)
      } else {
        marker.setIcon(defaultIcon)
      }
      marker.bindTooltip(formatMemberTooltip(row)).bindPopup(formatMemberPopup(row)).addTo(mymap)
    })
  })
}

var formatMemberPopup = function (data) {
  data.statusLabel = data.status.label
  data.mailingAddressLine2 = data.mailingAddressLine2 ? data.mailingAddressLine2 : ''
  data.link = Routing.generate('member', {localIdentifier: data.localIdentifier})
  data.tags = formatTags(data)
  return L.Util.template(
    '<strong><a href="{link}">{preferredName} {lastName}</a> ({classYear})</strong>{tags}<br />{localIdentifierShort} / {statusLabel}<hr />{mailingAddressLine1} {mailingAddressLine2}<br />{mailingCity}, {mailingState} {mailingPostalCode}',
    data
  )
}

var formatMemberTooltip = function (data) {
  data.statusLabel = data.status.label
  data.tags = formatTags(data)
  return L.Util.template(
    '<strong>{preferredName} {lastName} ({classYear})</strong>{tags}<br />{localIdentifierShort} / {statusLabel}',
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

// Prevent enter press from submitting form
$('#search-radius-form').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) { 
    e.preventDefault();
    return false;
  }
});

$(document).ready( function () {
  drawMap()
})
