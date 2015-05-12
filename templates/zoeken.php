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
                <form action="<?php echo  $this->home_url() ?>zoeken/" role="form" id="zoek-form">
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
          
        
          <div id="filters" class="tab-pane sr-filter-box">
            <h3><?php echo  __('Filter facilities', 'simpelreserveren'); ?></h3>
            <?php foreach($faciliteiten as $facil) { ?>
                <a class="filter <?php echo (isset($_GET['facil'.$facil->id]) ? 'selected' : '')?>" href="<?php echo  setParam('facil'.$facil->id, (isset($_GET['facil'.$facil->id]) ? '' : 1)) ?>">
                  <span class="facil-icon ss-icon">&<?php echo  $facil->icon ?></span>
                  <span class="title"><?php echo  $facil->title ?></span>
                    <?php if(isset($_GET['facil'.$facil->id]) ) : ?>
                        <span class="actief"><?php echo  __('active', 'simpelreserveren'); ?></span>
                    <?php else : ?>
                        <span class="nr">(<?php echo (isset($facil->accos) && is_array($facil->accos) ? count($facil->accos) : 0) ?>)</span>
                    <?php endif; ?>
                </a>
            <?php } ?>
                    
            <div class="filter-info"><?php echo  __('Click on the faclity to switch on or off', 'simpelreserveren'); ?></div>
                        
            <a href="#resultaten" id="filtersTerugTab" class="btn btn-default only-phone">Terug naar zoekresultaten</a>

          </div>          

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
          <div class="pull-right view-modes">
            <a href="javascript:;" class="selected" data-view="list"><span class="glyphicon glyphicon-th-list"></span></a>
            <a href="javascript:;" data-view="square"><span class="glyphicon glyphicon-th-large"></span></a>
          </div>
          <h1 class="sr-zoek-head"><?php echo  count($results) ?> <?php echo strtolower(_sr('Accommodaties')) ?> <?php echo  __('found', 'simpelreserveren'); ?></h1>
          <div class="row sr-zoekresultaten">
          <?php if(is_array($results) && count($results)) : ?>
            <?php $j = 0; ?>
            <?php foreach($results as $accommodatie) : ?>
                <div class="acco">
                  <article class="sr-zoekresultaat" data-url="<?php echo $accommodatie->url . ($accommodatie->arrangement ? '?arrangement='.$accommodatie->arrangement->id : '')?>" data-name="<?= $accommodatie->title ?>" data-id="<?= $accommodatie->id ?>" data-price="<?= $accommodatie->prijs ?>" data-category="<?= $accommodatie->type->title ?>">
                    <div class="sr-images">
                      <img src="<?php echo $accommodatie->resized_img ?>" alt="<?php echo $accommodatie->title?>" title="<?php echo  $accommodatie->title ?>" class="main-img"/>
                      <?php if(count($accommodatie->afbeeldingen)) : ?>
                          <?php foreach($accommodatie->thumbs as $i => $thumb) : ?>
                              <div class="col-sm-3 thumb hidden-phone">
                                  <img src="<?php echo  $thumb ?>" alt="<?php echo $accommodatie->title?>" title="<img src='<?php echo  $accommodatie->afbeeldingen[$i] ?>'>"/>
                              </div>
                              <?php if($i>=3) break; ?>
                          <?php endforeach; ?>
                      <?php endif; ?>
                    </div>

                    <div class="sr-text">
                      <h2><a href="<?php echo  $accommodatie->url  . ($accommodatie->arrangement ? '?arrangement='.$accommodatie->arrangement->id : '')?>"><?php echo  $accommodatie->title ?></a></h2>
                      <?php if(SIMPEL_MULTIPLE) : ?>
                        <h3><a href="<?php echo  $accommodatie->camping->url ?>"><?php echo  $accommodatie->camping->title ?></a></h3>
                      <?php endif; ?>
                      <p><?php echo  $accommodatie->samenvatting ?><a href="#">meer info</a></p>
                    </div>

                    <div class="sr-boeken clearfix">
                        <?php if(!isset($_GET['arrangement']) && defined('SIMPEL_ONLY_ARRANGEMENT') && SIMPEL_ONLY_ARRANGEMENT) : ?>
                            <?php $arrangementen = $accommodatie->get_new_arrangementen(filter_input(INPUT_GET, 'aankomst'), filter_input(INPUT_GET, 'vertrek')); ?>
                            <?php foreach ($arrangementen as $arrangement) : ?>
                                <div class="sr-boeken-arrangement  sr-boeken-alternatieve-prijs row" data-url="<?php echo  $accommodatie->url ?>?arrangement=<?= $arrangement->id ?>" data-price="<?= $arrangement->accommodatie->prijs ?>" data-title="<?= $arrangement->title ?>">
                                    <div class="col-xs-6 sr-title">
                                        <?php echo utf8_decode($arrangement->title) ?>
                                    </div>
                                    <div class="col-xs-3 sr-prijs">
                                        <?php if($arrangement->accommodatie->korting) : ?>
                                            <span class="korting"><?= number_format($arrangement->accommodatie->prijs + $arrangement->accommodatie->korting, 2) ?></span>
                                        <?php endif; ?>
                                        &euro; <?= number_format($arrangement->accommodatie->prijs, 2) ?>
                                    </div>
                                    <div class="col-xs-3 sr-book">
                                        <a class="pull-right" href="<?php echo $arrangement->accommodatie->boek_url ?>?arrangement=<?= $arrangement->id ?>"><?php echo  __('book now', 'simpelreserveren'); ?></a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                          <?php if($accommodatie->alternative) : ?>
                            <?php $van = date('d-m-Y', strtotime($accommodatie->van)); $tot = date('d-m-Y', strtotime($accommodatie->tot)); ?>
                              <div class="sr-boeken-alert alert-warning">
                                  <strong><?php echo  __('No price found for the selected period', 'simpelreserveren'); ?></strong><br/>
                                  <?php echo  __('Price for', 'simpelreserveren'); ?> <?php echo date('d-m', strtotime($accommodatie->van))?> tot <?php echo date('d-m-Y', strtotime($accommodatie->tot))?>:
                              </div>
                          <?php else : ?>
                              <?php $van = date('d-m-Y', strtotime($_GET['aankomst'])); $tot = date('d-m-Y', strtotime($_GET['vertrek'])); ?>
                          <?php endif ?>

                          <div class="sr-boeken-prijs clearfix">
                            <?php if($accommodatie->korting) : ?>
                              <?php $button_class = 'discount'; ?>
                                <p>
                                    <?php echo  $accommodatie->periode_title ?>
                                    <?php echo  __('From', 'simpelreserveren') ?> <span class="sr-boeken-prijs-van">&euro; <?php echo number_format($accommodatie->prijs + $accommodatie->korting, 2)?></span>
                                    <?php echo  __('for', 'simpelreserveren') ?> <span class="sr-boeken-prijs-voor">&euro; <?php echo number_format($accommodatie->prijs, 2, ',', '.')?></span>
                                </p>
                                <p><?php echo ( isset($accommodatie->aanbieding) && isset($accommodatie->aanbieding->omschrijving) ? $accommodatie->aanbieding->omschrijving : '') ?></p>
                            <?php else: ?>
                              <?php $button_class = 'no-discount'; ?>
                                <p>
                                    <?php echo  $accommodatie->periode_title ?>
                                    <span class="sr-boeken-prijs-voor">&euro; <?php echo number_format($accommodatie->prijs, 2, ',', '.')?></span>
                                </p>
                            <?php endif; ?>
                          </div>
                          <div class="sr-boeken-button <?php echo  $button_class ?>">
                            <a href="<?php echo  $accommodatie->boek_url . '?aankomst=' . $van . '&vertrek=' . $tot . ($accommodatie->arrangement ? '&arrangement='.$accommodatie->arrangement->id : '')?>" class="btn sr-primary-button"><?php echo  __('book now', 'simpelreserveren') ?></a>
                          </div>
                            <?php if($accommodatie->arrangement) : ?>
                                <div style="clear:both"></div>
                                <div class="alert alert-success no-margin"> <?=  __('Included', 'simpelreserveren') ?> <?= $accommodatie->arrangement->title  ?> <span class="fa fa-info-circle sr-tip" title="<?= nl2br($accommodatie->arrangement->omschrijving) ?>"></span></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if((!defined('SIMPEL_HIDE_ALTERNATIVE') || !SIMPEL_HIDE_ALTERNATIVE) && count($accommodatie->alternatieve_prijzen)) : ?>
                        <div class="clearfix"></div>
                        <?php /*<h3><?php echo  __('Alternative prices', 'simpelreserveren') ?></h3> */ ?>
                        <?php foreach($accommodatie->alternatieve_prijzen as $periode => $prijs) : ?>
                            <div class="clearfix cont">
                                <div class="sr-boeken-alternatieve-prijs row" onclick="go('<?php echo  $prijs['url'] ?>')">
                                  <div class="col-xs-3 col-sm-3 sr-periode">
                                    <?php echo  $periode ?>
                                  </div>
                                  <div class="col-xs-3 col-sm-3 sr-datum">
                                    <?php echo  date('d/m', strtotime($prijs['van'])) ?> - <?php echo  date('d/m', strtotime($prijs['tot'])) ?>
                                  </div>
                                  <div class="col-sm-2 hidden-phone sr-korting">
                                    <?php if($prijs['korting']) : ?>
                                          <span class="sr-boeken-prijs-van">&euro; <?php echo  number_format($prijs['prijs'] + $prijs['korting'], 2) ?></span>
                                    <?php endif; ?>
                                  </div>
                                  <div class="col-sm-4 col-xs-6 sr-prijs">
                                    <span class="sr-boeken-prijs-voor">&euro; <?php echo  number_format($prijs['prijs'], 2) ?></span>
                                    <a class="pull-right" href="<?php echo  $prijs['url'] ?>"><?php echo  __('book now', 'simpelreserveren'); ?></a>
                                  </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

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