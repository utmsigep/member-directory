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
      },
      {
        targets: [-1],
        orderable: true,
        searchable: false,
        render: function (data, type, row, meta) {
          if (type == "sort" || type == 'type') {
            return data;
          }
          if (!data) {
            return '<span class="badge badge-secondary">Unknown</span>'
          }
          var score = data;
          var scoreString = (parseFloat(data) * 100).toFixed(0);
          var scoreLabel,
              scoreClass;
          if (score > .9) {
            scoreLabel = 'Excellent';
            scoreClass = 'success';
          } else if (score > .8) {
            scoreLabel = 'Good';
            scoreClass = 'success';
          } else if (score > .6) {
            scoreLabel = 'Fair';
            scoreClass = 'warning';
          } else if (score > .4) {
            scoreLabel = 'Poor';
            scoreClass = 'danger';
          } else {
            scoreLabel = 'Very Poor';
            scoreClass = 'danger';
          }
          return '<span class="badge badge-' + scoreClass + '" title="' + score.toString() +'">' + scoreLabel + ': ' + scoreString + '</span>'
        }
      }
    ]
  });
});
