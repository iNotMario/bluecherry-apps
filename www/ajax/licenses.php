<?php 

class licenses extends Controller {
	
    public function __construct()
    {
        parent::__construct();
		$this->chAccess('admin');
    }

    public function getData()
    {
        $this->setView('ajax.licenses');

        $this->view->licenses = data::getObject('Licenses');
    }

    public function postData()
    {
        if (!empty($_GET['mode']) && $_GET['mode'] == 'add'){
        	if (!bc_license_check($_POST['licenseCode'])){
        		data::responseJSON(false, L_INVALID_LICENSE);
        		exit();
        	};
        	$exists = data::getObject('Licenses', 'license', $_POST['licenseCode']);
        	if (!empty($exists)){
        		data::responseJSON(false, L_INVALID_LICENSE_EXISTS);
        		exit();
        	};
        	$machine_id = bc_license_machine_id();
        	$confirmation = fopen(VAR_LICENSE_AUTH."/?license_code={$_POST['licenseCode']}&id=".$machine_id, 'r');
        	#if auto auth fails, show message, opt to confirm manually
        	if (empty($confirmation)){
        		data::responseJSON('CONF', $machine_id);
        		exit();
        	}
        	#if auto ok, 
        	$confirmation = fread($confirmation, 1024);
        	if (strlen($confirmation) < 9){
        		data::responseJSON('F', constant('L_AUTO_RESP_'.$confirmation));
        		exit();
        	} else {
        		$confirmation = preg_replace('/([^0-9a-zA-Z\-])/', '', $confirmation);
        		$result = data::query("INSERT INTO Licenses VALUES ('{$_POST['licenseCode']}', '{$confirmation}', UNIX_TIMESTAMP())", true);
        		if ($result){
        			data::responseJSON(true, L_LICENSE_ADDED);
        			exit();
        		} else {
        			data::responseJSON(false, false);
        			exit();
        		}
        	}
        	exit();
        }

        if (!empty($_GET['mode']) && $_GET['mode'] == 'confirm'){
        	if (bc_license_check_auth($_POST['licenseCode'], $_POST['confirmLicense'])) {
        		$exists = data::getObject('Licenses', 'license', $_POST['licenseCode']);
        		if (!empty($exists)){
        			data::responseJSON(false, L_INVALID_LICENSE_EXISTS);
        			exit();
        		} else {
        			$result = data::query("INSERT INTO Licenses VALUES ('{$_POST['licenseCode']}', '{$_POST['confirmLicense']}', UNIX_TIMESTAMP())", true);
        			if ($result){
        				data::responseJSON(true, L_LICENSE_ADDED);
        				exit();
        			} else {
        				data::responseJSON(false, false);
        				exit();
        			}
        		}
        	} else {
        		data::responseJSON(false, L_INVALID_CONFIRMATION);
        		exit();
        	}
        }
        if (!empty($_GET['mode']) && $_GET['mode'] == 'delete'){
            $result = data::query("DELETE FROM Licenses WHERE license = '{$_GET['license']}'", true);
            data::responseJSON(true);
        }
    }
}


