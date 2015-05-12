<div class="last-minutes-inner">

    <?php foreach($results as $accommodatie) : ?>

        <div class="lm-item" onclick="document.location = '<?php echo $accommodatie->url?>';">
            <div class="afbeelding" style="background-image: url('<?php echo $accommodatie->img?>');"></div>
            <div class="sterren ster<?php echo  $accommodatie->sterren ?>"></div>
            <div class="name"><a href="<?php echo $accommodatie->url?>"><?php echo $accommodatie->title?></a></div>
            <?php if(SIMPEL_MULTIPLE) : ?>
                <div class="camping"><?php echo  $accommodatie->camping->title ?></div>
            <?php endif; ?>
            <div class="personen"><?php echo  $accommodatie->aantal_personen ?> <?php echo  __('persons', 'simpelreserveren') ?></div>
            <div class="prijs">
                <div class="melding"><?php echo  $accommodatie->melding ?></div>
                <div class="origineel">&euro; <?php echo number_format($accommodatie->prijs_orig, 2)?></div>
                <div class="huidig">&euro; <?php echo number_format($accommodatie->prijs, 2, ',', '.')?></div>
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