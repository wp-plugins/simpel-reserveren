<?php get_header('simpel-reserveren'); ?>
<div class="simpel-reserveren container">
		<div class="row">

		
			
			<div class="only-phone">
				<ul id="mainTab" class="nav nav-tabs">
					<li class="active">
						<a href="#boeken" id="boekenTab"><?php echo  __('Accommodation', 'simpelreserveren') ?></a>
					</li>
					<li>
						<a href="#kassabon" id="kassabonTab"><?php echo  __('Availability', 'simpelreserveren') ?></a>
					</li>	 
				</ul>
			</div>
			
			<div id="mainTabContent" class="sr-accommodatie">
				
				
				<!-- main content -->
				<div class="col-sm-8 tab-pane visible" id="boeken">
					<?php echo $this->show_message('accommodatie'); ?>
					<div class="row">
						<div class="col-xs-12">
							<h2><?php echo  $accommodatie->title ?></h2>
						</div>
					</div>

					 <div class="row">
						 <div class="col-sm-6 sr-object-photos">
							<img src="<?php echo  $accommodatie->img ?>" title="<?php echo  $accommodatie->title ?>"/>
							<div class="row">
								<?php if(count($accommodatie->afbeeldingen)) : ?>
										<?php foreach($accommodatie->thumbs as $i => $thumb) : ?>
												<div class="col-xs-4 thumb"><a href="<?php echo  $accommodatie->afbeeldingen_large[$i] ?>" class="fancybox" rel="fancybox-thumb"><img src="<?php echo  $thumb ?>" alt=""/></a></div>
										<?php endforeach; ?>
								<?php endif; ?>
							</div>
						 </div>
						 <!-- /.col-sm-6 -->
						 <div class="col-sm-6">
								<div class="sr-boeken clearfix sr-accommodatie-prijs"></div>	
								<?php echo  $accommodatie->omschrijving ?>
								<?php if($accommodatie->button_url) : ?>
										<a href="<?php echo  $accommodatie->button_url ?>" class="btn btn-default" target="_blank"><?php echo  $accommodatie->button_tekst ?></a>
								<?php endif; ?>
						 </div>
						 <!-- /.col-sm-6 -->
					 </div>
					 <!-- /.row -->

					<div id="accommodatie-arrangementen"></div>
					

				 <div class="sr-meer-informatie">
			
						<ul id="informatieTab" class="nav nav-tabs">
                            <?php if(isset($_GET['arrangement'])) : ?>
                                <li class="active"><a href="#arr-overview" data-toggle="tab"><?php echo  __('Overview', 'simpelreserveren') ?></a></li>
                                <li><a href="#informatieFaciliteiten" data-toggle="tab"><?php echo  __('Facilities', 'simpelreserveren') ?></a></li>
                                <li><a href="#arr-images" data-toggle="tab"><?php echo  __('Images', 'simpelreserveren') ?></a></li>
                                <li><a href="#arr-terms" data-toggle="tab"><?php echo  __('Terms & conditions', 'simpelreserveren') ?></a></li>

                            <?php else: ?>
                                <li class="active"><a href="#informatieFaciliteiten" data-toggle="tab"><?php echo  __('Facilities', 'simpelreserveren') ?></a></li>
                                <li><a href="#informatieAfbeeldingen" data-toggle="tab"><?php echo  __('Images', 'simpelreserveren') ?></a></li>
                                <?php if($accommodatie->camping->plattegrond_full) : ?>
                                    <li><a href="#informatiePlattegrond" data-toggle="tab"><?php echo  __('Plan', 'simpelreserveren') ?></a></li>
                                <?php endif; ?>
                            <?php endif; ?>
						</ul>


						<div id="informatieTabContent" class="tab-content">
                            <?php if(isset($_GET['arrangement'])) : ?>
                                <?php $arrangement = new Arrangement($_GET['arrangement']); ?>
                                <div class="tab-pane fade <?= (isset($_GET['arrangement']) ? 'active' : '') ?> in arr-overview" id="arr-overview">
                                    <h3><?php echo  __('Overview', 'simpelreserveren') ?></h3>
                                    <?= $arrangement->overview ?>
                                </div>
                                <div class="tab-pane fade" id="arr-images">
                                    <h3><?php echo  __('Images', 'simpelreserveren') ?></h3>
                                    <div class="row">
                                        <?php
                                        if(count($arrangement->afbeeldingen))
                                        {
                                            foreach($arrangement->thumbs as $i => $thumb)
                                            {
                                                ?><div class="thumb col-xs-3"><a href="<?php echo  $arrangement->afbeeldingen_large[$i] ?>" class="fancybox" rel="fancybox-thumb"><img src="<?php echo  $thumb ?>" alt=""/></a></div><?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade in arr-terms" id="arr-terms">
                                    <h3><?php echo  __('Terms & conditions', 'simpelreserveren') ?></h3>
                                    <?= $arrangement->terms ?>
                                </div>

                            <?php endif; ?>
							<div class="tab-pane fade <?= (isset($_GET['arrangement']) ? '' : 'active') ?> in faciliteiten" id="informatieFaciliteiten">
								<div>
									<div class="col-xs-6">
										<h3><?php echo  __('Accommodation', 'simpelreserveren') ?></h3>
										<ul>
										<?php foreach($accommodatie->faciliteiten as $facil) { 
												if(preg_match('/voorkeur/is', $facil->title)) continue; 
												?>
												<li><span class="ss-icon facil">&<?php echo  $facil->icon ?></span> <?php echo utf8_decode($facil->title)?></li>
										<?php } ?>
										</ul>
									</div>
									<div class="col-xs-6">
										<h3><?php echo  _sr('Camping') ?></h3>
										<ul>
										<?php foreach($accommodatie->camping->faciliteiten as $facil) { 
												if(preg_match('/voorkeur/is', $facil->title)) continue; 
												?>
												<li><span class="ss-icon facil">&<?php echo  $facil->icon ?></span> <?php echo utf8_decode($facil->title)?></li>
										<?php } ?>
										</ul>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="informatieAfbeeldingen">
								<h3 style="margin-left:15px"><?php echo  __('Images', 'simpelreserveren') ?></h3>
								<div>
										<?php
										if(count($accommodatie->afbeeldingen)) 
										{ 
												foreach($accommodatie->thumbs as $i => $thumb) 
												{
														?><div class="thumb col-xs-3"><a href="<?php echo  $accommodatie->afbeeldingen_large[$i] ?>" class="fancybox" rel="fancybox-thumb"><img src="<?php echo  $thumb ?>" alt=""/></a></div><?php
												}	 
										}
										?>
								</div>
							</div>
							<?php if($accommodatie->camping->plattegrond_full) : ?>
									<div class="tab-pane fade" id="informatiePlattegrond">
										<h3><?php echo  __('Plan', 'simpelreserveren') ?></h3>
										<p>
												<a href="<?php echo  $accommodatie->camping->plattegrond_full ?>" class="fancybox"><img src="<?php echo  $accommodatie->camping->plattegrond_full ?>" alt=""/></a>
										</p>
									</div>
							<?php endif; ?>
						</div>

				 </div>
				 <!-- /.sr-meer-informatie -->

					
			</div>
			
			<!-- sidebar -->
				<div class="col-sm-4 sidebar tab-pane sr-kassabon" id="prijsberekening">

				<h3><?php echo  __('Availability', 'simpelreserveren') ?></h3>	
				
		
				<div class="row">
					<div class="col-sm-12">

						<?php $this->show_prices( $accommodatie->id ); ?>

					
					
					</div>
				
				</div>

				<div id="accommodatie-alternatieve-prijzen"></div>

				
				 <?php $aanbiedingen = $accommodatie->get_all_aanbiedingen(); ?>
				 <?php if(count($aanbiedingen)) : ?>
				 	<div class="row sr-aanbiedingen">
						<div class="col-sm-12">
							<h3><?php echo  __('Discounts', 'simpelreserveren') ?></h3>
						</div>
						<div class="col-sm-12">
		                    <?php foreach($aanbiedingen as $aanbieding) : ?>
		                        <button type="button" class="btn btn-default discount" data-cont="body" data-toggle="popover" data-placement="top" data-content="<?php echo  ($aanbieding->voorwaarden ? nl2br($aanbieding->voorwaarden) : $aanbieding->geldig) ?>" data-original-title="" title=""><?php echo $aanbieding->omschrijving?></button>
		                    <?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

			<!-- #mainTabContent -->

		</div>


		</div>
	</div>
</div>
<?php get_footer('simpel-reserveren'); ?>