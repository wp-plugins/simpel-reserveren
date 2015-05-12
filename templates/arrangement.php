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
							<h2><?php echo utf8_decode($arrangement->title) ?></h2>
						</div>
					</div>

					 <div class="row">
						 <div class="col-sm-6 sr-object-photos">
							<img src="<?php echo  $arrangement->img ?>" title="<?php echo  $arrangement->title ?>"/>
						 </div>
						 <!-- /.col-sm-6 -->
						 <div class="col-sm-6">
								<?php echo utf8_decode($arrangement->omschrijving) ?>
						 </div>
						 <!-- /.col-sm-6 -->
					 </div>
					 <!-- /.row -->

					<div id="accommodatie-arrangementen"></div>
					

				 <div class="sr-meer-informatie">
			
						<ul id="informatieTab" class="nav nav-tabs">
                            <li class="active"><a href="#arr-overview" data-toggle="tab"><?php echo  __('Overview', 'simpelreserveren') ?></a></li>
                            <li><a href="#arr-images" data-toggle="tab"><?php echo  __('Images', 'simpelreserveren') ?></a></li>
                            <li><a href="#arr-terms" data-toggle="tab"><?php echo  __('Terms & conditions', 'simpelreserveren') ?></a></li>

						</ul>


						<div id="informatieTabContent" class="tab-content">
                            <div class="tab-pane fade active in arr-overview" id="arr-overview">
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


						</div>

				 </div>
				 <!-- /.sr-meer-informatie -->

					
			</div>
			
			<!-- sidebar -->
				<div class="col-sm-4 sidebar tab-pane sr-kassabon">

				<h3><?php echo  _sr('Accommodaties') ?></h3>


                <?php $this->show_prices( null, null, $arrangement->id ); ?>



                    <!-- #mainTabContent -->

		</div>


		</div>
	</div>
</div>
<?php get_footer('simpel-reserveren'); ?>