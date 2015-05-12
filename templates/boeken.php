<?php get_header('simpel-reserveren'); ?>
<div class="simpel-reserveren container zoeken">
    <div class="row">

      <div class="sr-boeken-proces col-sm-8">
            <div class="clearfix">
              <div class="sr-boeken-stap pull-left">
                <?php $stap_titles = array('', __('Price calculation', 'simpelreserveren'), __('Your information', 'simpelreserveren'), __('Confirmation', 'simpelreserveren')); ?>
                <?php echo  $stap_titles[$stap] ?>
              </div>
              <div class="sr-boeken-totaal pull-right">
                <?php echo  __('Total', 'simpelreserveren') ?> &euro;<span class="sr-total"><?php echo  number_format(isset($_SESSION['boeken']['totaal_incl_borgsom']) ? $_SESSION['boeken']['totaal_incl_borgsom'] : 0, 2, ',', '.') ?></span></div>
            </div>
            <div class="progress">
              <div class="progress-bar <?php echo ($stap>1?'done':'')?>" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"><span><?php echo  __('Step', 'simpelreserveren') ?> 1</span></div>
              <div class="progress-bar grey" role="progressbar" aria-valuenow=".5" aria-valuemin="0" aria-valuemax="100" style="width: .5%;"><span></div>
              <div class="progress-bar <?php echo ($stap>2?'done':'')?> <?php echo ($stap<2?'todo':'')?>" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"><span><?php echo  __('Step', 'simpelreserveren') ?> 2</span></div>
              <div class="progress-bar grey" role="progressbar" aria-valuenow=".5" aria-valuemin="0" aria-valuemax="100" style="width: .5%;"><span></div>
              <div class="progress-bar <?php echo ($stap<3?'todo':'')?>" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100" style="width: 33%;"><span><?php echo  __('Step', 'simpelreserveren') ?> 3</span></div>
          </div>
      </div>
      
      <div class="only-phone sr-boeken-tabs">
        <ul id="mainTab" class="nav nav-tabs">
          <li class="active">
            <a href="#boeken" id="boekenTab" ><?php echo  __('Book', 'simpelreserveren') ?></a>
          </li>
          <li>
            <a href="#prijsberekening" id="kassabonTab"><?php echo  __('Receipt', 'simpelreserveren') ?></a>
          </li>   
        </ul>
      </div>
      
      <div id="mainTabContent">
        
        
        <!-- main content -->

        

        <div class="col-sm-8 tab-pane visible" id="boeken">

          <?php echo $this->show_message('boeken'); ?>
          
          <form action="<?php echo  $action ?>" role="form" id="sr-formBoeken" class="formular stap-<?php echo $stap?>" method="post">
            <input type="hidden" name="stap" id="stap" value="<?php echo $stap?>"/>
            <input type="hidden" name="id" id="id" value="<?php echo $accommodatie->id?>"/>

            <?php switch($stap) : 
                case 1: ?>
                     <script type="text/javascript">
                        var _max_personen = <?php echo $accommodatie->aantal_personen + 0?>;
                    </script>
                    <input type="hidden" name="action" id="action" value="check_form" />
                    <input type="hidden" name="next_step" id="next_step" value="<?php echo $volgende_stap?>" />
                      <div class="row">
                        <div class="col-xs-6">
                          <label for="aankomst" id="lbl-aankomst"><?php echo  __('Arrival', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="text" class="form-control validate[required custom[date]] " id="aankomst" name="aankosmt" value="<?php echo  $boeken['aankomst'] ?>">
                        </div>
                        <div class="col-xs-6">
                          <label for="vertrek" id="lbl-vertrek"><?php echo  __('Departure', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="text" class="form-control validate[required custom[date]]" id="vertrek" name="vertrek" value="<?php echo  $boeken['vertrek'] ?>">
                        </div>  
                      </div>
                      <!-- /.row -->
                      <div class="row">
                        <div class="col-xs-6">
                          <label for="volwassenen" id="lbl-volw"><?php echo  __('Adults', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="text" class="form-control validate[funcCall[maxPersonen] required]" id="volw" name="volw" value="<?php echo  $boeken['volw'] ?>">
                        </div>
                        <div class="col-xs-6">
                            <?php if($accommodatie->camping->age_youth || $accommodatie->camping->age_child || $accommodatie->camping->age_baby) : ?>
	                            <?php $boeken['has_children'] = $boeken['youth'] + $boeken['kind'] + $boeken['baby']; ?>
                              <label for="#"><?php echo  __('Do you bring children?', 'simpelreserveren') ?></label><br />
                              <label class="radio-inline">
                                <input type="radio" name="children" id="sr-children-yes" value="1" <?php echo  ($boeken['has_children'] ? 'checked="checked"' : '') ?>>
                                <?php echo  __('Yes', 'simpelreserveren') ?>
                              </label>
                              <label class="radio-inline">
                                <input type="radio" name="children" id="sr-children-no" value="0" <?php echo  ($boeken['has_children'] ? '' : 'checked="checked"') ?>>
                                <?php echo  __('No', 'simpelreserveren') ?>
                              </label>
                            <?php endif; ?>
                        </div> 
                      </div>
                      <div class="row has-children <?php echo  ($boeken['has_children'] ? '' : 'hide') ?>"> 
                        <?php if(!empty($accommodatie->camping->age_youth)) : ?>
                            <div class="col-xs-6">
                              <label for="youth" id="lbl-youth"><?php echo  __('Youth', 'simpelreserveren') ?> <?php echo  $accommodatie->camping->age_youth ?> <?php echo  __('year', 'simpelreserveren') ?></label>
                              <input type="text" class="form-control validate[funcCall[maxPersonen]]" name="youth" id="youth" value="<?php echo  $boeken['youth'] + 0?>">
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($accommodatie->camping->age_child)) : ?>
                            <div class="col-xs-6">
                              <label for="kind" id="lbl-kind"><?php echo  __('Children', 'simpelreserveren') ?> <?php echo  $accommodatie->camping->age_child ?> <?php echo  __('year', 'simpelreserveren') ?></label>
                              <input type="text" class="form-control validate[funcCall[maxPersonen]]" name="kind" id="kind" value="<?php echo  $boeken['kind'] + 0 ?>">
                            </div>
                        <?php endif; ?>
                        <?php if(!empty($accommodatie->camping->age_baby)) : ?>
                            <div class="col-xs-6">
                              <label for="baby" id="lbl-baby"><?php echo  __('Babies', 'simpelreserveren') ?> <?php echo  $accommodatie->camping->age_baby ?> <?php echo  __('year', 'simpelreserveren') ?></label>
                              <input type="text" class="form-control validate[funcCall[maxPersonen]]" name="baby" id="baby" value="<?php echo  $boeken['baby'] + 0?>">
                            </div>
                        <?php endif; ?>
                      </div>
                      <!-- /.row -->
                      

                      <div class="row sr-extras">
                        <div class="col-sm-12">
                          <h3><?php echo  __("Extra's", 'simpelreserveren') ?></h3>
                        </div> 
                        <?php foreach($toeslagen as $j => $toeslag) : ?>
                            <div class="col-sm-6">
                                <div class="checkbox">
                                  <label>
                                    <?php if($toeslag->type == 'ja/nee') : ?>
                                        <input class="check" type="checkbox" name="toeslag-<?php echo $toeslag->id?>" id="toeslag-<?php echo $toeslag->id?>" value="1" data-voorkeursplaats="<?php echo  $toeslag->voorkeursplaats ?>" <?php echo ($toeslag->verplicht || (isset($boeken['toeslag-'.$toeslag->id]) && $boeken['toeslag-'.$toeslag->id]) ? 'checked="checked"' : '') . ($toeslag->verplicht ? ' disabled' : '')?>/>
                                    <?php else : ?>
                                        <select name="toeslag-<?php echo $toeslag->id?>" id="toeslag-<?php echo $toeslag->id?>">
                                        <?php for($i=0; $i<=($toeslag->max ? $toeslag->max : 8); $i++) : ?>
                                            <option value="<?php echo $i?>" <?php echo (isset($boeken['toeslag-'.$toeslag->id]) && $boeken['toeslag-'.$toeslag->id] == $i ? 'selected' : '')?>><?php echo $i?> &euro;<?php echo  number_format($toeslag->totaal_prijs*$i, 2) ?></option>
                                        <?php endfor ?>
                                        </select>
                                    <?php endif ?>
                                    <?php echo  $toeslag->title ?>
                                    <?php if($toeslag->omschrijving) : ?>
                                        <span class="extra-info" data-content="<?php echo  $toeslag->omschrijving ?>" data-cont="body" data-placement="bottom"><span class="glyphicon glyphicon-info-sign"></span></span>
                                    <?php endif; ?>
                                    <span class="badge pull-right">&euro; <?php echo  number_format($toeslag->totaal_prijs, 2) ?></span>
                                  </label>
                                </div>
                            </div>
                        <?php endforeach; ?>                        
                      </div>

                      <?php if($accommodatie->camping->plattegrond_full && $accommodatie->plattegrond && $_SESSION['boeken']['show_plattegrond']) : ?>
                        <div class="row">
                          <div class="col-sm-12">
                            <h3><?php echo  __('Book on map', 'simpelreserveren'); ?></h3>
                            <input type="hidden" name="plaats_voorkeur" id="hidden_plaats_voorkeur" value="<?php echo  $boeken['plaats_voorkeur'] ?>"/>
                            <p>
                              <a href="javascript:;" id="boek-op-plattegrond" class="btn btn-default"><span class="glyphicon glyphicon-zoom-in"></span> <?php echo  __('Click here to view the map and pick your preferred position', 'simpelreserveren'); ?></a>
                            </p>
                            <p id="sr-plattegrond-voorkeur"></p>
                          </div>
                        </div>
                        <div id="plattegrond-layer" style="display:none" class="simpel-reserveren">
                          <div class="row">
                            <div class="col-sm-9 zoom-outer">
                                <img src="<?php echo  $accommodatie->camping->plattegrond_full ?>" id="zoom1" alt=""/>
                            </div>
                            <div class="col-sm-3 form-outer">
                              <a class="close round-close" onclick="jQuery('.sr-close-form.btn').trigger('click');">X</a>
                              <div class="only-phone plattegrond-phone-header"><?php echo  __('Describe your preference over here', 'simpelreserveren'); ?></div>
                              <div class="plattegrond-form sr-boeken">
                                <p>
                                  <label for="plaats_voorkeur"><?php echo  __('Describe your preference over here', 'simpelreserveren'); ?></label>
                                  <p><?php echo  __("Please pick your spot on the map on the left. After your booking is made we will try to make a reservation on that spot. If that spot isn't available, we will contact you for an alternative", 'simpelreserveren'); ?></p>
                                  <textarea class="form-control" id="plaats_voorkeur" rows="8" placeholder="<?php echo  __('We would like to stay on place number 1 or 2', 'simpelreserveren'); ?>"><?php echo  $boeken['plaats_voorkeur'] ?></textarea>
                                </p>
                                <p>&nbsp;</p>
                                <p>
                                  <button class="btn sr-primary-button"><?php echo  __('Ga verder met uw reservering', 'simpelreserveren'); ?> <span class="glyphicon glyphicon-chevron-right"></span></button>
                                  <button class="btn sr-secondary-button sr-close-form"><span class="glyphicon glyphicon-chevron-left"></span> <?php echo  __('Go back', 'simpelreserveren') ?></button>
                                </p>
                              </div>
                            </div>
                          </div>
                        </div>
                      <?php endif; /* PLATTEGROND */ ?>

                      <div class="row">
                        <div class="col-sm-12 sr-volgende-stap clearfix">
                          <?php /*<a class="btn btn-default pull-left" href="<?php echo  $accommodatie->url ?>"><span class="glyphicon glyphicon-chevron-left"></span> <?php echo  __('Back to accommodation', 'simpelreserveren') ?></a>*/ ?>
                          <button type="submit" class="btn sr-primary-button pull-right"><?php echo  __('Continue to address details', 'simpelreserveren') ?> <span class="glyphicon glyphicon-chevron-right"></span></button>
                          <span class="pull-right hide">Bedrag €395,- &nbsp; </span>
                        </div>

                    </div>
                <?php break; ?>
                <?php case 2: ?>
                    <input type="hidden" name="action" id="action" value="check_form" />
                    <input type="hidden" name="next_step" id="next_step" value="<?php echo $volgende_stap?>" />
                    <div class="row">
                        <div class="col-xs-6">
                          <label for="voornaam" id="lbl-voornaam"><?php echo  __('First name', 'simpelreserveren') ?></label>
                          <input type="text" class="form-control" id="voornaam" name="voornaam">
                        </div>
                        <div class="col-xs-6">
                          <label for="achternaam" id="lbl-achternaam"><?php echo  __('Family name', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="text" class="form-control validate[required]" id="achternaam" name="achternaam">
                        </div>  
                    </div>
                      <!-- /.row -->
                    <div class="row">
                        <div class="col-xs-6">
                          <label for="email" id="lbl-email"><?php echo  __('E-mail address', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="email" class="form-control validate[custom[email] required]" id="email" name="email">
                        </div>
                        <div class="col-xs-6">
                          <label for="email-confirm" id="lbl-email-confirm"><?php echo  __('Confirm e-mail address', 'simpelreserveren') ?> <span class="req">*</span></label>
                          <input type="email" class="form-control validate[required, equals[email]]" id="email-confirm" name="email-confirm">
                        </div>  
                    </div>
                      <!-- /.row -->
                    <div class="row">
                        <div class="col-xs-6">
                          <label for="telefoon" id="lbl-telefoon"><?php echo  __('Phone number', 'simpelreserveren') ?></label>
                          <input type="text" class="form-control validate[ custom[phone]]" id="telefoon" name="telefoon">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xs-12">
                          <label for="opmerkingen" id="lbl-opmerkingen"><?php echo  __('Do you have any special requests? Please fill them in here.', 'simpelreserveren') ?></label>
                          <textarea class="form-control" id="opmerkingen" name="opmerkingen" rows="5"></textarea>
                        </div>
                    </div>
                <?php if (defined('SIMPEL_SHOW_CC') && SIMPEL_SHOW_CC) : ?>
                <div class="row">
                    <div class="col-sm-6">
                        <label for="cc" id="lbl-cc"><?php echo __('Credit card number', 'simpelreserveren') ?> <span class="req">*</span></label>
                        <input type="text" class="form-control validate[creditCard, required]" id="cc" name="cc"/>
                    </div>
                    <div class="col-sm-3">
                        <label for="cc_valid_month" id="lbl-cc_valid_month"><?php echo __('Valid until m/y', 'simpelreserveren') ?> <span class="req">*</span></label>
                        <input type="text" class="form-control validate[required, min[1], max[12]]" id="cc_valid_month" name="cc_valid_month" placeholder="11"/>
                    </div>
                    <div class="col-sm-3">
                        <label for="cc_valid_year" id="lbl-cc_valid_year">&nbsp;</label>
                        <input type="text" class="form-control validate[required, min[2015], max[2045]]" id="cc_valid_year" name="cc_valid_year" placeholder="2017"/>
                    </div>
                </div>
                <?php endif; ?>

                    <div class="row">
                        <div class="col-xs-12">
                            <p>
                                <?php echo  __('By booking this accommodation you agree with our', 'simpelreserveren') ?> <a href="<?php echo  $accommodatie->camping->voorwaarden_url ?>" target="_blank"><?php echo  __('terms and conditions', 'simpelreserveren') ?></a>.
                            </p>
                            <p id="u-bent-klaar"><?php echo  __('You are ready too book', 'simpelreserveren') ?></p>
                        </div>
                    </div>
                      

                    <div class="row">
                        <div class="col-sm-12 sr-volgende-stap clearfix text-center">
                          <?php /*<a class="btn btn-default pull-left" href="<?php echo  $accommodatie->boek_url ?>"><span class="glyphicon glyphicon-chevron-left"></span> <?php echo  __('Back to step 1', 'simpelreserveren') ?></a> */ ?>
                          <button type="submit" class="btn sr-primary-button pull-right"><?php echo  __('Book for', 'simpelreserveren') ?> &euro; <?php echo  number_format($_SESSION['boeken']['totaal_incl_borgsom'], 2, ',', '.') ?> <span class="glyphicon glyphicon-ok"></span></button>
                        </div>

                    </div>

                <?php break; ?>
                <?php case 3: ?>
                    <?php if(!$boek_result && !$hash) : ?>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class=" alert alert-danger">
                                    <h2><?php echo  __('Error, something went wrong. Please try again.', 'simpelreserveren') ?></h2>
                                    <p><?php echo  __('Or contact us by phone to make your reservation.', 'simpelreserveren'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <input type="hidden" name="action" id="action" value="opslaan-naw" />
                        <input type="hidden" name="boeken-id" id="boeken-id" value="<?php echo $_SESSION['boeken']['boeken_id'] ?>" />
                        <div class="row">
                            <div class="col-xs-12">
                                <br/>
                                <?php if(!$hash) : ?>
                                  <div class="no-margin alert-boeken alert alert-success ">
                                    <h2><?php echo  __('Congratulations, your booking is complete!', 'simpelreserveren') ?></h2>
                                    <p><?php echo  $accommodatie->camping->confirm_tekst ?></p>
                                  </div>
                                <?php endif; ?>
                                <h3><?php echo  __('Additional data', 'simpelreserveren') ?></h3>
                                <p><?php echo  __('To complete your booking, we would like the following data', 'simpelreserveren') ?>:</p>
                            </div>
                            <div class="col-xs-12">
                              <label><?php echo  __('Name', 'simpelreserveren') ?></label>
                              <p><?php echo  $boeken->naam ?></p>
                            </div>
                            <div class="col-xs-12">
                              <label for="adres" id="lbl-adres"><?php echo  __('Address + house number', 'simpelreserveren') ?> <span class="req">*</span></label>
                              <input type="text" class="form-control validate[required]" id="adres" name="adres">
                            </div>
                        </div>
                          <!-- /.row -->
                        <div class="row">
                            <div class="col-xs-6">
                              <label for="postcode" id="lbl-postcode"><?php echo  __('Postal code', 'simpelreserveren') ?> <span class="req">*</span></label>
                              <input type="text" class="form-control validate[required]" id="postcode" name="postcode">
                            </div>  
                            <div class="col-xs-6">
                              <label for="plaats" id="lbl-plaats"><?php echo  __('City', 'simpelreserveren') ?> <span class="req">*</span></label>
                              <input type="text" class="form-control validate[required]" id="plaats" name="plaats">
                            </div>
                        </div>
                          <!-- /.row -->
                        <div class="row">
                            <div class="col-xs-6">
                                <label for="factuur-per-post" id="lbl-factuur-per-post">
                                    <input value="1" type="checkbox" name="factuur-per-post" id="factuur-per-post"> 
                                    <?php echo  __('I would like to receive an invoice by regular mail', 'simpelreserveren') ?>
                                </label>
                            </div>
                        </div> 
                     
                          

                        <div class="row"  id="naw-submit">
                            <div class="col-sm-12 sr-volgende-stap clearfix text-center">
                              <button type="submit" class="btn sr-primary-button pull-right"><?php echo  __('Save data', 'simpelreserveren') ?></button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div id="ajax-message">&nbsp;</div>
                            </div>
                        </div>

                        <div class="row">
                          <?php if($this->get_setting('facebook-id')) : ?>
                          <div class="col-sm-2">
                            <div id='fb-root'></div>
                            <script src='http://connect.facebook.net/en_US/all.js'></script>
                            <p><a onclick='postToFeed(); return false;' id="facebook-share" class="btn btn-primary"><span class="glyphicon glyphicon-thumbs-up"></span> <?php echo  __('Facebook', 'simpelreserveren') ?></a></p>
                            <p id='msg'></p>

                            <script> 
                              FB.init({appId: "<?php echo  $this->get_setting('facebook-id') ?>", status: true, cookie: true});

                              function postToFeed() {

                                // calling the API ...
                                var obj = {
                                  method: 'feed',
                                  redirect_uri: '<?php echo  $accommodatie->url ?>',
                                  link: '<?php echo  $accommodatie->url ?>',
                                  picture: '<?php echo  $accommodatie->img ?>',
                                  name: '<?php echo  $accommodatie->camping->title ?>',
                                  description: '<?php echo  htmlentities(str_replace("\r\n", ' ', $accommodatie->camping->facebook_tekst)) ?>'
                                };

                                function callback(response) {
                                  jQuery('#ajax-message').html('Bedankt voor uw post');
                                  //document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
                                }

                                FB.ui(obj, callback);
                              }

                            </script>
                          </div>
                          <?php endif; ?>
                          <div class="col-sm-10">
                            <a href="https://twitter.com/share?url=<?php echo  $accommodatie->url ?>&text=<?php echo  $accommodatie->camping->tweet_tekst ?>" class="twitter-share-button" data-related="jasoncosta" data-lang="nl" data-size="large" data-count="none">Twitter</a>
                            <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="https://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
                          </div>

                        </div>

                        <?php if(!isset($_SESSION['boeken']['boeken_test']) && $_SESSION['boeken']['boeken_test'] && !$hash) : ?>

                        <?php echo str_replace(array(
                          '[sr-totaal]',
                          '[boek_id]',
                          '[accommodatie_bedrag]',
                          '[totaal_bedrag]',
                          '[accommodatie_titel]',
                        ), 
                        array(
                          $boeken['prijs'],
                          $_SESSION['boeken']['boeken_id'],
                          $boeken['prijs'],
                          $boeken['totaal'],
                          urlencode($accommodatie->title),
                        ), $this->get_setting('conversie-codes')); ?>

                        <?php if(isset($ua_code) && !empty($ua_code)) : ?>
                          <!-- Google Code for Reservering Online Conversion Page -->
                          <script type="text/javascript">

                            <?php if($ua_code) : // in het geval van Analytics for Wordpress is geinstalleerd ?>
                              var _gaq = _gaq || [];
                              _gaq.push(['_setAccount', '<?php echo  $ua_code ?>']);
                              _gaq.push(['_trackPageview', '/boeking']);
                              _gaq.push(['_addTrans',
                                '<?php echo  $_SESSION['boeken']['boeken_id'] ?>',           // order ID - required
                                '<?php echo  $accommodatie->title ?>',  // affiliation or store name
                                '<?php echo  $boeken['prijs'] ?>',          // total - required
                                '0',           // tax
                                '0',              // shipping
                                '<?php echo  $boeken['plaats'] ?>',       // city
                                '',     // state or province
                                'Nederland'             // country
                              ]);

                               // add item might be called for every item in the shopping cart
                               // where your ecommerce engine loops through each item in the cart and
                               // prints out _addItem for each
                              _gaq.push(['_addItem',
                                '<?php echo  $_SESSION['boeken']['boeken_id'] ?>',           // order ID - required
                                '<?php echo  $accommodatie->id ?>',           // SKU/code - required
                                '<?php echo  $accommodatie->title ?>',        // product name
                                '<?php echo  $accommodatie->type->title ?>',   // category or variation
                                '<?php echo  $boeken['prijs'] ?>',          // unit price - required
                                '1'               // quantity - required
                              ]);
                              _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers

                              (function() {
                                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                              })();

                            <?php endif; ?>

                          </script> 
                        <?php endif; ?>

                      <?php endif; ?>
                    <?php endif; ?>
                <?php break; ?>
            <?php endswitch; ?>


          </form>


          
      </div>
      
      <!-- sidebar -->
        <div class="col-sm-4 sidebar tab-pane sr-kassabon" id="prijsberekening">

        <h3><?php echo  $accommodatie->title ?></h3>  
        <?php if(SIMPEL_MULTIPLE) : ?>
          <h4><?php echo $accommodatie->camping->title ?></h4>  
        <?php endif; ?>
        
        <div class="row">
          <div class="col-xs-6"><img src="<?php echo  $accommodatie->img ?>" alt="<?php echo  $accommodatie->title ?>"></div>
          <div class="col-xs-6">
            <ul>
              <?php if($accommodatie->aantal_slaapkamers > 0) : ?>
                <li><?php echo  $accommodatie->aantal_slaapkamers ?> <?php echo  __('bedrooms', 'simpelreserveren') ?></li>
              <?php endif; ?>
              <?php if($accommodatie->aantal_personen > 0) : ?>
                <li><?php echo  $accommodatie->aantal_personen ?> <?php echo  __('persons', 'simpelreserveren') ?></li>
              <?php endif; ?>
              <li><?php echo  $accommodatie->huisdieren ?></li>
            </ul>
          </div>
        </div>

        <div class="row">
          <div class="col-sm-12">
            <h4><?php echo  __('Price calculation', 'simpelreserveren') ?></h4>
            <div class="table-responsive" id="kassabon">
                <?php echo  $kassabon_html ?>
            </div>
          </div>
          
        </div>
        
        </div>

      <!-- #mainTabContent -->

    </div>


  </div>
</div>
<?php get_footer('simpel-reserveren'); ?>