    <script type="text/javascript" src="http://code.jquery.com/jquery-2.1.4.js"></script>
    <div id="content" style="position:absolute;top:5%;padding:10px; left:25%;background:#e4e4e4; width:580px;border:1px solid #000">  
    <div>      
       <p style="display:inline;font-weight:bold;">Welcome <?php if(!empty($userDetails['first_name'])&&(!empty($userDetails['last_name']))){echo sprintf('%s %s', $userDetails['first_name'], $userDetails['last_name']);}?></p>
       <p style="float:right;display:inline"><?php echo $this->Html->link('Logout',['plugin' =>'User','controller' => 'Users', 'action' => 'logout','_full' =>true]);?>
      </div>
      <div style="clear:both;"></div>
       <?php //echo $this->Html->link('Delete',['controller' => 'Users', 'action' => 'logout', 6],['confirm' => 'Are you sure you wish to delete this recipe?']);?>

       <p style="display:inline;font-weight:bold;">Enter URL *</p>
       <form action="" name="frm">
       <label>Enter URL : <?php echo WEBSITE_URL?>api/queryService/<input type="text" class="ajx_url" name="ajx_url" value="1007" checked="checked" placeholder="Service No/Service Code"/> 
       <br/>
       <p style="display:inline;font-weight:bold;">Example</p>
       <br>       
       <label>Get selected user detail : </label> 5<br/>
       </p>
       <div>
       <h4>Request Param (If any)</h4>
       <textarea name="req_param" rows="7" cols="65">id=2</textarea>
       <h4>JSON Response</h4>
       <textarea name="response_text" rows="7" cols="65" readonly="readonly"></textarea>
       </div>
       <input type="button" name="subbt" value="Submit" />
       

       </form>

    </div>
    <script type="text/javascript">
	var WEBSITE_URL = '<?php echo WEBSITE_URL?>';
    (function($){
    $('.apiactbut').click(function(e){
        var radval = $(this).val();
        $('.ajx_url').val(radval);
    
    });
    $('input[name="subbt"]').click(function(e){    
   
       var ajx_url = $('input[name="ajx_url"]').val();
       var serviceUrl = WEBSITE_URL+'api/queryService/'+ajx_url;
       var reqParams = $('[name="req_param"]').val();       
      // serviceUrl = serviceUrl+"?"+reqParams
      
        $.ajax({
      url: serviceUrl,
      data: reqParams,
      contentType: 'application/x-www-form-urlencoded',
     
      error: function() {
         alert('<p>An error has occurred</p>');
      },
     // dataType: 'jsonp',
      success: function(data) {
        $('[name="response_text"]').val('').val(data);
      },
      type: 'POST'
   });

    })
    }(jQuery));
    </script>
	