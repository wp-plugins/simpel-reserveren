<?php
/**
 * The Template for displaying all single posts
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
get_header('simpel-reserveren');
the_post(); ?>

	<div id="primary" class="site-content layout container sr" ng-app="sr">
		<div id="content" role="main" class="sr sr-entity" ng-controller="EntityController" ng-cloak>

			<div class="sr-row clearfix">
                <div class="sr-entity-main">
                    <div class="main-content-inner">
                        <div class="sr-row">
                            <div class="sr-entity-head">
                                <h1>
                                    <?php the_title(); ?>
                                    <?php if ($stars = SR_PostType_Entity::get_stars()): ?>
                                        <small class="sr-entity-stars">
                                            <?php foreach (SR_PostType_Entity::get_stars() as $star): ?>
                                                <span class="fa fa-star"></span>
                                            <?php endforeach ?>
                                        </small>
                                    <?php endif ?>
                                </h1>
                                <div class="sr-entity-head-sub" ng-if="entity">
                                    {{ entity.entity_type.title }}, maximaal {{entity.max_persons}} personen
                                </div>
                            </div>
                        </div>
                        <div class="sr-row">
                            <div class="sr-entity-side-content">
                                <?php $gallery = SR_PostType_Entity::get_gallery_images(get_the_id()); ?>
                                <?php $image = SR_PostType_Entity::get_image(get_the_id()); ?>
                                <?php if ($gallery || $image) : ?>

                                    <div class="sr-entity-thumbnail">

                                        <?php if($video = SR_PostType_Entity::get_video()): ?>

                                            <a href="<?=$video?>" class="sr-entity-play" data-toggle="lightbox" data-title="Video: <?=get_the_title()?>">
                                                <img src="<?= $image['thumb'] ?>" alt="<?php the_title() ?>"/>
                                                <div class="sr-entity-play-button"><span class="fa fa-play"></span></div>
                                            </a>
                                        <?php else: ?>

                                            <a href="<?=$image['large']?>" data-toggle="lightbox" data-width="700" data-gallery="sr-gallery" >
                                                <img src="<?= $image['thumb'] ?>" alt="<?php the_title() ?>"/>
                                            </a>

                                        <?php endif; ?>

                                        <?php foreach ($gallery as $i => $img) : ?>
                                            <div>
                                                <a href="<?php echo $img['large'] ?>" data-gallery="sr-gallery" data-toggle="lightbox" data-width="700"><img src="<?= $img['thumb'] ?>" alt="<?php the_title() ?>"/></a>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>

                                    <div class="sr-entity-gallery">
                                        <div>
                                            <img src="<?= $image['small'] ?>" alt="<?php the_title() ?>"/>
                                        </div>
                                        <?php foreach ($gallery as $i => $img) : ?>
                                            <div>
                                                <img src="<?= $img['small'] ?>" alt="<?php the_title() ?>"/>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php endif ?>

                            </div>
                            <div class="sr-entity-content">
                                <?php if ($quote = SR_PostType_Entity::get_quote()) : ?>
                                    <div class="sr-entity-quote">"<?= $quote ?>"</div>
                                <?php endif; ?>

                                <?php the_content(); ?>
                                <div class="actions">
                                    <?php if (DBK_SR::get_option('book_on_map')): ?>
                                        <a class="sr-btn-primary btn-lg" ng-click="openMap()"><?= __('Book on map', 'dbk-sr') ?> &nbsp; <span class="fa fa-map-marker"></span></a>
                                    <?php endif ?>
                                    <a class="sr-btn-secondary btn-lg" ng-click="book()"><?= __('Book now', 'dbk-sr') ?> &nbsp; <span class="fa fa-angle-right"></span></a>
                                </div>
                            </div>

                        </div>
                    </div>

					<div class="sr-row sr-clear">
						<div id="sr-entity-tabs" class="sr-entity-tabs">
							<ul class="sr-tabs">
							    <li class="sr-tab active"><a href="#tableprices" role="tab" aria-controls="tableprices">Alternatieven</a></li>
							    <li class="sr-tab"><a href="#extras" class="">Faciliteiten</a></li>
							    <li class="sr-tab"><a href="#pics">Foto's</a></li>
						  	</ul>
						  	<div class="tab-content">
							  <div id="extras" class="tab-pane" role="tabpanel">
							    <h3>Faciliteiten</h3>
	   							<div ng-if="entity.facilities" class="sr-entity-facilities">
									<div class="sr-entity-facility" ng-repeat="facility in entity.facilities">
										<span class="fa fa-{{facility.icon}}"></span> {{ facility.title }}
                                        <span ng-if="facility.description" class="fa fa-info-circle" title="{{ facility.description }}" data-toggle="tooltip" data-placement="top"></span>
									</div>
								</div>

							  </div>
							  <div id="tableprices" class="tab-pane active">
							  	<h3>Alternatieven op basis van {{bookgroups}} <a class="sr-change-bookgroup sr-btn" ng-click="changeBookgroup()"><span class="fa fa-group"></span> aanpassen</a></h3>
							    
							    <div class="sr-price-table" ng-if="tablePrices.other.length > 0">
							    	<div class="sr-price-table-head">
										Arrangementen
									</div>
									<div class="sr-price-blocks">
										<div class="sr-price-block" ng-repeat="price in tablePrices.other">
											<div class="sr-price-title">{{price.title}}</div>
											<div class="sr-price-info">({{price.nights}} nachten)&nbsp;<br ng-if="price.period_title"/>{{price.period_title}}</div>
											<a href="{{bookURL}}?entity_id={{entity.id}}&start={{price.start}}&end={{price.end}}" class="label-green-light"> <em ng-if="price.oldprice" ng-bind-html="price.oldprice | money"></em> <span ng-bind-html="price.price | money"></span></a>
										</div>
									</div>
								</div>

							    <div class="sr-price-table" ng-if="tablePrices.week.length > 0">
							    	<div class="sr-price-table-head">
										Week prijzen
									</div>
									<div class="sr-price-blocks">
										<div class="sr-price-block" ng-repeat="price in tablePrices.week">
											<div class="sr-price-title">{{price.title}}</div>
											<div class="sr-price-info">week (7 nachten)</div>
											<a href="{{bookURL}}?entity_id={{entity.id}}&start={{price.start}}&end={{price.end}}" class="label-green-light"> <em ng-if="price.oldprice" ng-bind-html="price.oldprice | money"></em> <span ng-bind-html="price.price | money"></span></a>
										</div>
									</div>
								</div>

							    
							    <div class="sr-price-table" ng-if="tablePrices.midweek.length > 0">
							    	<div class="sr-price-table-head">
										Midweek prijzen
									</div>
									<div class="sr-price-blocks">
										<div class="sr-price-block" ng-repeat="price in tablePrices.midweek">
											<div class="sr-price-title">{{price.title}}</div>
											<div class="sr-price-info">midweek (4 nachten)</div>
											<a href="{{bookURL}}?entity_id={{entity.id}}&start={{price.start}}&end={{price.end}}" class="label-green-light"> <em ng-if="price.oldprice" ng-bind-html="price.oldprice | money"></em> <span ng-bind-html="price.price | money"></span></a>
										</div>
									</div>
								</div>

							    <div class="sr-price-table" ng-if="tablePrices.weekend.length > 0">
							    	<div class="sr-price-table-head">
										Weekend prijzen
									</div>
									<div class="sr-price-blocks">
										<div class="sr-price-block" ng-repeat="price in tablePrices.weekend">
											<div class="sr-price-title">{{price.title}}</div>
											<div class="sr-price-info">weekend (3 nachten)</div>
											<a href="{{bookURL}}?entity_id={{entity.id}}&start={{price.start}}&end={{price.end}}" class="label-green-light"> <em ng-if="price.oldprice" ng-bind-html="price.oldprice | money"></em> <span ng-bind-html="price.price | money"></span></a>
										</div>
									</div>
								</div>


							    
							  </div>
							  <div id="plattegrond" class="tab-pane" role="tabpanel">
							    <h3>Plattegrond</h3>
							    <!-- content -->
							  </div>
							  <div id="prices" class="tab-pane" role="tabpanel">
							  	<h3>Tarieven</h3>
							  	<p>hier komen de prijzen</p>
							  </div>

							  <div id="pics" class="tab-pane" role="tabpanel">
							  	<h3>Foto's</h3>
							  	<div class="sr-tab-pics">
							  		<?php foreach ($gallery as $i => $image) : ?>
										<div>
											<a href="<?= $image['large'] ?>" data-toggle="lightbox" data-gallery="sr-gallery" data-parent data-width="700">
												<img src="<?= $image['small'] ?>" alt="<?php the_title() ?>"/>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							  </div>
							</div>
						</div>
					</div>

				</div>
				<div class="sr-entity-side sr-availability" data-offset-top="160" data-offset-bottom="40">
					<div class="sr-widget">
						<div class="sr-widget-head"><?= __('Availability', 'dbk-sr') ?></div>

						<div class="sr-price-container">
							<div class="sr-entity-datepicker"></div>

							<div class="sr-datepicker-legend">
								<div class="sr-legend-label">Aankomst en vertrek mogelijk</div>
								<div class="sr-legend-item"><span class="available"></span></div>
								<div class="clearfix"></div>
								<div class="sr-legend-label">Bezet</div>
								<div class="sr-legend-item"><span class="occupied"></span></div>

								<div class="clearfix"></div>
								<div class="sr-legend-label">Geselecteerd</div>
								<div class="sr-legend-item"><span class="selected"></span></div>

								<div class="clearfix"></div>
							</div>

							<div class="sr-price-result" ng-show="entity.price">
								<div class="sr-text-primary sr-price-about">
									{{ form.start }} <?= __('to', 'dbk-sr') ?> {{ form.end }}
								</div>

								<div class="sr-row">
									<div class="sr-price-price">
										<div ng-if="entity.price.oldprice" class="sr-oldprice" ng-bind-html="entity.price.oldprice | money"></div>
										<span ng-bind-html="entity.price.price | money"></span>
									</div>
									<div class="sr-price-book"><a class="sr-btn-secondary" ng-click="book()"><?= __('Book now', 'dbk-sr') ?> &nbsp; <span class="fa fa-angle-right"></span></a></div>
									<div class="sr-info">deze prijs is exclusief bijkomende kosten</div>
								</div>
							</div>
							<div class="sr-entity-no-price" ng-if="entity.price == null">
								<?= __('Loading price..', 'dbk-sr') ?>
							</div>
							<div class="sr-entity-no-price" ng-if="entity.price == 0">
								Geen prijs gevonden, probeer een andere periode.
							</div>

						</div>
					</div>
				</div>
			</div>
			<div class="sr-row">
				<div class="sr-entity-bottom">
					<h3><?php echo __('More', 'dbk-sr') ?> <?php echo strtolower(DBK_SR::get_option('entity_title')) ?></h3>
                    <div class="sr-more-entities sr-row">
                        <?php $alternatives = SR_PostType_Entity::get_alternatives() ?>
                        <?php foreach ($alternatives as $alt) : ?>
                            <div class="sr-entity sr-entity-alt">
                                <div class="sr-widget-body sr-widget-secondary">
                                    <h4>
                                        <?= $alt->post_title ?>
                                    </h4>
                                    <div class="sr-entity-image">
                                        <a href="<?= get_permalink($alt->ID) ?>"><?= get_the_post_thumbnail($alt->ID, 'sr-small') ?></a>
                                    </div>
                                    <div class="sr-alt-txt"><?php echo SR_PostType_Entity::get_excerpt(120, $alt->post_content) ?></div>
                                    <a class="sr-btn-primary" href="<?= get_permalink($alt->ID) ?>"><?php echo __('View', 'dbk-sr') ?> &nbsp; <span class="fa fa-angle-right"></a>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    </div>

					<p>&nbsp;</p>
				</div>

			</div>

			<script>
	        dbk_sr.entity_id = <?= get_post_meta($post->ID, '_entity_id', true); ?>;
			</script>

		  	<div class="modal hide fade sr sr-bookgroups">
		        <div class="modal-dialog">
		            <div class="modal-content">
		                <div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		                    <h4 class="modal-title">Pas reisgezelschap aan</h4>
		                </div>
		                <div class="modal-body">
							<div class="form-group" ng-repeat="bookgroup in form.bookgroups">
		                        <label for="group1">{{ bookgroup.title }}</label>
		                        <select ng-model="bookgroup.nr">
		                            <option ng-repeat="y in [] | range:bookgroup.minimum:(bookgroup.max ? (bookgroup.max + 1) : 20)">{{ y }}</option>
		                        </select>
		                    </div>
		                    <div class="form-group clearfix">
		                        <a id="bookgroup-ok" class="sr-btn-primary pull-right" ng-click="applyBookgroups()">OK</a>
		                    </div>					                    	

		                </div>
		            </div>
		        </div>
		    </div>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer('simpel-reserveren'); ?>
