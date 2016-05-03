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
            var _WEBSITE_URL = '<?php echo WEBSITE_URL; ?>';     
            var _APP_NAME = 'DFA Monitoring';
        </script>
        <?php echo $this->Html->meta('icon') ?>
        <!-- CSS -->
        <?php echo $this->element('css'); ?>
        <!-- JS -->
        <?php echo $this->element('js'); ?>

        <!-- <?php echo $this->fetch('content');?> -->
    </head>
    <body ng-controller="appController">
        <header ui-view="header">
        </header>
        <div ui-view="content">
        </div>
        <footer ui-view="footer">
        </footer>
    </body>
</html>
