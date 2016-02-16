var SeoSerpPreview = new Class({
    Implements: [Options],

    /**
     * Default options
     */
    options: {
        'bodySelector': '.preview-body',
        'hintSelector': '.preview-hint',
        'titleSelector': '[data-ssp-title]',
        'urlSelector': '[data-ssp-url]',
        'descriptionSelector': '[data-ssp-description]',
        'descriptionSiblingClass': 'sibling',
        'counterClass': 'seo-serp-preview-counter',
        'counterLimitClass': 'limit-exceeded',
        'titleLimit': 55,
        'descriptionLimit': 156
    },

    /**
     * Initialize the class
     *
     * @param {object} el
     * @param {object} engine
     * @param {object} options
     */
    initialize: function (el, engine, options) {
        this.el = el;
        this.engine = engine;
        this.setOptions(options);

        // Collect the widget elements
        this.collectElements();

        // Add the description character counter
        this.addDescriptionCounter();

        // Add the event listener
        this.engine.addEvent('change', function () {
            this.refresh.apply(this);
        }.bind(this));

        // Refresh the preview state
        this.refresh();
    },

    /**
     * Add the description character counter to the fields
     */
    addDescriptionCounter: function () {
        this.descriptionCounter = this.engine.addDescriptionCounter(
            new Element('span', {'class': this.options.counterClass})
        );
    },

    /**
     * Update the description character counter
     *
     * @param {string} text
     */
    updateDescriptionCounter: function (text) {
        this.descriptionCounter.set('text', '(' + text.length + '/' + this.options.descriptionLimit + ')');

        if (text.length > this.options.descriptionLimit) {
            this.descriptionCounter.addClass(this.options.counterLimitClass);
        } else {
            this.descriptionCounter.removeClass(this.options.counterLimitClass);
        }
    },

    /**
     * Refresh the preview state
     */
    refresh: function () {
        var data = this.collectData();

        this.updateDescriptionCounter(data.description);

        if (!this.validateData(data)) {
            this.hideBody();
            return;
        }

        this.renderData(data);
        this.showBody();
    },

    /**
     * Collect the elements
     */
    collectElements: function () {
        this.body = this.el.getElement(this.options.bodySelector);
        this.hint = this.el.getElement(this.options.hintSelector);
        this.title = this.el.getElement(this.options.titleSelector);
        this.url = this.el.getElement(this.options.urlSelector);
        this.description = this.el.getElement(this.options.descriptionSelector);
    },

    /**
     * Collect the data
     *
     * @returns {object}
     */
    collectData: function () {
        return data = {
            'title': this.engine.getTitle(),
            'url': this.engine.getUrl(),
            'description': this.engine.getDescription()
        };
    },

    /**
     * Validate the data if it can be applied to the preview
     *
     * @param {object} data
     *
     * @returns {boolean}
     */
    validateData: function (data) {
        var valid = false;

        for (var i in data) {
            if (data[i] != '') {
                valid = true;
            }
        }

        return valid;
    },

    /**
     * Render the data to the preview
     *
     * @param {object} data
     */
    renderData: function (data) {
        this.setTitle(data.title);
        this.setUrl(data.url);
        this.setDescription(data.description);
    },

    /**
     * Set the title
     *
     * @param {string} value
     */
    setTitle: function (value) {
        if (value.length > this.options.titleLimit) {
            value = value.substr(0, this.options.titleLimit) + '...';
        }

        this.title.set('text', value);
    },

    /**
     * Set the URL
     *
     * @param {string} value
     */
    setUrl: function (value) {
        this.url.set('text', value);
    },

    /**
     * Set the description
     *
     * @param {string} value
     */
    setDescription: function (value) {
        if (value.length > this.options.descriptionLimit) {
            value = value.substr(0, this.options.descriptionLimit) + '...';
        }

        var chunks = value.split(' ');
        var siblingClass = this.options.descriptionSiblingClass;

        function markSiblings() {
            var text = this.get('text');
            var siblings = this.getSiblings();

            for (var j = 0; j < siblings.length; j++) {
                if (siblings[j].get('text').toLowerCase() === text.toLowerCase()) {
                    siblings[j].addClass(siblingClass);
                }
            }

            this.addClass(siblingClass);
        }

        function unmarkSiblings() {
            this.removeClass(siblingClass);
            this.getSiblings().removeClass(siblingClass);
        }

        // Empty the description
        this.description.set('html', '');

        // Generate teh <span> elements
        for (var i = 0; i < chunks.length; i++) {
            var span = new Element('span', {'text': chunks[i]});
            var space = new Element('span', {'text': ' '});

            span.addEvent('mouseenter', markSiblings);
            span.addEvent('mouseleave', unmarkSiblings);

            span.inject(this.description, 'bottom');
            space.inject(this.description, 'bottom');
        }
    },

    /**
     * Hide the body and show hint
     */
    hideBody: function () {
        this.hint.show();
        this.body.hide();
    },

    /**
     * Show the body and hide hint
     */
    showBody: function () {
        this.hint.hide();
        this.body.show();
    }
});