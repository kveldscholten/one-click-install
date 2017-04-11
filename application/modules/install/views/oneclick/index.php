<?php $errors = $this->get('errors'); ?>

<h2><?=$this->getTrans('menuOneClickInstall') ?></h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <strong> <?=$this->getTrans('errorsOccured') ?>:</strong>
        <ul>
            <?php foreach ($errors as $key => $error): ?>
                <?php if ("writable" == substr($key,0,8)): ?>
                    <li><?=$this->getTrans('notWritablePath', $error); ?></li>
                <?php elseif ("missing" == substr($key,0,7)): ?>
                    <li><?=$this->getTrans('missingExtension', $error); ?></li>
                <?php elseif ($key == "dbName"): ?>
                    <li><?=$this->getTrans('fieldEmpty'); ?></li>
                <?php elseif ($key == "dbUser"): ?>
                    <li><?=$this->getTrans('fieldEmpty'); ?></li>
                <?php else: ?>
                    <li><?=$this->getTrans($key); ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="form-group <?php if (!empty($errors['dbName']) OR !empty($errors['dbConnectionError']) OR !empty($errors['dbDatabaseDoesNotExist'])) { echo 'has-error'; }; ?>">
    <label for="dbName" class="col-lg-3 control-label">
        <?=$this->getTrans('dbName') ?>
    </label>
    <div class="col-lg-9 input-group">
        <input type="text"
               class="form-control"
               id="dbName"
               name="dbName"
               value="<?=($this->get('dbName') != '') ? $this->escape($this->get('dbName')) : $_SESSION['install']['dbName'] ?>" />
    </div>
    <?php if (!empty($errors['dbName'])): ?>
        <span class="col-lg-offset-3 col-lg-9 help-block"><?=$this->getTrans($errors['dbName']) ?></span>
    <?php endif; ?>
</div>
<div class="form-group <?php if (!empty($errors['dbUser']) OR !empty($errors['dbConnectionError'])) { echo 'has-error'; }; ?>">
    <label for="dbUser" class="col-lg-3 control-label">
        <?=$this->getTrans('dbUser') ?>
    </label>
    <div class="col-lg-9 input-group">
        <input type="text"
               class="form-control"
               id="dbUser"
               name="dbUser"
               value="<?=($this->get('dbUser') != '') ? $this->escape($this->get('dbUser')) : $_SESSION['install']['dbUser'] ?>" />
        <div class="input-group-addon" rel="tooltip" title="<?=$this->getTrans('dbUserInfo') ?>"><i class="fa fa-info-circle"></i></div>
    </div>
    <?php if (!empty($errors['dbUser'])): ?>
        <span class="col-lg-offset-3 col-lg-9 help-block"><?=$this->getTrans($errors['dbUser']) ?></span>
    <?php endif; ?>
</div>
<div class="form-group <?php if (!empty($errors['dbConnection']) OR !empty($errors['dbConnectionError'])) { echo 'has-error'; }; ?>">
    <label for="dbPassword" class="col-lg-3 control-label">
        <?=$this->getTrans('dbPassword') ?>
    </label>
    <div class="col-lg-9">
        <input type="password"
               class="form-control"
               id="dbPassword"
               name="dbPassword"
               value="<?=($this->get('dbPassword') != '') ? $this->escape($this->get('dbPassword')) : $_SESSION['install']['dbPassword'] ?>" />
    </div>
</div>

<script>
$(function () {
    $("[rel='tooltip']").tooltip({
        'placement': 'bottom',
        'container': 'body'
    });
});
</script>
