<?php
//echo '<pre>';
//print_r($data);
//print_r($configData);
?>
<!DOCTYPE html>
<html>
    <head>
        <!-- Cache control -->
        <meta http-equiv="cache-control" content="max-age=0" />
        <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="expires" content="0" />
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <meta http-equiv="pragma" content="no-cache" />
        
        <style>
            .customLog { margin: 20px; padding: 10px; color: #333; font-family: sans-serif; font-size: 15px;}
            h1 { margin-top: 20px; margin-bottom: 0; text-align: left;}
            h1.mainheading { text-align: center; margin: 0; color: #000;}
            hr { color: #eee; border: 1px dashed #aaa;}
            h2.heading { margin-bottom: 5px; color: #333; font-size: 20px;}
            table tr th, table tr td { padding: 5px; text-align: left; font-size: 14px; }

            .summary .details { margin-bottom: 20px;}
            .summary .details table tr td:first-child {width: 30%;}
            .issue table tr th:first-child, .issue table tr td:first-child {width: 30%;}
            .sheetNames { margin-top: 30px; margin-bottom: 20px; padding-bottom: 5px;}
            .summaryHeading { margin-top: 10px;}
            .detailsHeading { margin-top: 30px;}
            .noIssue { text-align: center;}
            .success { color: #4cae4c;}
            .failed { color: #c9302c;}
        </style>
    </head>
    <body>
        <div class="customLog">
            <div class="summary">
                <div class="details">
                    <table border='0' style="border: 1px; width:100%; text-align:left; border-collapse: collapse;">
                        <tr>
                            <td>Dear <?php  echo ucfirst($configData['name']);?> </td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Please 	<a href="<?php echo $configData['url'];?> ">Click here  </a> to activate and setup your password.</td>
                            <td></td>
                        </tr>
						<tr>
                            <td>Thank you.</td>
                            <td></td>
                        </tr>
						<tr>
                            <td>Regards,</td>
                            <td></td>
                        </tr>
						<tr>
                            <td><?php  echo  $configData['appName'];;?></td>
                            <td></td>
                        </tr>
                        
                    </table>
                </div>
            </div>
			
					
						
				
        </div>
    </body>
</html>