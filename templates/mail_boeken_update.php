<?php 	$font_family = 'Arial'; ?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Update boekingsgegevens</title>
<style>
body, td{ font-family:<?php echo $font_family ?>; font-size:14px; margin:0px; line-height: 20px; }
.totaal{ font-weight: bold; border-top: 1px solid #000; }
</style>
</head>
<body style="font-family:<?php echo  $font_family ?>; font-size:14px; margin:0px; line-height: 20px; ">
<table width="600" cellspacing='0' cellpadding="0" border="0" align="center">
	<tr>
		<td><a href='<?php echo  site_url() ?>'>
		<img src='<?php echo  $accommodatie->camping->logo_url ?>' alt="<?php echo utf8_decode($accommodatie->camping->title) ?>" border='0'/>
		</a></td>
	</tr> 
	<tr>
		<td><br/>Geachte <?php echo $boeken->voornaam?> <?php echo $boeken->achternaam?>,<br/><br/>
		Uw gegevens zijn zojuist opgeslagen
        <br/><br/></td>
	</tr>
	<tr>
		<td>
		<table width="400">
			<tr>
				<td width="220">Accommodatie</td>
				<td><?php echo  $accommodatie->title ?></td>
			</tr>
			<tr>
				<td>Aankomst</td>
				<td><?php echo  date('d-m-Y', strtotime($boeken->datum_aankomst)) ?></td>
			</tr>
			<tr>
				<td>Vertrek</td>
				<td><?php echo  date('d-m-Y', strtotime($boeken->datum_vertrek)) ?></td>
			</tr>
			<tr>
				<td colspan="2"><br/><strong>Persoonsgegevens</strong></td>
			</tr>
			<tr>
				<td>Naam</td>
				<td><?php echo  $boeken->voornaam ?> <?php echo $boeken->achternaam?></td>
			</tr>
            <tr>
				<td>Adres + huisnummer</td>
				<td><?php echo $boeken->adres?></td>
			</tr>
            <tr>
				<td>Postcode / Plaats</td>
				<td><?php echo $boeken->postcode?> <?php echo $boeken->plaats?></td>
			</tr>
            <tr>
				<td>Telefoon</td>
				<td><?php echo $boeken->telefoon?></td>
			</tr>
            <tr>
				<td>Factuur per post</td>
				<td><?php echo  ($boeken->factuur_per_post ? 'Ja' : 'Nee') ?></td>
			</tr>
		</table>
		</td>
	</tr>
	<tr>
		<td><br/>
		Mochten er gegevens niet kloppen, dan kunt u altijd contact met ons opnemen via <?php echo  $accommodatie->camping->email ?><br/><br/>
		Met vriendelijke groet,<br/>
		<?php echo utf8_decode($accommodatie->camping->title) ?></td>
	</tr>
</table>
</body>
</html>
