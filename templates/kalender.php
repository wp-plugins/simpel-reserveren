<script>
    <?php if(isset($accommodatie)) : ?>
        var beschikbaarheid = <?php echo json_encode($accommodatie->beschikbaarheid)?>;
        var type = 'accommodatie';
    <?php else: ?>
        var beschikbaarheid = <?php echo json_encode($arrangement->beschikbaarheid)?>;
        var type = 'arrangement';
    <?php endif; ?>
    var root_url = '<?php echo  home_url(); ?>';
</script>
<div class="sr-periode">
  <form action="">
    <?php if(isset($camping) && $camping) : ?>
     <div class="form-group">
      <label for="accommodatie"><?php echo  __('Accommodation', 'simpelreserveren'); ?></label>
      <select id="accommodatie" name="accommodatie" class="form-control">
            <?php foreach($accommodaties as $acco) : ?>
                <option value="<?php echo  $acco->id ?>"><?php echo  $acco->title ?></option>
            <?php endforeach; ?>
      </select>
     </div>
    <?php else: ?>
        <?php if(isset($accommodatie)) : ?>
            <input type="hidden" id="accommodatie" name="accommodatie" value="<?php echo  $accommodatie->id ?>" />
        <?php else: ?>
            <input type="hidden" id="arrangement" name="arrangement" value="<?php echo  $arrangement->id ?>" />
        <?php endif; ?>
    <?php endif; ?>

     <div class="form-group">
      <label for="aankomst"><?php echo  __('Arrival', 'simpelreserveren'); ?></label>
      <input class="form-control" id="aankomst" value="">
    </div>
    <div class="form-group">
      <label for="vertrek"><?php echo  __('Departure', 'simpelreserveren'); ?></label>
      <input class="form-control" id="vertrek" value="">
    </div>
  </form>
</div>

<div id="kalender" class="table-responsive"></div>
<div id="legenda">
    <h4><?php echo  __('Legenda', 'simpelreserveren'); ?></h4>
    <div class="uitleg"><?php echo  __('Click to select arrival and departure date', 'simpelreserveren') ?>.</div>
    <div class="blokje aankomst"></div><div class="floatleft"><?php echo  __('Arrival or departure possible', 'simpelreserveren') ?></div>
    <div class="blokje beschikbaar"></div><div class="floatleft"><?php echo  __('Available', 'simpelreserveren') ?></div>
    <div class="blokje bezet"></div><div class="floatleft"><?php echo  __('Taken', 'simpelreserveren') ?></div>
    <div class="blokje geselecteerd"></div><div class="floatleft"><?php echo  __('Selected', 'simpelreserveren') ?></div>
</div>
<div class="sr-accommodatie-prijs sr-boeken clearfix"><?php echo  __('Loading prices', 'simpelreserveren') ?>..</div>

