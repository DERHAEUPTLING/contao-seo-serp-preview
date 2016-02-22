SeoSerpPreview.NewsEngine = new Class({
    Implements: [Events],

    /**
     * Initialize the engine
     */
    initialize: function () {
        this.title = document.id('ctrl_headline');
        this.alias = document.id('ctrl_alias');

        // @todo - find a better way to detect tinyMCE initialization than setTimeout()
        setTimeout(function () {
            this.description = {
                'textarea': document.id('ctrl_teaser'),
                'tinymce': tinyMCE.get('ctrl_teaser')
            };

            this.addEventListeners();
            this.fireEvent('ready');
        }.bind(this), 1000);
    },

    /**
     * Add the event listeners
     */
    addEventListeners: function () {
        var fields = [this.title, this.alias];

        for (var i = 0; i < fields.length; i++) {
            fields[i].addEvent('keyup', function () {
                this.fireEvent('change');
            }.bind(this));
        }

        this.description.tinymce.on('keyup', function () {
            this.fireEvent('change');
        }.bind(this));
    },

    /**
     * Add the description character counter
     *
     * @param {object} el
     *
     * @return {object}
     */
    addDescriptionCounter: function (el) {
        return el.inject(this.description.textarea.getPrevious('h3'), 'bottom');
    },

    /**
     * Get the title
     *
     * @returns {string}
     */
    getTitle: function () {
        return this.title.get('value');
    },

    /**
     * Get the URL
     *
     * @returns {string}
     */
    getUrl: function () {
        return this.alias.get('value');
    },

    /*
     * Get the description
     *
     * @returns {string}
     */
    getDescription: function () {
        return this.description.tinymce.getContent({ format: 'text' });
    }
});