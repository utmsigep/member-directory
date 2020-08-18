// Javascript
const $ = require('jquery')
global.$ = global.jQuery = $
require('popper.js')
require('bootstrap')
require('bootstrap-select')
require('bootstrap-autocomplete')
require('../../node_modules/startbootstrap-sb-admin-2/js/sb-admin-2.js')
require('../../node_modules/datatables.net/js/jquery.dataTables.js')
require('datatables.net-buttons')
require('../../node_modules/datatables.net-buttons/js/buttons.html5.js')
require('datatables.net-buttons-bs4')
require('../../node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js')
require('../../node_modules/@fortawesome/fontawesome-free/js/all.js')
global._ = require('underscore')

// Routing
const routes = require('../js/fos_js_routes.json');
import Routing from '../../vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.min.js';
Routing.setRoutingData(routes);

// CSS
require('../../node_modules/bootstrap/dist/css/bootstrap.css')
require('../../node_modules/startbootstrap-sb-admin-2/scss/sb-admin-2.scss')
require('../../node_modules/bootstrap-select/dist/css/bootstrap-select.css')
require('../../node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css')
require('../../node_modules/@fortawesome/fontawesome-free/css/all.css')
require('../css/app.scss')

// Tooltips
$('[data-toggle="tooltip"]').tooltip();

// Bootstrap SelectPicker
$('.selectpicker').selectpicker();

// Hide sidebar on mobile
if ($(window).width() < 768) {
  $('#sidebarToggleTop').trigger('click')
}

// Search field autocomplete
$(document).ready(function () {
  $('.member-search-autocomplete').autoComplete({
      resolverSettings: {
          url: Routing.generate('search_autocomplete')
      },
      formatResult: function (item) {
        return {
          text: item.displayName
        }
      }
  });
  $('.member-search-autocomplete').on('autocomplete.select', function(evt, item) {
    window.location.href = Routing.generate('member_show', {localIdentifier: item.localIdentifier})
  })
})

// File uploads
$('.custom-file-input').on('change', function(event) {
    var inputFile = event.currentTarget;
    $(inputFile).parent()
      .find('.custom-file-label')
      .html(inputFile.files[0].name);
});

// Show privacy warning once per day
if (typeof localStorage != undefined) {
  var privacyWarning = localStorage.getItem('privacyWarning') || 0;
  if (parseInt(privacyWarning, 10) < Date.now() - (1000 * 60 * 60 * 24)) {
    $('#modalConfidential').modal('show')
    localStorage.setItem('privacyWarning', Date.now());
  }
}
