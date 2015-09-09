<?php defined('INDVR') or exit();
#template common functions
require('../template/template.lib.php');
?>


<!--
<ul id="settingsMenu">
		<li><a href="#videoadj" class="videoadj"><?php echo DEVICE_EDIT_VIDADJ;?></a></li>
		<li><a href="#editmap" class="editmap"><?php echo DEVICE_EDIT_MMAP;?></a></li>
		<li><a href="#deviceschedule" class="deviceschedule"><?php echo DEVICE_EDIT_SCHED; ?></a></li>
		<li><a href="#ptzsettings" class="ptzsettings"><?php echo DEVICE_EDIT_PTZ; ?></a></li>
</ul>
-->



<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?php echo MMENU_DEVICES; ?>
    
        <ol class="breadcrumb">
           <li class="active"><?php echo TOTAL_DEVICES; ?>: <b><?php echo $devices->info['total_devices']; ?></b></li>
        </ol>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <a href="/ajax/addip.php" class="btn btn-success pull-right ajax-content"><i class="fa fa-plus fa-fw"></i> <?php echo AIP_HEADER; ?></a>
        <div class="clearfix"></div>
    </div>
</div>
<br>

<div class="row">
    <div class="col-lg-12">
        <div class="panel-group" id="devices-bl" role="tablist" aria-multiselectable="true">

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="devices-bl-ip-head">
                    <h4 class="panel-title">
                        <a href="#devices-bl-ip" role="button" data-toggle="collapse" data-parent="#devices-bl"><i class="fa fa-list-ul fa-fw"></i>IP Cameras</a>
                    </h4>
                </div>
                <div id="devices-bl-ip" class="panel-collapse collapse" role="tabpanel" aria-labelledby="devices-bl-ip">
                    <div class="panel-body">

                    <div class="row" id="devices-ip-ok">
                        <span id="devices-text-status-OK" style="display: none;"><?php echo DEVICE_VIDEO_STATUS_CHANGE_OK; ?></span>
                        <span id="devices-text-status-disabled" style="display: none;"><?php echo DEVICE_VIDEO_STATUS_CHANGE_disabled; ?></span>
                        <?php foreach($devices->ipCameras->ok as $val) { echo $val; } ?>
                    </div>

                    <div class="row" id="devices-ip-disabled" <?php echo ($devices->ipCameras->disabled) ? '' : 'style="display: none;"'; ?>>
                        <hr>
                        <?php foreach($devices->ipCameras->disabled as $val) { echo $val; } ?>
                    </div>

                    </div>
                </div>
            </div>

            <?php if (!$devices->cards){ ?>
                <br>
                <div class="alert alert-warning"><i class="fa fa-warning fa-fw"></i> <?php echo NO_CARDS; ?></div>
            <?php } else { ?>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="devices-bl-cards-head">
                    <h4 class="panel-title">
                        <a href="#devices-bl-cards" role="button" data-toggle="collapse" data-parent="#devices-bl"><i class="fa fa-ellipsis-h fa-fw"></i>cards</a>
                    </h4>
                </div>
                <div id="devices-bl-cards" class="panel-collapse collapse" role="tabpanel" aria-labelledby="devices-bl-cards">
                    <div class="panel-body">




                    </div>
                </div>
            </div>
            <?php } ?>


        </div>    


    </div>
</div>




<?php
echo "<div id='header'>".TOTAL_DEVICES.": <b>{$devices->info['total_devices']}</b></div><div id='addIPCamera'>".AIP_HEADER."</div><div class='bClear'></div><div id='devicesGroup'>";

if ($devices->ipCameras){
	echo "<div id='dvrCard'><div class='cardHeader'>IP Cameras</div><div class='cardContent'>";
		foreach($devices->ipCameras as $key => $device){
			echo "<div id='ipDevice' class='{$device->info['id']}'>
				<div>
				<div class='name'><a id='{$device->info['id']}' class='editName'>{$device->info['device_name']}</a></div>
				</div>
				<div class='model'>{$device->info['manufacturer']} ".(($device->info['manufacturer']==$device->info['model']) ? '' : $device->info['model'])." | id: {$device->info['id']}</div>
				<div id='{$device->info['id']}' cliss='{$device->info['status']}'>".constant('DEVICE_VIDEO_STATUS_'.$device->info['status'])." <a href='#' class='change_state' id='{$device->info['id']}'>[".constant('DEVICE_VIDEO_STATUS_CHANGE_'.$device->info['status'])."]</a> | <a class='ipSettingsOpen' href='#' id='{$device->info['id']}'"." name='{$device->info['id']}'>[".SETTINGS."]</a></div>";
			echo "</div>";
		}
	echo "<div class='bClear'></div></div></div>"; #end ip cameras
}

if (!$devices->cards){
	echo '<div id="ajaxMessage" class="INFO">'.NO_CARDS.'</div>';
} else {
	$counter = 0;
	foreach($devices->cards as $key => $card){
		$counter++;
		echo "<div id='dvrCard' class='{$card->info['id']}'><div class='cardHeader'>#{$counter} ".CARD_HEADER." ({$card->info['ports']} ".PORT.")</div>";
		echo"<div class='cardContent'>"; #begin card/header
		echo "<div id='cardInfo'>Encoding: ".(($card->info['encoding']=='notconfigured') ? '<b>'.SIGNAL_TYPE_NOT_CONFD."</b> | <div class='enableAll' id='{$card->info['id']}'>".ENABLE_ALL_PORTS."</div>" : "<a href='#'><div class='cardEncoding' title='".CARD_CHANGE_ENCODING."' id='{$card->info['id']}'>{$card->info['encoding']}</div></a>")." | Unused capacity: <div id='unusedCapacity' class='uc{$card->info['id']}'><a>{$card->info['available_capacity']}</a></div></div>";
foreach ($card->cameras as $key =>$device){
	if (empty($device->info['id'])) { $device->info['id']=''; };
	if (empty($device->info['device_name'])) { $device->info['device_name'] = ($device->info['status'] == 'notconfigured') ? DEVICE_VIDEO_NAME_notconfigured : DEVICE_UNNAMED; };
	echo "<div id='localDevice' class='{$device->info['id']}'>";
	echo "<div><div class='name'><a id='{$device->info['id']}' class='editName'>{$device->info['device_name']}</a></div>";
	if ($device->info['status'] != 'notconfigured') echo "<div class='portId'>id: {$device->info['id']}</div>";
	echo "</div><div id='{$device->info['id']}' class='{$device->info['status']}'>".constant('DEVICE_VIDEO_STATUS_'.$device->info['status'])."";
	echo "&nbsp;<a href='#' class='change_state' id='"."{$device->info['device']}'>[".constant('DEVICE_VIDEO_STATUS_CHANGE_'.$device->info['status'])."]</a>";
	if ($device->info['status'] != 'notconfigured') {
		echo "&nbsp;<a class='settingsOpen' href='#' id='{$device->info['id']}'".
			" name='{$device->info['device']}'>[".SETTINGS."]</a>";
	}
	echo "</div><div id='port'>{$device->info['port']}</div>";
	if ($device->info['status'] != 'notconfigured' && !$device->info['disabled']) {
		echo "<div class='background-color:#efefef;' id='cameraSettings'>
				<hr />
				<div id='resolutionFps'>
					<div id='RES'>Resolution:".arrayToSelect($GLOBALS['resolutions'][$card->info['encoding']], $device->info['resolutionX'].'x'.$device->info['resolutionY'], $device->info['device'] ,'RES')."</div>
					<div id='FPS'>FPS:".arrayToSelect($GLOBALS['local_device_fps'], 30/$device->info['video_interval'], $device->info['device'],'FPS')."</div>
				</div>
			 </div>";
	}
	
	echo	"</div>";
}
	echo "<div class='bClear'></div></div></div>"; #end card
}

echo '</div>';
};
?>
