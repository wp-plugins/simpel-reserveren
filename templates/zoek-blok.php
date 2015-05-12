<div class="simpel-reserveren simpel-reserveren-mobile-boeken">
    <button type="button" class="btn sr-primary-button navbar-btn pull-right only-phone" data-toggle="collapse" data-target=".sr-order-box"><?php echo  __('Book now', 'simpelreserveren') ?></button>
</div>
<div id="zoeken" class="simpel-reserveren collapse zoeken <?php echo  $mode ?> sr-order-box <?php echo  $this->theme_settings['zoek_blok_position'] ?> clearfix">
    <div class="cont">
        <div class="row">
            <div class="col-xs-12"><h3><?php echo  __('Make reservation', 'simpelreserveren') ?></h3></div>
     
     
        	<form method="get" action="<?php echo  $this->home_url() ?>/zoeken/" id="zoek-form" role="form">
                <div class="cont">
                    <div class="row">
                        <div class="col-xs-6">
                            <label for="aankomst"><?php echo  __('Arrival', 'simpelreserveren'); ?></label>
                            <input type="text" class="form-control" id="aankomst" name="aankomst" value="">
                        </div>
                        <div class="col-xs-6">
                            <label for="vertrek"><?php echo  __('Departure', 'simpelreserveren'); ?></label>
                            <input type="text" class="form-control" id="vertrek" name="vertrek" value="">
                        </div>  

                        <div class="col-xs-6 adults">
                            <label for="volw"><?php echo  __('Adults', 'simpelreserveren'); ?></label>
                            <input type="text" class="form-control" id="volw" name="volw" value="">
                        </div>
                        <div class="col-xs-6 children">
                            <label for="kind"><?php echo  __('Children', 'simpelreserveren'); ?></label>
                            <input type="text" class="form-control" id="kind" name="kind" value="">
                        </div>  

                        <div class="col-xs-6">
                            <label for="type"><?php echo  _sr('Type'); ?></label>
                            <select name="type" class="form-control" id="type">
                                <option value="0"><?php echo  __('No preference', 'simpelreserveren'); ?></option>
                                <?php foreach($this->types as $row) : ?>
                                    <option value="<?php echo $row->id?>"><?php echo $row->title ?></option>
                                <?php endforeach; ?>
                            </select> 
                    
                        </div>
                        <div class="col-xs-6"><button type="submit" class="btn sr-primary-button"><?php echo  $this->get_setting('btn-zoeken'); ?></button></div>

                    </div>

                    <div class="row persuasion">
                        <div class="col-xs-12">
                            <span class="pull-right"><?php echo $this->get_setting('pursuision'); ?></span>
                        </div>
                    </div>

                </div>


            </form>
        </div>     
    </div>
</div>