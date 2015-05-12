<div class="campings">

     <?php foreach($campings as $camping) : ?>
     
        <div class="camping" onclick="document.location = '<?php echo  $camping->url ?>';">
            <div class="logo">
                <img src="<?php echo  $camping->logo_url ?>" alt="<?php echo  $camping->title ?>" />
            </div>
            <div class="title">
                <a href="<?php echo  $camping->url ?>"><?php echo  $camping->title ?></a>
            </div>
            <?php echo  $camping->samenvatting ?><br/>
            <a href="<?php echo  $camping->url ?>"><?php echo  __('Read more', 'simpelreserveren'); ?></a>
        </div>

    <?php endforeach; ?>

</div>
