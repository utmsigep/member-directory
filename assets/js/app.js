// Javascript
const $ = require('jquery')
global.$ = global.jQuery = $
require('popper.js')
require('bootstrap')
require('../../node_modules/datatables.net/js/jquery.dataTables.js')
require('../../node_modules/datatables.net-bs4/js/dataTables.bootstrap4.js')
global._ = require('underscore')

// CSS
require('../../node_modules/bootstrap/dist/css/bootstrap.css')
require('../../node_modules/datatables.net-bs4/css/dataTables.bootstrap4.css')
require('../css/app.scss')

$('[data-toggle="tooltip"]').tooltip();
