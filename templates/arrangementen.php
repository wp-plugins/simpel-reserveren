<div class="arrangementen-inner">

    <?php foreach($arrangementen as $arrangement) : ?>

        <div class="lm-item arrangement" onclick="document.location = '<?php echo $accommodatie->url?>';">
            <div class="afbeelding">
                <div class="img-divider"></div>
                <div class="img-accommodatie" style="background-image: url('<?php echo $arrangement->accommodatie->img?>');"></div>
                <div class="img-arrangement" style="background-image: url('<?php echo  $arrangement->img ?>');"></div>
            </div>
            <div class="sterren ster<?php echo  $accommodatie->sterren ?>"></div>
            <div class="name"><a href="<?php echo $accommodatie->url?>"><?php echo $arrangement->accommodatie->title?> + <?php echo  $arrangement->title ?></a></div>
            <?php if(SIMPEL_MULTIPLE) : ?>
                <div class="camping"><?php echo  $arrangement->accommodatie->camping->title ?></div>
            <?php endif; ?>
            <div class="personen"><?php echo  $arrangement->accommodatie->aantal_personen ?> <?php echo  __('persons', 'simpelreserveren') ?></div>
            <div class="prijs">
                <div class="melding">vanaf prijs per week:<br/><br/></div>
                <div class="origineel no-strike">&euro; <?php echo number_format($arrangement->accommodatie->vanaf_prijs, 2)?> + &euro; <?php echo number_format($arrangement->prijs, 2)?> </div>
                <div class="huidig">&euro; <?php echo number_format($arrangement->accommodatie->vanaf_prijs + $arrangement->prijs, 2, ',', '.')?></div>
                <?php if($accommodatie->inclusief && 0) : ?>
                    <div class="melding"><?php echo  __('Price is including', 'simpelreserveren'); ?> <?php echo  $accommodatie->inclusief ?> <?php echo  __('persons', 'simpelreserveren') ?></div>
                <?php endif; ?>
            </div>
            <div class="clear"></div>
            
            <a href="<?php echo  $accommodatie->boek_url ?>?aankomst=<?php echo  $accommodatie->van ?>&vertrek=<?php echo  $accommodatie->tot ?>" class="more-info"><?php echo  __('Book Now', 'simpelreserveren'); ?></a>
            <div class="clear"></div>
        </div>

    <?php endforeach; ?>

</div>
<div class="clear"></div>