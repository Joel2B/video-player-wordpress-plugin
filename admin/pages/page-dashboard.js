//jetpack fix
_.contains = _.includes;
window.lodash = _.noConflict();

jQuery(document).ready(function () {
    if (document.getElementById('dashboard')) {
        /**
         * Add i18n globaly for translations
         */
        Vue.prototype.$i18n = CVP_dashboard.i18n;

        /**
         * Create main Vue instance
         */
        var vueDashboard = new Vue({
            // main instance el
            el: '#dashboard',

            // main instance data
            data: {
                error: '',
                loading: {
                    checkingAccount: false,
                    checkingLicense: false,
                    loadingData: false,
                    updatingCore: false,
                    connectingSite: false,
                },
                changelog: [],
                dataLoaded: false,
                core: {},
            },

            // main instance mounted hook
            mounted: function () {
                this.loadData();
            },

            // main instance methods
            methods: {
                loadData: function () {
                    this.loading.loadingData = true;
                    this.$http
                        .post(
                            CVP_dashboard.ajax.url,
                            {
                                action: 'cvp_load_dashboard_data',
                                nonce: CVP_dashboard.ajax.nonce,
                            },
                            {
                                emulateJSON: true,
                            }
                        )
                        .then(
                            function (response) {
                                // success callback
                                this.core = response.body.core;
                                this.changelog = response.body.changelog;
                            },
                            function (error) {
                                // error callback
                                console.error(error);
                            }
                        )
                        .then(function () {
                            this.loading.loadingData = false;
                            this.dataLoaded = true;
                        });
                },
                updateCore: function () {
                    this.loading.updatingCore = true;
                    this.$http
                        .post(
                            CVP_dashboard.ajax.url,
                            {
                                action: 'cvp_install_product',
                                nonce: CVP_dashboard.ajax.nonce,
                                product_sku: this.core.sku,
                                product_type: 'plugin',
                                product_zip: this.core.zip_file,
                                product_slug: this.core.slug,
                                product_folder_slug: this.core.folder_slug,
                                method: 'upgrade',
                                new_version: this.core.latest_version,
                            },
                            {
                                emulateJSON: true,
                            }
                        )
                        .then(
                            function (response) {
                                // success callback
                                if (response.body === true || response.body == '<div class="wrap"><h1></h1></div>') {
                                    this.loading.updatingCore = this.$i18n['loading_reloading'];
                                    document.location.href = 'admin.php?page=cvp-dashboard';
                                }
                            },
                            function (error) {
                                // error callback
                                console.error(error);
                            }
                        )
                        .then(function () {});
                },
            },
        });
    }
});
