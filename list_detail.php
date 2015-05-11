<?php
$this->table = $table;
$db_table    = SIMPEL_DB_PREFIX . $table;
if ( isset( $_GET['del'] ) ) {
	$result = $this->wpdb->query( $this->wpdb->prepare( 'delete from ' . $db_table . ' where id = "%d"', $_GET['del'] ) );
	if ( ! $result ) {
		$_SESSION['msg']      = 'Verwijderen mislukt, waarschijnlijk is er nog een item gekoppeld welke eerst moet worden verwijderd.';
		$_SESSION['msg_type'] = 'error';
	}

	echo '<script>document.location="?page=' . $_GET['page'] . '";</script>';
	exit;
}
if ( isset( $_GET['id'] ) || isset( $_GET['act'] ) && $_GET['act'] == 'new' || $this->table == 'settings' ) :

	include( dirname( __FILE__ ) . '/FH3/class.dbFormHandler.php' );
	$form = new dbFormHandler( 'simpel-reserveren', $_SERVER['REQUEST_URI'] );
	$form->useTable( false );
	if ( ! isset( $_GET['act'] ) ) {
		$_GET['act'] = '';
	}
	if ( ! in_array( $table, array( 'beschikbaarheid', 'prijs' ) ) && $_GET['act'] != 'periodes' ) {
		$form->setConnectionResource( $this->wpdb->dbh, $db_table );
	}
	$form->setMask( '%field% %error% %help%' );
	$form->addValue( 'datum', 'now()', 1 );
	// detail view
	?>
	<div class="wrap columns-2">
	<?php if ( isset( $_GET['id'] ) ) { ?>
		<h2><?= ucfirst( $title ) ?> bewerken
			<?php if ( $table == 'accommodatie' ) : ?>
				<a href="<?= $accommodatie->url ?>" class="button" target="_blank">bekijken</a>
			<?php endif; ?>
		</h2>
	<?php } else { ?>
		<h2>Nieuwe <?= $title ?> toevoegen</h2>
	<?php
	}

	if ( isset( $_SESSION['msg'] ) && ! empty( $_SESSION['msg'] ) ) : ?>
		<div id="message" class="<?= $_SESSION['msg_type'] ?> below-h2">
			<p><?= $_SESSION['msg'] ?></p>
		</div>
		<?php unset( $_SESSION['msg'] ); ?>
	<?php endif;

	if ( ! in_array( $table, array( 'prijs', 'boeking' ) ) ) {
		$form->addHTML( '<div id="poststuff" class="metabox-holder has-right-sidebar">
        	<div id="side-info-column" class="inner-sidebar">
            	<div id="side-sortables" class="meta-box-sortables ui-sortable">
                	<div id="submitdiv" class="postbox">
                    	<h3 class="hndle"><span>Publiceren</span></h3>
                        <div class="inside">
                        	
							<div class="submitbox" id="submitbox">
                                <div id="major-publishing-actions">' );
		if ( in_array( $table, array( 'faciliteiten', 'periode', 'accommodatie', 'toeslagen', 'aanbiedingen', 'arrangementen', 'acco_type' ) ) && isset( $_GET['id'] ) ) {
			$form->addHTML( '<div id="delete-action">
											<a class="submitdelete deletion" href="?page=' . $_GET['page'] . '&del=' . $_GET['id'] . '" onclick="return window.confirm(\'Weet u zeker dat u dit item wilt verwijderen?\')">verwijder dit item</a>
										</div><div class="clear"></div>' );
		}

		$form->cancelButton( 'Terug', null, null, 'class="button"' );
		if ( in_array( $table, array( 'accommodatie' ) ) && isset( $_GET['id'] ) ) {
			$form->Button( 'Kopieren', 'Kopieren', 'class="button" onclick="document.location=\'?page=' . $_GET['page'] . '&id=' . $_GET['id'] . '&act=copy\'"' );
		}
		if ( ! in_array( $table, array( 'beschikbaarheid' ) ) ) {
			$form->submitButton( 'Opslaan', 'Opslaan', 'class="button-primary" style="float:right"' );
		} else {
			$form->AddHTML( '<br/>Het opslaan gebeurt zodra er op een datum wordt geklikt.' );
		}

		$form->addHTML( '<div class="clear"></div>
                                </div>
                            </div>
                        </div>
                    </div>' );

		if ( $table == 'settings' ) {
			$form->addHTML( '<div id="tagsdiv-post_tag" class="postbox ">
                    <h3 class="hndle"><span>Geavanceerd</span></h3>
                    <div class="inside">
                        <p><input type="button" class="button" value="Database updaten" onclick="document.location=\'?page=' . $_GET['page'] . '&act=update_database\'"></p>
                        <p><input type="button" class="button" value="Afbeeldingen updaten" onclick="document.location=\'?page=' . $_GET['page'] . '&act=update_images\'"></p>
                        <p><input type="button" class="button" value="Links updaten" onclick="document.location=\'?page=' . $_GET['page'] . '&act=update_links\'"></p>
                    </div>
                </div>' );
		}

		$form->addHTML( '</div></div>' );
	}

	$form->addHTML( '
			
            <div id="post-body" class="metabox-holder" style="padding-top:0">
            	<div id="post-body-content">
                	<div id="titlediv">
                    	<div id="titlewrap">' );

	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$lang_suffix = '_' . ICL_LANGUAGE_CODE;
	} else {
		$lang_suffix = '';
	}

	switch ( $table ) {
		case 'accommodatie':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
			if ( ! isset( $_POST['title'] ) ) {
				$form->setValue( 'title', utf8_encode( $form->getDBValue( 'title' ) ), 1 );
			}
			$form->addHTML( '</div></div>' );
			$form->addHTML( '<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
				<div class="postbox">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table>' );
			$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );

			$form->textField( 'Titel Intern', 'title_intern', FH_NOT_EMPTY, 50, null, 'placeholder="Titel intern"' );
			$form->textField( 'Permalink', 'name', FH_NOT_EMPTY, 50, null, 'placeholder="Naam"' );
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = icl_get_languages( 'skip_missing=N' );
				foreach ( $languages as $lang ) {
					if ( $lang['language_code'] == 'nl' ) {
						continue;
					}
					$form->textField( 'Titel ' . strtoupper( $lang['language_code'] ), 'title_' . $lang['language_code'], null, 80 );
				}
			}

			if ( $accommodatie->afbeelding ) {
				$form->addHTML( '<tr><td colspan="3"><div id="afbeelding-div"><img src="' . $accommodatie->resized_img . '" alt="" width="150" /></div></td></tr>' );
				$form->setValue( 'afbeelding', $accommodatie->afbeelding );
			}

			$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %field% %error% %help%</td></tr>' );
			$form->textField( "Afbeelding", 'afbeelding' );
			$form->hiddenField( 'afbeelding_id' );
			$form->button( "Upload", 'afbeelding_button', 'class="btn uploader"' );

			$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );

			if ( $this->camping_rights ) {
				$form->hiddenField( 'camping_id' );
				if ( isset( $_GET['id'] ) ) {
					if ( $form->getValue( 'camping_id' ) != $this->camping_rights ) {
						die( 'Geen rechten' );
					} else {
						$form->setValue( 'camping_id', $this->camping_rights );
					}
				} else {
					$form->setValue( 'camping_id', $this->camping_rights );
				}
			} else {
				$form->dbSelectField( 'Camping', 'camping_id', SIMPEL_DB_PREFIX . 'camping', array( 'id', 'title' ), 'order by title' );
			}
//                $form->textField('Permalink', 'name', FH_NOT_EMPTY, 50);
			$form->dbSelectField( 'Type', 'acco_type_id', SIMPEL_DB_PREFIX . 'acco_type', array( 'id', 'title' ), 'order by title' );
			$sterren = array( 1, 2, 3, 4, 5 );
			//$form->SelectField('Aantal sterren', 'sterren', $sterren, null, false);
			$form->textField( 'Vanaf prijs p/week', 'vanaf_prijs', FH_FLOAT, 50 );
			$form->textField( 'Aantal personen', 'aantal_personen', FH_INTEGER, 50 );
			$form->textField( 'Aantal slaapkamers', 'aantal_slaapkamers', FH_INTEGER, 50 );
			$form->checkBox( 'Huisdieren toegestaan', 'huisdieren_toegestaan', 1 );
			$form->checkBox( 'Boekbaar op plattegrond', 'plattegrond', 1 );
			$form->textField( 'Minimaal aantal nachten', 'min_aantal_nachten', FH_INTEGER );
			$form->textField( 'Aankomst tijd', 'aankomst_tijd' );
			$form->textField( 'Vertrek tijd', 'vertrek_tijd' );
			$form->textField( 'Volgorde', 'seq_inner', FH_INTEGER, 5 );
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$form->textArea( 'Samenvatting ' . strtoupper( ICL_LANGUAGE_CODE ), 'samenvatting' . $lang_suffix, null, 80, 3 );
			} else {
				$form->textArea( 'Samenvatting', 'samenvatting', null, 80, 3 );
			}
			if ( ! isset( $_POST[ 'samenvatting' . $lang_suffix ] ) ) {
				$form->setValue( 'samenvatting' . $lang_suffix, utf8_encode( $form->getDBValue( 'samenvatting' . $lang_suffix ) ), 1 );
			}

			$form->setMask( '<tr><td colspan="3"><label for="%name%">%title%</label><div class="clear"></div>%field% %error% %help%</td></tr>' );

			$field   = 'omschrijving' . ( defined( 'ICL_LANGUAGE_CODE' ) ? $lang_suffix : '' );
			$content = $accommodatie->$field;
			ob_start();
			?>
			<div id="poststuff" class="clear">
				<br/>
				<label><strong>Omschrijving <?= ( defined( 'ICL_LANGUAGE_CODE' ) ? strtoupper( ICL_LANGUAGE_CODE ) : '' ) ?></strong></label>

				<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
					<?php wp_editor( $content, 'omschrijving' . ( defined( 'ICL_LANGUAGE_CODE' ) ? $lang_suffix : '' ) ); ?>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				</div>
			</div>
			<?php
			$editor = ob_get_contents();
			ob_end_clean();
			add_filter( 'admin_head', array( &$this, 'booking_tinymce' ) );

			$form->addHTML( '<tr><td colspan="3">' . $editor . '</td></tr></table></div></div></div>' );

			$form->addHTML( '
				<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Faciliteiten</span></h3>
						<div class="inside">' );
			$sql          = 'select * from ' . SIMPEL_DB_PREFIX . 'faciliteiten where is_camping = 0 order by title';
			$faciliteiten = $this->wpdb->get_results( $sql );
			foreach ( $faciliteiten as $i => $faciliteit ) {
				$form->setMask( '<div class="checkbox-group">%field% %title%</div>', 0 );
				$form->checkBox( utf8_decode( $faciliteit->title ), 'faciliteit_' . $faciliteit->id, 1 );
				if ( isset( $_GET['id'] ) && $this->wpdb->get_var( $this->wpdb->prepare( 'select id from ' . SIMPEL_DB_PREFIX . 'faciliteiten_per where faciliteit_id = "%d" and accommodatie_id = "%d"', $faciliteit->id, $_GET['id'] ) ) ) {
					$form->setValue( 'faciliteit_' . $faciliteit->id, 1 );
				}
			}
			$form->addHTML( '<div class="clear"></div></div>
					</div>
				</div>' );
			break;

		case 'camping':
			if ( $this->camping_rights && $_GET['id'] != $this->camping_rights ) {
				die( 'Geen rechten voor deze camping' );
			}
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Naam camping"' );
			$form->addHTML( '</div>
				<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Details</span></h3>
						<div class="inside"><table>' );
			$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );
			$form->textField( 'Plaats', 'plaats', FH_NOT_EMPTY, 50 );
			$form->textField( 'E-mail', 'email', FH_EMAIL, 50 );
			//$form->textField('URL Plattegrond', 'plattegrond_url', null, 50);
			$form->textField( 'URL Voorwaarden', 'voorwaarden_url', null, 50 );
			$form->checkBox( 'Controle stap?', 'has_controle_stap', 1 );
			$form->checkBox( 'NAW gegevens in controle stap?', 'stap3_naw', 1 );
			$form->textField( 'Leeftijd jeugd', 'age_youth', null, 50 );
			$form->textField( 'Leeftijd kind', 'age_child', null, 50 );
			$form->textField( 'Leeftijd baby', 'age_baby', null, 50 );
			$form->radioButton( 'Bereken jeugd als', 'youth_as_child', array( 'Volwassene', 'Kind' ), FH_NOT_EMPTY );
			$form->textArea( 'Tweet standaard tekst', 'tweet_tekst', null, 55, 4 );
			$form->textArea( 'Facebook standaard tekst', 'facebook_tekst', null, 55, 4 );

			if ( $camping->logo_medium ) {
				$form->addHTML( '<tr><td colspan="3"><div id="afbeelding-div"><img src="' . $camping->logo_medium . '" alt="" /></div></td></tr>' );
			}
			$form->setMask( '<tr><td colspan="3">%field% %error% %help%</td></tr>' );
			$form->hiddenField( 'logo' );
			$form->hiddenField( 'logo_id' );
			$form->button( "Kies logo", 'logo_button', 'class="button uploader"' );

			if ( $camping->plattegrond_thumb ) {
				$form->addHTML( '<tr><td colspan="3"><img src="' . $camping->plattegrond_thumb . '" alt="" /></td></tr>' );
			}
			$form->hiddenField( 'plattegrond' );
			$form->hiddenField( 'plattegrond_id' );
			$form->button( "Kies plattegrond", 'plattegrond_button', 'class="button uploader"' );

			$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );

			$form->addHTML( '</table><strong>Teksten</strong><div id="tabs">' );

			// maak tabjes van alle verschillende tekst velden
			if ( SIMPEL_MULTIPLE ) {
				$fields = array(
					'txt_camping'   => 'Algemene informatie',
					'txt_omgeving'  => 'Tekst omgeving',
					'email_header'  => 'Bevestigingsmail header',
					'email_footer'  => 'Bevestigingsmail footer',
					'confirm_tekst' => 'Tekst na boeking',
				);
			} else {
				$fields = array(
					'email_header'  => 'Bevestigingsmail header',
					'email_footer'  => 'Bevestigingsmail footer',
					'confirm_tekst' => 'Tekst na boeking',
				);
			}

			$form->addHTML( '<ul>' );
			foreach ( $fields as $fld => $title ) {
				$form->addHTML( '<li><a href="#div_' . $fld . '">' . $title . ( defined( 'ICL_LANGUAGE_CODE' ) ? ' (' . strtoupper( ICL_LANGUAGE_CODE ) . ')' : '' ) . '</a></li>' );
			}
			$form->addHTML( '</ul>' );

			$form->setMask( '%field% %error% %help%' );
			foreach ( $fields as $fld => $title ) {
				$form->addHTML( '<div id="div_' . $fld . '">' );
				$content = ( isset( $_GET['id'] ) ? $form->getDbValue( $fld . $lang_suffix ) : '' );
				ob_start();
				?>
				<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
					<?php wp_editor( $content, $fld . $lang_suffix ); ?>
				</div>
				<?php
				$editor = ob_get_contents();
				ob_end_clean();
				$form->addHTML( $editor );

				$form->addHTML( '</div>' );
			}
			$form->addHTML( '</div>' );


			wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
			wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );

			add_filter( 'admin_head', array( &$this, 'booking_tinymce' ) );

			$form->addHTML( '<br/><br/></div>' );


			$form->addHTML( '</div></div></div><div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Faciliteiten</span></h3>
						<div class="inside">' );
			$sql          = 'select * from ' . SIMPEL_DB_PREFIX . 'faciliteiten where is_camping = 1 order by title';
			$faciliteiten = $this->wpdb->get_results( $sql );
			foreach ( $faciliteiten as $i => $faciliteit ) {
				$form->setMask( '<div class="checkbox-group">%field% %title%</div>', 0 );
				$form->checkBox( $faciliteit->title, 'faciliteit_' . $faciliteit->id, 1 );
				if ( isset( $_GET['id'] ) && $this->wpdb->get_var( $this->wpdb->prepare( 'select id from ' . SIMPEL_DB_PREFIX . 'faciliteiten_per where faciliteit_id = "%d" and camping_id = "%d"', $faciliteit->id, $_GET['id'] ) ) ) {
					$form->setValue( 'faciliteit_' . $faciliteit->id, 1 );
				}
			}
			$form->addHTML( '<div class="clear"></div></div>
					</div>
				</div>' );
			break;

		case 'beschikbaarheid':
			$form->addHTML( '</div>' );
			$acco    = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
			$camping = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $acco->camping_id ) );
			if ( $this->camping_rights && $camping->id != $this->camping_rights ) {
				die( 'geen rechten' );
			}
			$form->addHTML( '
				<div class="postbox">
					<h3><span>' . $camping->title . ' - ' . $acco->title . '</span></h3>
					<div class="inside" id="beschikbaarheid">
					<input type="hidden" id="month" value="' . date( 'n' ) . '"/>
					<input type="hidden" id="year" value="' . date( 'Y' ) . '"/>
					<input type="button" class="button floatright" onclick="beschikbaarheid_next(3)" value="Volgende &gt;&gt;" />
					<input type="button" class="button" onclick="beschikbaarheid_prev(3)" value="&lt;&lt; Vorige" />
					<div id="beschikbaarheid-data">' );
			$form->addHTML( $this->show_beschikbaarheid( $acco->id ) );
			$form->addHTML( '</div><div class="clear"></div></div></div>' );
			break;

		case 'periode':
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="0" cellpadding="0">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );

			if ( $this->camping_rights ) {
				$form->hiddenField( 'camping_id' );
				if ( isset( $_GET['id'] ) ) {
					if ( $form->getValue( 'camping_id' ) != $this->camping_rights ) {
						die( 'Geen rechten' );
					} else {
						$form->setValue( 'camping_id', $this->camping_rights );
					}
				} else {
					$form->setValue( 'camping_id', $this->camping_rights );
				}
			} else {
				$form->dbSelectField( 'Camping', 'camping_id', SIMPEL_DB_PREFIX . 'camping', array( 'id', 'title' ), 'order by title' );
			}

			if ( isset( $_GET['copy'] ) ) {
				$form->onCorrect( array( &$this, 'copy_periodes' ) );
				$form->dbSelectField( 'Kopieer periodes van type', 'source_acco_type_id',
					SIMPEL_DB_PREFIX . 'acco_type', array( 'id', 'title' ), 'order by title' );
				$form->dbSelectField( 'Kopieer naar type', 'target_acco_type_id', SIMPEL_DB_PREFIX . 'acco_type',
					array( 'id', 'title' ), 'order by title' );

				$form->addHTML( '<tr><td colspan="3">Kopieer allen periodes tussen de volgende data: (leeg laten als alles gekopieerd moet worden)</td></tr>' );
				$form->jsDateField( 'Vanaf', 'van', null, false, 'd-m-y', '5:5' );
				$form->jsDateField( 'Tot', 'tot', null, false, 'd-m-y', '5:5' );

				$form->checkBox( 'Verwijder bestaande periodes', 'verwijderen' );
			} elseif ( isset( $_GET['bulk'] ) ) {
				$form->onCorrect( array( &$this, 'bulk_periodes' ) );
				$form->dbSelectField( 'Type', 'source_acco_type_id', SIMPEL_DB_PREFIX . 'acco_type', array( 'id', 'title' ), 'order by title' );

				$form->addHTML( '<tr><td colspan="3">Maak periodes aan tussen de volgende data:</td></tr>' );
				$form->jsDateField( 'Vanaf', 'van', FH_NOT_EMPTY, false, 'd-m-y', '5:5' );
				$form->jsDateField( 'Tot', 'tot', FH_NOT_EMPTY, false, 'd-m-y', '5:5' );

				$form->textField( 'Nachten per periode', 'nights', FH_INTEGER );
				$form->setValue( 'nights', 1 );
			} else {
				$form->dbSelectField( 'Type', 'acco_type_id', SIMPEL_DB_PREFIX . 'acco_type', array( 'id', 'title' ), 'order by title' );
				$form->jsDateField( 'Periode van', 'van', null, true, 'd-m-y', '5:5' );
				$form->jsDateField( 'Periode tot', 'tot', null, true, 'd-m-y', '5:5' );
				$form->textField( 'Naam periode', 'naam', null, 50 );
				$form->textArea( 'Omschrijving', 'omschrijving', null, 50, 5 );

				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$languages = icl_get_languages( 'skip_missing=N' );
					foreach ( $languages as $lang ) {
						if ( $lang['language_code'] == 'nl' ) {
							continue;
						}
						$form->textField( 'Naam periode ' . strtoupper( $lang['language_code'] ), 'naam_' . $lang['language_code'], null, 80 );
						$form->textArea( 'Omschrijving ' . strtoupper( $lang['language_code'] ), 'omschrijving_' . $lang['language_code'], null, 50, 5 );
					}
				}

				$form->textField( 'Alternatieve aankomsttijd', 'alternatieve_aankomst_tijd' );
				$form->textField( 'Alternatieve vertrektijd', 'alternatieve_vertrek_tijd' );
				$form->checkBox( 'Boeken op plattegrond mogelijk?', 'boeken_op_plattegrond', 1 );
				if ( ! isset( $_GET['id'] ) ) {
					$form->setValue( 'boeken_op_plattegrond', 1 );
				}

				$form->addHTML( '<tr><td colspan="5"><p>&nbsp;</p><strong>Weekaankomst mogelijk op de volgende dagen:</strong></td></tr>' );
				$dagen = array( 'ma' => 'Maandag', 'di' => 'Dinsdag', 'wo' => 'Woensdag', 'do' => 'Donderdag', 'vr' => 'Vrijdag', 'za' => 'Zaterdag', 'zo' => 'Zondag' );
				foreach ( $dagen as $db_dag => $dag ) {
					$form->checkbox( $dag, $db_dag, 1 );

					if ( ! isset( $_GET['id'] ) ) {
						$form->setValue( $db_dag, 1 );
					}
				}
			}
			$form->addHTML( '</table><div class="clear"></div></div></div></div>' );
			break;

		case 'boeking':
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
                <div class="postbox">
                    <div class="inside" style="text-align:left">
                    <table cellspacing="0" cellpadding="2"><tbody>' );
			$fields = array( 'naam', 'datum_boeking', 'datum_aankomst', 'datum_vertrek', 'prijs' );
			foreach ( $fields as $fld ) {
				$form->textField( $fld, $fld );
				$form->setFieldViewMode( $fld );
			}
			$form->setMask( '<tr><td colspan="2">%field%</td></tr>' );
			$form->textField( 'html', 'mail_html' );
			$form->setFieldViewMode( 'mail_html' );

			$form->addHTML( '</tbody></table></div></div>' );
			break;
		case 'yield':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Naam camping"' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
                <div class="postbox margintop">
                    <div class="inside" style="text-align:left">
                    <table cellspacing="0" cellpadding="2"><tbody>' );
			$form->setMask( '<tr><th width="200"><label for="%name%">%title%  %help%</label></th><td>%field% %error%</td></tr>' );

			$form->jsDateField( 'Geldig van', 'datum_van', null, false, 'd-m-y', '10:10' );
			$form->jsDateField( 'Geldig tot', 'datum_tot', null, false, 'd-m-y', '10:10' );
			$form->setHelpText( 'datum_van', 'Deze kan leeg gelaten worden als deze altijd geldig is.' );
			$form->setHelpText( 'datum_tot', 'Deze kan leeg gelaten worden als deze altijd geldig is.' );

			$form->textField( 'Prijs bovenop boeking', 'prijs', _FH_FLOAT, 50 );
			$form->textField( 'Percentage bovenop boeking', 'percentage', _FH_FLOAT, 50 );

			$form->checkbox( 'Geldig voor accommodaties', 'accommodaties_geldig', $this->get_accommodaties( 999, 'title', true ) );
			$form->setHelpText( 'accommodaties_geldig', 'Niks aanvinken als deze voor allemaal geldt.' );

			$form->addHTML( '</tbody></table></div></div>' );
			break;
		case 'aanbiedingen':
			if ( $_GET['act'] == 'periodes' ) {
				$form->addHTML( '</div>' );
				$form->onCorrect( array( &$this, 'save_aanbiedingen' ) );
				$acco    = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
				$camping = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $acco->camping_id ) );
				if ( $this->camping_rights && $camping->id != $this->camping_rights ) {
					die( 'geen rechten' );
				}
				$form->addHTML( '
					<div class="postbox">
						<h3><span>' . $camping->title . ' - ' . $acco->title . '</span></h3>
						<div class="inside" id="" style="padding:10px;overflow-x:auto">
						<table width="100%" cellspacing="0" cellpadding="4">
							<thead>
								<tr>
									<th>Periode</th>' );
				$aanbiedingen = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen where camping_id = "' . $camping->id . '" order by title' );
				foreach ( $aanbiedingen as $aanb ) {
					$form->addHTML( '<th>' . $aanb->title . '</th>' );
				}
				$form->addHTML( '</tr>
							</thead><tbody>' );
				$periodes = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode
					where camping_id = "%d" and acco_type_id = "%d" and tot >= now() 
					order by van', $camping->id, $acco->acco_type_id ) );
				$form->setMask( '<td>%field% %error%</td>' );
				foreach ( $periodes as $i => $periode ) {
					$form->addHTML( '<tr class="' . ( $i % 2 ? 'dark' : '' ) . '"><td><nobr>' . date( 'd/m', strtotime( $periode->van ) ) . ' - ' . date( 'd/m', strtotime( $periode->tot ) ) . '</nobr></td>' );
					$form->setMask( '<td>%field% %error%</td>' );
					foreach ( $aanbiedingen as $aanb ) {
						$form->checkbox( '', 'aanb_' . $periode->id . '_' . $aanb->id, 1 );
						$results = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per where accommodatie_id = "%d" and periode_id = "%d" and aanbieding_id="%d" limit 1', $acco->id, $periode->id, $aanb->id ) );
						if ( count( $results ) ) {
							$form->setValue( 'aanb_' . $periode->id . '_' . $aanb->id, 1 );
						}
					}
				}
				$form->addHTML( '</tbody></table></div></div>' );
				$form->setMask( '%field% ' );
				$form->cancelButton( 'Terug', null, null, 'class="button"' );
				$form->submitButton( 'Opslaan', 'Opslaan', 'class="button-primary"' );
			} else {
				$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
				$form->addHTML( '</div></div>' );
				$form->addHTML( '<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Detail opties</span></h3>
						<div class="inside"><table><tbody>' );
				$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );
				if ( $this->camping_rights ) {
					$form->hiddenField( 'camping_id' );
					if ( isset( $_GET['id'] ) ) {
						if ( $form->getValue( 'camping_id' ) != $this->camping_rights ) {
							//die('Geen rechten');
						} else {
							$form->setValue( 'camping_id', $this->camping_rights );
						}
					} else {
						$form->setValue( 'camping_id', $this->camping_rights );
					}
				} else {
					$form->dbSelectField( 'Camping', 'camping_id', SIMPEL_DB_PREFIX . 'camping', array( 'id', 'title' ), 'order by title' );
				}


				$form->textField( "Omschrijving", 'omschrijving', FH_NOT_EMPTY );
				$form->textField( "Minimum aantal nachten", 'min_nachten', FH_INTEGER );
				$form->textField( "Aantal nachten korting", 'nachten_korting', _FH_INTEGER );
				$form->textField( "Percentage korting", 'perc_korting', _FH_FLOAT );
				$form->textArea( 'Voorwaarden', 'voorwaarden' );

				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$languages = icl_get_languages( 'skip_missing=N' );
					foreach ( $languages as $lang ) {
						if ( $lang['language_code'] == 'nl' ) {
							continue;
						}
						$form->textField( 'Omschrijving ' . strtoupper( $lang['language_code'] ), 'omschrijving_' . $lang['language_code'], null, 80 );
						$form->textArea( 'Voorwaarden ' . strtoupper( $lang['language_code'] ), 'voorwaarden_' . $lang['language_code'], null, 80 );
					}
				}
				$form->addHTML( '</tbody></table></div></div>' );
			}
			break;

		case 'arrangementen':
			if ( $_GET['act'] == 'periodes' ) {
				$form->addHTML( '</div>' );
				$form->onCorrect( array( &$this, 'save_arrangementen' ) );
				$acco    = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
				$camping = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $acco->camping_id ) );
				if ( $this->camping_rights && $camping->id != $this->camping_rights ) {
					die( 'geen rechten' );
				}
				$form->addHTML( '
					<div class="postbox">
						<h3><span>' . $camping->title . ' - ' . $acco->title . '</span></h3>
						<div class="inside" id="" style="padding:10px;overflow-x:auto">
						<table width="100%" cellspacing="0" cellpadding="4">
							<thead>
								<tr>
									<th>Periode</th>' );
				$arrangementen = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen where camping_id = "' . $camping->id . '" and altijd_geldig = 0 order by title' );
				foreach ( $arrangementen as $arrangement ) {
					$form->addHTML( '<th onclick="toggleArrangementen(' . $arrangement->id . ')">' . $arrangement->title_nl . '</th>' );
				}
				$form->addHTML( '</tr>
							</thead><tbody>' );
				$periodes = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode
					where camping_id = "%d" and acco_type_id = "%d" and tot >= now()
					order by van', $camping->id, $acco->acco_type_id ) );
				$form->setMask( '<td>%field% %error%</td>' );
				foreach ( $periodes as $i => $periode ) {
					$form->addHTML( '<tr class="' . ( $i % 2 ? 'dark' : '' ) . '"><td><nobr>' . date( 'd/m', strtotime( $periode->van ) ) . ' - ' . date( 'd/m', strtotime( $periode->tot ) ) . '</nobr></td>' );
					$form->setMask( '<td>%field% %error%</td>' );
					foreach ( $arrangementen as $arrangement ) {
						$form->checkbox( '', 'arrangement_' . $periode->id . '_' . $arrangement->id, 1, null, null, 'class="arr' . $arrangement->id . '"' );
						$results = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen_per where accommodatie_id = "%d" and periode_id = "%d" and arrangement_id="%d" limit 1', $acco->id, $periode->id,
								$arrangement->id ) );
						if ( count( $results ) ) {
							$form->setValue( 'arrangement_' . $periode->id . '_' . $arrangement->id, 1 );
						}
					}
				}
				$form->addHTML( '</tbody></table></div></div>' );
				$form->setMask( '%field% ' );
				$form->cancelButton( 'Terug', null, null, 'class="button"' );
				$form->submitButton( 'Opslaan', 'Opslaan', 'class="button-primary"' );
			} else {
				$arrangement = new Arrangement( filter_input( INPUT_GET, 'id' ) );
				$form->addHTML( '</div></div>' );
				$form->addHTML( '<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Detail opties</span></h3>
						<div class="inside"><table><tbody>' );
				$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );
				$form->textField( 'Permalink', 'name', FH_NOT_EMPTY, 50, null, 'placeholder="Naam"' );
				$form->textField( "Prijs", 'prijs', _FH_FLOAT );
				$form->selectField( "Prijs per", 'per', array( 'stay' => 'verblijf', 'night' => 'nacht', 'person' => 'persoon', 'person_night' => 'p.persoon/p.nacht' ), null, true );
				$form->checkBox( "Prijs accommodatie overschrijven", 'overwrite', 1 );
				$form->checkBox( "Zichtbaar in overzicht", 'zichtbaar', 1 );
				$form->checkBox( "Altijd geldig", 'altijd_geldig', 1 );
				$form->textField( "Minimum aantal nachten", 'min_nights', _FH_INTEGER );
				$form->textField( "Maximum aantal nachten", 'max_nights', _FH_INTEGER );

				if ( $arrangement->afbeelding ) {
					$form->addHTML( '<tr><td colspan="3"><div id="afbeelding-div"><img src="' . $arrangement->resized_img . '" alt="" width="150" /></div></td></tr>' );
					$form->setValue( 'afbeelding', $arrangement->afbeelding );
				}

				$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %field% %error% %help%</td></tr>' );
				$form->textField( "Afbeelding", 'afbeelding' );
				$form->hiddenField( 'afbeelding_id' );
				$form->button( "Upload", 'afbeelding_button', 'class="btn uploader"' );


				$form->addHTML( '</tbody></table></div></div>' );
				$form->addHTML( '<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Prijzen per accommodatie</span></h3>
						<div class="inside"><table width="100%" class="table table-hover">' );

				$form->addHTML( '<thead>
						<tr>
							<th>Accommodatie</th>
							<th>Prijs arrangement</th>
							<th>Meerprijs volw</th>
							<th>Meerprijs kind</th>
						</tr>
					</thead>' );

				$accommodaties = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie  order by title' );
				foreach ( $accommodaties as $acco ) {
					$form->addHTML( '<tr><td>' . $acco->title . '</td>' );
					$form->setMask( '<td>%field% %error% %help%</td>' );

					$form->textField( 'Prijs acco', 'prijs_acco_' . $acco->id, _FH_FLOAT, 10 );
					$form->textField( 'Meerprijs volw', 'meerprijs_volw_' . $acco->id, _FH_FLOAT, 10 );
					$form->textField( 'Meerprijs kind', 'meerprijs_kind_' . $acco->id, _FH_FLOAT, 10 );

					$form->addHTML( '</tr>' );

					// set values
					$row = $this->wpdb->get_row( $this->wpdb->prepare(
						'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen_prijzen where accommodatie_id = "%d" and arrangement_id = "%d"',
						$acco->id, filter_input( INPUT_GET, 'id' ) ) );
					if ( $row ) {
						$form->setValue( 'prijs_acco_' . $acco->id, $row->prijs );
						$form->setValue( 'meerprijs_volw_' . $acco->id, $row->meerprijs_volw );
						$form->setValue( 'meerprijs_kind_' . $acco->id, $row->meerprijs_kind );
					}
				}


				$form->addHTML( '</tbody></table></div></div>' );
				$form->addHTML( '<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
					<div class="postbox">
						<h3 class="hndl"><span>Teksten</span></h3>
						<div class="inside"><table>' );


				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$languages = icl_get_languages( 'skip_missing=N' );
					foreach ( $languages as $lang ) {
						$form->setMask( '<tr><td><label for="%name%">%title%</label></td><td>%field% %error% %help%</td></tr>' );
						$form->addHTML( '<tr><td colspan="4"><hr/></td></tr>' );
						$form->textField( 'Titel ' . strtoupper( $lang['language_code'] ), 'title_' . $lang['language_code'], null, 80 );
						$form->textArea( 'Omschrijving ' . strtoupper( $lang['language_code'] ), 'omschrijving_' . $lang['language_code'], null, 80 );
						$form->textArea( 'Tekst bevestigingsmail ' . strtoupper( $lang['language_code'] ), 'mail_' . $lang['language_code'], null, 80 );
						//$form->setMask('<tr><td colspan="3">%field% %error% %help%</td></tr>');
						$fields = array(
							'overview_' . $lang['language_code'] => 'Uitgebreide omschrijving ' . strtoupper( $lang['language_code'] ),
							'terms_' . $lang['language_code']    => 'Voorwaarden ' . strtoupper( $lang['language_code'] ),
						);
						foreach ( $fields as $fld => $title ) {
							$form->addHTML( '<tr><td colspan="3"><div id="div_' . $fld . '"><p><strong>' . $title . '</strong></p>' );
							$content = utf8_encode( isset( $_GET['id'] ) ? $form->getDbValue( $fld ) : '' );
							ob_start();
							?>
							<div id="<?php echo user_can_richedit() ? 'postdivrich' : 'postdiv'; ?>" class="postarea">
								<?php wp_editor( $content, $fld ); ?>
							</div>
							<?php
							$editor = ob_get_contents();
							ob_end_clean();
							$form->addHTML( $editor );

							$form->addHTML( '</div>' );
						}
						$form->addHTML( '</div></td></tr>' );
					}
				} else {
					$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
				}
				if ( $this->camping_rights ) {
					$form->hiddenField( 'camping_id' );
					if ( isset( $_GET['id'] ) ) {
						if ( $form->getValue( 'camping_id' ) != $this->camping_rights ) {
							//die('Geen rechten');
						} else {
							$form->setValue( 'camping_id', $this->camping_rights );
						}
					} else {
						$form->setValue( 'camping_id', $this->camping_rights );
					}
				} else {
					$form->dbSelectField( 'Camping', 'camping_id', SIMPEL_DB_PREFIX . 'camping', array( 'id', 'title' ), 'order by title' );
				}


				$form->addHTML( '</tbody></table></div></div>' );
			}
			break;

		case 'available':
			$form->addHTML( '</div>' );
			$form->onCorrect( array( &$this, 'save_available' ) );
			$acco    = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
			$camping = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $acco->camping_id ) );
			if ( $this->camping_rights && $camping->id != $this->camping_rights ) {
				die( 'geen rechten' );
			}

			$form->addHTML( '
                <div class="postbox">
                    <h3><span>' . utf8_decode( $camping->title ) . ' - ' . $acco->title . '</span></h3>
                    <div class="inside" style="padding:10px">
                    <table width="400" cellspacing="0" cellpadding="4" style="margin-top: 14px">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th># kamers beschikbaar</th>
                                <th># kamers geboekt</th>
                            </tr>
                        </thead><tbody>' );
			$dag = time();
			$end = strtotime( '+1 year' );

			while ( $dag < $end ) {

				$form->addHTML( '<tr style="border-bottom:1px solid #eee"><td><nobr>' . date( 'd/m', $dag ) . '</nobr></td>' );
				$form->setMask( '<td>%field% %error%</td>' );
				$form->textField( '', 'kamers_' . date( "Y_m_d", $dag ), _FH_INTEGER, 4 );

				$nr = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'available where datum = "%s" and accommodatie_id = "%d"', date( 'Y-m-d', $dag ), $_GET['id'] ) );
				if ( $nr ) {
					$form->setValue( 'kamers_' . date( "Y_m_d", $dag ), $nr->nr );
				}

				$boekings = $this->wpdb->get_var( $this->wpdb->prepare( 'select count(*) from ' . SIMPEL_DB_PREFIX . 'boeking where datum_aankomst = "%s" and accommodatie_id = %d', date( 'Y-m-d', $dag ), $_GET['id'] ) );
				$form->addHTML( '<td align="center"><nobr>' . ( $boekings + 0 ) . '</nobr></td>' );

				$dag = strtotime( '+1 day', $dag );
			}

			$form->addHTML( '</tbody></table></div></div>' );
			$form->setMask( '%field% ' );
			$form->cancelButton( 'Terug', null, null, 'class="button"' );
			$form->submitButton( 'Opslaan', 'Opslaan', 'class="button-primary"' );

			break;
		case 'prijs':
			$form->addHTML( '</div>' );
			$form->onCorrect( array( &$this, 'save_prices' ) );
			$form->hiddenField( 'json' );
			$acco    = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
			$camping = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $acco->camping_id ) );
			if ( $this->camping_rights && $camping->id != $this->camping_rights ) {
				die( 'geen rechten' );
			}
			$form->addHTML( '
				<div class="postbox">
					<h3><span>' . utf8_decode( $camping->title ) . ' - ' . $acco->title . '</span></h3>
					<div class="inside" id="beschikbaarheid" style="padding:10px">
					<input id="show-aanbiedingen" type="checkbox"><label for="show-aanbiedingen" style="float:none;">Laat de aanbiedingen zien</label>
					<div class="clear"></div>
					<table width="100%" cellspacing="0" cellpadding="4" style="margin-top: 14px">
						<thead>
							<tr>
								<th>Periode</th>
								<th class="dark periode">Nacht</th>
								<th class="periode">Midweek</th>
								<th class="dark periode">Weekend</th>
								<th class="periode">Week</th>
								<th class="dark periode">Periode</th>
								<th rowspan="2">Inclusief toeslagen</th>
								<th rowspan="2">Nr. personen inclusief</th>
								<th rowspan="2">Meerprijs volwassene/ nacht</th>
								<th rowspan="2">Meerprijs kind/ nacht</th>
							</tr>
							<tr>
								<th></th>
								<th class="dark">Prijs</th>
								<th class="dark aanbieding">Aanbieding</th>
								<th>Prijs</th>
								<th class="aanbieding">Aanbieding</th>
								<th class="dark">Prijs</th>
								<th class="dark aanbieding">Aanbieding</th>
								<th>Prijs</th>
								<th class="aanbieding">Aanbieding</th>
								<th class="dark">Prijs</th>
								<th class="dark aanbieding">Aanbieding</th>
							</tr>
						</thead><tbody>' );
			$periodes = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "%d" and acco_type_id = "%d" and tot >= now() order by van', $camping->id, $acco->acco_type_id ) );
			$form->setMask( '<td>%field% %error%</td>' );
			foreach ( $periodes as $periode ) {
				$form->addHTML( '<tr><td><nobr>' . date( 'd/m', strtotime( $periode->van ) ) . ' - ' . date( 'd/m', strtotime( $periode->tot ) ) . ( $periode->naam ? '<br/>(' . $periode->naam . ')' : '' ) . '</nobr></td>' );
				$form->setMask( '<td class="dark">%field% %error%</td>' );
				$form->textField( '', 'nachtprijs_' . $periode->id, null, 4 );
				$form->setMask( '<td class="dark aanbieding">%field% %error%</td>' );
				$form->textField( '', 'nachtaanbieding_' . $periode->id, null, 4 );

				$form->setMask( '<td>%field% %error%</td>' );
				$form->textField( '', 'midweekprijs_' . $periode->id, null, 4 );
				$form->setMask( '<td class="aanbieding">%field% %error%</td>' );
				$form->textField( '', 'midweekaanbieding_' . $periode->id, null, 4 );

				$form->setMask( '<td class="dark">%field% %error%</td>' );
				$form->textField( '', 'weekendprijs_' . $periode->id, null, 4 );
				$form->setMask( '<td class="dark aanbieding">%field% %error%</td>' );
				$form->textField( '', 'weekendaanbieding_' . $periode->id, null, 4 );

				$form->setMask( '<td>%field% %error%</td>' );
				$form->textField( '', 'weekprijs_' . $periode->id, null, 4 );
				$form->setMask( '<td class="aanbieding">%field% %error%</td>' );
				$form->textField( '', 'weekaanbieding_' . $periode->id, null, 4 );

				$form->setMask( '<td class="dark">%field% %error%</td>' );
				$form->textField( '', 'periodeprijs_' . $periode->id, null, 4 );
				$form->setMask( '<td class="dark aanbieding">%field% %error%</td>' );
				$form->textField( '', 'periodeaanbieding_' . $periode->id, null, 4 );

				$form->setMask( '<td>%field% %error%</td>' );
				$form->checkBox( 'inclusief toeslagen', 'inclusief_toeslagen_' . $periode->id, 1 );
				$form->textField( 'personen inclusief', 'inclusief_' . $periode->id, null, 4 );
				$form->textField( 'meerprijs volw', 'meerprijs_volw_' . $periode->id, null, 4 );
				$form->textField( 'meerprijs kind', 'meerprijs_kind_' . $periode->id, null, 4 );
				$db_prijs = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'prijs where periode_id = "%d" and accommodatie_id = "%d"', $periode->id, $_GET['id'] ) );
				if ( $db_prijs ) {
					if ( $db_prijs->nachtprijs != 0 ) {
						$form->setValue( 'nachtprijs_' . $periode->id, $db_prijs->nachtprijs );
					}
					if ( $db_prijs->nachtaanbieding != 0 ) {
						$form->setValue( 'nachtaanbieding_' . $periode->id, $db_prijs->nachtaanbieding );
					}
					if ( $db_prijs->midweekprijs != 0 ) {
						$form->setValue( 'midweekprijs_' . $periode->id, $db_prijs->midweekprijs );
					}
					if ( $db_prijs->midweekaanbieding > 0 ) {
						$form->setValue( 'midweekaanbieding_' . $periode->id, $db_prijs->midweekaanbieding );
					}
					if ( $db_prijs->weekendprijs != 0 ) {
						$form->setValue( 'weekendprijs_' . $periode->id, $db_prijs->weekendprijs );
					}
					if ( $db_prijs->weekendaanbieding > 0 ) {
						$form->setValue( 'weekendaanbieding_' . $periode->id, $db_prijs->weekendaanbieding );
					}
					if ( $db_prijs->weekprijs != 0 ) {
						$form->setValue( 'weekprijs_' . $periode->id, $db_prijs->weekprijs );
					}
					if ( $db_prijs->weekaanbieding != 0 ) {
						$form->setValue( 'weekaanbieding_' . $periode->id, $db_prijs->weekaanbieding );
					}
					if ( $db_prijs->periodeprijs != 0 ) {
						$form->setValue( 'periodeprijs_' . $periode->id, $db_prijs->periodeprijs );
					}
					if ( $db_prijs->periodeaanbieding != 0 ) {
						$form->setValue( 'periodeaanbieding_' . $periode->id, $db_prijs->periodeaanbieding );
					}
					if ( $db_prijs->meerprijs_volw != 0 ) {
						$form->setValue( 'meerprijs_volw_' . $periode->id, $db_prijs->meerprijs_volw );
					}
					if ( $db_prijs->meerprijs_kind != 0 ) {
						$form->setValue( 'meerprijs_kind_' . $periode->id, $db_prijs->meerprijs_kind );
					}
					if ( $db_prijs->inclusief != 0 ) {
						$form->setValue( 'inclusief_' . $periode->id, $db_prijs->inclusief );
					}
					$form->setValue( 'inclusief_toeslagen_' . $periode->id, $db_prijs->inclusief_toeslagen );
				}
			}
			$form->addHTML( '</tbody></table></div></div>' );
			$form->setMask( '%field% ' );
			$form->cancelButton( 'Terug', null, null, 'class="button"' );
			$form->submitButton( 'Opslaan', 'Opslaan', 'class="button-primary" onclick="priceJson()"', false );

			break;

		case 'toeslagen':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Naam toeslag"' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox margintop">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );
			if ( $this->camping_rights ) {
				$form->hiddenField( 'camping_id' );
				if ( isset( $_GET['id'] ) ) {
					if ( $form->getValue( 'camping_id' ) != $this->camping_rights ) {
						die( 'Geen rechten' );
					} else {
						$form->setValue( 'camping_id', $this->camping_rights );
					}
				} else {
					$form->setValue( 'camping_id', $this->camping_rights );
				}
			} else {
				$form->dbSelectField( 'Camping', 'camping_id', SIMPEL_DB_PREFIX . 'camping', array( 'id', 'title' ), 'order by title' );
			}
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = $this->wpdb->get_results( 'select * from ' . $this->wpdb->prefix . 'icl_languages where active = "1"' );
				foreach ( $languages as $lang ) {
					$form->textField( 'Titel ' . $lang->code, 'title_' . $lang->code, FH_NOT_EMPTY );
				}
			}

			if ( isset( $_GET['id'] ) ) {
				$upload_dir = wp_upload_dir();
				$form->addHTML( '<tr><td colspan="3"><div id="afbeelding-div">' . ( $form->getValue( 'afbeelding' ) ? '<img src="' . $upload_dir['baseurl'] . '/toeslagen/' . $_GET['id'] . '/_thumb_' . $form->getValue( 'afbeelding' ) . '" alt=""/>' : '' ) . '</div></td></tr>' );
				$form->uploadField( "Afbeelding", 'afbeelding' );
			}

			$form->textArea( 'Omschrijving', 'omschrijving', null, 50, 3 );
			$form->checkbox( 'Verplicht', 'verplicht', 1 );
			$form->checkbox( 'Borgsom', 'borgsom', 1 );
			$form->checkbox( 'Ter plaatse betalen', 'ter_plaatse_betalen', 1 );
			$form->checkbox( 'Hoort bij arrangement', 'arrangement', 1 );
			$form->checkbox( 'Voorkeursplaats', 'voorkeursplaats', 1 );
			$form->selectField( 'Type', 'type', array( 'ja/nee', 'aantal' ), null, false );
			$form->selectField( 'Prijs per', 'per', array( 'nacht', 'persoon', 'verblijf', 'p.persoon p.nacht', 'aantal' ), null, false );
			$form->textField( 'Algemene prijs camping', 'prijs_camping', _FH_FLOAT );
			$form->textField( 'Percentage van prijs accommodatie', 'percentage', _FH_FLOAT );
			$form->textField( 'Maximum', 'max', _FH_INTEGER );
			$form->textField( 'Volgorde', 'seq', _FH_INTEGER );

			if ( isset( $_GET['id'] ) ) {
				// prijzen per accommodatie
				$form->onCorrect( array( &$this, 'save_toeslagen' ) );
				$form->addHTML( '</table></div></div>
					<div class="postbox margintop">
						<h3 class="hndl"><span>Prijzen per accommodatie</span></h3>
						<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
				$accommodaties = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where camping_id = "%d" order by title', $form->getValue( 'camping_id' ) ) );
				foreach ( $accommodaties as $acco ) {
					$form->textField( 'Prijs ' . $acco->title, 'prijs_' . $acco->id, _FH_FLOAT );
					$prijs = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'toeslagen_per where toeslag_id = "%d" and accommodatie_id = "%d"',
						$_GET['id'], $acco->id ) );
					if ( count( $prijs ) ) {
						$form->setValue( 'prijs_' . $acco->id, $prijs->prijs );
					}
				}


				// geldig per arrangement
				$form->addHTML( '</table></div></div>
					<div class="postbox margintop">
						<h3 class="hndl"><span>Alleen geldig voor de volgende arrangementen (leeg laten als deze altijd geldig is)</span></h3>
						<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
				$arrangementen  = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen where camping_id = "%d" order by title', $form->getValue( 'camping_id' ) ) );
				$checkbox_value = array();
				$title          = 'title';
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$title = 'title_' . ICL_LANGUAGE_CODE;
				}
				foreach ( $arrangementen as $arr ) {
					$checkbox_value[ $arr->id ] = utf8_decode( $arr->$title );
				}
				$form->checkbox( 'Geldig voor de volgende arrangementen', 'arrangementen', $checkbox_value );

				// geldigheid toeslagen per periode
				$form->addHTML( '</table></div></div>
					<div class="postbox margintop">
						<h3 class="hndl"><span>Geldigheid toeslagen</span></h3>
						<div class="inside">
						<p>Alleen invullen als deze toeslag geldig is in specifieke periodes. Als er niks wordt ingevuld is deze altijd geldig.</p>
						<table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
				$acco_types       = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'acco_type' );
				$acco_type_values = array();
				foreach ( $acco_types as $type ) {
					$acco_type_values[ $type->id ] = $type->title;
				}

				$periodes       = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'periode where tot > now() order by acco_type_id, van, tot' );
				$checkbox_value = array();
				foreach ( $periodes as $periode ) {
					if ( ! isset( $acco_type_values[ $periode->acco_type_id ] ) ) {
						continue;
					}
					$checkbox_value[ $periode->id ] = '&nbsp;' . $acco_type_values[ $periode->acco_type_id ] . ' ' . date( 'd-m-Y', strtotime( $periode->van ) ) . ' / ' . date( 'd-m-Y', strtotime( $periode->tot ) ) . ' ' . $periode->naam;
				}

				$form->checkbox( 'Geldig in de volgende periodes', 'periodes', $checkbox_value );
			}
			$form->addHTML( '</table></div></div></div>' );
			break;

		case 'faciliteiten':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox margintop">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = $this->wpdb->get_results( 'select * from ' . $this->wpdb->prefix . 'icl_languages where active = "1" and code != "nl"' );
				foreach ( $languages as $lang ) {
					$form->textField( 'Titel ' . $lang->code, 'title_' . $lang->code, FH_NOT_EMPTY );
				}
			}

			$form->checkBox( 'Is filter?', 'is_filter', 1 );
			$form->radioButton( 'Hoort bij', 'is_camping', array( 'accommodatie', 'camping' ), FH_NOT_EMPTY, true );

			$form->hiddenField( 'icon' );
			$form->addHTML( '<tr><td style="width:100px" valign="top">Icon</td><td>' );

			if ( $form->getValue( 'icon' ) ) {
				$form->addHTML( '<div style="float:left"><strong>Geselecteerd: </strong> </div><span class="ss-icon facil-icon selected">&' . $form->getValue( 'icon' ) . '</span><div class="clear"></div>' );
			}
			foreach ( $this->icons as $icon ) {
				$val = substr( $icon, 1 );
				$form->addHTML( '<span class="facil-icon ss-icon ' . ( $val == $form->getValue( 'icon' ) ? 'selected' : '' ) . '" data-value="' . $val . '">' . $icon . '</span> ' );
			}
			$form->addHTML( '</td></tr>' );


			$form->addHTML( '</table></div></div></div>' );
			break;

		case 'settings':
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox">
					<h3 class="hndl"><span>Instellingen Simpel Reserveren</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );

			$fields = $this->wpdb->get_results( 'select * from ' . $db_table );
			foreach ( $fields as $field ) {
				if ( isset( $field->multilang ) && $field->multilang && defined( 'ICL_LANGUAGE_CODE' ) ) {
					$languages = icl_get_languages( 'skip_missing=N' );
					foreach ( $languages as $lang ) {
						$form->textField( $field->title . ' ' . strtoupper( $lang['language_code'] ), $field->field . '_' . $lang['language_code'], null, 80 );
						$value_field = 'value_' . $lang['language_code'];
						$form->setValue( $field->field . '_' . $lang['language_code'], $field->$value_field );
					}
				} elseif ( $field->input_type == 'select' ) {
					$form->SelectField( $field->title, $field->field, explode( ',', $field->input_value ) );
					$form->setValue( $field->field, $field->value );
				} elseif ( $field->input_type == 'checkbox' ) {
					$form->checkBox( $field->title, $field->field, 1 );
					$form->setValue( $field->field, $field->value );
				} elseif ( $field->input_type == 'textarea' ) {
					$form->textArea( $field->title, $field->field );
					$form->setValue( $field->field, $field->value );
				} else {
					$form->textField( $field->title, $field->field, null, 80 );
					$form->setValue( $field->field, $field->value );
				}

				if ( $field->field == 'conversie-codes' ) {
					$form->addHTML( '<tr><td>De volgende codes kunnen worden gebruikt</td><td>[boek_id] [accommodatie_bedrag] [totaal_bedrag] [accommodatie_titel]</td></tr>' );
				}
			}
			$form->addHTML( '</table></div></div></div>' );
			break;

		case 'meldingen':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Melding"' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox margintop">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = icl_get_languages( 'skip_missing=N' );
				foreach ( $languages as $lang ) {
					if ( $lang['language_code'] == 'nl' ) {
						continue;
					}
					$form->textField( 'Titel ' . strtoupper( $lang['language_code'] ), 'title_' . $lang['language_code'], null, 80 );
				}
			}
			$form->jsDateField( 'Vanaf', 'van', null, true, 'd-m-y', '10:10' );
			$form->jsDateField( 'Tot', 'tot', null, true, 'd-m-y', '10:10' );
			$form->selectField( 'Type', 'type', array( 'alert-success' => 'groen', 'alert-info' => 'blauw', 'alert-waring' => 'oranje', 'alert-danger' => 'rood' ) );
			$form->checkbox( 'Waar moet deze zichtbaar zijn?', 'plaats', array( 'zoeken', 'accommodatie', 'boeken' ), null, false );

			$form->addHTML( '</table></div></div></div>' );
			break;

		case 'acco_type':
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
			$form->addHTML( '</div>' );
			$form->addHTML( '
				<div class="postbox margintop">
					<h3 class="hndl"><span>Detail opties</span></h3>
					<div class="inside"><table class="form-table" style="width:100%" cellspacing="2" cellpadding="5">' );
			$form->setMask( '<tr><th><label for="%name%">%title%</label></th><td>%field% %error% %help%</td></tr>' );
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = icl_get_languages( 'skip_missing=N' );
				foreach ( $languages as $lang ) {
					if ( $lang['language_code'] == 'nl' ) {
						continue;
					}
					$form->textField( 'Titel ' . strtoupper( $lang['language_code'] ), 'title_' . $lang['language_code'], null, 80 );
				}
			}
			$form->addHTML( '</table></div></div></div>' );

			break;

		default:
			$form->textField( 'Naam', 'title', FH_NOT_EMPTY, 30, null, 'placeholder="Titel"' );
			$form->addHTML( '</div></div>' );
	}

	$form->onSaved( array( &$this, 'form_saved' ) );
	$form->flush();

	if ( $table == 'accommodatie' || ( $table == 'arrangementen' && $_GET['act'] != 'periodes' ) || ( $table == 'camping' && SIMPEL_MULTIPLE ) ) :
		?>
		<div id="normal-sortables" class="meta-box-sortables ui-sortable margintop">
			<div class="postbox">
				<h3 class="hndl"><span>Foto's</span></h3>

				<div class="inside">
					<button id='fotos_button' class="button uploader multiple">Upload foto's</button>
					<div id="foto-list">
						<?php

						if ( $table == 'accommodatie' ) {
							$afbeeldingen = $accommodatie->afbeeldingen;
							$thumbs       = $accommodatie->thumbs;
							$img_ids      = $accommodatie->img_ids;
						} elseif ( $table == 'arrangementen' ) {
							$afbeeldingen = $arrangement->afbeeldingen;
							$thumbs       = $arrangement->thumbs;
							$img_ids      = $arrangement->img_ids;
						} else {
							$afbeeldingen = $camping->afbeeldingen;
							$thumbs       = $camping->thumbs;
							$img_ids      = $camping->img_ids;
						}
						$value = ',' . implode( ',', $img_ids ) . ',';
						if ( isset( $_POST['fotos'] ) ) {
							$value = $_POST['fotos'];
						}


						foreach ( $thumbs as $i => $thumb ) : ?>
							<div class="foto">
								<img src="<?= $thumb ?>" data-id="<?= $img_ids[ $i ] ?>" alt="" title="klik om te verwijderen" width="150"/>
							</div>
						<?php endforeach; ?>
					</div>
					<input type="hidden" id="fotos" name="fotos" value="<?= $value ?>"/>

					<div class="clear"></div>
				</div>
			</div>
		</div>
	<?php endif; ?>
	</div>
<?php
else :
	switch ( $table ) {
		case 'accommodatie':
		case 'beschikbaarheid':
		case 'prijs':
		case 'available':
			$sql    = 'select a.*, c.title as camping, t.title as type from ' . SIMPEL_DB_PREFIX . 'accommodatie a
				inner join ' . SIMPEL_DB_PREFIX . 'camping c on a.camping_id = c.id
				inner join ' . SIMPEL_DB_PREFIX . 'acco_type t on a.acco_type_id = t.id
				' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
			' . ( $table == 'accommodatie' ? 'order by c.title, seq_inner, t.title, a.title' : 'order by c.title, t.title, a.title' );
			$fields = array( 'title', 'title_intern', 'camping', 'type', 'datum' );
			$titles = array( 'Naam', 'Interne naam', 'Camping', 'Type', 'Laatst gewijzigd' );
			if ( $this->camping_rights ) {
				unset( $fields[2] );
				unset( $titles[2] );
			}
			break;

		case 'periode':
			$sql    = 'select p.*, c.title as camping, t.title as type from ' . SIMPEL_DB_PREFIX . 'periode p
				inner join ' . SIMPEL_DB_PREFIX . 'camping c on p.camping_id = c.id
				inner join ' . SIMPEL_DB_PREFIX . 'acco_type t on p.acco_type_id = t.id
			where p.tot >= now() ' . ( $this->camping_rights ? ' and c.id = "' . $this->camping_rights . '"' : '' ) . '
			order by c.title, t.title, p.van';
			$fields = array( 'camping', 'type', 'van', 'tot', 'naam' );
			$titles = array( 'Camping', 'Type', 'Periode vanaf', 'Periode tot', 'Naam periode' );
			if ( $this->camping_rights ) {
				unset( $fields[0] );
				unset( $titles[0] );
			}
			break;

		case 'toeslagen':
			$sql    = 'select t.*, c.title as camping from ' . SIMPEL_DB_PREFIX . 'toeslagen t
				inner join ' . SIMPEL_DB_PREFIX . 'camping c on t.camping_id = c.id
			' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
			order by c.title, t.seq, t.title';
			$fields = array( 'camping', 'title' );
			$titles = array( 'Camping', 'Naam' );

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = $this->wpdb->get_results( 'select * from ' . $this->wpdb->prefix . 'icl_languages where active = "1" and code != "nl"' );
				foreach ( $languages as $lang ) {
					$fields[] = 'title_' . $lang->code;
					$titles[] = 'Naam ' . strtoupper( $lang->code );
				}
			}

			$fields = array_merge( $fields, array( 'type', 'per', 'seq' ) );
			$titles = array_merge( $titles, array( 'Type', 'Prijs per', 'Volgorde' ) );
			if ( $this->camping_rights ) {
				unset( $fields[0] );
				unset( $titles[0] );
			}
			break;

		case 'faciliteiten':
			$sql    = 'select * from ' . SIMPEL_DB_PREFIX . 'faciliteiten order by title';
			$fields = array( 'title' );
			$titles = array( 'Faciliteit' );
			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				$languages = $this->wpdb->get_results( 'select * from ' . $this->wpdb->prefix . 'icl_languages where active = "1" and code != "nl"' );
				foreach ( $languages as $lang ) {
					$fields[] = 'title_' . $lang->code;
					$titles[] = 'Faciliteit ' . strtoupper( $lang->code );
				}
			}

			$titles[] = 'Is camping';
			$titles[] = 'Is filter';

			$fields[] = 'is_camping';
			$fields[] = 'is_filter';
			break;

		case 'camping':
			$sql = 'select * from ' . $db_table;
			if ( $this->camping_rights ) {
				$sql .= ' where id = "' . $this->camping_rights . '"';
			}
			$sql .= ' order by title';
			$fields = array( 'title', 'datum' );
			$titles = array( 'Naam', 'Laatst gewijzigd' );
			break;

		case 'boeking':
			$sql    = 'select b.*, c.title as camping, a.title as accommodatie from ' . SIMPEL_DB_PREFIX . 'boeking b
				inner join ' . SIMPEL_DB_PREFIX . 'camping c on b.camping_id = c.id
				inner join ' . SIMPEL_DB_PREFIX . 'accommodatie a on b.accommodatie_id = a.id
				' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
				order by datum_boeking desc';
			$fields = array( 'camping', 'accommodatie', 'naam', 'datum_boeking', 'prijs', 'datum_aankomst' );
			$titles = array( 'Camping', 'Accommodatie', 'Naam', 'Geboekt', 'Prijs', 'Aankomst' );
			if ( $this->camping_rights ) {
				unset( $fields[0] );
				unset( $titles[0] );
			}
			break;

		case 'aanbiedingen':
			if ( isset( $_GET['act'] ) && $_GET['act'] == 'periodes' ) {
				$sql    = 'select a.*, c.title as camping, t.title as type from ' . SIMPEL_DB_PREFIX . 'accommodatie a
					inner join ' . SIMPEL_DB_PREFIX . 'camping c on a.camping_id = c.id
					inner join ' . SIMPEL_DB_PREFIX . 'acco_type t on a.acco_type_id = t.id
					' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
				' . ( $table == 'accommodatie' ? 'order by c.title, seq_inner, t.title, a.title' : 'order by c.title, t.title, a.title' );
				$fields = array( 'title', 'camping', 'type', 'datum' );
				$titles = array( 'Naam', 'Camping', 'Type', 'Laatst gewijzigd' );
				if ( $this->camping_rights ) {
					unset( $fields[1] );
					unset( $titles[1] );
				}
			} else {
				$sql    = 'select a.*, a.title as ab, c.title as camping from ' . $db_table . ' a
					inner join ' . SIMPEL_DB_PREFIX . 'camping c on a.camping_id = c.id
					' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . ' order by a.title';
				$fields = array( 'ab', 'camping', 'min_nachten', 'datum' );
				$titles = array( 'Naam', 'Camping', 'Minimum aantal nachten', 'Laatst gewijzigd' );
				if ( $this->camping_rights ) {
					unset( $fields[1] );
					unset( $titles[1] );
				}
			}
			break;

		case 'arrangementen':
			if ( isset( $_GET['act'] ) && $_GET['act'] == 'periodes' ) {
				$sql    = 'select a.*, c.title as camping, t.title as type from ' . SIMPEL_DB_PREFIX . 'accommodatie a
					inner join ' . SIMPEL_DB_PREFIX . 'camping c on a.camping_id = c.id
					inner join ' . SIMPEL_DB_PREFIX . 'acco_type t on a.acco_type_id = t.id
					' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
				' . ( $table == 'accommodatie' ? 'order by c.title, seq_inner, t.title, a.title' : 'order by c.title, t.title, a.title' );
				$fields = array( 'title', 'camping', 'type', 'datum' );
				$titles = array( 'Naam', 'Camping', 'Type', 'Laatst gewijzigd' );
				if ( $this->camping_rights ) {
					unset( $fields[1] );
					unset( $titles[1] );
				}
			} else {
				$sql    = 'select a.*, a.title as ab, c.title as camping from ' . $db_table . ' a
					inner join ' . SIMPEL_DB_PREFIX . 'camping c on a.camping_id = c.id
					' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . ' order by a.title';
				$fields = array( 'title_nl', 'camping', 'prijs' );
				$titles = array( 'Naam', 'Camping', 'Prijs' );
				if ( $this->camping_rights ) {
					unset( $fields[1] );
					unset( $titles[1] );
				}
			}
			break;

		case 'yield':
			$sql    = 'select * from ' . $db_table . ' order by title';
			$fields = array( 'title', 'datum_van', 'datum_tot' );
			$titles = array( 'Naam', 'Geldig van', 'Geldig tot' );
			break;

		default:
			$sql    = 'select * from ' . $db_table . ' order by title';
			$fields = array( 'title', 'datum' );
			$titles = array( 'Naam', 'Laatst gewijzigd' );
	}
	$results = $this->wpdb->get_results( $sql );
	?>

	<div class="wrap">
		<h2><?= ucfirst( $title ) ?>
			<?php if ( ! in_array( $table, array( 'beschikbaarheid', 'boeking', 'aanbiedingen', 'arrangementen' ) ) ) { ?>
				<a href="<?= $_SERVER['REQUEST_URI'] ?>&act=new" class="add-new-h2">Nieuw</a>
			<?php } ?>
			<?php if ( in_array( $table, array( 'aanbiedingen', 'arrangementen' ) ) ) { ?>
				<a href="admin.php?page=<?= $_GET['page'] ?>&act=new" class="add-new-h2">Nieuw</a>
				<a href="admin.php?page=<?= $_GET['page'] ?>" class="add-new-h2"><?= ucfirst( $table ) ?></a>
				<a href="<?= $_SERVER['REQUEST_URI'] ?>&act=periodes" class="add-new-h2">Periodes</a>
			<?php } ?>
			<?php if ( in_array( $table, array( 'periode' ) ) ) : ?>
				<a href="<?= $_SERVER['REQUEST_URI'] ?>&amp;act=new&amp;copy=1" class="add-new-h2">Periodes kopiëren</a>
				<a href="<?= $_SERVER['REQUEST_URI'] ?>&amp;act=new&amp;bulk=1" class="add-new-h2">Bulk periodes aanmaken</a>
			<?php endif; ?>
		</h2>

		<?php if ( isset( $_SESSION['msg'] ) && ! empty( $_SESSION['msg'] ) ) : ?>
			<div id="message" class="<?= $_SESSION['msg_type'] ?> below-h2">
				<p><?= $_SESSION['msg'] ?></p>
			</div>
			<?php unset( $_SESSION['msg'] ); ?>
		<?php endif; ?>

		<table class="wp-list-table widefat fixed pages" cellspacing="0">
			<thead>
			<tr>
				<?php foreach ( $titles as $col ) { ?>
					<th class="col" id="<?= $col ?>" class="manage-column"><?= $col ?></th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $results as $row ) { ?>
				<tr>

					<?php
					$i = 0;
					foreach ( $fields as $fld ) {
						$waarde = $row->$fld;

						if ( in_array( $table, array( 'faciliteiten', 'acco_type', 'periode', 'toeslagen', 'aanbiedingen', 'arrangementen', 'meldingen' ) ) ) {
							$waarde = utf8_decode( $waarde );
						}

						if ( substr( $waarde, 4, 1 ) == '-' && substr( $waarde, 7, 1 ) == '-' ) {
							$waarde = date( 'd-m-Y', strtotime( $waarde ) );
						}
						?>
						<?php if ( $i ++ == 0 ) { ?>
							<td><a href="?page=<?= $_GET['page'] ?>&id=<?= $row->id ?><?= ( isset( $_GET['act'] ) ? '&act=' . $_GET['act'] : '' ) ?>"><?= $waarde ?></a></td>
						<?php } else { ?>
							<td><?= $waarde ?></td>
						<?php } ?>
					<?php } ?>
				</tr>
			<?php } ?>
			<?php if ( ! count( $results ) ) { ?>
				<tr>
					<td colspan="<?= count( $titles ) ?>">Geen <?= $title ?> gevonden</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>