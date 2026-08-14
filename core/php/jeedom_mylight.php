
<?php
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
require_once dirname(__FILE__) . '/mylight150_api.class.php';

class jeedom_mylight extends eqLogic {
    public static function cronHourly() {
        foreach (eqLogic::byType('jeedom_mylight') as $eqLogic) {
            if ($eqLogic->getIsEnable() == 1) {
                $eqLogic->refreshData();
            }
        }
    }

    public function refreshData() {
        $username = config::byKey('username', 'jeedom_mylight');
        $password = config::byKey('password', 'jeedom_mylight');
        if (empty($username) || empty($password)) {
            log::add('jeedom_mylight', 'error', 'Identifiants MyLight non configurés dans la configuration globale du plugin.');
            return;
        }

        $api = new MyLight150API($username, $password);
        
        // 1. Get installation code
        $v2_data = $api->callAPI('/v2');
        $installation_code = null;
        if (isset($v2_data['links'])) {
            foreach ($v2_data['links'] as $link) {
                if ($link['rel'] == 'installation') {
                    $parts = explode('/', rtrim($link['href'], '/'));
                    $installation_code = end($parts);
                    break;
                }
            }
        }

        if (!$installation_code) {
            log::add('jeedom_mylight', 'error', 'Impossible de récupérer le code d\'installation.');
            return;
        }

        // 2. Home Data (Puissances)
        $home_data = $api->callAPI("/v2/installations/{$installation_code}/home?msb=msb01");
        if ($home_data) {
            $this->checkAndUpdateCmd('solar_production', isset($home_data['solarProduction']['value']) ? $home_data['solarProduction']['value'] : 0);
            $grid_val = (isset($home_data['grid']['value']) ? $home_data['grid']['value'] : 0) - (isset($home_data['injection']['value']) ? $home_data['injection']['value'] : 0);
            $this->checkAndUpdateCmd('grid', $grid_val);
            $this->checkAndUpdateCmd('load', isset($home_data['load']['value']) ? $home_data['load']['value'] : 0);
        }

        // 3. Virtual Battery
        $batt_data = $api->callAPI("/v3/virtual-battery/state");
        if ($batt_data && isset($batt_data['status'])) {
            $status = $batt_data['status'];
            $this->checkAndUpdateCmd('msb_state', isset($status['state']) ? $status['state'] : 'unknown');
            $power = isset($status['socEvolutionInkW']) ? $status['socEvolutionInkW'] : 0;
            if(isset($status['state']) && $status['state'] == 'charging') $power = $power * -1;
            $this->checkAndUpdateCmd('msb_power', $power);
            $this->checkAndUpdateCmd('msb_autonomy', isset($status['socInkWh']) ? $status['socInkWh'] : 0);
            $capacity = isset($status['capacity']) ? $status['capacity'] : 0;
            $this->checkAndUpdateCmd('msb_capacity', $capacity);
            if ($capacity > 0) {
                $this->checkAndUpdateCmd('msb_level', round((isset($status['socInkWh']) ? $status['socInkWh'] : 0) / $capacity * 100, 1));
            }
        }

        // 4. Money Pot
        $money_data = $api->callAPI("/v3/money-pot");
        if ($money_data && isset($money_data['payload']['balance']['value'])) {
            $this->checkAndUpdateCmd('money_pot', $money_data['payload']['balance']['value']);
        }
    }

    public function postSave() {
        $this->createCommand('Production Solaire (Live)', 'solar_production', 'info', 'numeric', 'kW');
        $this->createCommand('Réseau (Live)', 'grid', 'info', 'numeric', 'kW');
        $this->createCommand('Consommation (Live)', 'load', 'info', 'numeric', 'kW');
        $this->createCommand('Etat Batterie', 'msb_state', 'info', 'string');
        $this->createCommand('Puissance Batterie', 'msb_power', 'info', 'numeric', 'kW');
        $this->createCommand('Autonomie Batterie', 'msb_autonomy', 'info', 'numeric', 'kWh');
        $this->createCommand('Capacité Batterie', 'msb_capacity', 'info', 'numeric', 'kWh');
        $this->createCommand('Niveau Batterie', 'msb_level', 'info', 'numeric', '%');
        $this->createCommand('Cagnotte', 'money_pot', 'info', 'numeric', '€');
    }

    private function createCommand($name, $logicalId, $type, $subType, $unite = '') {
        $cmd = $this->getCmd(null, $logicalId);
        if (!is_object($cmd)) {
            $cmd = new jeedom_mylightCmd();
            $cmd->setLogicalId($logicalId);
            $cmd->setEqLogic_id($this->getId());
            $cmd->setName($name);
            $cmd->setType($type);
            $cmd->setSubType($subType);
            $cmd->setUnite($unite);
            $cmd->save();
        }
    }
}

class jeedom_mylightCmd extends cmd {
    public function execute($_options = array()) {}
}
?>
