// Directory Collection Icon Field
$('directory_collection_icon').ready(function () {
  var iconOptions = require('./icons.json');
  var iconField = $('#directory_collection_icon');
  var iconDropdown = $('<select id="directory_collection_icon_dropdown" />');
  var inputGroupContainer = $('<div class="input-group"><div class="input-group-text"><i id="directory_collection_icon_display" class="' + iconField.val() + '"></i></div></div>');

  // Build new form field
  inputGroupContainer.children().last().after(iconDropdown);
  iconDropdown.addClass('form-control');
  $(iconOptions).each(function (i, icon) {
    iconDropdown.append('<option value="fas fa-' + icon.value + '">' + icon.label + '</option>');
  });

  // Load initial icon
  iconDropdown.val(function (i, val) {
    return iconField.val();
  }).trigger('change');

  // Update icon when changed
  iconDropdown.on('change', function(e) {
    iconField.val($(e.target).val());
    $('#directory_collection_icon_display').attr('class', $(e.target).val());
  });

  // Hide actual field, add field group
  iconField.hide().after(inputGroupContainer);
});
