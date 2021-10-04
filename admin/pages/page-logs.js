//jetpack fix
_.contains = _.includes;
window.lodash = _.noConflict();

jQuery(document).ready(function () {
    if (document.getElementById('logs')) {
        var logs = new Vue({
            el: '#logs',
            data: {
                logs: {},
                loading: {
                    loadingData: false,
                    copyLogs: false,
                    deleteLogs: false,
                },
                dataLoaded: false,
                filters: {
                    type: '',
                    message: '',
                    location: '',
                    product: '',
                },
                products: [],
            },
            computed: {
                filteredlogs: function () {
                    var self = this;
                    return lodash.filter(this.logs, function (log) {
                        return (
                            (self.filters.type == '' || log.type == self.filters.type) &&
                            (self.filters.product == '' || log.product == self.filters.product) &&
                            (self.filters.message == '' || log.message.toLowerCase().search(self.filters.message.toLowerCase()) > -1) &&
                            (self.filters.location == '' || (log.file_uri + ':' + log.file_line).toLowerCase().search(self.filters.location.toLowerCase()) > -1)
                        );
                    });
                },
            },
            filters: {
                capitalize: function (text) {
                    return text.charAt(0).toUpperCase() + text.slice(1);
                },
            },
            methods: {
                copyLogs: function () {
                    var self = this;
                    self.$set(self.loading, 'copyLogs', true);
                    new ClipboardJS('.btn', {
                        text: function (trigger) {
                            setTimeout(function () {
                                self.$set(self.loading, 'copyLogs', false);
                            }, 500);
                            return JSON.stringify(self.filteredlogs);
                        },
                    });
                },
                deleteLogs: function () {
                    this.$set(this.loading, 'deleteLogs', true);

                    this.$http
                        .post(
                            CVP_logs.ajax.url,
                            {
                                action: 'cvp_delete_logs',
                                nonce: CVP_logs.ajax.nonce,
                            },
                            {
                                emulateJSON: true,
                            }
                        )
                        .then(
                            (response) => {
                                // success callback
                                this.logs = '';
                            },
                            (response) => {
                                // error callback
                            }
                        )
                        .then((_) => {
                            this.$set(this.loading, 'deleteLogs', false);
                        });
                },
                slugToName: function (slug) {
                    return lodash.startCase(slug);
                },
            },
            mounted: function () {
                this.loading.loadingData = true;
                this.$http
                    .post(
                        CVP_logs.ajax.url,
                        {
                            action: 'cvp_load_logs_data',
                            nonce: CVP_logs.ajax.nonce,
                        },
                        {
                            emulateJSON: true,
                        }
                    )
                    .then(
                        (response) => {
                            // success callback
                            var localLogs = response.body.logs;
                            // add product data
                            var regex = /.+wp-content[/\\\\](plugins|themes)[/\\\\](.+?)[/\\\\].+$/;
                            var self = this;
                            localLogs.map(function (log) {
                                var product_details = log.file_uri.match(regex);
                                var product_slug = '';
                                var product_name = '';
                                if (product_details) {
                                    product_slug = product_details[2];
                                    product_name = self.slugToName(product_slug);
                                }
                                // push product in this.products used for select filter
                                if (product_name) {
                                    log.product = product_name;
                                    if (
                                        !self.products.find(function (p) {
                                            return p == product_name;
                                        })
                                    ) {
                                        self.products.push(product_name);
                                    }
                                }
                                // add log bootstrapp class for display style
                                switch (log.type) {
                                    case 'notice':
                                        log.class = 'label-info';
                                        break;
                                    case 'error':
                                        log.class = 'label-danger';
                                        break;
                                    default:
                                        log.class = 'label-' + log.type;
                                        break;
                                }
                            });
                            this.logs = localLogs;
                        },
                        (response) => {
                            // error callback
                        }
                    )
                    .then((_) => {
                        this.loading.loadingData = false;
                        this.dataLoaded = true;
                    });
            },
        });
    }
});
