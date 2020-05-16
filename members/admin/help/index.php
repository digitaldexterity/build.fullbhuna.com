<?php require_once('../../../Connections/aquiescedb.php'); ?>
<!doctype html>
<html lang="en" class="full_bhuna admin <?php echo isset($html_class) ? $html_class : ""; ?>"><!-- InstanceBegin template="/Templates/Admin.dwt.php" codeOutsideHTMLIsLocked="false" --><head>
<meta charset="utf-8" />
<meta name="robots" content="noindex,nofollow" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo $site_name; ?> - Help</title>
<!-- InstanceEndEditable -->
<?php require_once('../../../core/seo/includes/seo.inc.php'); ?>
<?php require_once('../../../core/includes/adminHead.inc.php'); ?>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
</head>
<body class="bootstrap adminBody nojQuery <?php echo $body_class;  ?>">
<?php require_once('../../../core/includes/adminHeader.inc.php'); ?>
<main>
  <div class="container clearfix">
    <noscript>
    <p class="alert warning alert-warning" role="alert">For full functionality of the Control Panel it is necessary to enable JavaScript.</p>
    </noscript>
    <!-- InstanceBeginEditable name="Body" -->
      <h1><i class="glyphicon glyphicon-user"></i> Site Users</h1>
      <p>The web site offers different two main types of access: <em>public</em> and <em>members</em>. If you are a member you must log in to gain access to member features.</p>
      <p>Member access is also broken down into ranks that fall into two main  categories: <em>editorial</em> and ordinary members. Editorial members have the ability to change the content of the site whilst standard members usually only have the ability to to submit content.</p>
      <p>The access levels are summarised in the table below:</p>
      <table border="0" cellpadding="2" cellspacing="2" class="form-table">

        <tr>
          <td bgcolor="#FFFFFF"><strong>User Type</strong></td>
          <td bgcolor="#FFFFFF"><strong>Rank</strong></td>
          <td bgcolor="#FFFFFF"><strong>Privileges</strong></td>
        </tr>
        <tr>
          <td rowspan="3" bgcolor="#FFFF99"><p><em>Members<br />
          (editorial)</em></p>
          </td>
          <td bgcolor="#FFFF99">Systems Administrator*</td>
          <td bgcolor="#FFFF99"><ul>
            <li>Fix technical problems</li>
          </ul></td>
        </tr>
        <tr>
          <td bgcolor="#FFFF99">Manager*</td>
          <td bgcolor="#FFFF99"><ul>
            <li>Ultimate editorial resposibility</li>
          </ul></td>
        </tr>
        <tr>
          <td bgcolor="#FFFF99">Editor</td>
          <td bgcolor="#FFFF99"><ul>
            <li>Approve or reject submitted content</li>
            <li> Add, modify and delete site content</li>
            <li> Add, modify and remove users</li>
          </ul></td>
        </tr>
        
        <tr>
          <td bgcolor="#FFFFCC"><em>Members<br />
          </em></td>
          <td bgcolor="#FFFFCC">Member</td>
          <td bgcolor="#FFFFCC"><ul>
            <li>Access to members section</li>
            <li>Submit content for the site</li>
            <li>Some submissions may have to be approved by an Editor before they are displayed</li>
            <li> Update their own content and profile</li>
          </ul></td>
        </tr>
        <tr>
          <td bgcolor="#CCFFCC"><em>Public</em></td>
          <td bgcolor="#CCFFCC">Non-member</td>
          <td bgcolor="#CCFFCC"><ul>
            <li>Access the public site</li>
            <li> Cannot log in or submit content</li>
          </ul></td>
        </tr>
      </table>
      <p>Each rank also has the abilities of all the ranks below it.</p>
      <p>*The Systems Administrator and Manager are special case  individual Editors. They have a few more privileges that allow them slightly more control for their respective roles. These are detailed in the various help sections of the site.</p>
    <!-- InstanceEndEditable --> </div>
</main>
<?php require_once('../../../core/includes/adminFooter.inc.php'); ?>
</body>
<!-- InstanceEnd --></html>
