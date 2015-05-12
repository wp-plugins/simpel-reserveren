<?php if(count($alternatieve_prijzen)) : ?>
	<div class="row sr-speciale-aanbiedingen">
		<div class="col-sm-12">
			<h3><?php echo  __('Alternative prices', 'simpelreserveren') ?></h3>
		</div>
		 <?php foreach($alternatieve_prijzen as $periode => $prijs) : ?>
				<div class="col-sm-12" onclick="go('<?php echo  $prijs['url'] ?>')">
					<div class="sr-boeken clearfix sr-secondary-container">
							<div class="sr-boeken-prijs">
								<?php if($prijs['korting']) : ?>
										<p><?php echo  $periode ?>  
												<span class="sr-boeken-prijs-van">&euro; <?php echo number_format($prijs['prijs'] + $prijs['korting'], 2)?></span>
												<span class="sr-boeken-prijs-voor">&euro; <?php echo number_format($prijs['prijs'], 2, ',', '.')?></span>
										</p>
										<p><?php echo  $prijs['aanbieding'] ?> <?php echo  __('discount', 'simpelreserveren') ?></p> 
								<?php else: ?>
										<p><?php echo  $periode ?> 
												<span class="sr-boeken-prijs-voor">&euro; <?php echo number_format($prijs['prijs'], 2, ',', '.')?></span>
										</p>
								<?php endif; ?>
								<p><?php echo  $prijs['van'] ?> <?php echo  __('until', 'simpelreserveren') ?> <?php echo  $prijs['tot'] ?></p>

							</div>
							<div class="sr-boeken-button">
								<a href="#" class="btn sr-secondary-button"><?php echo  __('Book now', 'simpelreserveren') ?></a>
							</div>
					</div>
				</div>
		<?php endforeach; ?>

	</div>
<?php endif; ?>