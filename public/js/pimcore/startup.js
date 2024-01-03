pimcore.registerNS("pimcore.plugin.StarfruitHelperBundle");

pimcore.plugin.StarfruitHelperBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
    },

    pimcoreReady: function (e) {
        // alert("StarfruitHelperBundle ready!");
    }
});

var StarfruitHelperBundlePlugin = new pimcore.plugin.StarfruitHelperBundle();
