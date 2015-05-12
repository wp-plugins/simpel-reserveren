<?php get_header('simpel-reserveren'); ?>
    <div class="simpel-reserveren container">
        <div class="row">
            <div class="only-phone">
                <ul id="mainTab" class="nav nav-tabs">
                    <li class="active">
                        <a href="#boeken" id="boekenTab"><?php echo  __('Accommodation', 'simpelreserveren') ?></a>
                    </li>
                    <li>
                        <a href="#kassabon" id="kassabonTab"><?php echo  __('Availability', 'simpelreserveren') ?></a>
                    </li>    
                </ul>
            </div>
        
            <div id="mainTabContent" class="sr-accommodatie sr-camping">

                <div class="col-sm-8 tab-pane visible" id="boeken">
                    <?php echo $this->show_message('camping'); ?>
                    <div class="acco-title clearfix">
                        <div class="col-xs-12">
                            <h2><?php echo  $camping->title ?></h2>
                        </div>
                    </div>

                    <div class="acco-tekst clearfix">
                        <div class="col-sm-6 sr-object-photos">
                            <img src="<?php echo $camping->logo_medium ?>" title="<?php echo $camping->title ?>"/>
                            <div class="row">
                                <?php if(count($camping->afbeeldingen)) : ?>
                                    <?php foreach($camping->thumbs as $i => $thumb) : ?>
                                        <div class="col-xs-4 thumb"><a href="<?php echo  $camping->afbeeldingen[$i] ?>" class="fancybox" rel="fancybox-thumb"><img src="<?php echo  $thumb ?>" alt=""/></a></div>
                                        <?php if($i >= 2) break; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-sm-6">
                            <div class="sr-boeken clearfix sr-accommodatie-prijs"></div>
                            <div class="sr-acco-tekst">
                                <?php echo $camping->omschrijving ?>
                            </div>
                            <?php if($camping->plattegrond_full) : ?>
                                <p>&nbsp;</p>
                                <p><a href="<?php echo $camping->plattegrond_full ?>" class="plattegrond" target="_blank"><?php echo  __('click here for the plan', 'simpelreserveren'); ?></a></p>
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="sr-meer-informatie">
                
                        <ul id="informatieTab" class="nav nav-tabs">
                            <li class="active"><a href="#overzicht" data-toggle="tab"><?php echo  __('Overview', 'simpelreserveren') ?></a></li>
                            <li><a href="#accommodaties" data-toggle="tab"><?php echo  __('Accommodations', 'simpelreserveren') ?></a></li>
                            <li><a href="#fotos" data-toggle="tab"><?php echo  __('Pictures', 'simpelreserveren') ?></a></li>
                            <li><a href="#omgeving" data-toggle="tab"><?php echo  __('Surrounding', 'simpelreserveren') ?></a></li>
                            <li><a href="#faciliteiten" data-toggle="tab"><?php echo  __('Facilities', 'simpelreserveren') ?></a></li>
                        </ul>

                        <div id="informatieTabContent" class="tab-content">
                            <div class="tab-pane fade active in faciliteiten" id="overzicht">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h3><?php echo  __('Accommodations', 'simpelreserveren') ?></h3>
                                        <?php foreach($camping->accommodaties as $acco) : ?>
                                            <div class="col-xs-6">
                                                <a href="<?php echo $acco->url ?>"><img src="<?php echo $acco->resized_img ?>" alt="<?php echo $acco->title ?>"/></a><br/>
                                                <a href="<?php echo $acco->url ?>"><?php echo $acco->title?></a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="col-sm-6">
                                        <h3><?php echo  __('Facilities', 'simpelreserveren') ?></h3>
                                        <ul>
                                            <?php $i = 0; ?>
                                            <?php foreach($camping->faciliteiten as $facil) : ?>
                                                <?php if(preg_match('/voorkeur/is', $facil->title)) continue; ?>
                                                    <li><span class="ss-icon facil">&<?php echo  $facil->icon ?></span> <?php echo $facil->title?></li>
                                                    <?php if($i++>=4) break; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                        <a href="#faciliteiten" data-toggle="tab"><?php echo  __('Click here to see all facilities', 'simpelreserveren') ?></a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h3><?php echo  __('Pictures', 'simpelreserveren') ?></h3>
                                        <?php if(count($camping->afbeeldingen)) : ?>
                                            <div class="clearfix">
                                                <?php foreach($camping->thumbs as $i => $thumb) : ?>
                                                    <div class="col-xs-6">
                                                        <a href="#fotos"><img src="<?php echo $thumb ?>" alt=""/></a>
                                                    </div>
                                                    <?php if($i>=3) break; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        <a href="#fotos" data-toggle="tab"><?php echo  __('Click here to see all images', 'simpelreserveren') ?></a>
                                    </div>
                                    <div class="col-sm-6">
                                        <h3><?php echo  __('Surrounding', 'simpelreserveren') ?></h3>
                                        <?php echo  substr(strip_tags($camping->txt_omgeving), 0, 400) ?>...
                                        <div class="clear"></div>
                                        <a href="#omgeving" data-toggle="tab"><?php echo  __('Read more', 'simpelreserveren') ?></a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade in faciliteiten" id="accommodaties">
                                <h3><?php echo  __('Accommodations', 'simpelreserveren') ?></h3>
                                <?php foreach($camping->accommodaties as $acco) : ?>
                                    <div class="clearfix">
                                        <div class="col-xs-3">
                                            <a href="<?php echo $acco->url ?>"><img src="<?php echo $acco->resized_img ?>" alt="<?php echo $acco->title ?>"/></a>
                                        </div>
                                        <div class="col-xs-9">
                                            <h4><a href="<?php echo  $acco->url ?>"><?php echo $acco->title?></a></h4>
                                            <?php echo  $acco->samenvatting ?><br/>
                                            <a href="<?php echo  $acco->url ?>"><?php echo  __('Click here for more information about this accommodation', 'simpelreserveren'); ?></a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="tab-pane fade in faciliteiten" id="fotos">
                                <h3><?php echo  __('Pictures', 'simpelreserveren') ?></h3>
                                <div class="clearfix">
                                    <?php if(count($camping->afbeeldingen)) : ?>
                                        <?php foreach($camping->thumbs as $i => $thumb) : ?>
                                                <div class="col-xs-4 thumb"><a href="<?php echo  $camping->afbeeldingen_large[$i] ?>" class="fancybox" rel="fancybox-thumb"><img src="<?php echo  $thumb ?>" alt=""/></a></div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="tab-pane fade in faciliteiten" id="omgeving">
                                <h3><?php echo  __('Surrounding', 'simpelreserveren') ?></h3>
                                <div class="clearfix">
                                    <div class="col-sm-12">
                                        <?php echo  $camping->txt_omgeving ?>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane fade in faciliteiten" id="faciliteiten">
                                <h3><?php echo  __('Facilities', 'simpelreserveren') ?></h3>
                                <ul>
                                <?php foreach($camping->faciliteiten as $facil) { 
                                    if(preg_match('/voorkeur/is', $facil->title)) continue; 
                                    ?>
                                    <li><span class="ss-icon facil">&<?php echo  $facil->icon ?></span> <?php echo $facil->title?></li>
                                <?php } ?>
                                </ul>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="col-sm-4 sidebar tab-pane sr-kassabon" id="prijsberekening">
                    <h3><?php echo  __('Availability', 'simpelreserveren') ?></h3>  
                    
                    <div class="kassabon clearfix">
                    <div class="col-sm-12">
                        <?php $this->show_prices( null, $camping->id ); ?>
                    </div>
                
                </div>

                <?php $aanbiedingen = $camping->get_all_aanbiedingen(); ?>
                <?php if(count($aanbiedingen)) : ?>
                    <div class="row sr-speciale-aanbiedingen clearfix">
                        <div class="col-sm-12">
                            <h3><?php echo  __('Discounts', 'simpelreserveren') ?></h3>
                        </div>
                        <div class="col-sm-12">
                            <div class="inner">
                                <div class="info"><?php echo  __('Move the mouse over to see the terms', 'simpelreserveren'); ?></div>
                                <?php foreach($aanbiedingen as $aanbieding) : ?>
                                    <button type="button" class="btn btn-default discount" data-container="body" data-toggle="popover" data-placement="top" data-content="<?php echo  ($aanbieding->voorwaarden ? nl2br($aanbieding->voorwaarden) : $aanbieding->geldig) ?>" data-original-title="" title=""><?php echo $aanbieding->omschrijving?></button>
                                <?php endforeach; ?>
                                    
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>        
<?php get_footer('simpel-reserveren'); ?>