/* jshint esversion: 6 */

// Javascript
const $ = require('jquery');
global.$ = global.jQuery = $;
require('popper.js');
require('startbootstrap-sb-admin-2/vendor/bootstrap/js/bootstrap.js');
require('select2');
require('bootstrap-autocomplete');
require('startbootstrap-sb-admin-2/js/sb-admin-2.js');
require('datatables.net');
require('datatables.net-bs4');
require('datatables.net-buttons');
require('datatables.net-buttons/js/buttons.html5.js');
require('datatables.net-buttons-bs4');
require('datatables.net-fixedheader');
require('datatables.net-fixedheader-bs4');
require('datatables.net-responsive');
require('datatables.net-responsive-bs4');
require('tablednd');
require('@fortawesome/fontawesome-free/js/all.js');
global._ = require('underscore');

// Routing
const routes = require('../js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

// CSS
require('startbootstrap-sb-admin-2/scss/sb-admin-2.scss');
require('select2/dist/css/select2.css');
require('@ttskch/select2-bootstrap4-theme/dist/select2-bootstrap4.css');
require('datatables.net-bs4/css/dataTables.bootstrap4.css');
require('datatables.net-fixedheader-bs4/css/fixedHeader.bootstrap4.css');
require('datatables.net-responsive-bs4/css/responsive.bootstrap4.css');
require('@fortawesome/fontawesome-free/css/all.css');
require('../css/app.scss');

// Wait for document load
$(function () {

  // Tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Bootstrap SelectPicker
  $('.selectpicker[multiple]').select2({});
  $('.selectpicker').not('[multiple]').select2({
    theme: 'bootstrap4',
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
      formatResult: function (item) {
        return {
          text: item.displayName.replace(/[^\w. ]/gi, function (c) {
            return '&#' + c.charCodeAt(0) + ';';
          })
        };
      }
  });
  $('.member-search-autocomplete').on('autocomplete.select', function(evt, item) {
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

});
