var moment = require('moment')

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

  // Date Range Form
  var startDateField = $('#start_date')
  var endDateField = $('#end_date')
  $('a[href="#thisMonth"]').on('click', function() {
    startDateField.val(moment().startOf('month').format('YYYY-MM-DD'))
    endDateField.val(moment().format('YYYY-MM-DD'))
  })
  $('a[href="#thisYear"]').on('click', function() {
    startDateField.val(moment().startOf('year').format('YYYY-MM-DD'))
    endDateField.val(moment().format('YYYY-MM-DD'))
  })
  $('a[href="#lastMonth"]').on('click', function() {
    startDateField.val(moment().subtract(1, 'months').startOf('month').format('YYYY-MM-DD'))
    endDateField.val(moment().subtract(1, 'months').endOf('month').format('YYYY-MM-DD'))
  })
  $('a[href="#lastYear"]').on('click', function() {
    startDateField.val(moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD'))
    endDateField.val(moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD'))
  })
  $('a[href="#thirtyDays"]').on('click', function() {
    startDateField.val(moment().subtract(30, 'days').format('YYYY-MM-DD'))
    endDateField.val(moment().format('YYYY-MM-DD'))
  })
})
