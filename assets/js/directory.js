/* globals Routing, $ */

$(document).ready(function() {
  $('#memberTable').DataTable({
  	dom: "<'row'<'col-sm-3'l><'col-sm-3'f><'col-sm-6'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    pageLength: 50,
    columnDefs: [
      {
        targets: [1],
        orderable: false,
        searchable: false
      }
    ]
  });
});
