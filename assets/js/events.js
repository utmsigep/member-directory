/* jshint esversion: 6 */
/* globals Routing, $ */

$(document).ready(function() {
  var communicationsTable = $('#eventsTable').DataTable({
    responsive: true,
    fixedHeader: true,
    order: [
      [1, 'desc']
    ]
  });
});
