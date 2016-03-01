var SeoSerpTests = new Class({
    Implements: [Options],

    /**
     * Default options
     */
    options: {
        'itemsSelector': '[data-page-id]',
        'messagesSelector': '.messages',
        'classPending': 'tl_pending',
        'classInfo': 'tl_info',
        'classSuccess': 'tl_confirm',
        'classError': 'tl_error'
    },

    /**
     * Initialize the class
     *
     * @param {object} el
     * @param {object} data
     * @param {array}  tests
     * @param {object} options
     */
    initialize: function (el, data, tests, options) {
        this.el = el;
        this.data = data;
        this.tests = tests;
        this.setOptions(options);

        // Collect the items
        this.items = el.getElements(this.options.itemsSelector);

        // Run the tests
        this.runTests();
    },

    /**
     * Run the tests
     */
    runTests: function () {
        for (var i = 0; i < this.items.length; i++) {
            var status = null;
            var errors = [];
            var item = this.items[i];

            // Run the tests for this element
            for (var j = 0; j < this.tests.length; j++) {
                try {
                    status = this.tests[j].call(item, this.data[item.get('data-page-id')]);
                } catch (e) {
                    errors.push(e);
                }
            }

            // Remove the pending class
            item.removeClass(this.options.classPending);

            // There are some errors
            if (errors.length > 0) {
                var list = new Element('ul');

                // Add the error CSS class
                item.addClass(this.options.classError);

                for (var k = 0; k < errors.length; k++) {
                    (new Element('li', {'text': errors[k]})).inject(list);
                }

                list.inject(item.getElement(this.options.messagesSelector));
            } else if (status === true) {
                // Set the success status
                item.addClass(this.options.classSuccess);
            } else {
                // Set the info status
                item.addClass(this.options.classInfo);
            }
        }
    }
});