<?php if ( defined( 'ABSPATH' ) ) { ?>
<div ng-controller="SearchController as s">
	<div ng-show="$root.show_help1" class="jackmail_search_background ng-hide-animate ng-hide"></div>
	<div ng-show="$root.show_help2" class="jackmail_search_container ng-hide-animate ng-hide">
		<div>
			<div class="jackmail_search_input">
				<input type="text" ng-model="s.search" ng-enter="s.search_text()" placeholder="<?php esc_attr_e( 'Search...', 'jackmail-newsletters' ) ?>"/>
				<span ng-hide="s.show_suggestions" class="jackmail_grey"><?php _e( 'Press enter to search', 'jackmail-newsletters' ) ?></span>
				<span ng-show="s.show_suggestions" class="jackmail_grey"><?php _e( 'No result was found.', 'jackmail-newsletters' ) ?></span>
			</div>
			<div ng-show="s.show_results || s.show_suggestions">
				<div ng-show="s.show_results" class="jackmail_search_results">
					<p class="jackmail_14"><?php _e( 'Results:', 'jackmail-newsletters' ) ?></p>
					<div>
						<p class="jackmail_search_faq_title">
							<span class="dashicons dashicons-media-text"></span>
							<span><?php _e( 'FAQ', 'jackmail-newsletters' ) ?></span>
						</p>
						<p ng-repeat="result in s.search_faq track by $index" class="jackmail_search_faq">
							<span class="dashicons dashicons-media-text"></span>
							<a href="{{result.link}}" target="_blank" ng-bind-html="result.question"></a>
						</p>
						<p ng-show="s.search_faq.length === 0"><?php _e( 'No FAQ', 'jackmail-newsletters' ) ?></p>
					</div>
					<div>
						<p class="jackmail_search_campaigns_title">
							<span class="dashicons dashicons-edit"></span>
							<span><?php _e( 'Campaigns', 'jackmail-newsletters' ) ?></span>
						</p>
						<p ng-repeat="result in s.search_campaigns track by $index" ng-click="s.display_campaign_page( result.id, result.status )" ng-class="result.status === 'DRAFT' ? 'jackmail_pointer' : ''" class="jackmail_search_campaigns">
							<span class="jackmail_search_campaigns_left">
								<img ng-src="{{result.preview}}" alt=""/>
							</span>
							<span class="jackmail_search_campaigns_center">
								<span ng-bind-html="result.name"></span>
								<br/>
								<span class="jackmail_grey jackmail_11">
									<?php _e( 'Updated on', 'jackmail-newsletters' ) ?>
									{{result.updated_date_gmt | formatedDate : 'gmt_to_timezone' : 'hours'}}
									<span ng-show="result.status === 'DRAFT'" class="jackmail_11">(<?php _e( 'draft', 'jackmail-newsletters' ) ?>)</span>
								</span>
							</span>
						</p>
						<p ng-show="s.search_campaigns.length === 0"><?php _e( 'No campaigns', 'jackmail-newsletters' ) ?></p>
					</div>
					<div>
						<p class="jackmail_search_module_title">
							<span class="dashicons dashicons-editor-table"></span>
							<span><?php _e( 'Webpages', 'jackmail-newsletters' ) ?></span>
						</p>
						<p ng-repeat="result in s.search_module track by $index" class="jackmail_search_module">
							<span ng-click="s.display_page( result.id, result.type )">
								<span ng-show="result.type === 'campaign'"><?php _e( 'Campaign', 'jackmail-newsletters' ) ?></span>
								<span ng-show="result.type === 'scenario'"><?php _e( 'Workflow', 'jackmail-newsletters' ) ?></span>
								<span ng-show="result.type === 'list'"><?php _e( 'List', 'jackmail-newsletters' ) ?></span>
								<span ng-show="result.type === 'statistics'"><?php _e( 'Statistics', 'jackmail-newsletters' ) ?></span>
								<span ng-show="result.type === 'template'"><?php _e( 'Template', 'jackmail-newsletters' ) ?></span>
								<span> - </span>
								<span ng-bind-html="result.name"></span>
							</span>
						</p>
						<p ng-show="s.search_module.length === 0"><?php _e( 'No pages', 'jackmail-newsletters' ) ?></p>
					</div>
				</div>
				<div ng-show="s.show_suggestions && ( s.suggestion_faq.length > 0 || s.suggestion_forum.length > 0 )" class="jackmail_search_suggestions">
					<p class="jackmail_14"><?php _e( 'We suggest to:', 'jackmail-newsletters' ) ?></p>
					<div ng-show="s.suggestion_faq.length > 0">
						<p class="jackmail_search_faq_title">
							<span class="dashicons dashicons-media-text"></span>
							<span><?php _e( 'FAQ', 'jackmail-newsletters' ) ?></span>
						</p>
						<p ng-repeat="result in s.suggestion_faq track by $index" class="jackmail_search_faq">
							<span class="dashicons dashicons-media-text"></span>
							<a href="{{result.link}}" target="_blank" ng-bind-html="result.question"></a>
						</p>
					</div>
					<div ng-show="s.suggestion_forum.length > 0">
						<p class="jackmail_search_forum_title">
							<span class="dashicons dashicons-sos"></span>
							<span><?php _e( 'Help', 'jackmail-newsletters' ) ?></span>
						</p>
						<p ng-repeat="result in s.suggestion_forum track by $index" class="jackmail_search_forum">
							<span class="dashicons dashicons-sos"></span>
							<a href="{{result.link}}" target="_blank" ng-bind-html="result.title"></a>
						</p>
					</div>
				</div>
				<div class="jackmail_search_need_help">
					<p class="jackmail_14"><?php _e( 'Need help?', 'jackmail-newsletters' ) ?></p>
					<div>
						<div ng-click="s.go_faq()">
							<p class="jackmail_bold jackmail_green jackmail_14"><?php _e( 'In the FAQ', 'jackmail-newsletters' ) ?></p>
							<p><?php _e( 'The most common questions are <span class="jackmail_green">here</span>', 'jackmail-newsletters' ) ?></p>
						</div>
						<div class="jackmail_search_need_help_separator"></div>
						<div ng-click="s.go_formular()">
							<p class="jackmail_bold jackmail_green jackmail_14"><?php _e( 'In the contact form', 'jackmail-newsletters' ) ?></p>
							<p><?php _e( 'Your input is important and valuable to us. Feel free to share any <span class="jackmail_green">feedback</span>', 'jackmail-newsletters' ) ?></p>
						</div>
						<div class="jackmail_search_need_help_separator"></div>
						<div ng-click="s.go_forum()">
							<p class="jackmail_bold jackmail_green jackmail_14"><?php _e( 'In our forum', 'jackmail-newsletters' ) ?></p>
							<p><?php _e( 'Check out our <span class="jackmail_green">forum</span>', 'jackmail-newsletters' ) ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php } ?>