$(document).ready(function () {
  $('a.delete-row-button').on('click', function (el) {
    if (confirm('Delete this change?')) {
      $(el.target).parent().parent().remove();
    }
  })  
})
