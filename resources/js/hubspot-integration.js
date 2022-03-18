try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
} catch (e) { console.debug(e); }
require('datatables.net-bs4');
require('datatables.net-buttons-bs4');
require('datatables.net-responsive-bs4');