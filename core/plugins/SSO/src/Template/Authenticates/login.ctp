<div id="content" style="top:20%; position:absolute;left:32%">        
   <h2><?php echo __("Login to DFA Monitoring Products")?></h2>
   <form name="" action="" method="post">
	  <table bgcolor="#e4e4e4" style="border:1px dotted #000;width:600px;">
		 <tr>
			<td colspan="2" style="color:red">
			  <?php echo isset($error) ? $error : "";?>
			</td>
		 </tr>
		 <tr>
			<td>
			   <label><?= __("Username")?> <font color="red">*</font> :</label>
			</td>
			<td>
			   <?= $this->Form->input('username', ['label'=>false])?>
			</td>
		 </tr>
		 <tr>
			<td colspan="2">&nbsp;</td>
		 </tr>
		 <tr>
			<td>
			   <label><?=__("Password")?> <font color="red">*</font> :</label>
			</td>
			<td>
			   <?= $this->Form->input('password', ['label'=>false])?>
			</td>
		 </tr>
		 <tr>
			<td>  
			</td>
			<td>
			   <?= $this->Form->button(__('Login'), ['type' => 'submit', 'name'=>'submit', 'value'=>'login'])?>
			</td>
		 </tr>
	  </table>
   </form>
</div>