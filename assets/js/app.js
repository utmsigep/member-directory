/* jshint esversion: 6 */

// Javascript
const $ = require('jquery');
global.$ = global.jQuery = $;
require('@popperjs/core');
require('select2');
require('bootstrap');
require('bootstrap-autocomplete');
require('datatables.net');
require('datatables.net-bs5');
require('datatables.net-buttons');
require('datatables.net-buttons/js/buttons.html5.js');
require('datatables.net-buttons-bs5');
require('datatables.net-fixedheader');
require('datatables.net-fixedheader-bs5');
require('datatables.net-responsive');
require('datatables.net-responsive-bs5');
require('tablednd');
require('@fortawesome/fontawesome-free/js/all.js');
global._ = require('underscore');

// Routing
const routes = require('../js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

// CSS
require('bootstrap/dist/css/bootstrap.css');
require('select2/dist/css/select2.css');
require('select2-bootstrap-5-theme/dist/select2-bootstrap-5-theme.css');
require('datatables.net-bs5/css/dataTables.bootstrap5.css');
require('datatables.net-fixedheader-bs5/css/fixedHeader.bootstrap5.css');
require('datatables.net-responsive-bs5/css/responsive.bootstrap5.css');
require('@fortawesome/fontawesome-free/css/all.css');
require('../css/app.scss');

// Hotkeys
import hotkeys from 'hotkeys-js';

// Wait for document load
$(function () {

  // Tooltips
  $('[data-bs-toggle="tooltip"]').tooltip();

  // Bootstrap SelectPicker
  $('.selectpicker[multiple]').select2({});
  $('.selectpicker').not('[multiple]').select2({
    theme: 'bootstrap-5',
  });

  // Hide sidebar on mobile
  if ($(window).width() < 768) {
    $('#sidebarToggleTop').trigger('click');
  }

  // Search field autocomplete
  $('.member-search-autocomplete').autoComplete({
      resolverSettings: {
          url: Routing.generate('search_autocomplete')
      },
      preventEnter: true,
      formatResult: function (item) {
        return {
          text: item.displayName.replace(/[^\w. ]/gi, function (c) {
            return '&#' + c.charCodeAt(0) + ';';
          })
        };
      }
  });
  $('.member-search-autocomplete').on('autocomplete.select', function(event, item) {
    event.preventDefault();
    window.location.href = Routing.generate('member_show', {localIdentifier: item.localIdentifier});
  });

  // File uploads
  $('.custom-file-input').on('change', function(event) {
      var inputFile = event.currentTarget;
      $(inputFile).parent()
        .find('.custom-file-label')
        .html(inputFile.files[0].name);
  });

  // Toggle a long list of related checkboxes
  $('.column-check-toggle').on('click', function (event) {
    event.preventDefault();
    var allString = $(event.target).data('all-string');
    var noneString = $(event.target).data('none-string');
    var targets = $(event.target).data('targets');
    var status = $(event.target).data('status') == true;
    $('input[name="' + targets + '"]').prop('checked', status);
    if (status) {
      $(event.target).html('Select ' + noneString);
      $(event.target).data('status', false);
    } else {
      $(event.target).html('Select ' + allString);
      $(event.target).data('status', true);
    }
  });

  // Show privacy warning once per day
  if (typeof localStorage != undefined) {
    var privacyWarning = localStorage.getItem('privacyWarning') || 0;
    if (parseInt(privacyWarning, 10) < Date.now() - (1000 * 60 * 60 * 24)) {
      $('#modalConfidential').modal('show');
      localStorage.setItem('privacyWarning', Date.now());
    }
  }

  // Hotkeys
  hotkeys('/,shift+/', function(event, handler) {
    event.preventDefault();
    console.log(handler.key);
    switch(handler.key) {
      case 'shift+/':
        $('#modalHotkeys').modal('show');
        break;
      case '/':
        const searchField = document.querySelector("#page-top > form > header > input")
        searchField.focus();
        searchField.select();
        break;
    }
  });

});
