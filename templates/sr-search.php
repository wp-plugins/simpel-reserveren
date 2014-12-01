<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main" ng-app="sr" class="sr">

	        <div ng-controller="SearchController" class="sr-search">

	            <div class="page-header" id="banner">
	                <div class="row">
	                    <div class="col-sm-5 col-md-4">
	                        <?php dynamic_sidebar('sr-sidebar-search') ?>
	                    </div>
	                    <div class="col-sm-7 col-md-8">
	                        <div>

	                            <div class="panel" ng-repeat="item in foundEntities">
	                                <div class="panel-body withripple sr-entity" ng-click="viewEntitiy()">
	                                	<div class="sr-row">
	                                		<div class="sr-img">
	                                			<img src="{{ item.local.thumbnail }}">
	                                		</div>
	                                		<div class="sr-txt">
	                                			<h2>{{ item.title }}</h2>
	                                			<p>{{ item.local.excerpt }}</p>
	                                		</div>
	                                		<div class="sr-price">
	                                    		<h3>&euro; {{ item.price }}</h3>
	                                    	</div>
	                                    </div>
	                                </div>
	                            </div>

	                        </div>
	                        
	                    </div>
	                </div>
	            </div>

	        </div>

	        <script>
	        var bookgroups = [
			    {
			        id: 3,
			        title: 'Volwassenen',
			        amount: 2
			    },
			    {
			        id: 4,
			        title: 'Kinderen',
			        amount: 2
			    },
			    {
			        id: 5,
			        title: '50+',
			        amount: 2
			    }
			];
			
			</script>

        	<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>