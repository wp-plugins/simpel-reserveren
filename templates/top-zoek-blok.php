<div id="top-zoeken" class="zoeken">
    <div class="inner">
        <h2>Bekijk beschikbaarheid en actuele prijzen</h2>
    	<form method="get" action="<?php echo  (SITE == 'stoetenslagh' ? '/zoek-en-boek/' : site_url() . '/zoeken/' ) ?>">
        <table>
            <tr>
                <td><label for="type">Type</label></td>
                <td>
                    <select name="type" id="type">
                        <option value="">Geen voorkeur</option>
                        <? foreach($types as $row) : ?>
                            <option value="<?php echo $row->id?>" <?php echo ($type == $row->id ? 'selected' : '')?>><?php echo $row->title?>
                        <? endforeach; ?>
                    </select> 
                </td>
                <td><label for="top-aankomst">Aankomst</label></td>
                <td><input type="text" name="aankomst" id="top-aankomst" value="<?php echo $aankomst?>" size="12"/></td>
                <td><label for="top-vertrek">Vertrek</label></td>
                <td><input type="text" name="vertrek" id="top-vertrek" value="<?php echo $vertrek?>" size="12"/></td>
            </tr>
            <tr>
                <td><label for="volw">Volwassenen</label></td>
                <td>
                    <select name="volw" id="volw" style="width:130px">
                        <? for($i=1; $i<=8; $i++) { ?>
                            <option value="<?php echo $i?>" <?php echo ($volw == $i ? 'selected' : '')?>><?php echo $i?> Volwassenen</option>
                        <? } ?>
                    </select>
                </td>
                <td><label for="volw">Kinderen</label></td>
                <td>
                    <select name="kind" id="kind" style="width:130px">
                    	<? for($i=0; $i<=8; $i++) { ?>
                			<option value="<?php echo $i?>" <?php echo ($kind == $i ? 'selected' : '')?>><?php echo $i?> Kinderen</option>
                        <? } ?>
                	</select>
                </td>
                <td colspan="2">
                    <input type="submit" value="Nu Zoeken"/>
                    <? if(isset($_GET['refer'])) : ?>
                        <input type="hidden" name="refer" value="<?php echo  htmlentities($_GET['refer']) ?>" />
                    <? endif; ?>
                </td>
            </tr>
        </table>
        </form>
        <div class="clear"></div>
    </div>
</div>