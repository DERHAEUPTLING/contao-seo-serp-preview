SeoSerpPreview.PageEngine = new Class({
    Implements: [Events],

    /**
     * Initialize the engine
     */
    init: function () {
        this.title = document.id('ctrl_title');
        this.pageTitle = document.id('ctrl_pageTitle');
        this.alias = document.id('ctrl_alias');
        this.description = document.id('ctrl_description');
        this.robots = document.id('ctrl_robots');

        this.addEventListeners();
        this.fireEvent('ready');
    },

    /**
     * Add the event listeners
     */
    addEventListeners: function () {
        var fields = [this.title, this.pageTitle, this.alias, this.description];

        for (var i = 0; i < fields.length; i++) {
            fields[i].addEvent('keyup', function () {
                this.fireEvent('change');
            }.bind(this));
        }

        if (this.robots) {
            this.robots.addEvent('change', function () {
                this.fireEvent('change');
            }.bind(this));
        }
    },

    /**
     * Add the description character counter
     *
     * @param {object} el
     *
     * @return {object}
     */
    addDescriptionCounter: function (el) {
        return el.inject(this.description.getPrevious(), 'bottom');
    },

    /**
     * Get the title
     *
     * @returns {string}
     */
    getTitle: function () {
        var pageTitle = this.pageTitle.get('value');

        return pageTitle ? pageTitle : this.title.get('value');
    },

    /**
     * Get the URL
     *
     * @returns {string}
     */
    getUrl: function () {
        return this.alias.get('value');
    },

    /**
     * Get the description
     *
     * @returns {string}
     */
    getDescription: function () {
        return this.description.get('value');
    },

    /**
     * Get the index (true if the page is indexed, false otherwise)
     *
     * @returns {bool}
     */
    getIndex: function () {
        if (!this.robots) {
            return true;
        }

        return this.robots.get('value').indexOf('noindex') === -1;
    }
});
