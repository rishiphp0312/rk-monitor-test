<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->Html->charset() ?>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
        <title>DFA Monitoring</title>
        <script>
            var _WEBSITE_URL = '<?php echo _WEBSITE_URL; ?>';            
            var _SCREENHEIGHT = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        </script>
        <?php echo $this->Html->meta('icon') ?>
        
        <?php
            echo $this->Html->script([
            _WEBSITE_URL.'js/core/jquery-2.1.4.min.js'])
        ?>
    </head>
    <body ng-controller="appController">
        <header ui-view="header">
        </header>
        <div ui-view="content">
        <?php echo $this->fetch('content');?>
        </div>
        <footer ui-view="footer">
        </footer>
    </body>
</html>
