/* jshint esversion: 6 */
/* globals Routing, $ */

var gravatar = require('gravatar');

var sanitizeHTML = function (str) {
  if (!str) { return ''; }
	return str.replace(/[^\w. ]/gi, function (c) {
		return '&#' + c.charCodeAt(0) + ';';
	});
};

$(document).ready(function() {
  var memberDataTable = $('#memberDataTable').DataTable({
    responsive: {
        details: {
            display: $.fn.dataTable.Responsive.display.childRowImmediate,
            type: 'none',
            target: ''
        }
    },
    fixedHeader: {
      header: true
    },
    serverSide: true,
    processing: true,
    ajax: $('#memberDataTable').data('source'),
    dom: "<'row'<'col-sm-3'l><'col-sm-3'f><'col-sm-6'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    autoWidth: false,
    pageLength: 25,
    searching: false,
    createdRow: function (row, data, dataIndex) {
      if (data.status.isInactive == true) {
        $(row).addClass('inactive');
      }
    },
    drawCallback: function (settings) {
      var groupBy = $('#memberDataTable').data('group-by');
      var groupByCol = false;
      switch (groupBy) {
        case 'classYear':
          groupByCol = 4;
          break;
        case 'status':
          groupByCol = 3;
          break;
        case 'mailingState':
          groupByCol = 9;
          break;
      }
      if (!groupByCol) {
        return;
      }
      var api = this.api();
      var rows = api.rows( {page:'current'} ).nodes();
      var last = null;
      api.column(groupByCol, {page:'current'} ).data().each(function (group, i) {
        if (last !== group) {
          var groupByText = (group) ? group : '(not set)';
          $(rows).eq(i).before(
            '<tr class="h5 text-light bg-dark"><td colspan="99">' + groupByText + '</td></tr>'
          );
          last = group;
        }
      });
    },
    columns: [
      {
        data: "localIdentifier"
      },
      {
        data: "photoUrl",
        orderable: false,
        className: "col-img-profile text-center",
        render: function (data, type, row, meta) {
          var link = Routing.generate('member_show', {localIdentifier: row.localIdentifier});
          var photoUrl = sanitizeHTML(data);
          if (!photoUrl) {
            photoUrl = gravatar.url(row.primaryEmail, {default: 'mm'});
          }
          var output = '<a href="' + link + '">';
          output += '  <img src="' + photoUrl + '" class="img-fluid" alt="Profile Photo" />';
          output += '</a>';
          return output;
        }
      },
      {
        data: "displayName",
        render: function (data, type, row, meta) {
          var link = Routing.generate('member_show', {localIdentifier: row.localIdentifier});
          var output = '<a href="' + link + '">' + sanitizeHTML(data) + '</a><div class="mt-1">';
          // Deceased
          if (row.isDeceased) {
            var deceasedLink = Routing.generate('deceased');
            output += '<a href="' + deceasedLink + '"><sup class="badge badge-dark" title="Deceased">Deceased</sup></a> ';
          }
          // Lost
          if (row.isLost) {
            var lostLink = Routing.generate('lost');
            output += '<a href="' + lostLink + '"><sup class="badge badge-warning" title="Lost">Lost</sup></a> ';
          }
          // Do Not Contact
          if (row.isLocalDoNotContact) {
            var doNotContactLink = Routing.generate('do_not_contact');
            output += '<a href="' + doNotContactLink + '"><sup class="badge badge-danger" title="Do Not Contact">Do Not Contact</sup></a> ';
          }
          // Tags
          if (row.tags) {
            for (var i in row.tags) {
              var tagLink = Routing.generate('tag', {tagId: row.tags[i].id});
              output += '<a href="' + tagLink + '"><sup class="badge badge-secondary" title="' + sanitizeHTML(row.tags[i].tagName) + '">' + sanitizeHTML(row.tags[i].tagName) + '</sup></a> ';
            }
          }
          output += '</div>';
          return output;
        }
      },
      {
        data: "status.label"
      },
      { data: "classYear" },
      {
        data: "primaryEmail",
        render: function (data, type, row, meta) {
          if (!data) {
            return '';
          }
          var link = 'mailto:' + data;
          return '<a href="' + link + '">' + data + '</a>';
        }
      },
      {
        data: "facebookUrl",
        orderable: false,
        className: 'text-nowrap',
        render: function (data, type, row, meta) {
          var output = '';
          if (row.linkedinUrl) {
            var linkedinLink = row.linkedinUrl;
            output += '<a href="' + linkedinLink + '"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>';
          } else {
            output += '<i class="fab fa-linkedin fa-lg fa-fw" style="opacity:0.2"></i>';
          }
          if (row.facebookUrl) {
            var facebookLink = row.facebookUrl;
            output += '<a href="' + facebookLink + '"><i class="fab fa-facebook fa-lg fa-fw"></i></a>';
          } else {
            output += '<i class="fab fa-facebook fa-lg fa-fw" style="opacity:0.2"></i>';
          }
          return output;
        }
      },
      {
        data: "primaryTelephoneNumber"
      },
      {
        data: "mailingPostalCode",
        orderable: false,
        render: function (data, type, row, meta) {
          var output = '';
          if (!row.mailingCity || !row.mailingState) {
            return output;
          }
          if (row.mailingAddressLine1) {
            output += sanitizeHTML(row.mailingAddressLine1) + '<br />';
          }
          if (row.mailingAddressLine2) {
            output += sanitizeHTML(row.mailingAddressLine2) + '<br />';
          }
          output += sanitizeHTML(row.mailingCity) + ', ' + sanitizeHTML(row.mailingState) + ' ' + sanitizeHTML(row.mailingPostalCode) + '<br />';
          if (row.mailingCountry != 'United States' && row.mailingCountry != 'US') {
            output += sanitizeHTML(row.mailingCountry);
          }
          return output;
        }
      },
      {
        data: "mailingState",
        visible: false
      }
    ],
    columnDefs: [
      {
        targets: '_all',
        render: $.fn.dataTable.render.text()
      }
    ]
  });

  if (!$('#memberDataTable').data('show-status')) {
    memberDataTable.column(3).visible(false);
  }

  var toastTemplate = $('#toastTemplate').html();
  $('table[data-draggable]').tableDnD({
    dragHandle: $('.drag_handle'),
    onDrop: function (table, row) {
      var directoryCollectionId = $(row).data('id');
      var position = $(row).index();
      $.ajax({
        type: "POST",
        url: Routing.generate('directory_collection_reorder', {id: directoryCollectionId}),
        data: {position: position},
        success: function(data) {
          var toast = $(toastTemplate);
          $('.toast-header', toast).addClass('bg-success text-light');
          $('.toast-title', toast).html('Reordered!');
          $('.toast-body', toast).html('Refresh the page to see results.');
          $(toast).appendTo(toastContainer);
          $('.toast').toast({
            animation: true,
            autohide: true,
            delay: 5000
          });
          toast.toast('show');
        },
        error: function(data) {
          var toast = $(toastTemplate);
          $('.toast-header', toast).addClass('bg-danger text-light');
          $('.toast-title', toast).html('An error ocurred');
          $('.toast-body', toast).html('Please try again.');
          $(toast).appendTo(toastContainer);
          $('.toast').toast({
            animation: true,
            autohide: true,
            delay: 5000
          });
          toast.toast('show');
        },
        dataType: 'json'
      });
    }
  });
});
