<h2>Mail merge</h2>
   <p>Adding the following inserts will merge the recipients details within the email:</p><table border="0" cellpadding="10" cellspacing="0" class="form-table">
  <tr>
    <td><strong>Within body - merged per email send</strong></td>
    <td><strong>Within links - merged during edit</strong></td>
    <td><strong>Within body - merged once at preview</strong></td>
  </tr>
  <tr>
    <td class="top"><ul>
      <li>{salutation} - recipient's salutation</li>
      <li>{firstname} - recipient's first name</li>
      <li>{surname} - recipient's surname</li>
      <li>{email} - recipient's email</li>
      <li>{userID} - recipient's system ID</li>
       <li>{usertoken} - hash of ID for authentication links</li>
      <li>{company} - recipient's company</li>
      <li>{username} - recipient's username</li>
      <li>{password} - recipient's password*</li>
      <li>{date}- date sent dd month yyyy</li>
      <li>{news} - for news emails: title summary and link to story on site</li>
     
    </ul></td>
    <td class="top"><ul>
     
      <li>{forward} - URL to page to allow forwarding of the email</li>
      <li>{online} - URL to view page online</li>
      <li>{unsubscribe} - URL to unsubscribe</li>
    </ul></td>
    <td class="top"><ul>
      <li>{news} - add recent news stories (added at preview stage fro group email)</li>
      <li>{currentmonth} - the current month</li>
      <li>{currentyear} - the current year</li>
    </ul></td>
  </tr>
</table>
   
   <p>*Password is only added if one exisits and it is not encrypted, otherwise a link to get password is added</p>