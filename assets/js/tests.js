var SeoSerpTests = {
    /**
     * Open an iframe in a modal window
     *
     * @param {object} options An optional options object
     */
    openModalIframe: function(options) {
        var opt = options || {};
        var max = (window.getSize().y-180).toInt();
        if (!opt.height || opt.height > max) opt.height = max;
        var M = new SimpleModal({
            'width': opt.width,
            'hideFooter': true,
            'draggable': false,
            'overlayOpacity': .5,
            'onShow': function() { document.body.setStyle('overflow', 'hidden'); },
            'onHide': function() {
                document.body.setStyle('overflow', 'auto');
                AjaxRequest.displayBox(Contao.lang.loading + ' …');
                window.location.reload();
            }
        });
        M.show({
            'title': opt.title,
            'contents': '<iframe src="' + opt.url + '" width="100%" height="' + opt.height + '" frameborder="0"></iframe>'
        });
    }
};