<?php

/******************************************************************************
 * iSearch2 - website search engine                                           *
 *                                                                            *
 * Visit the iSearch homepage at http://www.iSearchTheNet.com/isearch         *
 *                                                                            *
 * Copyright (C) 2002-2005 Ian Willis. All rights reserved.                   *
 *                                                                            *
 ******************************************************************************/

// PHPLOCKITOPT NOENCODE


$isearch_groupNames = array();
for ($i = 0; $i < count($isearch_config['groups']); $i += 3)
{
    $isearch_groupNames[] = $isearch_config['groups'][$i];
}
if (!isset($group))
{
    $group = '';
}


if ($isearch_config['char_set_8_bit'])
{
    $trans = get_html_translation_table(HTML_ENTITIES);
	$htmlSearchString = strtr($s, $trans);
}
else
{
    $trans = get_html_translation_table(HTML_SPECIALCHARS);
	$htmlSearchString = htmlentities($s, ENT_COMPAT, "UTF-8");
}





if ($advanced)
{
echo '<form method="post" action="index.php" target="'.$isearch_config['results_frame'].'" name="isearch_form">

';
    echo '
<script>
<!--
function searchClickedAdvanced(form)
{
    if ((form.s_all.value==\'\') && (form.s_any.value==\'\') && (form.s_without.value==\'\') && ((form.s_exact == null) || (form.s_exact.value==\'\')))
    {
        alert("Please enter at least 1 term to search for.");
        return false;
    }
    return true;
}
// -->
</script>

<table  class="form-table isearch_advanced">
';
    if (!isset($s_all))
    {
        $s_all = '';
    }
    if (!isset($s_any))
    {
        $s_any = '';
    }
    if (!isset($s_without))
    {
        $s_without = '';
    }
    echo '<tr><td class="text-right">'.$isearch_lang['with_all'].'</td><td><input maxLength="255" name="s_all" size="'.$isearch_config['search_box_width'].'" value="'.$s_all.'" class="form-control"></td></tr>
<tr><td class="text-right">'.$isearch_lang['with_any'].'</td><td><input maxLength="255" name="s_any" size="'.$isearch_config['search_box_width'].'" value="'.$s_any.'" class="form-control"></td></tr>
';

    if (!isset($s_exact))
    {
        $s_exact = '';
    }
    echo '<tr><td class="text-right">'.$isearch_lang['with_exact'].'</td><td><input maxLength="255" name="s_exact" size="'.$isearch_config['search_box_width'].'" value="'.$s_exact.'" class="form-control"></td></tr>
';


    if (!$isearch_config['allow_dashes'])
    {
        echo '<tr><td class="text-right">'.$isearch_lang['without'].'</td><td><input maxLength="255" name="s_without" size="'.$isearch_config['search_box_width'].'" value="'.$s_without.'" class="form-control"></td></tr>

';
    }

    $checked = $partial ? ' checked' : '';
    echo '<tr><td class="text-right">'.$isearch_lang['partial'].':</td><td><input type="checkbox" name="partial"'.$checked.'></td></tr>';


    if (isset($isearch_groupNames[0]))
    {
        if ($isearch_config['form_show_groups'] == 2)
        {
            echo '<tr><td>&nbsp;</td><td><select name="groups[]" multiple class="form-control">';
        }
        else
        {
            echo '<tr><td>&nbsp;</td><td><select name="group" class="form-control">';
        }

        echo '<option value="isearch_all">All</option>';

        for ($i = 0; $i < count($isearch_config['groups']); $i += 3)
        {
            $selected = preg_match('/^(.*,)?'.$isearch_config['groups'][$i].'(,.*)?$/i', $group) ? ' selected' : '';
            echo '<option VALUE="'.$isearch_config['groups'][$i].'"'.$selected.'>'.$isearch_config['groups'][$i]."</option>";
        }

        echo "</select></td></tr>\n";
    } // end groups


   
    if ($isearch_config['search_internet'])
    {
        echo '<tr><td>&nbsp;</td><td><button type="submit" class="btn btn-primary" onClick="return searchClickedAdvanced(this.form);">'.$isearch_lang['searchbutton'].'</button></td>';
        //echo '<td align=center><input type="submit" name="internet" value="'.$isearch_lang['searchinternetbutton'].'" onClick="return searchClickedAdvanced(this.form);"></td>';
		echo '</tr>';
    }
    else
    {
        echo '<tr><td colspan=2><button type="submit" class="btn btn-primary" onClick="return searchClickedAdvanced(this.form);">'.$isearch_lang['searchbutton'].'</button></td></tr>';
    }

    echo '<tr><td>&nbsp;</td>
<td ><a class="btn btn-default btn-secondary" href="index.php">'.$isearch_lang['simple'].'</a> <a target="_blank" class="btn btn-default btn-secondary" href="help/help.php">'.$isearch_lang['helpbutton'].'</a></td></tr>
</table>
';
echo '

<input type="hidden" name="CSRFtoken" value="'.$CSRFtoken.'">
<input type="hidden" name="action" value="search">
</form>
';
} // end advanced
else
{
	/* DEPRACATED 
echo"<!-- Search Google  -->
<h2>Search the rest of the web</h2>
<script>
  (function() {
    var cx = '011925898223408055577:qjw9olzjznc';
    var gcse = document.createElement('script');
    gcse.type = 'text/javascript';
    gcse.async = true;
    gcse.src = 'https://cse.google.com/cse.js?cx=' + cx;
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gcse, s);
  })();
</script>
<gcse:search></gcse:search>
<!-- Search Google -->
";
   
   */
 if ($isearch_config['form_show_advanced'])
    {
        echo '<p><a href="index.php?advanced=1&CSRFtoken='.$CSRFtoken.'" class="btn btn-default btn-secondary">'.$isearch_lang['advanced'].'</a></p>';
    }

   
}



?>
