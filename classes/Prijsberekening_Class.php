<?php
$cache            = array();
$GLOBALS['cache'] = $cache;

class Prijsberekening {
	private $wpdb;
	private $sr;

	public $periodes = array();

	function __construct() {
		global $wpdb;

		$this->wpdb      = &$wpdb;
		$this->db_prefix = SIMPEL_DB_PREFIX;
		$this->cache     = $GLOBALS['cache'];
	}


	function do_boek( $accommodatie, $kassabon, $site = '' ) {
		$_SESSION['boeken']['voorkeuren'] = array();
		if ( isset( $_SESSION['boeken']['zoek'] ) && is_array( $_SESSION['boeken']['zoek'] ) ) {
			foreach ( $_SESSION['boeken']['zoek'] as $item ) {
				if ( preg_match( '/voorkeur/is', $item ) ) {
					$_SESSION['boeken']['voorkeuren'][] = $item;
				}
			}
		} else {
			$_SESSION['boeken']['voorkeuren'] = array();
		}
		$boeken    = $_SESSION['boeken'];
		$toeslagen = $accommodatie->get_toeslagen( $_SESSION['boeken']['aankomst'], $_SESSION['boeken']['vertrek'], $_SESSION['boeken']['volw'] + $_SESSION['boeken']['kind'] );

		// kijk of er een arrangement bij is geboekt, zo ja, voeg dan de tekst daarvan in de mail
		$arrangement_html = '';
		if ( isset( $_SESSION['boeken']['arrangement'] ) ) {
			$arrangement = new Arrangement( $_SESSION['boeken']['arrangement'] );
			$suffix = '';
			if ( defined( 'ICL_LANGUAGE_CODE') ) {
				$suffix .= '_' . ICL_LANGUAGE_CODE;
			}
			$field = 'mail' . $suffix;

			$arrangement_html .= '<p>' . $arrangement->$field . '</p>';
		}


		ob_start();
		$theme_file = get_theme_root() . '/' . get_template() . '/simpel-reserveren/mail_boeken.php';
		if ( file_exists( $theme_file ) ) {
			include( $theme_file );
		} else {
			include( dirname( __FILE__ ) . '/../templates/mail_boeken.php' );
		}
		$html = ob_get_contents();
		ob_end_clean();

		$data         = array(
			'email'  => $_SESSION['boeken']['email'],
			'prijs'  => $_SESSION['boeken']['totaal'],
			'body'   => $html,
			'van'    => date( 'Y-m-d', strtotime( $_SESSION['boeken']['aankomst'] ) ),
			'tot'    => date( 'Y-m-d', strtotime( $_SESSION['boeken']['vertrek'] ) ),
			'domein' => $_SERVER['HTTP_HOST'],
			'datum'  => date( 'Y-m-d G:i:s' )
		);
		$data['hash'] = sha1( 'ZOUT' . sha1( $data['domein'] . $data['email'] . $data['prijs'] ) );


		$url = 'http://www.simpelreserveren.nl/doUploadABooking/';

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

		//execute post
		$result = curl_exec( $ch );

		//close connection
		curl_close( $ch );

		$result = json_decode( $result );

		if ( $result->status == 'ok' ) {
			$fields = array(
				'accommodatie_id' => $accommodatie->id,
				'camping_id'      => $accommodatie->camping->id,
				'datum_boeking'   => date( 'Y-m-d G:i:s' ),
				'achternaam'      => $_SESSION['boeken']['achternaam'],
				'voornaam'        => $_SESSION['boeken']['voornaam'],
				'naam'            => trim( $_SESSION['boeken']['voornaam'] . ' ' . $_SESSION['boeken']['achternaam'] ),
				'email'           => $_SESSION['boeken']['email'],
				'prijs'           => $_SESSION['boeken']['totaal'],
				'datum_aankomst'  => date( 'Y-m-d', strtotime( $_SESSION['boeken']['aankomst'] ) ),
				'datum_vertrek'   => date( 'Y-m-d', strtotime( $_SESSION['boeken']['vertrek'] ) ),
				'mail_html'       => $html,
				'refer'           => $site,
			);
			if ( $_SESSION['boeken']['achternaam'] == 'test' ) {
				$fields['type']                    = 'test';
				$_SESSION['boeken']['boeken_test'] = 1;
			}
			$this->wpdb->insert( $this->db_prefix . 'boeking', $fields );
			$_SESSION['boeken']['boeken_id'] = $this->wpdb->insert_id;

			// voeg een link toe aan de mail, zodat nog naar de bevestigingspagina kan worden gelinked
			$html         = str_replace( '[link_naw_gegevens]', $accommodatie->boek_url( 3, $_SESSION['boeken']['boeken_id'] ), $html );
			$camping_html = '';
			if ( defined( 'SIMPEL_SHOW_CC' ) && SIMPEL_SHOW_CC ) {
				$camping_html .= '<p><strong>Credicard gegevens:</strong></p>';
				$camping_html .= '<table><tr><td>Nummer</td><td>' . $_SESSION['boeken']['cc'] . '</td></tr>';
				$camping_html .= '<tr><td>Geldig</td><td>' . $_SESSION['boeken']['cc_valid_month'] . ' / ' . $_SESSION['boeken']['cc_valid_year'] . '</td></tr>';
				$camping_html .= '</table>';
			}

			if ( defined( 'SIMPEL_AANTALLEN' ) && SIMPEL_AANTALLEN ) {
				$sql = 'update ' . SIMPEL_DB_PREFIX . 'available set nr = nr - 1 where accommodatie_id = "' . $accommodatie->id . '" and datum >= "' . $fields['datum_aankomst'] . '" and datum < "' . $fields['datum_vertrek'] . '"';
				$this->wpdb->query( $sql );
			}

			$headers = 'From: ' . $accommodatie->camping->title . ' <' . $accommodatie->camping->email . '>' . "\r\n";
			$subject = 'Boeking ' . $accommodatie->title;
			add_filter( 'wp_mail_content_type', create_function( '', 'return "text/html";' ) );
			wp_mail( $accommodatie->camping->email, $subject, $html . $camping_html, $headers );
			wp_mail( $_SESSION['boeken']['email'], $subject, $html, $headers );

			return 1;
		}

		return 0;
	}

	function render_nachtprijzen( $periode_id, $accommodatie_id ) {
		$types   = array( 'nacht', 'midweek', 'weekend', 'week', 'periode' );
		$nachten = array( 1, 4, 3, 7, 0 );
		$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and periode_id = "%d"', $accommodatie_id, $periode_id ) );
		$this->wpdb->query( $this->wpdb->prepare( 'delete from ' . $this->db_prefix . 'prijs_cache where accommodatie_id = "%d"', $accommodatie_id ) );

		$prijs_row = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'prijs where accommodatie_id = "%d" and periode_id = "%d" limit 1', $accommodatie_id, $periode_id ) );
		if ( ! $prijs_row ) {
			return;
		}


		// bereken voor alle types de nachtprijzen
		foreach ( $types as $i => $type ) {
			$prijs_field      = $type . 'prijs';
			$aanbieding_field = $type . 'aanbieding';
			$prijs            = $prijs_row->$prijs_field;
			$aanbieding       = $prijs_row->$aanbieding_field;

			// bereken het aantal nachten van de periode, om zo de nachtprijs uit te kunnen rekenen
			$periode = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'periode where id = "%d"', $periode_id ) );
			if ( $type == 'periode' ) {
				$aantal_nachten = round( ( strtotime( $periode->tot ) - strtotime( $periode->van ) ) / ( 60 * 60 * 24 ) );
			} else {
				$aantal_nachten = $nachten[ $i ];
			}

			// bereken de nachtprijs 
			if ( $prijs > 0 ) {
				$dag = $periode->van;
				while ( $dag < $periode->tot ) {
					$nachtprijs    = $prijs / $aantal_nachten;
					$nachtprijs_ab = 0;
					if ( $aanbieding > 0 ) {
						$nachtprijs_ab = $aanbieding / $aantal_nachten;
					}

					$this->wpdb->insert( $this->db_prefix . 'nachtprijzen',
						array(
							'accommodatie_id'  => $accommodatie_id,
							'periode_id'       => $periode_id,
							'datum'            => $dag,
							'type'             => $type,
							'nachtprijs'       => $nachtprijs,
							'nachtprijs_ab'    => $nachtprijs_ab,
							'incl_toeslagen'   => (int) $periode->inclusief_toeslagen,
							'nr_personen_incl' => (int) $prijs_row->inclusief,
							'meerprijs_volw'   => $prijs_row->meerprijs_volw,
							'meerprijs_kind'   => $prijs_row->meerprijs_kind
						)
					);

					$dag = date( 'Y-m-d', strtotime( '+1 day', strtotime( $dag ) ) );
				}
			}
		}
	}

	function reset_nachtprijzen() {
		$this->wpdb->query( 'truncate ' . $this->db_prefix . 'nachtprijzen' );

		$accommodatie_types = $this->wpdb->get_results( 'select * from ' . $this->db_prefix . 'acco_type' );
		foreach ( $accommodatie_types as $acco_type ) {
			$accommodaties = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'accommodatie where acco_type_id = "%d"', $acco_type->id ) );
			$periodes      = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'periode where tot >= now() and acco_type_id = "%d"', $acco_type->id ) );
			foreach ( $accommodaties as $acco ) {
				foreach ( $periodes as $periode ) {
					$this->render_nachtprijzen( $periode->id, $acco->id );

				}
			}
		}
	}

	function bereken( $accommodatie_id, $van, $tot, $volw = 2, $kind = 0 ) {
		//if($this->_check_cache($accommodatie_id, $van, $tot, $volw, $kind))
		//{
		//return $this->prijs;
		//}
		$this->accommodatie = new Accommodatie( $accommodatie_id );
		if ( ! is_numeric( $van ) ) {
			$van = date( 'Y-m-d', strtotime( $van ) );
		}
		if ( ! is_numeric( $tot ) ) {
			$tot = date( 'Y-m-d', strtotime( $tot ) );
		}
		$this->van            = $van;
		$this->tot            = $tot;
		$this->aantal_nachten = round( ( strtotime( $this->tot ) - strtotime( $this->van ) ) / ( 60 * 60 * 24 ) );
		$this->volw           = $volw;
		$this->kind           = $kind;

		$this->prijzen  = array();
		$this->periodes = array();
		$this->cache    = array();

		$this->tries = 0;
		$this->recursive_bereken( $van );
		usort( $this->prijzen, array( &$this, 'sort_prijzen' ) );
		$this->bereken_aanbiedingen();
		$this->add_yield();
		usort( $this->prijzen, array( &$this, 'sort_prijzen' ) );

		if ( count( $this->prijzen ) ) {
			$_SESSION['boeken']['show_plattegrond'] = 1;
			$prijs                                  = $this->prijzen[0];
			if ( SIMPEL_AFRONDEN ) {
				$prijs->prijs   = round( $prijs->prijs );
				$prijs->korting = round( $prijs->korting );
			} else {
				$prijs->prijs   = round( $prijs->prijs, 2 );
				$prijs->korting = round( $prijs->korting, 2 );
			}

			$all_periods = 1;
			foreach ( $prijs->periode_blocks as $block ) {
				if ( ! $block->periode->boeken_op_plattegrond ) {
					$_SESSION['boeken']['show_plattegrond'] = 0;
				}
				$this->periodes[] = $block->periode->id;
				if ( $block->periode->naam ) {
					$prijs->periode_title = $block->periode->naam;
				} else {
					$all_periods = 0;
				}
			}
			if ( ! $all_periods ) {
				$prijs->periode_title = '';
			}

			//$this->_update_cache($accommodatie_id, $van, $tot, $volw, $kind, $prijs);
			return $prijs;
		}
	}

	function _check_cache( $accommodatie_id, $van, $tot, $volw, $kind ) {
		$sql = 'delete from ' . $this->db_prefix . 'prijs_cache where datediff(now(), added) > 7';
		$this->wpdb->query( $sql );

		$sql       = $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'prijs_cache where accommodatie_id = "%d" and van = "%s" and tot = "%s" and volw="%s" and kind = "%s" limit 1', $accommodatie_id, date( 'Y-m-d', strtotime( $van ) ),
			date( 'Y-m-d', strtotime( $tot ) ), $volw + 0, $kind + 0 );
		$prijs_row = $this->wpdb->get_row( $sql );
		if ( $prijs_row->id ) {
			$this->prijs    = json_decode( $prijs_row->prijs );
			$this->periodes = json_decode( $prijs_row->periodes );

			return true;
		}
	}

	function _update_cache( $accommodatie_id, $van, $tot, $volw, $kind, $prijs ) {
		$fields = array(
			'accommodatie_id' => $accommodatie_id,
			'van'             => $van,
			'tot'             => $tot,
			'volw'            => (int) $volw,
			'kind'            => (int) $kind,
			'prijs'           => json_encode( $prijs ),
			'periodes'        => json_encode( $this->periodes ),
			'added'           => date( 'Y-m-d' )
		);
		//$this->wpdb->insert($this->db_prefix.'prijs_cache', $fields);
	}

	function recursive_bereken( $dag, $prijs = 0, $aanbieding = 0, $path = array() ) {
		if ( $this->tries ++ > 200 ) {
			return;
		}
		if ( $dag > $this->tot ) {
			return;
		}
		if ( count( $path ) >= 70 ) {
			return;
		} // php mag niet meer dan 100 geneste functies hebben
		if ( $dag == $this->tot ) {
			$this->prijzen[] = new Prijs( $prijs, $aanbieding, $path );

			return;
		}
		// kijk welke nachtprijzen er allemaal zijn
		$nachtprijzen = $this->_get_nachtprijzen( $dag );
		foreach ( $nachtprijzen as $nachtprijs_row ) {
			$add_prijs = $nachtprijs_row->nachtprijs;

			// als er een aanbieding is, deze prijs pakken
			$korting = 0;
			if ( $nachtprijs_row->nachtprijs_ab > 0 ) {
				$korting   = $add_prijs - $nachtprijs_row->nachtprijs_ab;
				$add_prijs = $nachtprijs_row->nachtprijs_ab;
			}

			// kijken of xxer een meerprijs is voor volwassenen of kinderen
			$meerprijs = 0;
			if ( $this->volw > $nachtprijs_row->nr_personen_incl ) {
				$meerprijs += ( ( $this->volw - $nachtprijs_row->nr_personen_incl ) * $nachtprijs_row->meerprijs_volw );
			}
			$nr_personen_incl = $nachtprijs_row->nr_personen_incl - $this->volw;
			if ( $nr_personen_incl < 0 ) {
				$nr_personen_incl = 0;
			}
			if ( $this->kind > $nr_personen_incl ) {
				$meerprijs += ( ( $this->kind - $nr_personen_incl ) * $nachtprijs_row->meerprijs_kind );
			}
			$add_prijs += $meerprijs;


			if ( $add_prijs ) {
				$stop = 0;
				// kijk welke periode is gebruikt om de prijs te berekenen
				$periode = $this->_get_periode( $nachtprijs_row->periode_id );

				// kijk of de hele week, midweek, weekend of 'periode' in de periode valt
				switch ( $nachtprijs_row->type ) {
					case 'periode':
						if ( $periode->van >= $this->van && $periode->tot <= $this->tot ) {
							$aantal_nachten = round( ( strtotime( $periode->tot ) - strtotime( $periode->van ) ) / ( 60 * 60 * 24 ) );
							$volgende_dag   = date( 'Y-m-d', strtotime( '+' . $aantal_nachten . ' days', strtotime( $dag ) ) );

							// kijk of alle prijzen in de prijstabel staan
							//for($i=1; $i<$aantal_nachten; $i++)
							//{
							$i         = 0;
							$check_dag = date( 'Y-m-d', strtotime( '+' . $i . ' days', strtotime( $dag ) ) );
							$sql       = 'select count(*) as cnt from ' . $this->db_prefix . 'nachtprijzen where
								accommodatie_id = "' . $nachtprijs_row->accommodatie_id . '" 
								and periode_id = "' . $nachtprijs_row->periode_id . '"
								and (datum >= "' . date( 'Y-m-d', strtotime( $dag ) ) . '" and datum < "' . date( 'Y-m-d', strtotime( '+' . $aantal_nachten . ' days', strtotime( $dag ) ) ) . '")
								and type = "periode" ';
							$rows      = $this->wpdb->get_row( $sql );

							if ( $rows->cnt != $aantal_nachten ) {
								$stop = 1;
							}

							// bereken de nachtprijs door het aantal nachten met de prijs per nacht te vermenigvuldigen, omdat ook de datum met N nachten wordt vermeerderd.
							$add_prijs *= $aantal_nachten;
							$korting *= $aantal_nachten;
							$new_path = new Path( $periode, $nachtprijs_row, $aantal_nachten, $add_prijs );
						} else {
							$stop = 1;
						}
						break;

					case 'week':
						$volgende_dag = date( 'Y-m-d', strtotime( '+1 week', strtotime( $dag ) ) );
						if ( $volgende_dag > $this->tot ) {
							$stop = 1;
							break;
						}

						$week_prijzen = $this->_get_nachtprijzen( $dag, $volgende_dag, 'week' );

						// kijk of er in deze periode op deze dag mag worden aangekomen
						$dagen             = array( 'zo', 'ma', 'di', 'wo', 'do', 'vr', 'za' );
						$aankomst_dag      = $dagen[ date( 'w', strtotime( $dag ) ) ];
						$aankomst_mogelijk = $periode->$aankomst_dag;

						// kijk of er voor alle komende 7 nachten een weekprijs beschikbaar is
						if ( count( $week_prijzen ) == 7 && $volgende_dag <= $this->tot && $aankomst_mogelijk ) {
							//if($_SERVER['REMOTE_ADDR'] == '94.212.250.141') die('week found');
							$week_prijs = 0;
							$korting    = 0;
							foreach ( $week_prijzen as $prijs_row ) {
								if ( $prijs_row->nachtprijs_ab > 0 ) {
									$week_prijs += $prijs_row->nachtprijs_ab;
									$korting += $prijs_row->nachtprijs - $prijs_row->nachtprijs_ab;
								} else {
									$week_prijs += $prijs_row->nachtprijs;
								}
							}
							$add_prijs = $week_prijs + ( $meerprijs * 7 );
							$new_path  = new Path( $periode, $nachtprijs_row, 7, $add_prijs );
						} elseif ( count( $week_prijzen ) && $aankomst_mogelijk && $this->aantal_nachten >= 7 ) {
							// dit gebeurd in het geval dat de totale periode wel minimaal een week is, maar de betreffende periode maar enkele dagen bevat
							$volgende_dag = date( 'Y-m-d', strtotime( '+1 day', strtotime( $dag ) ) );
							$prijs_row    = $week_prijzen[0];
							if ( $prijs_row->nachtprijs_ab > 0 ) {
								$week_prijs += $prijs_row->nachtprijs_ab;
								$korting += $prijs_row->nachtprijs - $prijs_row->nachtprijs_ab;
							} else {
								$week_prijs += $prijs_row->nachtprijs;
							}

							$add_prijs = $week_prijs + ( $meerprijs * 7 );
							$new_path  = new Path( $periode, $nachtprijs_row, 7, $add_prijs );
						} else {
							$stop = 1;
						}
						break;

					case 'midweek':

						$volgende_dag    = date( 'Y-m-d', strtotime( '+4 days', strtotime( $dag ) ) );
						$midweek_prijzen = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and type = "midweek" and datum >= "%s" and datum < "%s" group by datum order by datum',
								$this->accommodatie->id, $dag, $volgende_dag ) );

						if ( count( $midweek_prijzen ) == 4 && $volgende_dag <= $this->tot && date( 'w', strtotime( $dag ) ) == 1 ) {
							$midweek_prijs = 0;
							$korting       = 0;
							foreach ( $midweek_prijzen as $prijs_row ) {
								if ( $prijs_row->nachtprijs_ab > 0 ) {
									$midweek_prijs += $prijs_row->nachtprijs_ab;
									$korting += $prijs_row->nachtprijs - $prijs_row->nachtprijs_ab;
								} else {
									$midweek_prijs += $prijs_row->nachtprijs;
								}
							}
							$add_prijs = $midweek_prijs + ( $meerprijs * 4 );
							$new_path  = new Path( $periode, $nachtprijs_row, 4, $add_prijs );
						} else {
							$stop = 1;
						}
						break;

					case 'weekend':
						$volgende_dag    = date( 'Y-m-d', strtotime( '+3 days', strtotime( $dag ) ) );
						$weekend_prijzen = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and type = "weekend" and datum >= "%s" and datum < "%s" group by datum order by datum',
								$this->accommodatie->id, $dag, $volgende_dag ) );

						if ( count( $weekend_prijzen ) == 3 && $volgende_dag <= $this->tot && date( 'w', strtotime( $dag ) ) == 5 ) {
							$weekend_prijs = 0;
							$korting       = 0;
							foreach ( $weekend_prijzen as $prijs_row ) {
								if ( $prijs_row->nachtprijs_ab > 0 ) {
									$weekend_prijs += $prijs_row->nachtprijs_ab;
									$korting += $prijs_row->nachtprijs - $prijs_row->nachtprijs_ab;
								} else {
									$weekend_prijs += $prijs_row->nachtprijs;
								}
							}
							$add_prijs = $weekend_prijs + ( $meerprijs * 3 );
							$new_path  = new Path( $periode, $nachtprijs_row, 3, $add_prijs );
						} else {
							$stop = 1;
						}

						break;

					default:
						$volgende_dag = date( 'Y-m-d', strtotime( '+1 day', strtotime( $dag ) ) );
						$new_path     = new Path( $periode, $nachtprijs_row, 1, $add_prijs );
						break;
				}

				if ( ! $stop ) {
					$rec_path   = $path;
					$rec_path[] = $new_path;
					$this->recursive_bereken( $volgende_dag, $prijs + $add_prijs, $aanbieding + $korting, $rec_path );
				}
			}
		}

	}

	private function _get_nachtprijzen( $van, $tot = null, $type = null ) {
		$cache_field = $van . $tot . $type;
		if ( isset( $this->cache[ $cache_field ] ) ) {
			return $this->cache[ $cache_field ];
		}
		$start = microtime( true );
		if ( $tot == null ) {
			$nachtprijzen = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and datum = "%s" ' . ( $type == null ? '' : ' and type = "' . $type . '"' ) . '  order by type != "periode", nachtprijs',
					$this->accommodatie->id, $van ) );
		} else {
			$nachtprijzen   = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and (type = "week" or type = "nacht") ' . ( $type == null ? '' : ' and type = "' . $type . '"' ) . ' and datum >= "%s" and datum < "%s" group by datum order by type != "periode", nachtprijs',
					$this->accommodatie->id, $van, $tot ) );
			$aantal_nachten = round( ( strtotime( $tot ) - strtotime( $van ) ) / ( 60 * 60 * 24 ) );
			if ( count( $nachtprijzen ) != $aantal_nachten ) {
				$nachtprijzen = $this->wpdb->get_results( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'nachtprijzen where accommodatie_id = "%d" and (type = "week" or type = "nacht") ' . ( $type == null ? '' : ' and type = "' . $type . '"' ) . ' and datum >= "%s" and datum < "%s" group by datum order by type != "periode", nachtprijs',
						$this->accommodatie->id, $van, $tot ) );
			}
		}

		$this->cache[ $cache_field ] = $nachtprijzen;

		return $nachtprijzen;
	}

	private function _get_periode( $periode_id ) {
		$cache_field = 'periode' . $periode_id;
		if ( isset( $this->cache[ $cache_field ] ) ) {
			return $this->cache[ $cache_field ];
		}
		$periode                     = $this->wpdb->get_row( $this->wpdb->prepare( 'select * from ' . $this->db_prefix . 'periode where id = "%d"', $periode_id ) );
		$this->cache[ $cache_field ] = $periode;

		return $periode;
	}

	function sort_prijzen( $a, $b ) {
		return ( $a->prijs < $b->prijs ) ? - 1 : 1;
	}

	function add_yield() {
		$sql    = 'select * from ' . SIMPEL_DB_PREFIX . 'yield
			where (datum_van <= "' . $this->tot . '" or datum_van is null)
				and (datum_tot >= "' . $this->van . '" or datum_tot is null)
				and ( concat(",", accommodaties_geldig, ",") like "%,' . $this->accommodatie->id . ',%" or accommodaties_geldig is null)';
		$yields = $this->wpdb->get_results( $sql );

		foreach ( $this->prijzen as $prijs ) {

			foreach ( $yields as $yield ) {
				if ( $yield->percentage ) {
					$prijs->prijs += ( $prijs->prijs / 100 * $yield->percentage );
				}

				if ( $yield->prijs ) {
					$prijs->prijs += $yield->prijs;
				}
			}
		}
	}

	function bereken_aanbiedingen() {
		foreach ( $this->prijzen as $prijs ) {
			$current_period = null;

			// koppel eerst alle dagen in blokjes van periodes
			foreach ( $prijs->path as $path ) {
				if ( $path->periode->id != $current_period || ! isset( $prijs->periode_blocks[ count( $prijs->periode_blocks ) - 1 ] ) ) {
					$prijs->periode_blocks[] = new PeriodeBlock( $this->accommodatie, $path->periode, $path->nachtprijs->datum, $path->prijs, $path->aantal_nachten );
				} else {
					$prijs->periode_blocks[ count( $prijs->periode_blocks ) - 1 ]->tot = date( 'Y-m-d', strtotime( '+' . $path->aantal_nachten . ' days', strtotime( $path->nachtprijs->datum ) ) );
					$prijs->periode_blocks[ count( $prijs->periode_blocks ) - 1 ]->prijs += $path->prijs;
				}

				$current_period = $path->periode->id;
			}


			// loop vervolgens door alle periode blokken heen en kijk welke aanbiedingen er allemaal geldig zijn
			foreach ( $prijs->periode_blocks as $i => $periode_block ) {
				foreach ( $periode_block->aanbiedingen as $aanbieding ) {
					if ( ! in_array( $aanbieding->id, array_keys( $prijs->aanbiedingen ) ) ) {
						if ( ! $aanbieding ) {
							$aanbieding = new stdClass();
						}
						$aanbieding->van             = $periode_block->van;
						$aanbieding->tot             = $periode_block->tot;
						$aanbieding->originele_prijs = $periode_block->prijs;
						$j                           = $i + 1;
						while ( $j < count( $prijs->periode_blocks ) ) {
							if ( in_array( $aanbieding->id, array_keys( $prijs->periode_blocks[ $j ]->aanbiedingen ) ) ) {
								$aanbieding->tot = $prijs->periode_blocks[ $j ]->tot;
								$aanbieding->originele_prijs += $prijs->periode_blocks[ $j ]->prijs;
							} else {
								break;
							}
							$j ++;
						}
						$prijs->aanbiedingen[ $aanbieding->id ] = $aanbieding;
					}
				}
			}

			// alle mogelijke aanbiedingen zijn toegekend, kijk nu of deze wel geldig zijn, en gebruik vervolgens degene met de meeste korting.
			foreach ( $prijs->aanbiedingen as $i => $aanbieding ) {
				$nachten = round( ( strtotime( $aanbieding->tot ) - strtotime( $aanbieding->van ) ) / ( 60 * 60 * 24 ) );
				if ( $nachten < $aanbieding->min_nachten ) {
					unset( $prijs->aanbiedingen[ $i ] );
					continue;
				}

				if ( $aanbieding->perc_korting > 0 ) {
					//echo 'orig' . $aanbieding->originele_prijs . "\n";
					$aanbieding->korting = $aanbieding->originele_prijs * $aanbieding->perc_korting / 100;
				} elseif ( $aanbieding->min_nachten > 0 ) {
					$multiplier          = floor( $nachten / $aanbieding->min_nachten );
					$nachten_korting     = $multiplier * $aanbieding->nachten_korting;
					$aanbieding->korting = $aanbieding->originele_prijs / $nachten * $nachten_korting;
				}
			}

			usort( $prijs->aanbiedingen, array( &$this, 'sort_aanbiedingen' ) );
			if ( count( $prijs->aanbiedingen ) ) {
				$aanbieding = $prijs->aanbiedingen[0];
				$prijs->prijs -= $aanbieding->korting;
				$prijs->korting += $aanbieding->korting;
				$prijs->aanbieding = $aanbieding;
				//echo $aanbieding->korting . "\n";
			}

		}
	}

	function sort_aanbiedingen( $a, $b ) {
		return ( $a->korting > $b->korting ) ? - 1 : 1;
	}
}

class Prijs {
	function __construct( $prijs, $korting, $path ) {
		$this->prijs   = $prijs;
		$this->korting = $korting;
		$this->path    = $path;

		$this->periode_blocks = array();
		$this->aanbiedingen   = array();
	}
}

class Path {
	function __construct( $periode, $nachtprijs, $aantal_nachten, $prijs ) {
		$this->periode        = $periode;
		$this->nachtprijs     = $nachtprijs;
		$this->aantal_nachten = $aantal_nachten;
		$this->prijs          = $prijs;
	}
}

class PeriodeBlock {
	function __construct( $accommodatie, $periode, $van, $prijs, $aantal_nachten ) {
		global $wpdb;
		$this->wpdb      = &$wpdb;
		$this->db_prefix = SIMPEL_DB_PREFIX;
		$this->cache     = $GLOBALS['cache'];

		$this->accommodatie = $accommodatie;
		$this->periode      = $periode;
		$this->van          = $van;
		$this->tot          = date( 'Y-m-d', strtotime( '+' . $aantal_nachten . 'days', strtotime( $van ) ) );
		$this->prijs        = $prijs;
		$this->aanbiedingen = array();

		if ( ! isset( $this->cache['db_aanbiedingen'] ) ) {
			$_db_aanbiedingen      = $this->wpdb->get_results( 'select * from ' . $this->db_prefix . 'aanbiedingen' );
			$this->db_aanbiedingen = array();
			foreach ( $_db_aanbiedingen as $item ) {
				$this->db_aanbiedingen[ $item->id ] = $item;
			}
			$this->cache['db_aanbiedingen'] = $this->db_aanbiedingen;
		} else {
			$this->db_aanbiedingen = $this->cache['db_aanbiedingen'];
		}

		$cache_field = 'aanbiedingen_per-' . $periode->id . '-' . $this->accommodatie->id;
		if ( isset( $this->cache[ $cache_field ] ) ) {
			//$this->aanbiedingen = $this->cache[$cache_field];
		} else {
			$sql                 = 'select * from ' . $this->db_prefix . 'aanbiedingen_per where periode_id = "' . $periode->id . '" and accommodatie_id = "' . $this->accommodatie->id . '"';
			$aanbieding_periodes = $this->wpdb->get_results( $sql );
			foreach ( $aanbieding_periodes as $item ) {
				if ( isset( $this->db_aanbiedingen[ $item->aanbieding_id ] ) ) {
					$this->aanbiedingen[ $item->aanbieding_id ] = $this->db_aanbiedingen[ $item->aanbieding_id ];
				}
			}
			$this->cache[ $cache_field ] = $this->aanbiedingen;
		}

	}
}