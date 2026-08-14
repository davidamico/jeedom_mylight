<?php
require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Utilisateur MyLight (Email)}}</label>
            <div class="col-lg-4">
                <input class="configKey form-control" data-l1key="username" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">{{Mot de passe MyLight}}</label>
            <div class="col-lg-4">
                <input type="password" class="configKey form-control" data-l1key="password" />
            </div>
        </div>
    </fieldset>
</form>

