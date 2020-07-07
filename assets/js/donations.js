$(document).ready(function () {
  $('#donationsTable').DataTable({
    order: [
      [0, 'desc']
    ],
    pageLength: 50
  })
  $('#donorTable').DataTable({
    order: [
      [4, 'desc']
    ],
    pageLength: 50
  })
})
