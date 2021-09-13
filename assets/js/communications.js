/* jshint esversion: 6 */
/* globals Routing, $ */

$(document).ready(function() {
  var communicationsTable = $('#communicationsTable').DataTable({
    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            type: 'none',
            target: ''
        }
    },
    fixedHeader: true,
    order: [
      [0, 'desc']
    ]
  });
});
