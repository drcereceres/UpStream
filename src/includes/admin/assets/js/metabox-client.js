(function(window, document, $, undefined) {
  'use strict';

  if (!$) {
    console.error('UpStream requires jQuery.');
  }

  // Make the Client Name field required.
  (function() {
    var titleWrap = $('#titlewrap');
    var titleLabel = $('#title-prompt-text', titlewrap);

    titleLabel.text(titleLabel.text() + ' *');

    $('#title', titlewrap).attr('required', 'required');
  })();

  $(document).ready(function() {
    // @todo
  });
})(window, window.document, jQuery || null);
