/* jshint esversion: 6 */
/* globals Routing, $ */

var moment = require('moment');

$(document).ready(function () {
  var exportButtons = [
    {
      text: '<i class="fas fa-save fa-fw"></i> Download CSV',
      extend: 'csvHtml5'
    },
    {
      text: '<i class="fas fa-clipboard fa-fw"></i> Copy to Clipboard',
      extend: 'copyHtml5'
    }
  ];

  var donationsTable = $('#donationsTable').DataTable({
    responsive: true,
    fixedHeader: true,
    buttons: exportButtons,
    order: [
      [0, 'desc']
    ],
    pageLength: 50
  });
  donationsTable.buttons().container().addClass('d-block text-center py-3').appendTo(donationsTable.table().container());

  var donorTable = $('#donorTable').DataTable({
    responsive: true,
    fixedHeader: true,
    buttons: exportButtons,
    order: [
      [5, 'desc']
    ],
    pageLength: 50
  });
  donorTable.buttons().container().addClass('d-block text-center py-3').appendTo(donorTable.table().container());

  var campaignTable = $('#campaignTable').DataTable({
    responsive: true,
    fixedHeader: true,
    buttons: exportButtons,
    order: [
      [6, 'desc']
    ],
    pageLength: 50
  });
  campaignTable.buttons().container().addClass('d-block text-center py-3').appendTo(campaignTable.table().container());

  // Date Range Form
  var startDateField = $('#start_date');
  var endDateField = $('#end_date');
  $('a[href="#thisMonth"]').on('click', function() {
    startDateField.val(moment().startOf('month').format('YYYY-MM-DD'));
    endDateField.val(moment().format('YYYY-MM-DD'));
  });
  $('a[href="#thisYear"]').on('click', function() {
    startDateField.val(moment().startOf('year').format('YYYY-MM-DD'));
    endDateField.val(moment().format('YYYY-MM-DD'));
  });
  $('a[href="#lastMonth"]').on('click', function() {
    startDateField.val(moment().subtract(1, 'months').startOf('month').format('YYYY-MM-DD'));
    endDateField.val(moment().subtract(1, 'months').endOf('month').format('YYYY-MM-DD'));
  });
  $('a[href="#lastYear"]').on('click', function() {
    startDateField.val(moment().subtract(1, 'year').startOf('year').format('YYYY-MM-DD'));
    endDateField.val(moment().subtract(1, 'year').endOf('year').format('YYYY-MM-DD'));
  });
  $('a[href="#thirtyDays"]').on('click', function() {
    startDateField.val(moment().subtract(30, 'days').format('YYYY-MM-DD'));
    endDateField.val(moment().format('YYYY-MM-DD'));
  });
});
