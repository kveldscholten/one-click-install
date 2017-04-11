<!DOCTYPE html>
<html lang="de">
    <head>
        <meta charset="utf-8">
        <title>Ilch <?=VERSION ?> - Installation</title>
        <meta name="description" content="Ilch <?=VERSION ?> - Installation">
        <link rel="shortcut icon" type="image/x-icon" href="<?=$this->getStaticUrl('img/favicon.ico') ?>">
        <link href="<?=$this->getVendorUrl('twbs/bootstrap/dist/css/bootstrap.min.css') ?>" rel="stylesheet">
        <link href="<?=$this->getVendorUrl('fortawesome/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet">
        <link href="<?=$this->getStaticUrl('css/ilch.css') ?>" rel="stylesheet">
        <link href="<?=$this->getStaticUrl('../application/modules/install/static/css/install.css') ?>" rel="stylesheet">
        <link href="<?=$this->getVendorUrl('components/jqueryui/themes/ui-lightness/jquery-ui.min.css') ?>" rel="stylesheet">
        <script src="<?=$this->getVendorUrl('components/jquery/jquery.min.js') ?>"></script>
        <script src="<?=$this->getVendorUrl('components/jqueryui/jquery-ui.min.js') ?>"></script>
        <script src="<?=$this->getVendorUrl('twbs/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
    </head>
    <body>
        <div class="container">
            <div class="col-lg-offset-2 col-lg-8 col-md-12 col-sm-12 install_container oneclick">
                <form class="form-horizontal" method="POST" action="">
                    <?=$this->getTokenField() ?>
                    <div class="logo" title="<?=$this->getTrans('ilchInstall', (string)VERSION) ?>"></div>
                    <div class="installVersion" title="<?=$this->getTrans('ilchInstall', (string)VERSION) ?>">
                        <?=$this->getTrans('ilchInstallVersion', (string)VERSION) ?>
                    </div>
                    <div class="col-lg-12">
                        <?=$this->getContent() ?>
                    </div>
                    <div class="save_box">
                        <button type="submit" class="btn btn-primary pull-right" name="oneClick">
                            <?=$this->getTrans('installButton') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </body>
</html>
