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

	        <div ng-controller="BookController" class="sr-book">

	        	<div class="sr-row">
	        		<div class="sr-book-container">

                        <div class="sr-book-steps">
                            <div class="sr-book-step active" ng-class="(step > 1 ? 'completed' : '')">1 - <?= __('Price calculation', 'dbk-sr') ?>
                                <span class="sr-book-step-between"><span class="fa" ng-class="(step > 1 ? 'fa-check' : 'fa-angle-right')"></span></span>
                            </div>
                            <div class="sr-book-step" ng-class="(step >= 2 ? 'active' : '')" ng-class="(step > 2 ? 'completed' : '')">2 - <?= __('Your data', 'dbk-sr') ?>
                                <span class="sr-book-step-between"><span class="fa" ng-class="(step > 2 ? 'fa-check' : 'fa-angle-right')"></span></span>
                            </div>
                            <div class="sr-book-step" ng-class="(step >= 3 ? 'active' : '')">3 - <?= __('Confirmation', 'dbk-sr') ?></div>
                        </div>

                		<div ng-include="template" onload="initStep()"></div>
                	</div>

					<div class="sr-book-cart sr-widget">
                		<div class="sr-widget-head"><?= __('Price calculation', 'dbk-sr') ?></div>
                		<div class="sr-cart-container" ng-include="cart"><?= __('Loading...', 'dbk-sr') ?></div>
                        <?php if (DBK_SR::get_option('book_on_map')) : ?>
                        	<a class="sr-btn-primary sr-open-map" ng-click="openMap()" ng-if="entity && entity.can_book_on_map"><span class="fa fa-map-marker"></span>Kies uw accommodatie op de plattegrond</a>
                        <?php endif ?>
                		<div ng-if="form.entity_item" class="sr-selected-item">
                			<label>Geselecteerde accommodatie:</label>
                			<span class="sr-selected-item-title">{{ form.entity_item.title }}</span>
                		</div>
                		<div ng-if="entity_item_msg" class="sr-selected-item sr-entity-item-msg">
                			Verplicht deze accommodatie op plattegrond te boeken. <a href="#" ng-click="openMap()">Klik hier om een accommodatie te selecteren</a>
                		</div>
                	</div>
                </div>

	        </div>


        	<?php while (have_posts()) : the_post(); ?>

				<?php get_template_part('content', get_post_format()); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer('simpel-reserveren'); ?>
