var SeoSerpTests = {
    /**
     * Color the full record rows based on the message colors they have inside
     */
    colorRows: function () {
        var priority = ['error', 'warning'];
        var els = document.getElements('[data-seo-serp-messages]');

        for (var i = 0; i < els.length; i++) {
            var row = els[i].getParent('.tl_file') || els[i].getParent('.tl_content');

            if (!row) {
                continue;
            }

            var rowType = null;
            var messageType = null;
            var messages = els[i].getElements('[data-seo-serp-message]');

            // Determine the row type including the message type priority
            for (var j = 0; j < messages.length; j++) {
                messageType = messages[j].get('data-seo-serp-message');

                if (!rowType || priority.indexOf(messageType) < priority.indexOf(rowType)) {
                    rowType = messageType;
                }
            }

            if (rowType) {
                row.addClass('seo-serp-test-' + rowType + '-row');
            }
        }
    }
};

window.addEvent('domready', function () {
    SeoSerpTests.colorRows();
});

window.addEvent('ajax_change', function () {
    SeoSerpTests.colorRows();
});