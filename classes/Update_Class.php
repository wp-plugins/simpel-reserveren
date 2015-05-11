<?php

class SimpelReserveren_Update {

    public static function install() {
        global $wpdb;
        $version = '2.3.31';
        
        if (get_option('simpelreserveren_version') == '') {
            add_option('simpelreserveren_version', $version);
            self::update();
        } elseif (get_option('simpelreserveren_version') != $version) {
            update_option('simpelreserveren_version', $version);
            self::update();
        } else {
            $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie';
            global $wpdb;
            if (!$wpdb->query($sql)) {
                self::update();
            }
        }
        if(get_option('simpelreserveren_version') <= '2.3.15') {
          self::update_images();
        }
        if(get_option('simpelreserveren_version') <= '2.3.19') {
          self::update_slugs();
        }

    }

    public static function update_images()
    {
      global $wpdb;
      global $wp_simpelreserveren;

      $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie';
      $rows = $wpdb->get_results($sql);
      foreach($rows as $row) {
        if(empty($row->afbeelding_id) && !empty($row->afbeelding)) {
          if (SIMPEL_MULTIPLE) $wp_simpelreserveren->switch_to_blog($row->camping_id);

          $attachment_id = self::get_attachment_id($row->afbeelding);
          $sql = 'update ' . SIMPEL_DB_PREFIX . 'accommodatie set afbeelding_id = "' . $attachment_id . '" where id = "' . $row->id . '"';
          $wpdb->query($sql);
        }
      }

      $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie_foto';
      $rows = $wpdb->get_results($sql);
      foreach($rows as $row) {
        if(!empty($row->foto) && empty($row->foto_id)) {
          if (SIMPEL_MULTIPLE) {
            $accommodatie = $wpdb->get_row('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie_foto where id = "' . $row->accommodatie_id . '"');
            $wp_simpelreserveren->switch_to_blog($accommodatie->camping_id);
          }

          $attachment_id = self::get_attachment_id($row->foto);
          $sql = 'update ' . SIMPEL_DB_PREFIX . 'accommodatie_foto set foto_id = "' . $attachment_id . '" where id = "' . $row->id . '"';
          $wpdb->query($sql);
        }
      }

      $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'camping';
      $rows = $wpdb->get_results($sql);
      foreach($rows as $row) {
        if (SIMPEL_MULTIPLE) $wp_simpelreserveren->switch_to_blog($row->id);
        
        if(empty($row->logo_id) && !empty($row->logo)) {
          $attachment_id = self::get_attachment_id($row->logo);
          $sql = 'update ' . SIMPEL_DB_PREFIX . 'camping set logo_id = "' . $attachment_id . '" where id = "' . $row->id . '"';
          $wpdb->query($sql);
        }

        if(empty($row->plattegrond_id) && !empty($row->plattegrond)) {
          $attachment_id = self::get_attachment_id($row->plattegrond);
          $sql = 'update ' . SIMPEL_DB_PREFIX . 'camping set plattegrond_id = "' . $attachment_id . '" where id = "' . $row->id . '"';
          $wpdb->query($sql);
        }
      }


    }

    public static function get_attachment_id($image) 
    {
      global $wpdb;
      $upload_dir = wp_upload_dir();

      // check if attachment already exists
      $sql = 'select * from ' . $wpdb->prefix . 'posts where post_type="attachment" and guid = "' . $image . '"';
      $post = $wpdb->get_row($sql);
      if($post) {
        $attachment_id = $post->ID;
      } else {
        // copy the file 
        $info = pathinfo($image);
        $file = $info['basename'];
        $target = $upload_dir['path'].'/'.$file;
        if(!file_exists($target)) {
          $buffer = @file_get_contents(str_replace(' ', '%20', $image));
          if(empty($buffer)) $buffer = @file_get_contents(str_replace(' ', '%20', get_bloginfo('wpurl').$image));
          if(empty($buffer)) return;

          file_put_contents($target, $buffer);
        }

        // get mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $target);
        finfo_close($finfo);

        $data = array(
          'guid' => $image,
          'post_title' => $info['filename'],
          'post_content' => '',
          'post_status' => 'publish',
          'post_mime_type' => $mime
        );

        $attachment_id = wp_insert_attachment( $data, $target );
      }

      return $attachment_id;
    }

    public static function update() {
      global $wpdb; 
        $languages = array();
        if (defined('ICL_LANGUAGE_CODE')) {
            $languages = icl_get_languages('skip_missing=N');
        }

        $sql = "
        CREATE TABLE `" . SIMPEL_DB_PREFIX . "aanbiedingen` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL,
          `min_nachten` int(9) NOT NULL,
          `nachten_korting` int(9) NULL,
          `perc_korting` float(9,2) NULL,
          `datum` datetime NOT NULL,
          `voorwaarden` text NOT NULL,
          `omschrijving` varchar(255) NOT NULL,
        ";
        foreach ($languages as $lang) {
            $sql .= "`omschrijving_" . $lang['language_code'] . "` varchar(255) NOT NULL," . "\n";
            $sql .= "`voorwaarden_" . $lang['language_code'] . "` text," . "\n";
        }

        $sql .= "`camping_id` int(9) NOT NULL default '1',
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "arrangementen` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL,
          `name` varchar(255) NOT NULL,
          `per` varchar(255) NOT NULL,
          `prijs` float(9,2) NULL,
          `min_nights` integer(9) NULL,
          `max_nights` integer(9) NULL,
          `zichtbaar` tinyint(1) NOT NULL,
          `altijd_geldig` tinyint(1) NOT NULL,
          `overwrite` tinyint(1) NOT NULL,
          `datum` datetime NOT NULL,
          `omschrijving` text,
          `overview` text,
          `terms` text,
          `afbeelding_id` int(9) NULL,
        ";
        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NOT NULL," . "\n";
            $sql .= "`omschrijving_" . $lang['language_code'] . "` text," . "\n";
            $sql .= "`overview_" . $lang['language_code'] . "` text," . "\n";
            $sql .= "`terms_" . $lang['language_code'] . "` text," . "\n";
            $sql .= "`mail_" . $lang['language_code'] . "` text," . "\n";
        }

        $sql .= "`camping_id` int(9) NOT NULL default '1',
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "arrangementen_prijzen` (
          `id` int(9) NOT NULL auto_increment,
          `arrangement_id` int(9) NULL,
          `accommodatie_id` int(9) NULL,
          `prijs` float(5,2) NULL,
          `meerprijs_volw` float(5,2) NULL,
          `meerprijs_kind` float(5,2) NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;


        CREATE TABLE `" . SIMPEL_DB_PREFIX . "available` (
          `id` int(9) NOT NULL auto_increment,
          `datum` date NULL,
          `accommodaties_id` integer(9) NULL,
          `nr` integer(9) NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "yield` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL,
          `datum_van` date NULL,
          `datum_tot` date NULL,
          `accommodaties_geldig` varchar(255) NULL,
          `prijs` float(5,2) NULL,
          `percentage` float(5,2) NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "aanbiedingen_per` (
          `id` int(9) NOT NULL auto_increment,
          `aanbieding_id` int(9) NOT NULL default '0',
          `periode_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `per` (`periode_id`),
          KEY `acc` (`accommodatie_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "arrangementen_per` (
          `id` int(9) NOT NULL auto_increment,
          `arrangement_id` int(9) NOT NULL default '0',
          `periode_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `per` (`periode_id`),
          KEY `acc` (`accommodatie_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;


        CREATE TABLE `" . SIMPEL_DB_PREFIX . "arrangement_foto` (
          `id` int(9) NOT NULL auto_increment,
          `foto_id` int(9) NULL,
          `arrangement_id` int(9) NOT NULL default '0',
          `datum` datetime NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY  (`id`),
          UNIQUE KEY `uniq` (`arrangement_id`, `foto_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;


        CREATE TABLE `" . SIMPEL_DB_PREFIX . "acco_type` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL default '',
        ";

        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NOT NULL default ''," . "\n";
        }

        $sql .= "`datum` datetime NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "accommodatie` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL default '',
          `title_intern` varchar(255) NULL,
          `camping_id` int(9) NOT NULL default '0',
          `acco_type_id` int(9) NOT NULL default '0',
          `name` varchar(255) default NULL,
          `aantal_personen` int(9) NOT NULL default '0',
          `aantal_slaapkamers` int(9) NOT NULL,
          `sterren` int(9) NOT NULL default '1',
          `huisdieren_toegestaan` int(1) NULL default '0',
          `datum` datetime NOT NULL default '0000-00-00 00:00:00',
          `omschrijving` text NOT NULL,
          `samenvatting` text NOT NULL,
          `afbeelding_id` int(9) NOT NULL default '0',
          `seq` int(9) NOT NULL default '0',
          `seq_inner` int(9) NOT NULL default '0',
          `aankomst_tijd` varchar(255) NOT NULL default '',
          `vertrek_tijd` varchar(255) NOT NULL default '',
          `min_aantal_nachten` int(9) NOT NULL default '1',
          `button_url` varchar(255) default NULL,
          `button_tekst` varchar(255) default NULL,
          `vanaf_prijs` float(9,2) default NULL,
          `bekeken` int(9) NOT NULL default 0,
          `plattegrond` int(1) NOT NULL default '1',
        ";

        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NOT NULL default ''," . "\n";
            $sql .= "`samenvatting_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`omschrijving_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`button_tekst_" . $lang['language_code'] . "` varchar(255) NOT NULL default ''," . "\n";
        }

        $sql .= "PRIMARY KEY  (`id`),
          KEY `camping` (`camping_id`),
          KEY `type` (`acco_type_id`),
          UNIQUE KEY `name` (`name`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;


        CREATE TABLE `" . SIMPEL_DB_PREFIX . "accommodatie_foto` (
          `id` int(9) NOT NULL auto_increment,
          `foto` varchar(255) NOT NULL default '',
          `foto_id` int(9) NULL,
          `accommodatie_id` int(9) NOT NULL default '0',
          `datum` datetime NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY  (`id`),
          UNIQUE KEY `uniq` (`foto`,`accommodatie_id`, `foto_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "accommodatie_randomized` (
          `id` int(9) NOT NULL auto_increment,
          `datum` date NOT NULL default '0000-00-00',
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "beschikbaarheid` (
          `id` int(9) NOT NULL auto_increment,
          `dagen` text NOT NULL,
          `jaar` int(4) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `jaar` (`jaar`),
          KEY `acco` (`accommodatie_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "boeking` (
          `id` int(9) NOT NULL auto_increment,
          `naam` varchar(255) NOT NULL default '',
          `email` varchar(255) NOT NULL,
          `voornaam` varchar(255) NOT NULL,
          `achternaam` varchar(255) NOT NULL,
          `datum_boeking` datetime NOT NULL default '0000-00-00 00:00:00',
          `datum_aankomst` date NOT NULL default '0000-00-00',
          `datum_vertrek` date NOT NULL default '0000-00-00',
          `prijs` decimal(6,2) NOT NULL default '0.00',
          `mail_html` text NOT NULL,
          `camping_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          `refer` varchar(255) NOT NULL default '',
          `adres` varchar(255) default NULL,
          `postcode` varchar(255) default NULL,
          `plaats` varchar(255) default NULL,
          `telefoon` varchar(255) default NULL,
          `factuur_per_post` int(1) NOT NULL,
          `type` varchar(255) default NULL,
          `update_send` int(1) NOT NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;
        
        CREATE TABLE `" . SIMPEL_DB_PREFIX . "camping` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL default '',
          `name` varchar(255) default NULL,
          `datum` datetime NOT NULL default '0000-00-00 00:00:00',
          `email` varchar(255) NOT NULL default '',
          `email_header` text NULL,
          `email_footer` text NULL,
          `booking_footer` text NULL,
          `plattegrond` varchar(255) default NULL,
          `plattegrond_id` int(9) default NULL,
          `voorwaarden_url` varchar(255) default NULL,
          `has_controle_stap` int(1) NULL,
          `confirm_tekst` text,
          `stap3_naw` int(1) NOT NULL default '0',
          `tweet_tekst` text,
          `facebook_tekst` text,
          `terug_button` int(1) NOT NULL default '0',
          `terug_button_stap2` int(1) NOT NULL default '0',
          `logo` varchar(255) default NULL,
          `logo_id` int(9) default NULL,
          `plaats` varchar(255) default NULL,
          `txt_camping` text NULL,
          `txt_omgeving` text NULL,
          `seq` int(9) NOT NULL default '0',
          `age_youth` varchar(255) default NULL,
          `age_child` varchar(255) default NULL,
          `age_baby` varchar(255) NOT NULL,
          `youth_as_child` int(1) NOT NULL default '0',
        ";

        foreach ($languages as $lang) {
            $sql .= "`txt_camping_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`txt_omgeving_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`email_header_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`email_footer_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`confirm_tekst_" . $lang['language_code'] . "` text NOT NULL," . "\n";
            $sql .= "`booking_footer_" . $lang['language_code'] . "` text NOT NULL," . "\n";
        }

        $sql .= "PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "camping_foto` (
          `id` int(9) NOT NULL auto_increment,
          `foto` varchar(255) NOT NULL default '',
          `foto_id` int(9) NULL,
          `camping_id` int(9) NOT NULL default '0',
          `datum` datetime NOT NULL default '0000-00-00 00:00:00',
          PRIMARY KEY  (`id`),
          UNIQUE KEY `uniq` (`foto`,`camping_id`, `foto_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;


        CREATE TABLE `" . SIMPEL_DB_PREFIX . "faciliteiten` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL default '',
        ";
        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NOT NULL default ''," . "\n";
        }
        $sql .= "`is_camping` int(1) NOT NULL default '0',
          `is_filter` int(1) NOT NULL default '0',
          `icon` varchar(255) NOT NULL default '',
          PRIMARY KEY  (`id`),
          KEY `camping` (`is_camping`),
          KEY `filter` (`is_filter`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "faciliteiten_per` (
          `id` int(9) NOT NULL auto_increment,
          `faciliteit_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NULL,
          `camping_id` int(9) NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "meldingen` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NULL,
          ";
        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NULL," . "\n";
        }
        $sql .= "
          `van` date NULL,
          `tot` date NULL,
          `type` varchar(255) NULL,
          `plaats` varchar(255) NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "nachtprijzen` (
          `id` int(9) NOT NULL auto_increment,
          `periode_id` int(9) NOT NULL,
          `accommodatie_id` int(9) NOT NULL,
          `datum` date NOT NULL,
          `type` varchar(255) NOT NULL,
          `nachtprijs` float(9,5) NOT NULL,
          `nachtprijs_ab` float(9,5) default NULL,
          `incl_toeslagen` int(1) NOT NULL default '0',
          `nr_personen_incl` int(9) default NULL,
          `meerprijs_volw` float(9,5) default NULL,
          `meerprijs_kind` float(9,5) default NULL,
          PRIMARY KEY  (`id`),
          KEY `per` (`periode_id`),
          KEY `acco` (`accommodatie_id`),
          KEY `datum` (`datum`),
          KEY `type` (`type`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "periode` (
          `id` int(9) NOT NULL auto_increment,
          `camping_id` int(9) NOT NULL default '0',
          `acco_type_id` int(9) NOT NULL default '0',
          `van` date NOT NULL default '0000-00-00',
          `tot` date NOT NULL default '0000-00-00',
          `naam` varchar(255) NOT NULL,
          `ma` int(1) NULL default '1',
          `di` int(1) NULL default '1',
          `wo` int(1) NULL default '1',
          `do` int(1) NULL default '1',
          `vr` int(1) NULL default '1',
          `za` int(1) NULL default '1',
          `zo` int(1) NULL default '1',
          `boeken_op_plattegrond` int(1) NULL default '1',
          `omschrijving` text,
        ";
        foreach ($languages as $lang) {
            $sql .= "`naam_" . $lang['language_code'] . "` varchar(255) NOT NULL," . "\n";
            $sql .= "`omschrijving_" . $lang['language_code'] . "` text," . "\n";
        }

        $sql .= "`alternatieve_aankomst_tijd` varchar(255) default NULL,
          `alternatieve_vertrek_tijd` varchar(255) default NULL,
          `min_nachten` int(9) default NULL,
          PRIMARY KEY  (`id`),
          KEY `camping2` (`camping_id`),
          KEY `type2` (`acco_type_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "prijs` (
          `id` int(9) NOT NULL auto_increment,
          `periode_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          `nachtprijs` decimal(6,2),
          `nachtaanbieding` decimal(6,2),
          `weekendprijs` decimal(6,2),
          `weekendaanbieding` decimal(6,2),
          `midweekprijs` decimal(6,2),
          `midweekaanbieding` decimal(6,2),
          `weekprijs` decimal(6,2),
          `weekaanbieding` decimal(6,2),
          `periodeprijs` decimal(6,2),
          `periodeaanbieding` decimal(6,2),
          `meerprijs_volw` decimal(6,2),
          `meerprijs_kind` decimal(6,2),
          `inclusief` int(9) NOT NULL default '0',
          `inclusief_toeslagen` int(1) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `acco2` (`accommodatie_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "prijs_cache` (
          `id` int(9) NOT NULL auto_increment,
          `accommodatie_id` int(9) NOT NULL,
          `van` date NOT NULL,
          `tot` date NOT NULL,
          `volw` int(9) NOT NULL,
          `kind` int(9) NOT NULL,
          `added` date NOT NULL,
          `periodes` text,
          `prijs` text NOT NULL,
          PRIMARY KEY  (`id`),
          KEY `acco` (`accommodatie_id`),
          KEY `van` (`van`),
          KEY `tot` (`tot`),
          KEY `volw` (`volw`),
          KEY `kind` (`kind`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "settings` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL,
          `field` varchar(255) NOT NULL,
          `value` text NOT NULL,
          `multilang` int(1) NOT NULL default '0',
          `input_type` varchar(255) NULL,
          `input_value` varchar(255) NULL,
        ";

        foreach ($languages as $lang) {
            $sql .= "`value_" . $lang['language_code'] . "` text NULL,\n";
        }

        $sql .= "PRIMARY KEY (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "toeslagen` (
          `id` int(9) NOT NULL auto_increment,
          `title` varchar(255) NOT NULL default '',
        ";

        foreach ($languages as $lang) {
            $sql .= "`title_" . $lang['language_code'] . "` varchar(255) NULL,\n";
        }

        $sql .= "`verplicht` int(1) NOT NULL default '0',
          `type` varchar(255) NOT NULL default '',
          `per` varchar(255) NOT NULL default '',
          `camping_id` int(9) NOT NULL default '0',
          `prijs_camping` decimal(6,2) NOT NULL default '0.00',
          `percentage` decimal(5,2) NOT NULL default '0.00',
          `seq` int(11) NOT NULL default '0',
          `max` int(9) NOT NULL default '0',
          `omschrijving` text NULL,
          `borgsom` int(1) NOT NULL default '0',
          `ter_plaatse_betalen` int(1) NOT NULL default '0',
          `periodes` text NULL,
          `arrangementen` text NULL,
          `afbeelding` varchar(255) NULL,
          `voorkeursplaats` int(1) NOT NULL default '0',
          PRIMARY KEY  (`id`),
          KEY `toeslag_camping` (`camping_id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        CREATE TABLE `" . SIMPEL_DB_PREFIX . "toeslagen_per` (
          `id` int(9) NOT NULL auto_increment,
          `toeslag_id` int(9) NOT NULL default '0',
          `accommodatie_id` int(9) NOT NULL default '0',
          `prijs` decimal(6,2) NOT NULL,
          PRIMARY KEY  (`id`)
        )
        DEFAULT CHARACTER SET = utf8
        ENGINE = InnoDB
        Collate = utf8_general_ci;

        ";
        //$wpdb->show_errors();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        self::foreign_keys();

        self::sample_data();

        flush_rewrite_rules();
        //$prijsberekening = new Prijsberekening();
        //$prijsberekening->reset_nachtprijzen();
    }

    private function foreign_keys()
    {
      global $wpdb;
      $sql = array();
      $tables = array(
        'aanbiedingen',
        'aanbiedingen_per',
        'acco_type',
        'accommodatie',
        'accommodatie_foto',
        'accommodatie_randomized',
//        'beschikbaarheid', HEEFT FULLTEXT INDEX, kan daarom geen InnoDB en FOREIGN KEY hebben
        'boeking',
        'camping',
        'faciliteiten',
        'faciliteiten_per',
        'meldingen',
        'nachtprijzen',
        'periode',
        'prijs',
        'prijs_cache',
        'settings',
        'toeslagen',
        'toeslagen_per',
        'yield',
      );
      foreach($tables as $table) {
        $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . $table . ' ENGINE=InnoDB';
      }
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per  ADD CONSTRAINT `aanbieding`    FOREIGN KEY (aanbieding_id)   REFERENCES '.SIMPEL_DB_PREFIX.'aanbiedingen(id)  ON DELETE CASCADE';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per  ADD CONSTRAINT `periode`       FOREIGN KEY (periode_id)      REFERENCES '.SIMPEL_DB_PREFIX.'periode(id)       ON DELETE CASCADE';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'aanbiedingen_per  ADD CONSTRAINT `accommodatie`  FOREIGN KEY (accommodatie_id) REFERENCES '.SIMPEL_DB_PREFIX.'accommodatie(id)  ON DELETE CASCADE';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'accommodatie      ADD CONSTRAINT `accotype`      FOREIGN KEY (acco_type_id)    REFERENCES '.SIMPEL_DB_PREFIX.'acco_type(id)';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'accommodatie      ADD CONSTRAINT `camping`       FOREIGN KEY (camping_id)      REFERENCES '.SIMPEL_DB_PREFIX.'camping(id)';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'accommodatie_foto ADD CONSTRAINT `acco`          FOREIGN KEY (accommodatie_id) REFERENCES '.SIMPEL_DB_PREFIX.'accommodatie(id)  ON DELETE CASCADE';
      $sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'accommodatie_foto DROP INDEX IF EXISTS `uni`';
      //$sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'faciliteiten_per  ADD CONSTRAINT `faciliteit`    FOREIGN KEY (faciliteit_id)   REFERENCES '.SIMPEL_DB_PREFIX.'faciliteiten(id)  ON DELETE CASCADE';
      //$sql[] = 'ALTER TABLE ' . SIMPEL_DB_PREFIX . 'faciliteiten_per  ADD CONSTRAINT `accommodatie`  FOREIGN KEY (accommodatie_id) REFERENCES '.SIMPEL_DB_PREFIX.'accommodatie(id)  ON DELETE CASCADE';
      
      foreach($sql as $query) {
        $wpdb->query($query);
      }
    }

    public static function update_slugs()
    {
      global $wpdb;
      $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'accommodatie';
      $rows = $wpdb->get_results($sql);
      foreach($rows as $acco) {
        if(!empty($acco->name)) continue;
        self::set_accommodatie_slug($acco->id);
      }

      $sql = 'select * from ' . SIMPEL_DB_PREFIX . 'camping';
      $rows = $wpdb->get_results($sql);
      foreach($rows as $camping) {
        if(!empty($camping->name)) continue;
        self::set_camping_slug($camping->id);
      }

      $sql = 'alter table ' . SIMPEL_DB_PREFIX . 'accommodatie add unique index name (name)';
      $wpdb->query($sql);

      $sql = 'alter table ' . SIMPEL_DB_PREFIX . 'camping add unique index name (name)';
      $wpdb->query($sql);

      flush_rewrite_rules();
    }

    public static function set_accommodatie_slug($acco_id) 
    {
      global $wpdb;
      $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where id = "%d"', $acco_id);
      $acco = $wpdb->get_row($sql);
      if(!$acco) return;
      $name = self::seo_url($acco->title);

      $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where name = "%s"', $name);
      $row = $wpdb->get_row($sql);

      $post_sql = $wpdb->prepare('select * from '. $wpdb->prefix . 'posts where post_name = "%s"', $name);
      $post = $wpdb->get_row($post_sql);

      if($row->id || $post->ID) {
        for($i=1; $i<10; $i++) {
          $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie where name = "%s"', $name.'-'.$i);
          $row = $wpdb->get_row($sql);

          $post_sql = $wpdb->prepare('select * from ' . $wpdb->prefix . 'posts where post_name = "%s"', $name.'-'.$i);
          $post = $wpdb->get_row($post_sql);
          if(!$row->id && !$post->ID) break;
        }
        $name = $name.'-'.$i;
      }

      $sql = $wpdb->prepare('update ' . SIMPEL_DB_PREFIX . 'accommodatie set name = "%s" where id = "%d"', $name, $acco->id);
      $wpdb->query($sql);
    }


    public static function set_camping_slug($camping_id) 
    {
      global $wpdb;
      $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'camping where id = "%d"', $camping_id);
      $camping = $wpdb->get_row($sql);
      if(!$camping) return;
      $name = self::seo_url($camping->title);

      $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'camping where name = "%s"', $name);
      $row = $wpdb->get_row($sql);

      $post_sql = $wpdb->prepare('select * from '. $wpdb->prefix . 'posts where post_name = "%s"', $name);
      $post = $wpdb->get_row($post_sql);

      if($row->id || $post->ID) {
        for($i=1; $i<10; $i++) {
          $sql = $wpdb->prepare('select * from ' . SIMPEL_DB_PREFIX . 'camping where name = "%s"', $name.'-'.$i);
          $row = $wpdb->get_row($sql);

          $post_sql = $wpdb->prepare('select * from ' . $wpdb->prefix . 'posts where post_name = "%s"', $name.'-'.$i);
          $post = $wpdb->get_row($post_sql);
          if(!$row->id && !$post->ID) break;
        }
        $name = $name.'-'.$i;
      }

      $sql = $wpdb->prepare('update ' . SIMPEL_DB_PREFIX . 'camping set name = "%s" where id = "%d"', $name, $camping->id);
      $wpdb->query($sql);
    }

    private static function seo_url($tekst) {
        $replacor = '-';
                
        // omzetten naar lower
        $tekst = strtolower($tekst);
                
        // Vervang speciale tekens door normale variant
        $tekst = htmlentities($tekst,ENT_QUOTES,'UTF-8');
        $tekst = preg_replace('/&([a-z]|ae)(uml|acute|grave|circ|tilde|cedil|ring|slash|lig);/','$1',$tekst);
        $tekst = preg_replace('/&sup([1-3]);/','$1',$tekst);
        $tekst = str_replace('&szlig;','ss',$tekst);
        $tekst = html_entity_decode($tekst,ENT_QUOTES,'UTF-8');
                
        // verwijder overige speciale tekens
        $tekst = preg_replace('/[^a-z0-9-]/',$replacor,$tekst);
                
        // Verwijder dubbele replacors
        while(strpos($tekst,$replacor.$replacor) !== false){
            $tekst = str_replace($replacor.$replacor,$replacor,$tekst);
        }
        
        // Verwijder replacors aan begin en eind
        $tekst = trim($tekst,$replacor);
                
        return $tekst;
    }

    public static function sample_data() {
        global $wpdb;
        //$wpdb->show_errors();

        $accos = $wpdb->get_results('select * from ' . SIMPEL_DB_PREFIX . 'accommodatie');
        if (!count($accos)) {
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "acco_type` (id, title, datum) VALUES ('1', 'Kampeerplaats', NOW() )";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "camping` (id, title, email_header, email_footer, confirm_tekst, age_youth, age_child, age_baby)
                  VALUES ('1', 'Voorbeeld camping', 'Bedankt voor uw boeking', 'Met vriendelijke groet, het Campingteam', 'Ik ga akkoord met de voorwaarden', '10-17', '2-9', '0-1');";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "accommodatie` (id, title, name, camping_id, acco_type_id, aantal_personen, aantal_slaapkamers, omschrijving, samenvatting)
                  VALUES ('1', 'Voorbeeld accommodatie', 'voorbeeld-accommodatie', '1', '1', '6', '2', 'Voorbeeld tekst accommodatie', 'Voorbeeld samenvatting');";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "faciliteiten` (id, title, is_camping, is_filter) VALUES ('1', 'TV', '0', '1')";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "faciliteiten` (id, title, is_camping, is_filter) VALUES ('2', 'Internet', '1', '0')";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "faciliteiten_per` VALUES ('1', '1', '1', '0')";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "faciliteiten_per` VALUES ('2', '2', '0', '1')";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "periode` (id, camping_id, acco_type_id, van, tot, naam, ma, di, wo, do, vr, za, zo, min_nachten) VALUES ('1', '1', '1', '" . date('Y') . "-01-01', '" . date('Y') . "-12-31', '', '1', '1', '1', '1', '1', '1', '1', '1')";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "prijs` VALUES ('1', '1', '1', '20', '19', '50', '42', '65', '52', '100', '88', '0', '0', '2', '1', '2', '0');";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "toeslagen` (id, title, type, per, camping_id) VALUES ('1', 'Eindschoonmaak', 'ja/nee', 'verblijf', '1');";
            $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "toeslagen_per` VALUES ('1', '1', '1', '20')";
        }
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value, multilang) VALUES ('1', 'Zoek button', 'btn-zoeken', 'Zoek en boek', 1);";
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value, multilang) VALUES ('2', 'Persuasion', 'pursuision', 'Al meer dan 1000 bezoekers gingen u voor!', 1);";
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value) VALUES ('3', 'Vanaf datum', 'vanaf-datum', '');";
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value) VALUES ('7', 'Standaard aantal nachten', 'std-aantal-nachten', '7');";
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value, input_type) VALUES ('8', 'Conversie codes', 'conversie-codes', '', 'textarea');";
        $sql[] = "INSERT INTO `" . SIMPEL_DB_PREFIX . "settings` (id, title, field, value, input_type) VALUES ('9', 'Prijs incl. verpl. toeslagen', 'prijs-incl-verpl-toeslagen', '0', 'checkbox');";
        
        $sql[] = "UPDATE `" . SIMPEL_DB_PREFIX . "settings` set multilang = '1' where id IN (1, 2)";
        $sql[] = "DELETE FROM  `" . SIMPEL_DB_PREFIX . "settings` where field IN ('multicamping', 'bootstrap', 'facebook-id', 'klant-type')";

        foreach ($sql as $query) {
            $wpdb->query($query);
        }
    }

}
