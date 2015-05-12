<?php get_header('simpel-reserveren'); ?>
     
	<div class="simpel-reserveren container">
        
    <div class="row">
      
      <div class="only-phone">
        <ul id="mainTab" class="nav nav-tabs">
          <li class="active">
            <a href="#resultaten" id="resultatenTab" ><?php echo  count($results) ?> <?php echo  __('found', 'simpelreserveren'); ?></a>
          </li>
          <li>
            <a href="#filters" id="filtersTab"><?php echo  __('Filter facilities', 'simpelreserveren') ?></a>
          </li>   
        </ul>
      </div>
      <div class="clear"></div>
      
      <div id="mainTabContent">
        <!-- sidebar -->
        <div class="col-sm-4 sidebar">

          <div class="sr-order-box collapse clearfix">
            <div class="cont">
              <div class="row">
                <div class="col-xs-12"><h2><?php echo  __('Make reservation', 'simpelreserveren') ?></h2></div>
                <form action="<?php echo  $this->home_url() ?>arrangementen/" role="form" id="zoek-form">
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
                    </div>
                    <!-- /.row -->
                    <div class="row">
                      <div class="col-xs-6">
                        <label for="volw"><?php echo  __('Adults', 'simpelreserveren'); ?></label>
                        <input type="text" class="form-control" id="volw" name="volw" value="">
                      </div>
                      <div class="col-xs-6">
                        <label for="kind"><?php echo  __('Children', 'simpelreserveren'); ?></label>
                        <input type="text" class="form-control" id="kind" name="kind" value="">
                      </div>  
                    </div>
                    <!-- /.row -->
                    
                    <div class="row">
                      <div class="col-xs-6">
                      <label for="type"><?php echo  __('Accommodation', 'simpelreserveren'); ?></label>
                      <select name="type" class="form-control" id="type" name="type">
                            <option value="0"><?php echo  __('No preference', 'simpelreserveren'); ?></option>
                            <?php foreach($this->types as $row) : ?>
                              <option value="<?php echo $row->id?>"><?php echo $row->title ?></option>
                            <?php endforeach; ?>
                        </select> 
                      </div>
                      <div class="col-xs-6"><button type="submit" class="btn sr-primary-button"><?php echo  $this->get_setting('btn-zoeken'); ?></button></div>
                    </div>
                    <div class="row pursuaision">
                        <div class="col-xs-12">
                            <span class="pull-right"><?php echo $this->get_setting('pursuision'); ?></span>
                        </div>
                    </div>

                  </div>

                </form>
                
              </div>
            </div>
          </div>
          <!-- /.sr-order-box collapse -->
          
        


        </div>
        <!-- main content -->

        <div class="col-sm-8 tab-pane visible list" id="resultaten">
        <?php if(count($zoek->alternative_parameters)) : ?>
          <div class="simpel-melding no-margin alert alert-warning">
            <?php echo __('0 Accommodations found with current search request, changed the following:', 'simpelreserveren'); ?>
            <ul><li><?php echo implode('</li><li>', $zoek->alternative_parameters) ?></li></ul>
          </div>
        <?php else: ?>
          <?php echo $this->show_message('zoeken'); ?>
        <?php endif; ?>
          <h1 class="sr-zoek-head"><?php echo  count($results) ?> <?= __('packages', 'simpelreserveren') ?> <?php echo  __('found', 'simpelreserveren'); ?></h1>
          <div class="row sr-zoekresultaten">
          <?php if(is_array($results) && count($results)) : ?>
            <?php $j = 0; ?>
            <?php foreach($results as $arrangement) : ?>
                <div class="arrangement">
                  <article class="sr-zoekresultaat">
                    <div class="sr-images">
                      <img src="<?php echo $arrangement->resized_img ?>" alt="<?php echo $arrangement->title?>" title="<?php echo  $arrangement->title ?>" class="main-img"/>
                      <?php if(count($arrangement->afbeeldingen)) : ?>
                          <?php foreach($arrangement->thumbs as $i => $thumb) : ?>
                              <div class="col-sm-3 thumb hidden-phone">
                                  <img src="<?php echo  $thumb ?>" alt="<?php echo $arrangement->title?>" title="<img src='<?php echo  $arrangement->afbeeldingen[$i] ?>'>"/>
                              </div>
                              <?php if($i>=3) break; ?>
                          <?php endforeach; ?>
                      <?php endif; ?>
                    </div>

                    <div class="sr-text">
                      <h2><a href="<?php echo  $arrangement->url ?>"><?php echo utf8_decode($arrangement->title) ?></a></h2>
                      <p><?php echo  utf8_decode($arrangement->samenvatting) ?> <a href="<?= $arrangement->url ?>">meer info</a></p>


                    

                        <div class="clearfix"></div>
                        <?php /*<h3><?php echo  __('Alternative prices', 'simpelreserveren') ?></h3> */ ?>
                        <?php foreach($arrangement->accos as $accommodatie) : ?>
                            <div class="clearfix" >
                                <div class="sr-boeken-arrangement sr-boeken-alternatieve-prijs row" data-url="<?php echo  $accommodatie->url ?>?arrangement=<?= $arrangement->id ?>" data-price="<?= $accommodatie->prijs ?>" data-title="<?= $arrangement->title ?>">
                                    <div class="col-xs-5 sr-title">
                                    <?php echo $accommodatie->title ?>
                                  </div>
                                  <div class="col-xs-4 sr-prijs">
                                      <?php if($accommodatie->korting) : ?>
                                        <span class="korting sr-boeken-prijs-van"><?= number_format($accommodatie->prijs + $accommodatie->korting, 2) ?></span>
                                      <?php endif; ?>
                                      <span class="sr-boeken-prijs-voor">&euro; <?= number_format($accommodatie->prijs, 2) ?></span>
                                  </div>
                                  <div class="col-xs-3 sr-book">
                                    <a class="pull-right" href="<?php echo $accommodatie->boek_url ?>?arrangement=<?= $arrangement->id ?>"><?php echo  __('book now', 'simpelreserveren'); ?></a>
                                  </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                  </article>
                </div>
                <?php if($j++ % 2 == 1) : ?>
                  <div class="clearfix"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php else : ?>
            <br/><?php echo  __('There are no accomodations found with the current search criterea, please adjust and try again.', 'simpelreserveren'); ?>
        <?php endif; ?>
            
          </div>
        </div>
      </div><!-- #mainTabContent -->
      
    </div>
  </div>
<?php get_footer('simpel-reserveren'); ?>