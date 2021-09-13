/* jshint esversion: 6 */
/* globals Routing, $ */

$(document).ready(function() {
  var eventsTable = $('#eventsTable').DataTable({
    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            type: 'none',
            target: ''
        }
    },
    fixedHeader: true,
    order: [
      [1, 'desc']
    ]
  });
});
