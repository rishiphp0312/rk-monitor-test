 
    <div id="content" style="top:20%; position:absolute;left:32%">        
       <h2>Login to DFA Monitoring</h2>
       <form name="" action="" method="post">
       <table bgcolor="#e4e4e4" style="border:1px dotted #000;width:600px;">
       <tr>
       <td colspan="2" style="color:red">
       <?php 
       echo $error;
       ?>
       </td>
       </tr>

       <tr>
       <td><label>Username <font color="red">*</font> :</label></td><td><input type="text" name="username" value="" /></td>
       </tr>
       <tr>
       <td colspan="2">&nbsp;</td>
       </tr>
       <tr>
       <td><label>Password <font color="red">*</font> :</label></td><td><input type="password" name="password" value="" /></td>
       </tr>
       <tr>
       <td></td><td><input type="submit" name="subbt" value="Login" /></td>
       </tr>
       </table>
       </form>
    </div>
	