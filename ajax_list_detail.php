<?php
$this->table = $table;
$db_table = SIMPEL_DB_PREFIX. $table;
if (isset($_GET['del'])) {
    $this->wpdb->show_errors();
    $this->wpdb->query($this->wpdb->prepare('delete from ' . $db_table . ' where id = "%d"', $_GET['del']));
    echo '<script>document.location="?page=' . $_GET['page'] . '";</script>';
    exit;
}
if (isset($_GET['id']) || isset($_GET['act']) && $_GET['act'] == 'new') {
    include(dirname(__FILE__) . '/FH3/class.dbFormHandler.php');
    $form = new dbFormHandler();
    $form->useTable(false);
    if (!isset($_GET['act']))
        $_GET['act'] = '';
    if (!in_array($table, array('beschikbaarheid', 'prijs')) && $_GET['act'] != 'periodes') {
        $form->setConnectionResource($this->wpdb->dbh, $db_table);
    }
    $form->setMask('%field% %error% %help%');
    $form->addValue('datum', 'now()', 1);
    // detail view
    ?>
    <div class="wrap columns-2">
    <?php if (isset($_GET['id'])) { ?>
            <h2><?php echo ucfirst($title) ?> bewerken</h2>
        <?php } else { ?>
            <h2>Nieuwe <?php echo $title ?> toevoegen</h2> 
        <?php
        }

        if (!in_array($table, array('prijs'))) {
            $form->addHTML('<div id="poststuff" class="metabox-holder has-right-sidebar">
        	<div id="side-info-column" class="inner-sidebar">
            	<div id="side-sortables" class="meta-box-sortables ui-sortable">
                	<div id="submitdiv" class="postbox">
                    	<h3 class="hndle"><span>Publiceren</span></h3>
                        <div class="inside">
                        	
							<div class="submitbox" id="submitbox">
                                <div id="major-publishing-actions">');
            if (in_array($table, array('faciliteiten', 'periode', 'accommodatie', 'toeslagen', 'boeking')) && isset($_GET['id'])) {
                $form->addHTML('<div id="delete-action">
											<a class="submitdelete deletion" href="?page=' . $_GET['page'] . '&del=' . $_GET['id'] . '" onclick="return window.confirm(\'Weet u zeker dat u dit item wilt verwijderen?\')">verwijder dit item</a>
										</div><div class="clear"></div>');
            }

            $form->cancelButton('Terug', null, null, 'class="button"');

            if (in_array($table, array('boeking'))) {
                // do nothing
            } elseif (!in_array($table, array('beschikbaarheid'))) {
                $form->submitButton('Opslaan', 'Opslaan', 'class="button-primary" style="float:right"');
            } else {
                $form->AddHTML('<br/>Het opslaan gebeurt zodra er op een datum wordt geklikt.');
            }

            $form->addHTML('<div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                ');

            if (in_array($table, array('bezoekers'))) {
                $form->addHTML('
                	<div id="submitdiv" class="postbox">
                    	<h3 class="hndle"><span>E-mail</span></h3>
                        <div class="inside">
                        	
							<div class="submitbox" id="submitbox">
                                <div id="major-publishing-actions">');

                $form->button('Bekijk e-mail', null, 'class="button"');
                $form->button('Bekijk beoordeling', null, 'class="button" onclick="window.open(\'' . $this->_beoordeel_url($_GET['id']) . '\')"');

                $form->addHTML('<div class="clear"></div>
                                </div>
                            </div>
                        </div>
	                </div>');
            }

            $form->addHTML('</div></div>');
        }

        $form->addHTML('
			
            <div id="post-body" class="metabox-holder" style="padding-top:0">
            	<div id="post-body-content">
                	<div id="titlediv">
                    	<div id="titlewrap">');

        switch ($table) {
            case 'boeking':
                $form->setMask('<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>');
                $form->addHTML('</div>');
                $form->addHTML('
				<div class="postbox">
					<div class="inside" style="text-align:left">
					<table cellspacing="0" cellpadding="2"><tbody>');
                $fields = array('refer', 'naam', 'adres', 'postcode', 'plaats', 'telefoon', 'factuur_per_post', 'datum_boeking', 'datum_aankomst', 'datum_vertrek', 'prijs');
                foreach ($fields as $fld) {
                    $form->textField(str_replace('_', ' ', $fld), $fld);
                    $form->setFieldViewMode($fld);
                }
                $form->setMask('<tr><td colspan="2">%field%</td></tr>');
                $form->textField('html', 'mail_html');
                $form->setFieldViewMode('mail_html');

                $form->addHTML('</tbody></table></div></div>');
                break;

            default:
                $form->textField('Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"');
                $form->addHTML('</div></div>');
        }

        $form->onSaved(array(&$this, 'form_saved'));
        $form->flush();

        if ($table == 'hotels' && isset($_GET['id']) && is_numeric($_GET['id'])) {
            ?>  

            <link href="<?php echo $this->plugin_url ?>/uploadify/uploadify.css" type="text/css" rel="stylesheet"/>
            <script src="<?php echo $this->plugin_url ?>/beoordeling/uploadify/swfobject.js" type="text/javascript"></script>
            <script src="<?php echo $this->plugin_url ?>/beoordeling/uploadify/jquery.uploadify.v2.1.4.min.js" type="text/javascript"></script>
            <script type="text/javascript">
                jQuery(function() {

                    jQuery('#afbeelding').uploadify({
                        'uploader': '<?php echo $this->plugin_url; ?>/beoordeling/uploadify/uploadify.swf',
                        'script': '<?php echo $this->plugin_url; ?>/beoordeling/uploadify/uploadify.php',
                        'cancelImg': '<?php echo $this->plugin_url; ?>/beoordeling/uploadify/cancel.png',
                        'folder': '/<?php echo $_GET['id'] ?>',
                        'multi': false,
                        'auto': true,
                        'fileExt': '*.jpg;*.gif;*.png',
                        'fileDesc': 'Image Files (.JPG, .GIF, .PNG)',
                        'scriptData': {
                            'table': 'hotels',
                            'hotel_id': '<?php echo $_GET['id'] ?>'
                        },
                        'onComplete': function(event, ID, fileObj, response, data) {
                            if (response == '')
                                return;
                            jQuery('#afbeelding-div').empty().append('<img src="' + response + '" alt=""/>');
                        }
                    });
                });
            </script>


    <?php } ?> 
    </div>
    <?php
} else {
    switch ($table) {
        case 'boeking':
            $sql = 'select * from ' . $db_table . ' b inner join ' . SIMPEL_DB_PREFIX . 'accommodatie a on b.accommodatie_id = a.id';
            $titles = array('Id', 'Datum boeking', 'Naam', 'Accommodatie', 'Aankomst', 'Vertrek', 'Prijs');
            break;



        default:
            $sql = 'select * from ' . $db_table . ' order by title';
            $fields = array('title', 'datum');
            $titles = array('Naam', 'Laatst gewijzigd');
    }
    $this->wpdb->show_errors();
    $results = $this->wpdb->get_results($sql);
    ?>

    <input type="hidden" name="db-table" id="db-table" value="<?php echo $table ?>" />
    <input type="hidden" name="db-type" id="db-type" value="<?php echo $_GET['type'] ?>" />
    <div class="wrap">
        <h2><?php echo ucfirst($title) ?>
    <?php if (!in_array($table, array('boeking'))) : ?>
                <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&act=new" class="add-new-h2">Nieuw</a>
    <?php else : ?>
                <a href="admin.php?page=<?php echo $_GET['page'] ?>" class="add-new-h2">Echte boekingen</a>
                <a href="admin.php?page=<?php echo $_GET['page'] ?>&type=test" class="add-new-h2">Test boekingen</a>
                <a href="javascript:;" class="add-new-h2" data-toggle="modal" data-target="#modal-export">Export</a>
                <select name="jaar" id="boek-jaar">
                    <option value="">Select..</option>
                    <?php for($i=2012; $i<=date('Y'); $i++) : ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <select name="maand" id="boek-maand">
                    <option value="">Select..</option>
                    <?php $maanden = array('januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'); ?>
                    <?php foreach($maanden as $i => $maand) : ?>
                        <option value="<?= sprintf('%02d', ($i+1)) ?>"><?= $maand ?></option>
                    <?php endforeach; ?>
                </select>
    <?php endif; ?>
    <?php if (in_array($table, array('aanbiedingen'))) { ?>
                <a href="admin.php?page=<?php echo $_GET['page'] ?>&act=new" class="add-new-h2">Nieuw</a>
                <a href="admin.php?page=<?php echo $_GET['page'] ?>" class="add-new-h2">Aanbiedingen</a>
                <a href="<?php echo $_SERVER['REQUEST_URI'] ?>&act=periodes" class="add-new-h2">Periodes</a>
    <?php } ?>
        </h2>

        <table class="wp-list-table widefat fixed pages" cellspacing="0" id="ajax-table">
            <thead>
                <tr>
            <?php foreach ($titles as $col) { ?>
                        <th class="col" id="<?php echo $col ?>" class="manage-column"><?php echo $col ?></th>
            <?php } ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align:right">Totaal:</th>
                    <th></th>
                </tr>
            </tfoot>
            <tbody>
                <tr>
                    <td colspan="<?php echo count($titles) ?>" class="dataTables_empty">Loading data...</td>
                </tr>
    <?php if (!count($results)) { ?>
                    <tr>
                        <td colspan="<?php echo count($titles) ?>">Geen <?php echo $title ?> gevonden</td>
                    </tr>
                    <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="modal-export" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form role="form" method="POST" action="<?= $_SERVER['REQUEST_URI'] ?>">
                    <input name="act" value="export-boeking" type="hidden"/>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="myModalLabel">Exporteer boekingen in de volgende periode</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="export-van">Periode vanaf</label>
                            <input type="date" class="form-control" id="export-van" name="van" value="<?= date('Y') ?>-01-01"/>
                        </div>
                        <div class="form-group">
                            <label for="export-van">Periode tot</label>
                            <input type="date" class="form-control" id="export-tot" name="tot" value="<?= date('Y') ?>-12-31"/>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuleren</button>
                        <button type="submit" class="btn btn-primary" onclick="jQuery(this).prev().trigger('click')">Exporteer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php } ?>