<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="ScenarioController as s">
	<div class="jackmail_header_container">
		<div class="jackmail_header">
			<div>
				<div class="jackmail_header_menu" jackmail-header-menu></div>
				<div class="jackmail_header_buttons">
					<div jackmail-search></div>
				</div>
			</div>
		</div>
	</div>
	<div ng-hide="$root.show_help2" class="jackmail_campaign_create_scenario" ng-style="{'width': s.page_width + 'px'}">
		<p class="jackmail_title jackmail_center">
			<?php _e( 'Select a list and create a workflow', 'jackmail-newsletters' ) ?>
		</p>
		<p ng-show="s.woocommerce_active" class="jackmail_center">
			<?php _e( 'Display emails associated with', 'jackmail-newsletters' ) ?>
			<span jackmail-checkbox="s.jackmail_display"
				ng-click="s.change_option( 'jackmail' )"
				checkbox-title="<?php esc_attr_e( 'Jackmail', 'jackmail-newsletters' ) ?>">
			</span>
			<span jackmail-checkbox="s.woocommerce_display"
				ng-click="s.change_option( 'woocommerce' )"
				checkbox-title="<?php esc_attr_e( 'WooCommerce', 'jackmail-newsletters' ) ?>"
				ng-show="s.woocommerce_active">
			</span>
		</p>
		<div ng-show="s.jackmail_display">
			<p ng-show="s.woocommerce_active || s.contactform7_active" class="jackmail_subtitle">
				<?php _e( 'Jackmail', 'jackmail-newsletters' ) ?>
			</p>
			<div ng-repeat="event in s.jackmail_events track by $index" ng-click="s.create_scenario( event.id )" ng-class="'jackmail_campaign_create_scenario_' + event.id">
				<p class="jackmail_bold">{{event.title}}</p>
				<p class="jackmail_grey" title="{{event.description}}">{{event.description}}</p>
				<span><?php _e( 'Start', 'jackmail-newsletters' ) ?></span>
			</div>
		</div>
		<div ng-show="s.woocommerce_active && s.woocommerce_display">
			<p class="jackmail_subtitle"><?php _e( 'WooCommerce', 'jackmail-newsletters' ) ?></p>
			<div ng-repeat="event in s.woocommerce_events track by $index" ng-click="s.create_scenario( event.id )" ng-class="'jackmail_campaign_create_scenario_' + event.id">
				<p class="jackmail_bold">{{event.title}}</p>
				<p class="jackmail_grey" title="{{event.description}}">{{event.description}}</p>
				<span><?php _e( 'Start', 'jackmail-newsletters' ) ?></span>
			</div>
		</div>
	</div>
</div>
<?php } ?>