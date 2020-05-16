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
}
else
{
    $trans = get_html_translation_table(HTML_SPECIALCHARS);
}

$htmlSearchString = strtr($s, $trans);

echo '<FORM method="post" action="index.php" target="'.$isearch_config['results_frame'].'" name="isearch_form">
<CENTER>
';

if ($advanced)
{
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

<TABLE border="0" cellpadding="3" cellspacing="1" CLASS="isearch_advanced">
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
    echo '<TR><TD>'.$isearch_lang['with_all'].'</TD><TD><INPUT maxLength="255" name="s_all" size="'.$isearch_config['search_box_width'].'" value="'.$s_all.'"></TD></TR>
<TR><TD>'.$isearch_lang['with_any'].'</TD><TD><INPUT maxLength="255" name="s_any" size="'.$isearch_config['search_box_width'].'" value="'.$s_any.'"></TD></TR>
';

    if (!isset($s_exact))
    {
        $s_exact = '';
    }
    echo '<TR><TD>'.$isearch_lang['with_exact'].'</TD><TD><INPUT maxLength="255" name="s_exact" size="'.$isearch_config['search_box_width'].'" value="'.$s_exact.'"></TD></TR>
';


    if (!$isearch_config['allow_dashes'])
    {
        echo '<TR><TD>'.$isearch_lang['without'].'</TD><TD><INPUT maxLength="255" name="s_without" size="'.$isearch_config['search_box_width'].'" value="'.$s_without.'"></TD></TR>
<TR><TD COLSPAN=2>&nbsp;</TD></TR>
';
    }

    $checked = $partial ? ' CHECKED' : '';
    echo '<TR><TD>'.$isearch_lang['partial'].':</TD><TD><INPUT TYPE="checkbox" NAME="partial"'.$checked.'>'.$isearch_lang['partial'].'</TD></TR>';


    if (isset($isearch_groupNames[0]))
    {
        if ($isearch_config['form_show_groups'] == 2)
        {
            echo '<TR><TD>&nbsp;</TD><TD><SELECT NAME="groups[]" MULTIPLE>';
        }
        else
        {
            echo '<TR><TD>&nbsp;</TD><TD><SELECT NAME="group">';
        }

        echo '<OPTION VALUE="isearch_all">All</OPTION>';

        for ($i = 0; $i < count($isearch_config['groups']); $i += 3)
        {
            $selected = preg_match('/^(.*,)?/i'.$isearch_config['groups'][$i].'(,.*)?$', $group) ? ' SELECTED' : '';
            echo '<OPTION VALUE="'.$isearch_config['groups'][$i].'"'.$selected.'>'.$isearch_config['groups'][$i]."</OPTION>";
        }

        echo "</SELECT></TD></TR>\n";
    }


    echo '<TR><TD COLSPAN=2>&nbsp;</TD></TR>';
    if ($isearch_config['search_internet'])
    {
        echo '<TR><TD ALIGN=center><INPUT type="submit" value="'.$isearch_lang['searchbutton'].'" onClick="return searchClickedAdvanced(this.form);"></TD>';
        echo '<TD ALIGN=center><INPUT type="submit" name="internet" value="'.$isearch_lang['searchinternetbutton'].'" onClick="return searchClickedAdvanced(this.form);"></TD></TR>';
    }
    else
    {
        echo '<TR><TD COLSPAN=2 ALIGN=center><INPUT type="submit" value="'.$isearch_lang['searchbutton'].'" onClick="return searchClickedAdvanced(this.form);"></TD></TR>';
    }

    echo '<TR><TD ALIGN=center><A TARGET="_blank" href="help/help.php">'.$isearch_lang['helpbutton'].'</A></TD>
<TD ALIGN=center><A href="index.php">'.$isearch_lang['simple'].'</A></TD></TR>
</TABLE>
';
}
else
{
    echo '
<script>
<!--
function searchClickedSimple(form)
{
';
    if ($isearch_config['check_empty_search'])
    {
        echo '    if (form.s.value==\'\')
    {
        alert("'.$isearch_lang['please_enter'].'");
        return false;
    }
';
    }
    echo '    return true;
}
// -->
</script>
';


    if (!isset($s))
    {
        $s = '';
    }

    echo '
<TABLE border="0" cellpadding="3" cellspacing="1">
 <TR>
  <TD>
   <INPUT maxLength="255" name="s" size="'.$isearch_config['search_box_width'].'" value="'.$htmlSearchString.'">
';
    if ($isearch_config['form_show_partial'])
    {
        $checked = $partial ? ' CHECKED' : '';
        echo '&nbsp;'.$isearch_lang['partial'].':<INPUT TYPE="checkbox" NAME="partial"'.$checked.'>';
    }


    if (($isearch_config['form_show_groups']) && (isset($isearch_groupNames[0])))
    {
        if ($isearch_config['form_show_groups'] == 2)
        {
            echo '<SELECT NAME="groups[]" MULTIPLE>';
        }
        else
        {
            echo '<SELECT NAME="group">';
        }

        echo '<OPTION VALUE="isearch_all">All</OPTION>';
        for ($i = 0; $i < count($isearch_config['groups']); $i += 3)
        {
            $selected = preg_match('/^(.*,)?'.$isearch_config['groups'][$i].'(,.*)?$/i', $group) ? ' SELECTED' : '';
            echo '<OPTION VALUE="'.$isearch_config['groups'][$i].'"'.$selected.'>'.$isearch_config['groups'][$i]."</OPTION>";
        }

        echo '</SELECT>';
    }


    echo '<INPUT type="submit" value="'.$isearch_lang['searchbutton'].'" onClick="return searchClickedSimple(this.form);">';

    if ($isearch_config['search_internet'])
    {
        echo '&nbsp;<INPUT type="submit" name="internet" value="'.$isearch_lang['searchinternetbutton'].'" onClick="return searchClickedSimple(this.form);">';
    }

    if ($isearch_config['search_help_link'])
    {
        echo '&nbsp;<A TARGET="_blank" href="help/help.php">'.$isearch_lang['helpbutton'].'</A>';
    }

    if ($isearch_config['form_show_advanced'])
    {
        echo '&nbsp;<A href="index.php?advanced=1">'.$isearch_lang['advanced'].'</A>';
    }
    echo '</TD></TR></TABLE>';
}

echo '
</CENTER>
<INPUT type="hidden" name="action" value="search">
</FORM>
';

?>
