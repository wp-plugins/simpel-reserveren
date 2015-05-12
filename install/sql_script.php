
-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `min_nachten` int(9) NOT NULL,
  `nachten_korting` int(9) NOT NULL,
  `perc_korting` int(6) NOT NULL,
  `datum` datetime NOT NULL,
  `voorwaarden` text NOT NULL,
  `omschrijving` varchar(255) NOT NULL,
  `camping_id` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen` VALUES ('1', '14=10 nachten', '14', '4', '0', '<?php echo  date('Y') ?>-04-10 15:46:47', '14 nachten boeken, betalen voor 10 nachten. Gehele jaar geldig!', '14=10', '1');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen_per`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen_per` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `aanbieding_id` int(9) NOT NULL DEFAULT '0',
  `periode_id` int(9) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `per` (`periode_id`) USING BTREE,
  KEY `acc` (`accommodatie_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen_per`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>aanbiedingen_per` VALUES ('1', '1', '1', '1');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>acco_type`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>acco_type` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>acco_type`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>acco_type` VALUES ('2', 'Vakantiehuizen', '<?php echo  date('Y') ?>-02-15 10:38:30'), ('10', 'Chalets', '<?php echo  date('Y') ?>-02-15 10:38:37'), ('11', 'Huur caravans', '<?php echo  date('Y') ?>-04-09 11:57:33'), ('12', 'Bungalows', '<?php echo  date('Y') ?>-04-09 11:59:31');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `camping_id` int(9) NOT NULL DEFAULT '0',
  `acco_type_id` int(9) NOT NULL DEFAULT '0',
  `sterren` int(9) NOT NULL,
  `aantal_personen` int(9) NOT NULL DEFAULT '0',
  `aantal_slaapkamers` int(9) NOT NULL,
  `huisdieren_toegestaan` int(1) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `omschrijving` text NOT NULL,
  `omschrijving_nl` text NOT NULL,
  `samenvatting` text NOT NULL,
  `samenvatting_nl` text NOT NULL,
  `afbeelding` varchar(255) NOT NULL,
  `seq` int(9) NOT NULL DEFAULT '0',
  `seq_inner` int(9) NOT NULL DEFAULT '0',
  `aankomst_tijd` varchar(255) NOT NULL,
  `vertrek_tijd` varchar(255) NOT NULL,
  `min_aantal_nachten` int(9) NOT NULL DEFAULT '1',
  `samenvatting_de` varchar(255) DEFAULT NULL,
  `omschrijving_de` text,
  `button_url` varchar(255) DEFAULT NULL,
  `button_tekst_nl` varchar(255) DEFAULT NULL,
  `button_tekst_de` varchar(255) DEFAULT NULL,
  `vanaf_prijs` float(9,2) DEFAULT NULL,
  `bekeken` int(9) NOT NULL,
  `samenvatting_en` varchar(255) DEFAULT NULL,
  `omschrijving_en` text,
  `button_tekst_en` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camping` (`camping_id`) USING BTREE,
  KEY `type` (`acco_type_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie` VALUES ('1', 'Bungalow 6 persoons', '1', '12', '4', '6', '3', '0', '<?php echo  date('Y') ?>-04-11 14:32:47', 'Wij hebben voor u compleet ingerichte vrijstaande 6 persoons vakantiehuizen, met vlak voor uw deur een eigen ligplaats voor uw boot. Van het uitzicht zult u zeker onder de indruk zijn.\r\nDeze nieuwe vakantiehuizen (bouwjaar 2004) zijn geschikt voor maximaal zes personen. De vakantiehuizen zijn voorzien van cv, volledige keukeninventaris, gasfornuis, koelkast, afzuigkap, koffiezet-\r\napparaat, servies, bestek, kleuren-tv en tuinmeubels.\r\n\r\nDe begane grond kent de volgende indeling: woonkamer, badkamer met douche en ligbad en een apart toilet. De bovenverdieping telt drie slaapkamers met elk twee eenpersoonsbedden. Per bed zijn éénpersoons dekbedden en een hoofdkussen aanwezig. Slopen, onderlakens en dekbedhoezen (éénpersoons) dient u zelf mee te nemen, evenals huishoudlinnen. Linnengoed is eventueel te huur bij de receptie.\r\n\r\nHuisdieren zijn niet toegestaan in deze bungalows', '', 'Wij hebben voor u compleet ingerichte vrijstaande vakantiehuizen, met vlak voor uw deur een eigen ligplaats voor uw boot. Van het uitzicht zult u zeker onder de indruk zijn.', '', '6-persoons bungalow.JPG', '38', '1', '15.00 uur', '10.00 uur', '1', null, null, null, null, null, '325.00', '7', null, null, null);

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_foto`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_foto` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) NOT NULL DEFAULT '',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`foto`,`accommodatie_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_foto`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_foto` VALUES ('1', '6-persoons bungalows.JPG', '1', '<?php echo  date('Y') ?>-04-08 11:57:23'), ('2', 'bungalow 11a.JPG', '1', '<?php echo  date('Y') ?>-04-08 11:57:28'), ('4', 'Keuken 6 persoons klein.JPG', '1', '<?php echo  date('Y') ?>-04-08 12:11:06'), ('5', 'Woonkamer 6 persoons klein.JPG', '1', '<?php echo  date('Y') ?>-04-08 12:12:43'), ('51', 'Friesland camping Bergumermeer.vakantiehuizen 2 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:44'), ('32', 'Friesland camping Bergumermeer.Stacaravan 1 klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:19:56'), ('8', 'bungalow5.JPG', '5', '<?php echo  date('Y') ?>-04-08 12:22:10'), ('9', 'bungalow 7 tm 10.JPG', '5', '<?php echo  date('Y') ?>-04-08 12:22:18'), ('10', 'P1030473.JPG', '5', '<?php echo  date('Y') ?>-04-08 12:23:25'), ('11', 'P1030477.JPG', '5', '<?php echo  date('Y') ?>-04-08 12:25:18'), ('12', 'buitenplaats bp (14).jpg klein.jpg', '6', '<?php echo  date('Y') ?>-04-08 12:47:54'), ('13', 'buitenplaats bp (7). klein.jpg', '6', '<?php echo  date('Y') ?>-04-08 12:48:01'), ('114', 'bootverhuur 14 08 2012 020klein.jpg', '6', '<?php echo  date('Y') ?>-04-10 13:58:20'), ('16', 'B326 Achterkant kl.JPG', '6', '<?php echo  date('Y') ?>-04-08 12:50:30'), ('17', 'Voorkant B.326.jpg klein.jpg 1.jpg', '6', '<?php echo  date('Y') ?>-04-08 12:52:08'), ('18', 'Woonkamer WAld verkl.JPG', '7', '<?php echo  date('Y') ?>-04-08 12:58:04'), ('19', 'Keuken WALD verkl.JPG', '7', '<?php echo  date('Y') ?>-04-08 12:58:24'), ('20', 'Badkamer verkl.JPG', '7', '<?php echo  date('Y') ?>-04-08 12:58:35'), ('21', 'B. 268 Zonnebank.jpg klein.jpg', '7', '<?php echo  date('Y') ?>-04-08 13:05:16'), ('22', 'Friesland huis met terras.jpg klein.jpg', '8', '<?php echo  date('Y') ?>-04-08 13:19:12'), ('23', 'B. 515 tuin 2.JPG', '8', '<?php echo  date('Y') ?>-04-08 13:20:13'), ('24', 'DSC04600_4.jpg', '8', '<?php echo  date('Y') ?>-04-08 13:21:38'), ('25', 'Friesland luchtfoto.jpg klein.jpg', '8', '<?php echo  date('Y') ?>-04-08 13:23:20'), ('26', 'B. 230 Tuin.JPG', '9', '<?php echo  date('Y') ?>-04-08 13:31:10'), ('27', 'B. 230 uitzichtverkleind.JPG', '9', '<?php echo  date('Y') ?>-04-08 13:31:22'), ('28', 'CIMG1198.JPG  klein.JPG', '9', '<?php echo  date('Y') ?>-04-08 13:33:10'), ('29', 'DSC00523.JPG klein.JPG', '9', '<?php echo  date('Y') ?>-04-08 13:42:15'), ('30', 'Zonnebank en Sauna klein.JPG', '10', '<?php echo  date('Y') ?>-04-08 13:47:14'), ('31', 'Badkamer beneden klein.JPG', '10', '<?php echo  date('Y') ?>-04-08 13:47:23'), ('33', 'Friesland camping Bergumermeer.Stacaravan 2klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:19:57'), ('34', 'Friesland camping Bergumermeer.Stacaravan 3klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:19:58'), ('35', 'Friesland camping Bergumermeer.Stacaravan 4klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:19:59'), ('36', 'Friesland camping Bergumermeer.Stacaravan 5klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:20:00'), ('37', 'Friesland camping Bergumermeer.Stacaravan 6klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:20:01'), ('38', 'Friesland camping Bergumermeer.Stacaravan 7klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:20:02'), ('39', 'Friesland camping Bergumermeer.Stacaravan 8 klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:20:04'), ('40', 'Friesland camping Bergumermeer.Stacaravan 10 klein.jpg', '13', '<?php echo  date('Y') ?>-04-09 12:20:05'), ('42', 'Friesland camping Bergumermeer.Villa 14 _ 2klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:52'), ('43', 'Friesland camping Bergumermeer.Villa 14 _ 3klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:53'), ('44', 'Friesland camping Bergumermeer.Villa 14 _ 4klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:54'), ('45', 'Friesland camping Bergumermeer.Villa 14 _ 5klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:55'), ('46', 'Friesland camping Bergumermeer.Villa 14 _ 6klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:56'), ('47', 'Friesland camping Bergumermeer.Villa 14 _ 7klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:57'), ('48', 'Friesland camping Bergumermeer.Villa 14 _ 9klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:58'), ('49', 'Friesland camping Bergumermeer.Villa 14 _ 10 klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:21:59'), ('50', 'Friesland camping Bergumermeer.Villa 14 _ 11 klein.jpg', '11', '<?php echo  date('Y') ?>-04-09 12:22:02'), ('52', 'Friesland camping Bergumermeer.vakantiehuizen 3 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:45'), ('53', 'Friesland camping Bergumermeer.vakantiehuizen 4 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:46'), ('54', 'Friesland camping Bergumermeer.vakantiehuizen 5 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:47'), ('55', 'Friesland camping Bergumermeer.vakantiehuizen 6 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:48'), ('56', 'Friesland camping Bergumermeer.vakantiehuizen 7 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:49'), ('57', 'Friesland camping Bergumermeer.vakantiehuizen 8 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:50'), ('58', 'Friesland camping Bergumermeer.vakantiehuizen 9 klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:51'), ('59', 'Friesland camping Bergumermeer.villa 10 _ 1klein.jpg', '3', '<?php echo  date('Y') ?>-04-09 12:22:52'), ('60', 'Bungalette9.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:04'), ('61', 'Friesland bungalowpark Bergumermeer.bungalette 3.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:06'), ('62', 'Friesland bungalowpark Bergumermeer.bungalette 4.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:07'), ('63', 'Friesland bungalowpark Bergumermeer.bungalette 5.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:08'), ('64', 'Friesland bungalowpark Bergumermeer.chalet 1.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:09'), ('65', 'Frieslandbungalowpark Bergumermeer.bungalette 2.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:10'), ('66', 'Bangalette1.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:41'), ('67', 'Bangalette2.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:42'), ('68', 'Bungalette3.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:43'), ('69', 'Bungalette4.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:45'), ('70', 'Bungalette5.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:46'), ('71', 'Bungalette6.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:47'), ('72', 'Bungalette7.jpg', '12', '<?php echo  date('Y') ?>-04-09 12:24:48'), ('73', 'Friesland camping Bergumermeer.vakantiehuizen 2 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:16'), ('74', 'Friesland camping Bergumermeer.vakantiehuizen 3 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:17'), ('75', 'Friesland camping Bergumermeer.vakantiehuizen 4 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:18'), ('76', 'Friesland camping Bergumermeer.vakantiehuizen 5 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:19'), ('77', 'Friesland camping Bergumermeer.vakantiehuizen 6 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:20'), ('78', 'Friesland camping Bergumermeer.vakantiehuizen 7 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:21'), ('79', 'Friesland camping Bergumermeer.vakantiehuizen 8 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:22'), ('80', 'Friesland camping Bergumermeer.vakantiehuizen 9 klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:23'), ('81', 'Friesland camping Bergumermeer.villa 10 _ 1klein.jpg', '14', '<?php echo  date('Y') ?>-04-09 12:27:25'), ('83', 'Villa 17A _ 2.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:30:53'), ('96', 'Friesland camping Bergumermeer.vakantiehuizen 3 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:35:57'), ('85', 'Villa 17A _ 2 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:06'), ('86', 'Villa 17A _ 3 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:17'), ('88', 'Villa 17A _ 5 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:47'), ('89', 'Villa 17A _ 6 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:49'), ('91', 'Villa 17A _ 8 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:52'), ('92', 'Villa 17A _ 9 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:53'), ('95', 'Friesland camping Bergumermeer.vakantiehuizen 2 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:35:56'), ('94', 'Villa2 klein.jpg', '16', '<?php echo  date('Y') ?>-04-09 12:34:55'), ('97', 'Friesland camping Bergumermeer.vakantiehuizen 4 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:35:58'), ('98', 'Friesland camping Bergumermeer.vakantiehuizen 5 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:35:59'), ('99', 'Friesland camping Bergumermeer.vakantiehuizen 6 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:36:00'), ('100', 'Friesland camping Bergumermeer.vakantiehuizen 7 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:36:01'), ('101', 'Friesland camping Bergumermeer.vakantiehuizen 8 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:36:02'), ('102', 'Friesland camping Bergumermeer.vakantiehuizen 9 klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:36:03'), ('103', 'Friesland camping Bergumermeer.villa 10 _ 1klein.jpg', '15', '<?php echo  date('Y') ?>-04-09 12:36:04'), ('107', 'Relax ruimte Klein.jpg', '17', '<?php echo  date('Y') ?>-04-10 12:58:32'), ('121', '06_comfort_chalet_buitenkant.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:20:36'), ('110', '0646klein.jpg', '1', '<?php echo  date('Y') ?>-04-10 13:55:01'), ('111', 'Afbeelding 064.jpg', '5', '<?php echo  date('Y') ?>-04-10 13:55:33'), ('112', 'DSC_0013.JPG', '5', '<?php echo  date('Y') ?>-04-10 13:55:59'), ('113', 'DSC_0013klein.JPG', '5', '<?php echo  date('Y') ?>-04-10 13:56:31'), ('128', '101.JPG', '4', '<?php echo  date('Y') ?>-04-17 14:29:04'), ('116', 'DSC00870-1klein.JPG', '6', '<?php echo  date('Y') ?>-04-10 13:59:17'), ('129', '104-1.JPG', '4', '<?php echo  date('Y') ?>-04-17 14:29:39'), ('118', 'natuur achter camping vorjaar 2009 042klein.jpg', '7', '<?php echo  date('Y') ?>-04-10 14:02:22'), ('122', '05_comfort_chalet_buitenkant.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:21:01'), ('124', 'Kopie van _MG_5726 kopie.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:26:28'), ('125', '_MG_5639 kopie.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:26:47'), ('126', '_MG_5367 kopie.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:27:20'), ('130', 'Dinerbon2.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:30:20'), ('131', 'Dinerbon4.jpg', '4', '<?php echo  date('Y') ?>-04-17 14:30:34');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_randomized`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_randomized` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_randomized`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>accommodatie_randomized` VALUES ('5', '<?php echo  date('Y') ?>-04-24'), ('6', '<?php echo  date('Y') ?>-04-25'), ('7', '<?php echo  date('Y') ?>-04-26'), ('8', '<?php echo  date('Y') ?>-04-29'), ('9', '<?php echo  date('Y') ?>-04-30');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>beschikbaarheid`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>beschikbaarheid` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `dagen` text NOT NULL,
  `jaar` int(4) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `jaar` (`jaar`) USING BTREE,
  KEY `acco` (`accommodatie_id`) USING BTREE,
  FULLTEXT KEY `beschikbaar` (`dagen`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>beschikbaarheid`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>beschikbaarheid` VALUES ('1', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOA', '<?php echo  date('Y') ?>', '17'), ('2', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '1'), ('3', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '5'), ('4', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '9'), ('5', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOAAAAOOOAAAAAAAAAAAAAAAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '4'), ('6', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '11'), ('7', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAOOOOOOOOOOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '18'), ('8', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOAAAAOOOOOOAAAOOOOOOOOAAAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOAAAAAAAAAAAAAAAAAAAOOOOAAAOOOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '19'), ('9', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAOOOOOAAOOOAAOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '2'), ('10', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAOOOOOAAAAAAAAAOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOOOOOOOOOOOOOOOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '20'), ('11', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOAOOOOOOAAAOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '21'), ('12', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOAAAAAOOOOOOOOOOOOOOOOAAAAAAAAAOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOOOOOOAOOOOOOOAOOOOOOOOOOOOOOAAAAAOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '15'), ('13', 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAAAAAAAAAAAAAAAAAAAAOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAOOOOOOAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', '<?php echo  date('Y') ?>', '12');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>boeking`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>boeking` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `naam` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL,
  `voornaam` varchar(255) NOT NULL,
  `achternaam` varchar(255) NOT NULL,
  `datum_boeking` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `datum_aankomst` date NOT NULL DEFAULT '0000-00-00',
  `datum_vertrek` date NOT NULL DEFAULT '0000-00-00',
  `prijs` decimal(6,2) NOT NULL DEFAULT '0.00',
  `mail_html` text NOT NULL,
  `camping_id` int(9) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  `refer` varchar(255) NOT NULL DEFAULT '',
  `adres` varchar(255) DEFAULT NULL,
  `postcode` varchar(255) DEFAULT NULL,
  `plaats` varchar(255) DEFAULT NULL,
  `telefoon` varchar(255) DEFAULT NULL,
  `factuur_per_post` int(1) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `update_send` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>boeking`
-- ----------------------------

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>camping`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>camping` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `plaats` varchar(255) NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `email` varchar(255) NOT NULL DEFAULT '',
  `email_header` text NOT NULL,
  `email_footer` text NOT NULL,
  `booking_footer` text NOT NULL,
  `plattegrond_url` varchar(255) DEFAULT NULL,
  `voorwaarden_url` varchar(255) DEFAULT NULL,
  `has_controle_stap` int(1) NOT NULL,
  `confirm_tekst` text,
  `stap3_naw` int(1) NOT NULL,
  `tweet_tekst` text,
  `facebook_tekst` text,
  `terug_button` int(1) NOT NULL,
  `terug_button_stap2` int(1) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `email_header_nl` text,
  `email_footer_nl` text,
  `confirm_tekst_nl` text,
  `booking_footer_nl` text,
  `email_header_en` text,
  `email_footer_en` text,
  `confirm_tekst_en` text,
  `booking_footer_en` text,
  `email_header_de` text,
  `email_footer_de` text,
  `confirm_tekst_de` text,
  `booking_footer_de` text,
  `txt_camping` text,
  `txt_camping_nl` text,
  `txt_camping_en` text,
  `txt_camping_de` text,
  `txt_omgeving` text,
  `txt_omgeving_nl` text,
  `txt_omgeving_en` text,
  `txt_omgeving_de` text,
  `seq` int(9) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>camping`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>camping` VALUES ('1', 'Voorbeeld Camping', 'Ons Dorp', '<?php echo  date('Y') ?>-04-29 17:15:10', 'info@uwemailadres.nl', 
  'Bedankt voor uw reserveringsaanvraag!', 
  'Wij bedanken voor uw reserveringsaanvraag, en zullen deze zo snel mogelijk invoeren en daarna per mail aan u bevestigen. Na deze mail is uw reservering definitief.\r\n\r\nIn deze mail staan ook de betalingsvoorwaarden en ons bankrekeningnummer.\r\nWij wensen u alvast een prettig verblijf op ons park.\r\n\r\nMet vriendelijke groet,\r\Voorbeeld Camping', 
  'Met het bevestigen van bovenstaande gegevens gaat u tevens akkoord met de algemene voorwaarden.\r\n\r\nU ontvangt een zo snel mogelijk een mail ter bevestiging van uw reserveringsaanvraag.\r\nBij vragen kunt u mailen met', 
  '', null, '0', 
  'Er is een bevestigingsmail naar u gestuurd.\r\nMocht er iets niet correct zijn, of heeft u vragen dan kunt u contact met ons opnemen.', 
  '0', 'std tweet', 'std facebook', '0', '0', 'i_park_logo (1).gif', '', '', '', '', null, null, null, null, null, null, null, null, 
  'Tekst camping', 'Tekst Camping', 
  null, null, 
  'Tekst omgeving', 'Tekst omgeving', 
  null, null, '807');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>camping_foto`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>camping_foto` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `foto` varchar(255) NOT NULL DEFAULT '',
  `camping_id` int(9) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni` (`foto`,`camping_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>camping_foto`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>camping_foto` VALUES ('1', 'DSC_1549.jpg', '3', '<?php echo  date('Y') ?>-04-03 09:17:40');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_en` varchar(255) DEFAULT NULL,
  `is_camping` int(1) NOT NULL DEFAULT '0',
  `is_filter` int(1) NOT NULL DEFAULT '0',
  `title_de` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camping` (`is_camping`) USING BTREE,
  KEY `filter` (`is_filter`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten` VALUES ('90', 'Sportveld', 'Sport field', '1', '1', 'Sportplatz'), ('91', 'Boot verhuur', 'Boat rental', '1', '1', 'Bootsverleih');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten_per`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten_per` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `faciliteit_id` int(9) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  `camping_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten_per`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>faciliteiten_per` VALUES ('231', '31', '5', '0'), ('230', '30', '5', '0');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>meldingen`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>meldingen` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `van` date NOT NULL,
  `tot` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `plaats` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>meldingen`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>meldingen` VALUES ('1', 'Het gehele jaar door: 14 dagen boeken, 10 betalen!', '<?php echo  date('Y') ?>-01-18', '2014-02-18', 'uitroepteken geel', 'zoeken, accommodatie');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>nachtprijzen`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>nachtprijzen` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `periode_id` int(9) NOT NULL,
  `accommodatie_id` int(9) NOT NULL,
  `datum` date NOT NULL,
  `type` varchar(255) NOT NULL,
  `nachtprijs` float(9,5) NOT NULL,
  `nachtprijs_ab` float(9,5) DEFAULT NULL,
  `incl_toeslagen` int(1) NOT NULL,
  `nr_personen_incl` int(9) DEFAULT NULL,
  `meerprijs_volw` float(9,5) DEFAULT NULL,
  `meerprijs_kind` float(9,5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `per` (`periode_id`) USING BTREE,
  KEY `acco` (`accommodatie_id`) USING BTREE,
  KEY `datum` (`datum`) USING BTREE,
  KEY `type` (`type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>nachtprijzen`
-- ----------------------------


-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>periode`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>periode` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `camping_id` int(9) NOT NULL DEFAULT '0',
  `acco_type_id` int(9) NOT NULL DEFAULT '0',
  `van` date NOT NULL DEFAULT '0000-00-00',
  `tot` date NOT NULL DEFAULT '0000-00-00',
  `naam` varchar(255) NOT NULL,
  `ma` int(1) NOT NULL DEFAULT '1',
  `di` int(1) NOT NULL DEFAULT '1',
  `wo` int(1) NOT NULL DEFAULT '1',
  `do` int(1) NOT NULL DEFAULT '1',
  `vr` int(1) NOT NULL DEFAULT '1',
  `za` int(1) NOT NULL DEFAULT '1',
  `zo` int(1) NOT NULL DEFAULT '1',
  `omschrijving` text,
  `alternatieve_aankomst_tijd` varchar(255) DEFAULT NULL,
  `alternatieve_vertrek_tijd` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `camping2` (`camping_id`) USING BTREE,
  KEY `type2` (`acco_type_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>periode`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>periode` VALUES ('1', '3', '2', '<?php echo  date('Y') ?>-04-19', '<?php echo  date('Y') ?>-04-22', 'Weekprijs', '1', '0', '0', '0', '1', '0', '0', null, null, null);

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>prijs`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>prijs` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `periode_id` int(9) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  `nachtprijs` decimal(6,2) NOT NULL DEFAULT '0.00',
  `nachtaanbieding` decimal(6,2) NOT NULL,
  `weekendprijs` decimal(6,2) NOT NULL,
  `weekendaanbieding` decimal(6,2) NOT NULL,
  `midweekprijs` decimal(6,2) NOT NULL,
  `midweekaanbieding` decimal(6,2) NOT NULL,
  `weekprijs` decimal(6,2) NOT NULL,
  `weekaanbieding` decimal(6,2) NOT NULL,
  `periodeprijs` decimal(6,2) NOT NULL,
  `periodeaanbieding` decimal(6,2) NOT NULL,
  `meerprijs_volw` decimal(6,2) NOT NULL,
  `meerprijs_kind` decimal(6,2) NOT NULL,
  `inclusief` int(9) NOT NULL DEFAULT '0',
  `inclusief_toeslagen` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `acco2` (`accommodatie_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>prijs`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>prijs` VALUES ('1', '1', '3', '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '365.00', '169.00', '0.00', '0.00', '0.00', '0.00', '0', '0');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>prijs_cache`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>prijs_cache` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `accommodatie_id` int(9) NOT NULL,
  `van` date NOT NULL,
  `tot` date NOT NULL,
  `volw` int(9) NOT NULL,
  `kind` int(9) NOT NULL,
  `added` date NOT NULL,
  `periodes` text,
  `prijs` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>prijs_cache`
-- ----------------------------

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>settings`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>settings` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `field` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `value_nl` text NOT NULL,
  `value_de` text NOT NULL,
  `value_en` text NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>settings`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>settings` VALUES ('1', 'pursuision', 'Meer dan 178 gasten gingen u  voor!', 'Meer dan 178 gasten gingen u  voor!', 'Mehr dan 10.00 gasten gehen sie vohr', 'More than 178 guests were before you', 'Pursuiasion zin voor zoek blok'), ('2', 'btn-zoeken', 'Zoek en Boek', 'Zoek en Boek', 'Suchen', 'Search houses', 'Tekst voor zoek button');

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `title_en` varchar(255) DEFAULT NULL,
  `title_de` varchar(255) DEFAULT NULL,
  `verplicht` int(1) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT '',
  `per` varchar(255) NOT NULL DEFAULT '',
  `camping_id` int(9) NOT NULL DEFAULT '0',
  `prijs_camping` decimal(6,2) NOT NULL DEFAULT '0.00',
  `percentage` decimal(5,2) NOT NULL DEFAULT '0.00',
  `seq` int(11) NOT NULL DEFAULT '0',
  `max` int(9) NOT NULL DEFAULT '0',
  `omschrijving` text NOT NULL,
  `borgsom` int(1) NOT NULL DEFAULT '0',
  `periodes` varchar(255) NOT NULL DEFAULT '',
  `arrangement` int(1) NOT NULL,
  `afbeelding` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `toeslag_camping` (`camping_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen` VALUES ('39', 'Toeristenbelasting', 'Touristtax', 'Kurtaxe', '1', 'ja/nee', 'p.persoon p.nacht', '2', '1.00', '0.00', '0', '0', '', '0', '', '0', null);

-- ----------------------------
--  Table structure for `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen_per`
-- ----------------------------
CREATE TABLE IF NOT EXISTS `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen_per` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `toeslag_id` int(9) NOT NULL DEFAULT '0',
  `accommodatie_id` int(9) NOT NULL DEFAULT '0',
  `prijs` decimal(6,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen_per`
-- ----------------------------
INSERT INTO `<?php echo  SIMPEL_DB_PREFIX ?>toeslagen_per` VALUES ('80', '3', '10', '52.50'), ('79', '3', '9', '52.50'), ('78', '3', '8', '52.50');
