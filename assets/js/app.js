// Javascript
const $ = require('jquery')
global.$ = global.jQuery = $
require('popper.js')
require('bootstrap')
global._ = require('underscore')

// CSS
require('../../node_modules/bootstrap/dist/css/bootstrap.css')
require('../css/app.scss')

$('[data-toggle="tooltip"]').tooltip();
