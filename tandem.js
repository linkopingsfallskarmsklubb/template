jQuery(document).ready(function() {
  function giftcard_toggle() {
    if (jQuery('#pay-giftcard').prop('checked')) {
      jQuery('#book-giftcard').show();
    } else {
      jQuery('#book-giftcard').hide();
    }
  }

  jQuery('#pay-giftcard').click(giftcard_toggle);
  jQuery('#pay-now').click(giftcard_toggle);
  jQuery('#pay-later').click(giftcard_toggle);
});
