/* globals Routing, $ */

$(document).ready(function() {
  $('#memberTable').DataTable({
  	dom: "<'row'<'col-sm-3'l><'col-sm-3'f><'col-sm-6'p>>" +
      "<'row'<'col-sm-12'tr>>" +
      "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    pageLength: 50,
    searching: false
  });

  var toastTemplate = $('#toastTemplate').html()
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
          var toast = $(toastTemplate)
          $('.toast-header', toast).addClass('bg-success text-light')
          $('.toast-title', toast).html('Reordered!')
          $('.toast-body', toast).html('Refresh the page to see results.')
          $(toast).appendTo(toastContainer)
          $('.toast').toast({
            animation: true,
            autohide: true,
            delay: 5000
          })
          toast.toast('show')
        },
        error: function(data) {
          var toast = $(toastTemplate)
          $('.toast-header', toast).addClass('bg-danger text-light')
          $('.toast-title', toast).html('An error ocurred')
          $('.toast-body', toast).html('Please try again.')
          $(toast).appendTo(toastContainer)
          $('.toast').toast({
            animation: true,
            autohide: true,
            delay: 5000
          })
          toast.toast('show')
        },
        dataType: 'json'
      });
    }
  })
});
