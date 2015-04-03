<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
get_header('simpel-reserveren'); ?>

	<div id="primary" class="site-content layout container">
		<div id="content" role="main" ng-app="sr" class="sr">

	        <div ng-controller="SearchController" class="sr-searchresults">

                <div class="sr-row">
                    <div class="sr-sidebar">
                        <?php dynamic_sidebar('sr-sidebar-search') ?>

                        <div class="sr-widget-secondary sr-filters">
			                <div class="sr-widget-head"><?php echo __('Filter by', 'dbk-sr') ?></div>
			                <div class="sr-widget-body">
			                    <ul>
                                    <li ng-repeat="facility in facilities" ng-click="toggleFacility()" ng-class="(facility.selected ? 'selected' : '')" ng-show="facility.nr > 0">
                                        <span class="fa fa-{{facility.icon}}"></span> {{ facility.title }}
                                        <span class="sr-close">x</span>
                                        <span class="sr-nr">({{ facility.nr }})</span>
                                    </li>
			                    </ul>
			                </div>
			            </div>

                    </div>
                    <div class="sr-results">
                    	<div class="searchresult-container" ng-include="template"></div>
                    </div>
                </div>


	        </div>


        	<?php while (have_posts()) : the_post(); ?>

				<?php get_template_part('content', get_post_format()); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer('simpel-reserveren'); ?>
