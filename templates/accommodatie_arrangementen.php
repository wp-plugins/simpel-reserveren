<?php if(count($arrangementen)) : ?>
		<div class="row sr-arrangementen">
            <div class="col-sm-12">
              <h3><?php echo  __('Arrangements', 'simpelreserveren') ?></h3>
            </div>
	<?php foreach($arrangementen as $arrangement) : ?>
			<div class="col-sm-12" onclick="go('<?php echo  $arrangement->url ?>')">
  				<div class="sr-boeken clearfix sr-secondary-container">
                    <div class="sr-boeken-button pull-right">
                      <a href="<?php echo  $arrangement->url ?>" class="btn sr-secondary-button"><?php echo  __('Book', 'simpelreserveren') ?> <?php echo  $arrangement->naam ?>!</a>
                    </div>
					<p><?php echo $arrangement->naam ?> 
    		<?php if($arrangement->periodeaanbieding > 0) : ?>
    				<span class="sr-boeken-prijs-van">&euro; <?php echo  number_format($arrangement->periodeprijs, 2, ',', '.') ?></span>
    				<span class="sr-boeken-prijs-voor">&euro; <?php echo  number_format($arrangement->periodeaanbieding, 2, ',', '.') ?></span>
    		<?php else : ?>
    				<span class="sr-boeken-prijs-voor">&euro; <?php echo  number_format($arrangement->periodeprijs, 2, ',', '.') ?></span>
    		<?php endif ?>
    </p>
    <?php if($arrangement->omschrijving) : ?>
      <p><?php echo  $arrangement->omschrijving ?></p>
    <?php endif; ?>
    <p><?php echo  date('d-m', strtotime($arrangement->van)) ?> <?php echo  __('until', 'simpelreserveren') ?> <?php echo  date('d-m-Y', strtotime($arrangement->tot)) ?></p>
						</div>
					</div>
			<?php endforeach; ?>
		</div>
<?php endif; ?>