<?php

class Simpel_Reserveren_Admin {

	private $wpdb;
	private $sr;

	public $icons;

	function __construct( $sr ) {
		global $wpdb;
		$this->wpdb            = &$wpdb;
		$this->sr              = $sr;
		$this->prijsberekening = new Prijsberekening();
		$this->plugin_url      = SIMPEL_PLUGIN_URL;
	}

	function init() {
		global $icons;
		$this->icons = $icons;

		// INSTALL & UPDATE
		register_activation_hook( __FILE__, array( 'SimpelReserveren_Update', 'install' ) );

		// LANDINGSPAGE
		$landingpage = new SimpelReserveren_Landingpage( $this );
		add_action( 'admin_menu', array( &$landingpage, 'create_post_meta_box' ) );
		add_action( 'save_post', array( &$landingpage, 'save_post_meta_box' ), 10, 2 );
		add_filter( 'the_post', array( &$landingpage, 'add_content' ) );

		// CUSTOMIZER
		add_action( 'customize_register', array( 'SimpelReserveren_Customizer', 'register' ) );
		add_action( 'wp_head', array( 'SimpelReserveren_Customizer', 'header_output' ) );
		add_action( 'customize_preview_init', array( 'SimpelReserveren_Customizer', 'live_preview' ) );

		// ADMIN PAGES
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), - 10 );
		add_action( 'admin_menu', array( &$this, 'admin_menu' ) );


		// ADMIN AJAX
		add_action( 'wp_dashboard_setup', array( &$this, 'admin_add_dashboard_widgets' ) );
		add_action( 'wp_ajax_getTableData', array( &$this, 'get_table_data' ) );
		add_action( 'wp_ajax_set_beschikbaarheid', array( &$this, 'set_beschikbaarheid' ) );
		add_action( 'wp_ajax_update_cal', array( &$this, 'update_cal' ) );
		add_action( 'wp_ajax_nopriv_update_cal', array( &$this, 'update_cal' ) );
		add_action( 'wp_ajax_get_beschikbaarheid', array( &$this, 'get_beschikbaarheid' ) );
		add_action( 'wp_ajax_nopriv_get_beschikbaarheid', array( &$this, 'get_beschikbaarheid' ) );
		add_action( 'wp_ajax_delete_foto', array( &$this, 'delete_foto' ) );

	}


	function admin_init() {
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! isset( $_SESSION['boeken'] ) ) {
			$_SESSION['boeken'] = array( 'aankomst' => '', 'vertrek' => '' );
		}
		if ( isset( $_POST['act'] ) && $_POST['act'] == 'export-boeking' ) {
			$this->_export_boeking();
		}
	}

	function admin_enqueue_scripts() {
		if ( isset( $_GET['page'] ) && substr( $_GET['page'], 0, 6 ) == 'admin-' ) {
			echo '<link rel="stylesheet" id="bootstrap-css" href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css"/>' . "\n";

			wp_enqueue_script( 'bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js' );

			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-css', 'http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css' );
			wp_enqueue_style( 'simpelreserveren-bootstrap-css', $this->plugin_url . 'css/simpel-reserveren-bootstrap.css' );
			wp_enqueue_style( 'simpelreserveren-css', $this->plugin_url . 'css/admin.css' );
			wp_enqueue_script( 'jquery-dataTables', $this->plugin_url . 'js/jquery.dataTables.min.js', array( 'jquery' ), '1.10.0' );
			wp_enqueue_script( 'simpelreserveren-js', $this->plugin_url . 'js/admin.js?v=' . $this->sr->version, array( 'jquery-ui-tabs' ), $this->sr->version );
		}
	}

	function admin_menu() {
		// kijk tot welke campings de gebruiker rechten heeft (in te stellen in de gebruikers pagina)
		$current_user = wp_get_current_user();
		if ( ! SIMPEL_MULTIPLE ) {
			$this->camping_rights = 1; // alleen toegang tot de eerste camping
		} else {
			$this->camping_rights = get_the_author_meta( 'camping', $current_user->ID );
		}

		if ( is_user_logged_in() ) {
			add_menu_page( 'Simpel Reserveren', 'Simpel Reserveren', 'edit_pages', 'admin-booking' );
			add_submenu_page( 'admin-booking', 'Boekingen', 'Boekingen', 'edit_pages', 'admin-booking', array( &$this, 'admin_bookings' ) );
			if ( $this->camping_rights ) {
				add_submenu_page( 'admin-booking', _sr( 'Camping' ), _sr( 'Camping' ), 'edit_pages', 'admin-booking-camping', array( &$this, 'admin_camping' ) );
			} else {
				add_submenu_page( 'admin-booking', _sr( 'Campings' ), _sr( 'Campings' ), 'edit_pages', 'admin-booking-camping', array( &$this, 'admin_camping' ) );
			}

			if ( ! SIMPEL_MULTIPLE || is_super_admin() ) {
				add_submenu_page( 'admin-booking', 'Instellingen', 'Instellingen', 'edit_pages', 'admin-settings', array( &$this, 'admin_settings' ) );
				add_submenu_page( 'admin-booking', 'Types', 'Types', 'edit_pages', 'admin-booking-types', array( &$this, 'admin_accommodatie_types' ) );
				add_submenu_page( 'admin-booking', 'Faciliteiten', 'Faciliteiten', 'edit_pages', 'admin-booking-faciliteiten', array( &$this, 'admin_faciliteiten' ) );
			}
			add_submenu_page( 'admin-booking', _sr( 'Accommodaties' ), _sr( 'Accommodaties' ), 'edit_pages', 'admin-booking-accommodaties', array( &$this, 'admin_accommodaties' ) );
			if ( defined( 'SIMPEL_AANTALLEN' ) && SIMPEL_AANTALLEN ) {
				add_submenu_page( 'admin-booking', 'Aantallen', 'Aantallen', 'edit_pages', 'admin-booking-aantallen', array( &$this, 'admin_aantallen' ) );
			} else {
				add_submenu_page( 'admin-booking', 'Beschikbaarheid', 'Beschikbaarheid', 'edit_pages', 'admin-booking-beschikbaarheid', array( &$this, 'admin_beschikbaarheid' ) );
			}
			add_submenu_page( 'admin-booking', 'Periodes', 'Periodes', 'edit_pages', 'admin-booking-periodes', array( &$this, 'admin_periodes' ) );
			add_submenu_page( 'admin-booking', 'Prijzen', 'Prijzen', 'edit_pages', 'admin-booking-prijzen', array( &$this, 'admin_prijzen' ) );
			add_submenu_page( 'admin-booking', 'Toeslagen', 'Toeslagen', 'edit_pages', 'admin-booking-toeslagen', array( &$this, 'admin_toeslagen' ) );
			add_submenu_page( 'admin-booking', 'Aanbiedingen', 'Aanbiedingen', 'edit_pages', 'admin-booking-aanbiedingen', array( &$this, 'admin_aanbiedingen' ) );
			add_submenu_page( 'admin-booking', 'Arrangementen', 'Arrangementen', 'edit_pages', 'admin-booking-arrangementen', array( &$this, 'admin_arrangementen' ) );
			add_submenu_page( 'admin-booking', 'Meldingen', 'Meldingen', 'edit_pages', 'admin-booking-meldingen', array( &$this, 'admin_meldingen' ) );
			//add_submenu_page('admin-booking', 'Yield management', 'Yield management', 'administrator', 'admin-booking-yield', array(&$this, 'admin_yield'));
		}

	}

	function admin_settings() {
		if ( isset( $_GET['act'] ) ) {
			switch ( $_GET['act'] ) {
				case 'update_database':
					SimpelReserveren_Update::update();
					$msg = 'Database updated';
					break;
				case 'update_images':
					SimpelReserveren_Update::update_images();
					$msg = 'Afbeeldingen updated';
					break;
				case 'update_links':
					SimpelReserveren_Update::update_slugs();
					$msg = 'Links updated';
					break;
			}
		}
		if ( isset( $msg ) ) {
			$_SESSION['msg']      = $msg;
			$_SESSION['msg_type'] = 'updated';
		}
		$table = $this->table = 'settings';
		$title = 'instellingen';
		include( 'list_detail.php' );
	}

	function admin_bookings() {
		$table = $this->table = 'boeking';
		$title = 'boeking';
		include( 'ajax_list_detail.php' );
	}

	function admin_accommodaties() {
		if ( isset( $_GET['act'] ) && $_GET['act'] == 'copy' ) {
			$this->copy_accommodatie( $_GET['id'] );
		}
		$table        = $this->table = 'accommodatie';
		$accommodatie = new Accommodatie( isset( $_GET['id'] ) ? $_GET['id'] : null );
		$title        = _sr( 'accommodatie' );
		include( 'list_detail.php' );
	}

	function admin_accommodatie_types() {
		$table = 'acco_type';
		$title = 'accommodatie type';
		include( 'list_detail.php' );
	}

	function admin_camping() {
		wp_enqueue_media();
		if ( $this->camping_rights ) {
			$_GET['id'] = $this->camping_rights;
		}
		$camping = new Camping( isset( $_GET['id'] ) ? $_GET['id'] : null );
		$table   = 'camping';
		$title   = _sr( 'camping' );
		include( 'list_detail.php' );
	}

	function admin_beschikbaarheid() {
		$table = 'beschikbaarheid';
		$title = 'beschikbaarheid';
		include( 'list_detail.php' );
	}

	function admin_periodes() {
		$table = 'periode';
		$title = 'periode';
		include( 'list_detail.php' );
	}

	function admin_prijzen() {
		$table = 'prijs';
		$title = 'prijs';
		include( 'list_detail.php' );
	}

	function admin_toeslagen() {
		$table = 'toeslagen';
		$title = 'toeslagen';
		include( 'list_detail.php' );
	}

	function admin_faciliteiten() {
		$table = 'faciliteiten';
		$title = 'faciliteiten';
		include( 'list_detail.php' );
	}

	function admin_aanbiedingen() {
		$table = 'aanbiedingen';
		$title = 'aanbiedingen';
		include( 'list_detail.php' );
	}

	function admin_arrangementen() {
		$arrangement = new Arrangement( isset( $_GET['id'] ) ? $_GET['id'] : null );
		$table       = 'arrangementen';
		$title       = 'arrangementen';
		include( 'list_detail.php' );
	}

	function admin_yield() {
		$table = 'yield';
		$title = 'yield management';
		include( 'list_detail.php' );
	}

	function admin_aantallen() {
		$table = 'available';
		$title = 'aantallen';
		include( 'list_detail.php' );
	}

	function admin_meldingen() {
		$table = 'meldingen';
		$title = 'meldingen';
		include( 'list_detail.php' );
	}

	function admin_html_page() {
		?>
		<div>
			<h2>Accommodaties</h2>

			<form method="post" action="options.php">
				<?php wp_nonce_field( 'update-options' ); ?>

				<table>
					<tr valign="top">
						<td scope="row">Arrangement homepage big
						</th>
						<td><select name="homepage_arrangement_big" id="homepage_arrangement_big">
								<?php
								$args  = array(
									"sort_column" => "post_title",
									"numberposts" => 1000
								);
								$pages = get_pages( $args );
								foreach ( $pages as $i => $post ) {
									$id_en_language = icl_object_id( $post->ID, 'page', false, 'en' );
									//if($id_en_language != $post->ID) continue; // other then english language
									echo "<option value='" . $post->ID . "' " . ( get_option( 'homepage_arrangement_big' ) == $post->ID ? "selected" : "" ) . ">" . $post->post_title . "</option>";
								}
								?>
							</select></td>
					</tr>
					<tr>
						<td colspan="2"><br/>Tours:</td>
					</tr>
					<tr valign="top">
						<td scope="row">Arrangement homepage small (1)
						</th>
						<td><select name="homepage_arrangement_small1" id="homepage_arrangement_small1">
								<?php
								$args  = array(
									"orderby"     => "title",
									"order"       => "ASC",
									"numberposts" => 1000
								);
								$posts = get_posts( $args );
								foreach ( $posts as $i => $post ) {
									$id_en_language = icl_object_id( $post->ID, 'post', false, 'en' );
									if ( $id_en_language != $post->ID ) {
										continue;
									} // other then english language
									echo "<option value='" . $post->ID . "' " . ( get_option( 'homepage_arrangement_small1' ) == $post->ID ? "selected" : "" ) . ">" . $post->post_title . "</option>";
								}
								?>
							</select>
					</tr>
					<tr valign="top">
						<td scope="row">Arrangement homepage small (2)
						</th>
						<td><select name="homepage_arrangement_small2" id="homepage_arrangement_small2">
								<?php
								foreach ( $posts as $i => $post ) {
									$id_en_language = icl_object_id( $post->ID, 'post', false, 'en' );
									if ( $id_en_language != $post->ID ) {
										continue;
									} // other then english language
									echo "<option value='" . $post->ID . "' " . ( get_option( 'homepage_arrangement_small2' ) == $post->ID ? "selected" : "" ) . ">" . $post->post_title . "</option>";
								}
								?>
							</select>
					</tr>
				</table>

				<input type="hidden" name="action" value="update"/>
				<input type="hidden" name="page_options" value="homepage_arrangement_big,homepage_arrangement_small1,homepage_arrangement_small2"/>

				<p>
					<input type="submit" value="<?php _e( 'Save Changes' ) ?>"/>
				</p>

			</form>
		</div>
	<?php
	}

	function form_saved( $id, $data, &$form ) {
		if ( $this->table == 'accommodatie' ) {
			$acco       = new Accommodatie( $id );
			$fields     = array( 'title' => stripslashes( $_POST['title'] ) );
			$add_fields = array( 'samenvatting', 'omschrijving' );
			foreach ( $add_fields as $fld ) {
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$fields[ $fld . '_' . ICL_LANGUAGE_CODE ] = stripslashes( $_POST[ $fld . '_' . ICL_LANGUAGE_CODE ] );
				} else {
					$fields[ $fld ] = stripslashes( $_POST[ $fld ] );
				}
			}
			if ( ! $acco->name ) {
				SimpelReserveren_Update::set_accommodatie_slug( $id );
			}
			$this->wpdb->update( SIMPEL_DB_PREFIX . $this->table, $fields, array( 'id' => $id ) );

			if ( isset( $_POST['fotos'] ) ) {
				$fotos = explode( ',', $_POST['fotos'] );
				$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'accommodatie_foto where accommodatie_id = "%d"', $id ) );
				foreach ( $fotos as $foto_id ) {
					if ( empty( $foto_id ) ) {
						continue;
					}
					$fields = array(
						'accommodatie_id' => $id,
						'foto_id'         => $foto_id,
						'datum'           => date( 'Y-m-d G:i:s' )
					);
					$this->wpdb->insert( SIMPEL_DB_PREFIX . 'accommodatie_foto', $fields );
				}
			}
		} elseif ( $this->table == 'camping' ) {
			$camping = new Camping( $id );
			$fields  = array( 'txt_camping', 'txt_omgeving', 'email_header', 'email_footer', 'confirm_tekst', 'booking_footer' );
			$values  = array();
			foreach ( $fields as $fld ) {
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$values[ $fld . '_' . ICL_LANGUAGE_CODE ] = stripslashes( $_POST[ $fld . '_' . ICL_LANGUAGE_CODE ] );
				} else {
					$values[ $fld ] = stripslashes( $_POST[ $fld ] );
				}
			}
			if ( ! $camping->name ) {
				SimpelReserveren_Update::set_camping_slug( $id );
			}
			$this->wpdb->update( SIMPEL_DB_PREFIX . $this->table, $values, array( 'id' => $id ) );

			if ( isset( $_POST['fotos'] ) ) {
				$fotos = explode( ',', $_POST['fotos'] );
				$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'camping_foto where camping_id = "%d"', $id ) );
				foreach ( $fotos as $foto_id ) {
					if ( empty( $foto_id ) ) {
						continue;
					}
					$fields = array(
						'camping_id' => $id,
						'foto_id'    => $foto_id,
						'datum'      => date( 'Y-m-d G:i:s' )
					);
					$this->wpdb->insert( SIMPEL_DB_PREFIX . 'camping_foto', $fields );
				}
			}
		} elseif ( $this->table == 'arrangementen' ) {
			$arrangement = new Arrangement( $id );

			$fields = array( 'overview', 'terms' );
			$values = array();
			foreach ( $fields as $fld ) {
				if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
					$values[ $fld . '_' . ICL_LANGUAGE_CODE ] = stripslashes( $_POST[ $fld . '_' . ICL_LANGUAGE_CODE ] );
				} else {
					$values[ $fld ] = stripslashes( $_POST[ $fld ] );
				}
			}
			$this->wpdb->update( SIMPEL_DB_PREFIX . $this->table, $values, array( 'id' => $id ) );

			if ( isset( $_POST['fotos'] ) ) {
				$fotos = explode( ',', $_POST['fotos'] );
				$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'arrangement_foto where arrangement_id = "%d"', $id ) );
				foreach ( $fotos as $foto_id ) {
					if ( empty( $foto_id ) ) {
						continue;
					}
					$fields = array(
						'arrangement_id' => $id,
						'foto_id'        => $foto_id,
						'datum'          => date( 'Y-m-d G:i:s' )
					);
					$this->wpdb->insert( SIMPEL_DB_PREFIX . 'arrangement_foto', $fields );
				}
			}

			// save accommodatie prijzen
			$accommodaties = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie' );
			foreach ( $accommodaties as $acco ) {
				if ( $data[ 'prijs_acco_' . $acco->id ] ) {
					$fields = array(
						'arrangement_id'  => $id,
						'accommodatie_id' => $acco->id,
						'prijs'           => $data[ 'prijs_acco_' . $acco->id ],
						'meerprijs_volw'  => $data[ 'meerprijs_volw_' . $acco->id ],
						'meerprijs_kind'  => $data[ 'meerprijs_kind_' . $acco->id ],
					);

					$row = $this->wpdb->get_row( $this->wpdb->prepare(
						'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen_prijzen where accommodatie_id = "%d" and arrangement_id = "%d"',
						$acco->id, $id ) );

					if ( $row ) {
						$this->wpdb->update( SIMPEL_DB_PREFIX . 'arrangementen_prijzen', $fields, array( 'id' => $row->id ) );
					} else {
						$this->wpdb->insert( SIMPEL_DB_PREFIX . 'arrangementen_prijzen', $fields );
					}
				} else {
					$this->wpdb->delete( SIMPEL_DB_PREFIX . 'arrangementen_prijzen', array( 'arrangement_id' => $id, 'accommodatie_id' => $acco->id ) );
				}
			}

		} elseif ( $this->table == 'settings' ) {
			$fields = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'settings' );
			foreach ( $fields as $field ) {
				if ( isset( $field->multilang ) && $field->multilang && defined( 'ICL_LANGUAGE_CODE' ) ) {
					$languages = icl_get_languages( 'skip_missing=N' );
					$db_fields = array();
					foreach ( $languages as $lang ) {
						$fieldname                                      = $field->field . '_' . $lang['language_code'];
						$db_fields[ 'value_' . $lang['language_code'] ] = $data[ $fieldname ];
					}
				} else {
					$value     = $data[ $field->field ];
					$db_fields = array(
						'value' . $this->db_suffix => stripslashes( $value )
					);
				}
				$this->wpdb->update( SIMPEL_DB_PREFIX . 'settings', $db_fields, array( 'id' => $field->id ), array( '%s' ) );
			}

		}
		if ( in_array( $this->table, array( 'accommodatie', 'camping' ) ) ) {
			$sql = 'delete from ' . SIMPEL_DB_PREFIX . 'faciliteiten_per where ' . ( $this->table == 'camping' ? 'camping_id' : 'accommodatie_id' ) . ' = "' . $id . '"';
			$this->wpdb->query( $sql );

			$sql          = 'select * from ' . SIMPEL_DB_PREFIX . 'faciliteiten';
			$faciliteiten = $this->wpdb->get_results( $sql );
			foreach ( $faciliteiten as $faciliteit ) {
				if ( $data[ 'faciliteit_' . $faciliteit->id ] ) {
					$sql = 'insert into ' . SIMPEL_DB_PREFIX . 'faciliteiten_per (' . ( $this->table == 'camping' ? 'camping_id' : 'accommodatie_id' ) . ', faciliteit_id) values("' . $id . '", "' . $faciliteit->id . '")';
					$this->wpdb->query( $sql );
				}
			}
		}
		echo '<script>document.location="admin.php?page=' . $_GET['page'] . '";</script>';
		exit;
	}

	function save_prices() {
		if ( ! is_numeric( $_GET['id'] ) ) {
			exit( 'geen geldig id' );
		}
		$prijs_table = SIMPEL_DB_PREFIX . 'prijs';
		$acco        = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
		$periodes    = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "%d" and acco_type_id = "%d" and tot >= now()', $acco->camping_id, $acco->acco_type_id ) );

		$json = (array) json_decode( stripslashes( $_POST['json'] ) );
		foreach ( $periodes as $periode ) {
			$fields = array(
				'periode_id'          => $periode->id,
				'accommodatie_id'     => $acco->id,
				'nachtprijs'          => (float) $json[ 'nachtprijs_' . $periode->id ],
				'nachtaanbieding'     => (float) $json[ 'nachtaanbieding_' . $periode->id ],
				'midweekprijs'        => (float) $json[ 'midweekprijs_' . $periode->id ],
				'midweekaanbieding'   => (float) $json[ 'midweekaanbieding_' . $periode->id ],
				'weekendprijs'        => (float) $json[ 'weekendprijs_' . $periode->id ],
				'weekendaanbieding'   => (float) $json[ 'weekendaanbieding_' . $periode->id ],
				'weekprijs'           => (float) $json[ 'weekprijs_' . $periode->id ],
				'weekaanbieding'      => (float) $json[ 'weekaanbieding_' . $periode->id ],
				'periodeprijs'        => (float) $json[ 'periodeprijs_' . $periode->id ],
				'periodeaanbieding'   => (float) $json[ 'periodeaanbieding_' . $periode->id ],
				'meerprijs_volw'      => (float) $json[ 'meerprijs_volw_' . $periode->id ],
				'meerprijs_kind'      => (float) $json[ 'meerprijs_kind_' . $periode->id ],
				'inclusief'           => (int) $json[ 'inclusief_' . $periode->id ],
				'inclusief_toeslagen' => (int) $json[ 'inclusief_toeslagen_' . $periode->id ]
			);

			$db_prijs = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . $prijs_table . ' where periode_id = "%d" and accommodatie_id = "%d"', $periode->id, $_GET['id'] ) );
			if ( $db_prijs ) {
				$this->wpdb->update( $prijs_table, $fields, array( 'periode_id' => $periode->id, 'accommodatie_id' => $_GET['id'] ) );
			} else {
				$this->wpdb->insert( $prijs_table, $fields );
			}

			$this->prijsberekening->render_nachtprijzen( $periode->id, $_GET['id'] );

		}
		//$this->prijsberekening->reset_nachtprijzen();
		$this->form_saved();
	}

	function save_aanbiedingen() {
		$table        = SIMPEL_DB_PREFIX . 'aanbiedingen_per';
		$acco         = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
		$periodes     = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "%d" and acco_type_id = "%d" and tot >= now()', $acco->camping_id, $acco->acco_type_id ) );
		$aanbiedingen = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'aanbiedingen' );

		$sql = $this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per where accommodatie_id = "%d"', $_GET['id'] ) );
		foreach ( $periodes as $periode ) {
			foreach ( $aanbiedingen as $aanb ) {
				if ( $_POST[ 'aanb_' . $periode->id . '_' . $aanb->id ] ) {
					$fields = array(
						'periode_id'      => $periode->id,
						'accommodatie_id' => $acco->id,
						'aanbieding_id'   => $aanb->id
					);
					$this->wpdb->insert( $table, $fields );
				}
			}
		}
		echo '<script>document.location="admin.php?page=' . $_GET['page'] . '&act=periodes";</script>';
		$this->_exit();
	}

	function save_arrangementen() {
		$table         = SIMPEL_DB_PREFIX . 'arrangementen_per';
		$acco          = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );
		$periodes      = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "%d" and acco_type_id = "%d" and tot >= now()', $acco->camping_id, $acco->acco_type_id ) );
		$arrangementen = $this->wpdb->get_results( 'select * from ' . SIMPEL_DB_PREFIX . 'arrangementen' );

		$sql = $this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'arrangementen_per where accommodatie_id = "%d"', $_GET['id'] ) );
		foreach ( $periodes as $periode ) {
			foreach ( $arrangementen as $arrangement ) {
				if ( $_POST[ 'arrangement_' . $periode->id . '_' . $arrangement->id ] ) {
					$fields = array(
						'periode_id'      => $periode->id,
						'accommodatie_id' => $acco->id,
						'arrangement_id'  => $arrangement->id
					);
					$this->wpdb->insert( $table, $fields );
				}
			}
		}

		echo '<script>document.location="admin.php?page=' . $_GET['page'] . '&act=periodes";</script>';
		$this->_exit();
	}

	function save_toeslagen() {
		$accommodaties = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where camping_id = "%d" order by title', $_POST['camping_id'] ) );

		$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'toeslagen_per where toeslag_id = "%d"', $_GET['id'] ) );
		foreach ( $accommodaties as $acco ) {
			if ( $_POST[ 'prijs_' . $acco->id ] != 0 ) {
				$fields = array(
					'toeslag_id'      => $_GET['id'],
					'accommodatie_id' => $acco->id,
					'prijs'           => $_POST[ 'prijs_' . $acco->id ]
				);
				$this->wpdb->insert( SIMPEL_DB_PREFIX . 'toeslagen_per', $fields );
			}
		}
	}

	function copy_periodes( $data ) {
		echo 'Kopiëren periodes...';
		$this->wpdb->show_errors();
		if ( $_POST['verwijderen'] ) {
			$sql = 'delete from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "' . $data['camping_id'] . '" and acco_type_id = "' . $data['target_acco_type_id'] . '"';
			if ( $data['van'] ) {
				$sql .= ' and tot > "' . date( 'Y-m-d', strtotime( $data['van'] ) ) . '"';
			}
			if ( $data['tot'] ) {
				$sql .= ' and van < "' . date( 'Y-m-d', strtotime( $data['tot'] ) ) . '"';
			}
			$this->wpdb->query( $sql );
		}

		$sql = 'CREATE TEMPORARY TABLE tmp select * from ' . SIMPEL_DB_PREFIX . 'periode where camping_id = "' . $data['camping_id'] . '" and acco_type_id = "' . $data['source_acco_type_id'] . '"';
		if ( $data['van'] ) {
			$sql .= ' and tot > "' . date( 'Y-m-d', strtotime( $data['van'] ) ) . '"';
		}
		if ( $data['tot'] ) {
			$sql .= ' and van < "' . date( 'Y-m-d', strtotime( $data['tot'] ) ) . '"';
		}

		$this->wpdb->query( $sql );

		$sql = 'update tmp set id = null, acco_type_id = "' . $data['target_acco_type_id'] . '"';
		$this->wpdb->query( $sql );

		$sql = 'insert into ' . SIMPEL_DB_PREFIX . 'periode select * from tmp';
		$this->wpdb->query( $sql );

		echo '<script>document.location="admin.php?page=' . $_GET['page'] . '";</script>';
		$this->_exit();
	}

	function save_available() {
		if ( ! is_numeric( $_GET['id'] ) ) {
			exit( 'geen geldig id' );
		}
		$acco = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $_GET['id'] ) );

		$dag = time();
		$end = strtotime( '+1 year' );

		while ( $dag < $end ) {

			$fields = array(
				'datum'           => date( "Y-m-d", $dag ),
				'accommodatie_id' => $acco->id,
				'nr'              => (int) $_POST[ 'kamers_' . date( "Y_m_d", $dag ) ]
			);


			$nr = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'available where datum = "%s" and accommodatie_id = "%d"', date( "Y-m-d", $dag ), $_GET['id'] ) );
			if ( $nr ) {
				$this->wpdb->update( SIMPEL_DB_PREFIX . 'available', $fields, array( 'datum' => date( "Y-m-d", $dag ), 'accommodatie_id' => $_GET['id'] ) );
			} else {
				$this->wpdb->insert( SIMPEL_DB_PREFIX . 'available', $fields );
			}

			$dag = strtotime( '+1 day', $dag );
		}

		$this->form_saved();
	}

	function bulk_periodes( $data ) {
		echo 'Aanmaken periodes...';
		$this->wpdb->show_errors();

		if ( $data['nights'] < 1 ) {
			exit;
		}

		$day = strtotime( $data['van'] );
		$end = strtotime( $data['tot'] );
		while ( $day < $end ) {
			$next   = strtotime( '+' . $data['nights'] . ' day', $day );
			$fields = array(
				'camping_id'   => 1,
				'acco_type_id' => $data['source_acco_type_id'],
				'van'          => date( 'Y-m-d', $day ),
				'tot'          => date( 'Y-m-d', $next ),
			);
			$this->wpdb->insert( SIMPEL_DB_PREFIX . 'periode', $fields );

			$day = $next;
		}

		echo '<script>document.location="admin.php?page=' . $_GET['page'] . '";</script>';
		$this->_exit();
	}

	// Create the function to output the contents of our Dashboard Widget
	function admin_dashboard_widget_function() {
		$sql    = 'select b.*, c.title as camping, a.title as accommodatie from ' . SIMPEL_DB_PREFIX . 'boeking b
            inner join ' . SIMPEL_DB_PREFIX . 'camping c on b.camping_id = c.id
            inner join ' . SIMPEL_DB_PREFIX . 'accommodatie a on b.accommodatie_id = a.id
            ' . ( $this->camping_rights ? ' where c.id = "' . $this->camping_rights . '"' : '' ) . '
            order by datum_boeking desc
            limit 5';
		$fields = array( 'camping', 'accommodatie', 'naam', 'datum_boeking', 'prijs', 'datum_aankomst' );
		$titles = array( 'Camping', 'Accommodatie', 'Naam', 'Geboekt', 'Prijs', 'Aankomst' );
		if ( $this->camping_rights ) {
			unset( $fields[0] );
			unset( $titles[0] );
		}
		$results = $this->wpdb->get_results( $sql );
		?>
		<table class="wp-list-table widefat fixed pages" cellspacing="0">
			<thead>
			<tr>
				<?php foreach ( $titles as $col ) { ?>
					<th class="col" id="<?php echo $col ?>" class="manage-column"><?php echo $col ?></th>
				<?php } ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $results as $row ) { ?>
				<tr>

					<?php $i = 0;
					foreach ( $fields as $fld ) {
						?>
						<?php if ( $i ++ == 0 ) { ?>
							<td><a href="admin.php?page=admin-booking&id=<?php echo $row->id ?>"><?php echo $row->$fld ?></a></td>
						<?php } else { ?>
							<td><?php echo $row->$fld ?></td>
						<?php } ?>
					<?php } ?>
				</tr>
			<?php } ?>
			<?php if ( ! count( $results ) ) { ?>
				<tr>
					<td colspan="<?php echo count( $titles ) ?>">Geen boeking gevonden</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php
	}

	// Create the function use in the action hook

	function admin_add_dashboard_widgets() {
		wp_add_dashboard_widget( 'admin_dashboard_widget', 'Boekingen', array( &$this, 'admin_dashboard_widget_function' ) );
		// Globalize the metaboxes array, this holds all the widgets for wp-admin

		global $wp_meta_boxes;

		// Get the regular dashboard widgets array
		// (which has our new widget already but at the end)

		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		// Backup and delete our new dashbaord widget from the end of the array

		$admin_widget_backup = array( 'admin_dashboard_widget' => $normal_dashboard['admin_dashboard_widget'] );
		unset( $normal_dashboard['admin_dashboard_widget'] );

		// Merge the two arrays together so our widget is at the beginning

		$sorted_dashboard = array_merge( $admin_widget_backup, $normal_dashboard );

		// Save the sorted array back into the original metaboxes

		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
	}

	function show_beschikbaarheid( $id, $date = "", $months = 3 ) {
		$this->acco       = new accommodatie( $id );
		$this->acco->mode = 'manage';
		if ( $date == "" ) {
			if ( $_SESSION['boeken']['aankomst'] ) {
				$date = strtotime( $_SESSION['boeken']['aankomst'] );
			} else {
				$date = time();
			}
		}
		$html = '';
		for ( $i = 0; $i < $months; $i ++ ) {
			$html .= $this->show_maand( $id, date( "n", strtotime( "+" . $i . " month", $date ) ), date( "Y", strtotime( "+" . $i . " month", $date ) ) );
		}

		return $html;
	}

	function show_maand( $id, $maand, $jaar ) {
		$dag  = sprintf( "%04d-%02d-%02d", $jaar, $maand, 1 );
		$date = strtotime( $dag );

		/* $beschikbaarheid_table_name = $this->wpdb->prefix.$this->plugin_db_prefix."beschikbaarheid";
		  $jaren = $this->wpdb->get_results( $this->wpdb->prepare("select jaar, dagen from $beschikbaarheid_table_name where accommodatie_id = %s", $id));
		  $dagen = array();
		  foreach($jaren as $jaar)
		  {
		  $dagen[$jaar->jaar] = $jaar->dagen;
		  } */

		$maanden = array( '', 'Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December' );
		$html    = '
        <div class="maand">
            <div class="maand-title">' . $maanden[ date( "n", $date ) ] . ' ' . date( "Y", $date ) . '</div>
            <table cellspacing="2" width="100%">
                <thead>
                    <tr>
                        <th>Zo</th>
                        <th>Ma</th>
                        <th>Di</th>
                        <th>Wo</th>
                        <th>Do</th>
                        <th>Vr</th>
                        <th>Za</th>
                    </tr>
                </thead>
                <tbody>
                <tr>';

		for ( $i = 0; $i < date( "w", $date ); $i ++ ) {
			$html .= "<td>&nbsp;</td>";
		}
		for ( $i = 1; $i <= date( "t", $date ); $i ++ ) {
			$huidige_dag = strtotime( "+" . $i . " days", $date );
			if ( isset( $this->acco->beschikbaarheid[ date( 'Y', $huidige_dag ) ] ) ) {
				$dag = @$this->acco->beschikbaarheid[ date( 'Y', $huidige_dag ) ][ date( "z", $huidige_dag ) - 1 ];
			} else {
				$dag = 'A';
			}
			$html .= "<td class='" .
			         ( $dag == "O" ? "occupied" : "free" ) . ( $dag == "X" ? " arrival" : "" ) . "'" .
			         "onclick='flip_beschikbaarheid(this, \"" . date( "Y-m-d", strtotime( "+" . ( $i - 1 ) . " days", $date ) ) . "\")'"
			         . ">" . $i . "</td>";
			if ( date( "w", strtotime( "+" . $i . " days", $date ) ) == 0 ) {
				$html .= "</tr><tr>";
			}
		}
		$html .= '</tr></tbody></table></div>';

		return $html;
	}

	function set_beschikbaarheid() {

		$beschikbaarheid_table_name = SIMPEL_DB_PREFIX . "beschikbaarheid";
		$date                       = strtotime( $_POST['date'] );
		$dagen                      = $this->wpdb->get_var( $this->wpdb->prepare( "select dagen from $beschikbaarheid_table_name where jaar = %d and accommodatie_id = %d", date( "Y", $date ), $_POST['id'] ) );
		if ( ! $dagen ) {
			$dagen = "";
			for ( $i = 0; $i < 366; $i ++ ) {
				$dagen .= "A";
			}
			$insert = 1;
		}
		$dagen[ date( "z", $date ) ] = ( $_POST['beschikbaar'] == 'true' ? "A" : "O" );
		$fields                      = array(
			"accommodatie_id" => $_POST['id'],
			"dagen"           => $dagen,
			"jaar"            => date( "Y", $date )
		);

		if ( $insert ) {
			$this->wpdb->insert( $beschikbaarheid_table_name, $fields );
		} else {
			$this->wpdb->update( $beschikbaarheid_table_name, $fields, array( "accommodatie_id" => $_POST['id'], "jaar" => date( "Y", $date ) ) );
		}
		exit;
	}

	function update_cal() {
		$date = strtotime( sprintf( "%04d-%02d-%02d", $_POST['year'], $_POST['month'], 1 ) );
		if ( $_POST['js_action'] == 'prijzen' ) {
			$this->kalender_action = 'get_prices';
		}
		echo $this->show_beschikbaarheid( $_POST['id'], $date, $_POST['nr_months'] );
		exit;
	}

	function delete_foto() {
		$file       = str_replace( '_thumb_', '', basename( $_POST['foto'] ) );
		$upload_dir = wp_upload_dir();
		if ( preg_match( '/uploads\/campings/is', $_POST['foto'] ) ) {
			$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'camping_foto where foto = "%s" and camping_id = "%d"', $file, $_POST['id'] ) );
			$dir = $upload_dir['basedir'] . '/campings/' . $_POST['id'] . '/';
		} else {
			$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . SIMPEL_DB_PREFIX . 'accommodatie_foto where foto = "%s" and accommodatie_id = "%d"', $file, $_POST['id'] ) );
			$dir = $upload_dir['basedir'] . '/accommodaties/' . $_POST['id'] . '/';
		}
		unlink( $dir . '_thumb_' . $file );
		unlink( $dir . $file );
		$this->_exit();
	}

	function get_table_data() {
		/*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * Easy set variables
		 */

		/* Array of database columns which should be read and sent back to DataTables. Use a space where
		 * you want to insert a non-database field (for example a counter or static image)
		 */
		switch ( $_POST['db-table'] ) {
			case 'boeking':
				$aColumns = array( 'b.id', 'datum_boeking', 'naam', 'title', 'datum_aankomst', 'datum_vertrek', 'prijs' );
				$sTable   = SIMPEL_DB_PREFIX . $_POST['db-table'] . ' b inner join ' . SIMPEL_DB_PREFIX . 'accommodatie a on b.accommodatie_id = a.id';
				break;

			default:
				$this->_exit( 'geen tabel gevonden' );
				break;
		}

		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";

		/* DB table to use */

		/*         * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
		 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
		 * no need to edit below this line
		 */


		/*
		 * Paging
		 */
		$sLimit = "";
		if ( isset( $_POST['iDisplayStart'] ) && $_POST['iDisplayLength'] != '-1' ) {
			$sLimit = "LIMIT " . intval( $_POST['iDisplayStart'] ) . ", " .
			          intval( $_POST['iDisplayLength'] );
		}


		/*
		 * Ordering
		 */
		$sOrder = "";
		if ( isset( $_POST['iSortCol_0'] ) && $_POST['iSortCol_0'] ) {
			$sOrder = "ORDER BY  ";
			for ( $i = 0; $i < intval( $_POST['iSortingCols'] ); $i ++ ) {
				if ( $_POST[ 'bSortable_' . intval( $_POST[ 'iSortCol_' . $i ] ) ] == "true" ) {
					$sOrder .= "`" . $aColumns[ intval( $_POST[ 'iSortCol_' . $i ] ) ] . "` " .
					           ( $_POST[ 'sSortDir_' . $i ] ) . ", ";
				}
			}

			$sOrder = substr_replace( $sOrder, "", - 2 );
			if ( $sOrder == "ORDER BY" ) {
				$sOrder = "";
			}
		}


		/*
		 * Filtering
		 * NOTE this does not match the built-in DataTables filtering which does it
		 * word by word on any field. It's possible to do here, but concerned about efficiency
		 * on very large tables, and MySQL's regex functionality is very limited
		 */
		$sWhere = "";
		if ( isset( $_POST['sSearch'] ) && $_POST['sSearch'] != "" ) {
			$sWhere = "WHERE (";
			for ( $i = 0; $i < count( $aColumns ); $i ++ ) {
				if ( isset( $_POST[ 'bSearchable_' . $i ] ) && $_POST[ 'bSearchable_' . $i ] == "true" ) {
					$sWhere .= "" . $aColumns[ $i ] . " LIKE '%" . ( $_POST['sSearch'] ) . "%' OR ";
				}
			}
			$sWhere = substr_replace( $sWhere, "", - 3 );
			$sWhere .= ')';
		}

		if ( $this->camping_rights && $_POST['db-table'] == 'boeking' ) {
			if ( $sWhere == "" ) {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			$sWhere . 'a.camping_id = "' . $this->camping_rights . '"';

		}

		/* Individual column filtering */
		for ( $i = 0; $i < count( $aColumns ); $i ++ ) {
			if ( isset( $_POST[ 'bSearchable_' . $i ] ) && $_POST[ 'bSearchable_' . $i ] == "true" && $_POST[ 'sSearch_' . $i ] != '' ) {
				if ( $sWhere == "" ) {
					$sWhere = "WHERE ";
				} else {
					$sWhere .= " AND ";
				}
				$sWhere .= "`" . $aColumns[ $i ] . "` LIKE '%" . ( $_POST[ 'sSearch_' . $i ] ) . "%' ";
			}
		}

		if ( $_POST['db-table'] == 'boeking' ) {
			if ( $sWhere == "" ) {
				$sWhere = "WHERE ";
			} else {
				$sWhere .= " AND ";
			}
			if ( $_POST['type'] == 'test' ) {
				$sWhere .= '( b.type = "test")';
			} else {
				$sWhere .= '( b.type is null)';
			}
		}


		/*
		 * SQL queries
		 * Get data to display
		 */
		$sQuery = "
            SELECT SQL_CALC_FOUND_ROWS " . str_replace( " , ", " ", implode( ", ", $aColumns ) ) . "
            FROM   $sTable
            $sWhere
            $sOrder
            $sLimit
            ";
		$this->wpdb->show_errors();
		$rResult = $this->wpdb->get_results( $sQuery, ARRAY_N );

		/* Data set length after filtering */
		$sQuery         = "
            SELECT FOUND_ROWS()
        ";
		$iFilteredTotal = $this->wpdb->get_var( $sQuery );

		/* Total data set length */
		$sQuery = "
            SELECT COUNT(*)
            FROM   $sTable
        ";
		$iTotal = $this->wpdb->get_var( $sQuery );


		/*
		 * Output
		 */
		$output = array(
			"sEcho"                => intval( $_POST['sEcho'] ),
			"iTotalRecords"        => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData"               => array()
		);

		foreach ( $rResult as $aRow ) {
			$row = array();
			for ( $i = 0; $i < count( $aColumns ); $i ++ ) {
				if ( $aColumns[ $i ] == "version" ) {
					/* Special output formatting for 'version' column */
					$row[] = ( $aRow->$aColumns[ $i ] == "0" ) ? '-' : $aRow[ $i ];
				} else if ( $aColumns[ $i ] != ' ' ) {
					/* General output */
					$row[] = ( $aRow[ $i ] );
				}
			}
			$output['aaData'][] = $aRow;
		}

		echo json_encode( $output );
		$this->_exit();
	}

	function _exit( $msg = '' ) {
		if ( $msg ) {
			echo $msg;
		}
		do_action( 'shutdown' );
		exit;
	}

	private function _export_boeking() {
		$van = date( 'Y-m-d', strtotime( $_POST['van'] ) );
		$tot = date( 'Y-m-d', strtotime( $_POST['tot'] ) );

		$xlsDoc = new xlsDocument( 'Boekingen' );

		$header = array( 'Voornaam', 'Achternaam', 'Email', 'Accommodatie', 'Type', 'Geboekt', 'Aankomst', 'Vertrek', 'Prijs' );

		$lines = array();
		$sql   = $this->wpdb->prepare( 'select * from ' . SIMPEL_DB_PREFIX . 'boeking where datum_boeking >= "%s" and datum_boeking <= "%s" order by datum_boeking', $van, $tot );
		$rows  = $this->wpdb->get_results( $sql );
		foreach ( $rows as $row ) {
			$acco    = new Accommodatie( $row->accommodatie_id );
			$lines[] = array(
				$row->voornaam,
				$row->achternaam,
				$row->email,
				$acco->title,
				$acco->type->title,
				date( 'd-m-Y', strtotime( $row->datum_boeking ) ),
				date( 'd-m-Y', strtotime( $row->datum_aankomst ) ),
				date( 'd-m-Y', strtotime( $row->datum_vertrek ) ),
				$row->prijs
			);
		}

		$xlsDoc->output( $header, $lines );

		die;
	}

	private function strip_for_csv( $txt ) {
		return strip_tags( str_replace( ',', '', $txt ) );
	}

	private function copy_accommodatie( $id ) {
		if ( ! $id || ! is_numeric( $id ) ) {
			return;
		}
		$acco = $this->wpdb->get_row( 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "' . $id . '"' );
		unset( $acco->id );
		$acco->name .= '-copy';
		$this->wpdb->insert( SIMPEL_DB_PREFIX . 'accommodatie', (array) $acco );
		echo 'Kopieren gedaan';
		die;
	}
}
