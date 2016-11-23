'use strict';

var $ = global.jQuery = require('jquery');
var bootstrap = require('bootstrap');  // eslint-disable-line no-unused-vars
var parsley = require('parsleyjs');  // eslint-disable-line no-unused-vars

// Add non-English locales to Parsley
// require('parsleyjs/dist/i18n/fr');
// Use one of the locales right now instead of the last loaded
// parsley.setLocale('fr');

$().ready(function() {
  // Integrate Parsley with Twitter Bootstrap
  // Initially inspired by https://gist.github.com/askehansen/6809825
  // ...and http://jimmybonney.com/articles/parsley_js_twitter_bootstrap/
  // CAUTION: $.fn.parsley.defaults({...}) was IGNORED.
  $('form').parsley({
    excluded    : 'input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden',  // eslint-disable-line max-len
    successClass: 'has-success',
    errorClass  : 'has-error',
    classHandler: function(el) {
      // This differs from all examples I could find!
      return $(el.$element).closest('.form-group');
    },
    errorsContainer: function() {},
    errorsWrapper  : '<span class="help-block"></span>',
    errorTemplate  : '<span></span>'
  });
});

