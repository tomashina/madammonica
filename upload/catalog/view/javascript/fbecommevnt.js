(function (window) {
  'use strict';

  // Compatibility bridge for event snippets inserted by the legacy OpenCart
  // modification. Page/product/cart tracking lives in meta-pixel.js.
  window.fbecommevnt = {
    trackevent: function (productId, eventName, standardEvent) {
      if (!window.mmMetaPixel) return;

      if (standardEvent === 1) {
        window.mmMetaPixel.productEvent(productId, eventName, 1);
      }
    },

    checkoutfunnel: function (stepName, cartData, addPaymentInfo) {
      if (!window.mmMetaPixel) return;

      if (stepName) window.mmMetaPixel.trackCustom(stepName, cartData || {});
      if (addPaymentInfo === 1) window.mmMetaPixel.track('AddPaymentInfo', cartData || {});
    },

    // Kept as no-ops so cached third-party snippets cannot re-attach the old
    // click handlers that fired AddToCart before OpenCart confirmed success.
    applyevent: function () {},
    initjson: function () {}
  };
}(window));
