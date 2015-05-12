<?php $font_family = 'Arial'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title>Bevestiginsmail</title>
	<style>
		body, td {
			font-family: <?php echo $font_family ?>;
			font-size: 14px;
			margin: 0px;
			line-height: 20px;
		}

		.totaal {
			font-weight: bold;
			border-top: 1px solid #000;
		}
	</style>
</head>
<body style="font-family:<?php echo $font_family ?>; font-size:14px; margin:0px; line-height: 20px; ">
<table width="600" cellspacing='0' cellpadding="0" border="0" align="center">
	<tr>
		<td><a href='<?php echo site_url() ?>'>
				<img src='<?php echo $accommodatie->camping->logo_url ?>' alt="<?php echo $accommodatie->camping->title ?>" border='0'/>
			</a></td>
	</tr>
	<tr>
		<td><br/>Geachte <?php echo $boeken['voornaam'] ?> <?php echo $boeken['achternaam'] ?>,<br/><br/>
			<?php echo nl2br( $accommodatie->camping->email_header ) ?>
			<br/><br/></td>
	</tr>
	<?php if ( $arrangement_html ) : ?>
		<tr>
			<td><?= utf8_decode(nl2br($arrangement_html)) ?><br/><br/></td>
		</tr>
	<?php endif; ?>
	<tr>
		<td>
			<table width="400">
				<tr>
					<td width="220">Accommodatie</td>
					<td><?php echo $accommodatie->title ?></td>
				</tr>
				<tr>
					<td>Aankomst</td>
					<td><?php echo $boeken['aankomst'] ?></td>
				</tr>
				<tr>
					<td>Vertrek</td>
					<td><?php echo $boeken['vertrek'] ?></td>
				</tr>
				<tr>
					<td colspan="2"><br/><strong>Kosten</strong></td>
				</tr>
				<?php echo $kassabon ?>
				<tr>
					<td colspan="2"><br/><strong>Persoonsgegevens</strong></td>
				</tr>
				<tr>
					<td>Naam</td>
					<td><?php echo $boeken['voornaam'] ?> <?php echo $boeken['achternaam'] ?></td>
				</tr>
				<?php if ( $boeken['telefoon'] ) { ?>
					<tr>
						<td>Telefoon</td>
						<td><?php echo $boeken['telefoon'] ?></td>
					</tr>
				<?php } ?>
				<tr>
					<td>E-mailadres</td>
					<td><?php echo $boeken['email'] ?></td>
				</tr>
				<tr>
					<td>Aantal volwassenen</td>
					<td><?php echo $boeken['volw'] ?> volwassene<?php echo( $boeken['volw'] > 1 ? 'n' : '' ) ?></td>
				</tr>
				<?php if ( $accommodatie->camping->age_youth ) : ?>
					<tr>
						<td>Aantal jeugd (<?php echo $accommodatie->camping->age_youth ?> jaar)</td>
						<td><?php echo $boeken['youth'] ?> kinderen</td>
					</tr>
				<?php endif; ?>
				<?php if ( $accommodatie->camping->age_child ) : ?>
					<tr>
						<td>Aantal kinderen (<?php echo $accommodatie->camping->age_child ?> jaar)</td>
						<td><?php echo $boeken['kind'] ?> kinderen</td>
					</tr>
				<?php endif; ?>
				<?php if ( $accommodatie->camping->age_baby ) : ?>
					<tr>
						<td>Aantal baby's (<?php echo $accommodatie->camping->age_baby ?> jaar)</td>
						<td><?php echo $boeken['baby'] ?> baby's</td>
					</tr>
				<?php endif; ?>
				<?php if ( $boeken['opmerkingen'] ) : ?>
					<tr>
						<td>Opmerkingen</td>
						<td><?php echo $boeken['opmerkingen'] ?></td>
					</tr>
				<?php endif; ?>
				<?php if ( $boeken['plaats_voorkeur'] ) : ?>
					<tr>
						<td>Voorkeursplaats</td>
						<td><?php echo $boeken['plaats_voorkeur'] ?></td>
					</tr>
				<?php endif; ?>

				<?php if ( count( $this->session->zoek ) ) : ?>
					<tr>
						<td valign="top">Voorkeuren</td>
						<td>
							<?php foreach ( $this->session->zoek as $voorkeur ) : ?>
								<?php echo $voorkeur ?><br/>
							<?php endforeach; ?>
						</td>
					</tr>
				<?php endif; ?>
			</table>
		</td>
	</tr>
	<tr>
		<td><br/>
			<?php echo nl2br( $accommodatie->camping->email_footer ) ?></td>
	</tr>
</table>
</body>
</html>
