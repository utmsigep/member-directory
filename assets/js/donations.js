$(document).ready(function () {
  $('#donationsTable').DataTable({
    order: [
      [0, 'desc']
    ],
    pageLength: 50
  })
})
