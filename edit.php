<?
$table = $wpdb->prefix . $this->plugin_db_prefix . $table;
if (isset($_GET['id']) || isset($_GET['act']) && $_GET['act'] == 'new') {
    include(dirname(__FILE__) . '/FH3/class.dbFormHandler.php');
    $form = new dbFormHandler();
    $form->setConnectionResource($wpdb->dbh, $table);
    $form->textField('test', 'test');
    $form->setMask('%field% %error% %help%');
    $form->addValue('datum', 'now()', 1);
    // detail view
    ?>
    <div class="wrap columns-2">
        <? if (isset($_GET['id'])) { ?>
            <h2>Camping bewerken</h2>
        <? } else { ?>
            <h2>Nieuwe camping toevoegen</h2>
        <?
        }

        $form->addHTML('<div id="poststuff" class="metabox-holder has-right-sidebar">
        	<div id="side-info-column" class="inner-sidebar">
            	<div id="side-sortables" class="meta-box-sortables ui-sortable">
                	<div id="submitdiv" class="postbox">
                    	<h3 class="hndle"><span>Publiceren</span></h3>
                        <div class="inside">
                        	<div class="submitbox" id="submitbox">
                                <div id="major-publishing-actions">');
        $form->cancelButton('Annuleren', null, null, 'class="button"');
        $form->submitButton('Opslaan', 'Opslaan', 'class="button-primary" style="float:right"');

        $form->addHTML('
                                </div>
                            </div>
                        </div>
                    </div>
                </div>           
            </div>
            <div id="post-body">
            	<div id="post-body-content">
                	<div id="titlediv">
                    	<div id="titlewrap">');
        $form->textField('Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Naam van de camping"');
        $form->addHTML('
                        </div>
                    </div>
                </div>
            </div>
        </div>
    ');
        $form->onSaved(array(&$this, 'form_saved'));
        $form->flush();
        ?>  
    </div>
    <?
} else {
    $campings = $wpdb->get_results('select * from wp_booking_camping order by title');
    ?>

    <div class="wrap">
        <h2>Campings
            <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&act=new" class="add-new-h2">Nieuwe camping</a>
        </h2>

        <table class="wp-list-table widefat fixed pages" cellspacing="0">
            <thead>
                <tr>
                    <th class="col" id="naam" class="manage-column">Naam</th>
                    <th class="col" id="datum" class="manage-column">Laatst gewijzigd</th>
                </tr>
            </thead>
            <tbody>
    <? foreach ($campings as $camping) { ?>
                    <tr>
                        <td><a href="?page=<?php echo $_GET['page'] ?>&id=<?php echo $camping->id ?>"><?php echo $camping->title ?></a></td>
                        <td><?php echo $camping->datum ?></td>
                    </tr>
    <? } ?>
    <? if (!$campings) { ?>
                    <tr>
                        <td colspan="2">Geen campings gevonden</td>
                    </tr>
    <? } ?>
            </tbody>
        </table>
    </div>
<? } ?>