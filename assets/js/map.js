/* jshint esversion: 6 */
/* globals Routing, $ */

import 'leaflet/dist/leaflet.css';
import 'leaflet-defaulticon-compatibility/dist/leaflet-defaulticon-compatibility.webpack.css'; // Re-uses images from ~leaflet package
import * as L from 'leaflet';
import 'leaflet-defaulticon-compatibility';

// Routing
const routes = require('../js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

var sanitizeHTML = function (str) {
  if (!str) { return ''; }
	return str.replace(/[^\w. ]/gi, function (c) {
		return '&#' + c.charCodeAt(0) + ';';
	});
};

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

var memberMarkers = [];
var defaultIcon = new markerIcon();

var memberStatuses = (function() {
  var a = [];
  $('input[name="map_filter[status][]"]:checked').each(function() {
    a.push(this.value);
  });
  return a;
});

var drawMap = function () {
  $('#mapContainerLoading').show();
  var directoryMap = L.map('mapContainer').setView([39.828175, -98.5795], 4);
  L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}{r}.png', {
    subdomains: 'abcd',
    maxZoom: 19,
    minZoom: 1,
    attribution: '&copy; <a href="http://www.openstreetmap.org/copyright" target="blank">OpenStreetMap</a> &copy; <a href="http://cartodb.com/attributions" target="blank">CartoDB</a>'
  }).addTo(directoryMap);

  L.control.scale().addTo(directoryMap);

  // Search Radius Controls
  var circle = L.circle();
  var searchResultsContainer = $('#searchResults');
  var searchNoResultsFoundContainer = $('#searchNoResultsFound');
  var searchResultCountContainer = $('#searchResultCount');
  var searchResultsExport = $('#searchResultsExport');
  var mapExportButton = $('#mapExportButton');
  directoryMap.on('click', function(ev) {
      // Remove last circle drawn
      directoryMap.removeLayer(circle);
      // Draw circle and radius
      var radius = parseInt($('#search-radius').val(), 10);
      circle = L.circle(ev.latlng, {
          color: '#999',
          fillColor: '#000',
          fillOpacity: 0.1,
          radius: (radius * 1609.344)
      }).addTo(directoryMap);
      mapExportButton.attr('href', Routing.generate('export_by_location', {latitude: ev.latlng.lat, longitude: ev.latlng.lng, radius: radius, member_statuses: memberStatuses()}));
      $.getJSON(Routing.generate('map_search', {latitude: ev.latlng.lat, longitude: ev.latlng.lng, radius: radius, member_statuses: memberStatuses()}), {}, function(data) {
          searchResultsContainer.empty();
          searchNoResultsFoundContainer.show();
          searchResultsExport.hide();
          searchResultCountContainer.html(0);
          if (data.length > 0) {
            searchResultCountContainer.html(data.length);
            searchNoResultsFoundContainer.hide();
            searchResultsExport.show();
            $(data).each(function (i, row) {
              var item = formatMemberData(row);
              searchResultsContainer.append(L.Util.template(
                '<div><div><strong><a href="{link}" target="_blank">{displayName}</a> {classYear}</strong> {tags}</div> {localIdentifier} / {statusLabel}</div><hr />', item
              ));
            });
          }
      });
  });

  var addMarkers = function () {
    searchResultsExport.hide();
    $.getJSON(Routing.generate('map_data'), {member_statuses: memberStatuses()}, function(data) {
      if (data.length === 0) {
        return;
      }
      $(data).each(function (i, row) {
        var marker = L.marker(L.latLng(row.mailingLatitude, row.mailingLongitude)).setIcon(defaultIcon);
        row = formatMemberData(row);
        marker.bindTooltip(formatMemberTooltip(row)).bindPopup(formatMemberPopup(row)).addTo(directoryMap);
        memberMarkers.push(marker);
      });
    })
      // Fit map bounds based on markers on screen
      .done(function () {
        var group = new L.featureGroup(memberMarkers);
        if (group.getLayers().length > 0) {
          directoryMap.fitBounds(group.getBounds());
        }
        $('#mapContainerLoading').hide();
      });
  };

  // Draw initial markers
  addMarkers();

  // Change map with filter update
  $('form[name="map_filter"]').on('change', function (form) {
    // Require at least one box to be checked
    if ($('input[name="map_filter[status][]"]:checked').length === 0) {
      $('input[name="map_filter[status][]"]').first().prop('checked', true);
    }
    // Clear out previouis markers
    $(memberMarkers).each(function (i, marker) {
      marker.remove();
    });
    circle.remove();
    searchResultsContainer.empty();
    searchNoResultsFoundContainer.show();
    searchResultsExport.hide();
    searchResultCountContainer.html(0);
    addMarkers();
  });
};

var formatMemberData = function (data) {
  data.displayName = sanitizeHTML(data.displayName);
  data.statusLabel = sanitizeHTML(data.status.label);
  data.mailingAddressLine1 = data.mailingAddressLine1 ? sanitizeHTML(data.mailingAddressLine1) : '';
  data.mailingAddressLine2 = data.mailingAddressLine2 ? sanitizeHTML(data.mailingAddressLine2) : '';
  data.mailingCity = data.mailingCity ? sanitizeHTML(data.mailingCity) : '';
  data.mailingState = data.mailingState ? sanitizeHTML(data.mailingState) : '';
  data.mailingPostalCode = data.mailingPostalCode ? sanitizeHTML(data.mailingPostalCode) : '';
  data.link = Routing.generate('member_show', {localIdentifier: data.localIdentifier});
  data.classYear = data.classYear ? '(' + data.classYear + ')' : '';
  data.tags = formatTags(data);
  return data;
};

var formatMemberPopup = function (data) {
  return L.Util.template(
    '<div><div><strong><a href="{link}" target="_blank">{displayName}</a> {classYear}</strong> {tags}</div> {localIdentifier} / {statusLabel}<hr />{mailingAddressLine1} {mailingAddressLine2}<br />{mailingCity}, {mailingState} {mailingPostalCode}</div>',
    data
  );
};

var formatMemberTooltip = function (data) {
  return L.Util.template(
    '<div><div><strong>{displayName} {classYear}</strong> {tags}</div> {localIdentifier} / {statusLabel}</div>',
    data
  );
};

var formatTags = function (data) {
  var tags = '';
  if (data.isLost) {
    tags += '<span class="badge badge-warning mr-1">Lost</span>';
  }
  if (data.isDeceased) {
    tags += '<span class="badge badge-dark mr-1">Deceased</span>';
  }
  if (data.isLocalDoNotContact) {
    tags += '<span class="badge badge-danger mr-1">Do Not Contact</span>';
  }
  if (data.tags.length > 0) {
    $(data.tags).each(function (i, tag) {
      var tagName = sanitizeHTML(tag.tagName);
      tags += '<span class="badge badge-secondary mr-1">' + tagName + '</span>';
    });
  }
  if (!tags) {
    return '';
  }
  return '<div>' + tags + '</div>';
};

// Prevent enter press from submitting form
$('#search-radius-form').on('keyup keypress', function(e) {
  var keyCode = e.keyCode || e.which;
  if (keyCode === 13) {
    e.preventDefault();
    return false;
  }
});

$(document).ready( function () {
  drawMap();
});
