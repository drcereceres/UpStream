const wpPot = require('wp-pot');

const PLUGIN_NAME = 'UpStream';
const LANGUAGE_DOMAIN = 'upstream';

wpPot({
  destFile: `./src/languages/${LANGUAGE_DOMAIN}.pot`,
  domain: LANGUAGE_DOMAIN,
  package: PLUGIN_NAME,
  src: [
    './src/**/*.php',
    '!./src/includes/libraries'
  ]
});
