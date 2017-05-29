const wpPot = require('wp-pot');

const PLUGIN_NAME = 'UpStream';
const LANGUAGE_DOMAIN = 'upstream';

/*
TODO:
- Add 'Project-Id-Version' property
- Add 'Report-Msgid-Bugs-To' property
- Add 'POT-Creation-Date' property
- Add 'POT-Revision-Date' property
- Add 'Last-Translator' property
- Maybe add 'Language-Team' property
- Checkout what other properties that might be useful to use
*/

wpPot({
  destFile: `./src/languages/${LANGUAGE_DOMAIN}.pot`,
  domain: LANGUAGE_DOMAIN,
  package: PLUGIN_NAME,
  src: [
    './src/**/*.php',
    '!./src/includes/libraries'
  ]
});
