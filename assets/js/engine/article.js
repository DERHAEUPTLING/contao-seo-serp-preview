SeoSerpPreview.ArticleEngine = new Class({
    Implements: [Events],

    /**
     * Initialize the engine
     */
    init: function () {
        this.title = document.id('ctrl_title');
        this.alias = document.id('ctrl_alias');
        this.showTeaser = document.id('opt_showTeaser_0');

        var attempts = 20;

        var interval = setInterval(function () {
            if (!tinyMCE.hasOwnProperty('editors')) {
                // Resign after the attempts limit is reached
                if (attempts-- <= 1) {
                    console.error('Unable to determine the tinyMCE instance for SEO SERP Preview extension.');
                    clearInterval(interval);
                }

                return;
            }

            clearInterval(interval);

            this.description = {
                'textarea': document.id('ctrl_teaser'),
                'tinymce': tinyMCE.get('ctrl_teaser')
            };

            this.addEventListeners();
            this.fireEvent('ready');
        }.bind(this), 500);
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

        if (this.showTeaser) {
            this.showTeaser.addEvent('change', function () {
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

    /**
     * Get the description
     *
     * @returns {string}
     */
    getDescription: function () {
        return this.description.tinymce.getContent({ format: 'text' });
    },

    /**
     * Get the index (true if the page is indexed, false otherwise)
     *
     * @returns {bool}
     */
    getIndex: function () {
        if (!this.showTeaser) {
            return true;
        }

        return this.showTeaser.checked ? true : false;
    }
});