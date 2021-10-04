<?php
/**
 * Default page
 *
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Dashboard page callback function
 *
 * @return void
 */
function cvp_dashboard_page() {
	?>
	<div id="cvp">
		<div class="content-tabs" id="dashboard">
			<?php CVP()->display_tabs(); ?>
			<div class="tab-content">
				<div class="tab-pane active">
					<div v-cloak class="padding-top-15">
						<div class="row text-center v-cloak--block">
							<div class="col-xs-12 loading"><p><i class="fa fa-cog fa-spin fa-2x fa-fw" aria-hidden="true"></i><br><?php esc_html_e( 'Loading Core', 'cvp_lang' ); ?>...</span></p></div>
						</div>
						<div class="v-cloak--hidden">
							<?php if ( ! CVP()->php_version_ok() ) : ?>
								<div class="row">
									<div class="col-xs-12 col-md-6 col-md-push-3">
										<h3 class="text-center"><?php esc_html_e( 'PHP > 5.3.0 is required', 'cvp_lang' ); ?></h3>
										<div class="alert alert-danger">
											<?php /* translators: %s is the current too old installed PHP version */ ?>
											<p><strong>PHP >= <?php echo esc_html( CVP_PHP_REQUIRED ); ?></strong> <?php printf( esc_html_x( 'is required. Your PHP version (%s) is too old. Please contact your hoster to update it', '[PHP version] is required...', 'cvp_lang' ), PHP_VERSION ); ?></p>
										</div>
									</div>
								</div>
							<?php else : ?>
								<!--**************-->
								<!-- LOADING DATA -->
								<!--**************-->
								<template v-if="loading.loadingData">
									<div class="row text-center">
										<div class="col-xs-12 loading"><p><i class="fa fa-cog fa-spin-reverse fa-2x fa-fw" aria-hidden="true"></i><br><?php esc_html_e( 'Loading Data', 'cvp_lang' ); ?>...</span></p></div>
									</div>
								</template>
								<transition name="fade">
									<div v-if="dataLoaded">
										<div v-if="core !== false" class="row">
											<div class="col-xs-12">
												<p class="core__version text-right">
													<?php echo CVP_NAME; ?>  v{{core.installed_version}}
													<template v-if="!core.is_latest_version">
														<button v-if="loading.updatingCore == false" @click="updateCore" class="btn btn-sm btn-success"><i class="fa fa-refresh" aria-hidden="true"></i> Update to v{{core.latest_version}}</button>
														<button v-if="loading.updatingCore == true" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> Updating to v{{core.latest_version}}</button>
														<button v-if="loading.updatingCore == 'activating'" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin fa-fw" aria-hidden="true"></i> Activating...</button>
														<button v-if="loading.updatingCore == 'reloading'" class="btn btn-sm btn-success disabled" disabled><i class="fa fa-cog fa-spin-reverse fa-fw" aria-hidden="true"></i> Reloading...</button>
													</template>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-xs-12">
												<div class="alert text-center">
													<h3>
														<template v-if="changelog"><?php esc_html_e( 'Changelog', 'cvp_lang' ); ?></template>
														<template v-else><?php esc_html_e( 'Error getting changelog', 'cvp_lang' ); ?></template>
													</h3>
													<div class="row" v-if="changelog">
														<div class="col-xs-12 col-md-8 col-md-push-2 col-lg-6 col-lg-push-3">
															<table class="table table-bordered table-striped">
																<tr>
																	<th><?php esc_html_e( 'Version', 'cvp_lang' ); ?></th>
																	<th><?php esc_html_e( 'Date', 'cvp_lang' ); ?></th>
																	<th><?php esc_html_e( 'Changes', 'cvp_lang' ); ?></th>
																</tr>
																<tr v-for="item of changelog">
																	<td><strong>{{item.version}}</strong></td>
																	<td>{{item.date}}</td>
																	<td><div v-for="change of item.changes">{{change}}</div></td>
																</tr>
															</table>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</transition>
							<?php endif; ?>
						</div>
						<p class="text-right"><small>cUrl v<?php echo esc_html( CVP()->get_curl_version() ); ?></small></p>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
