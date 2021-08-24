/* jshint esversion: 6 */
/* globals Routing, $ */

$(document).ready(function() {
  var communicationsTable = $('#communicationsTable').DataTable({
    responsive: true,
    fixedHeader: true,
    order: [
      [0, 'desc']
    ]
  });
});
