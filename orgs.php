<?php
/**
 * Created by PhpStorm.
 * User: Зарема
 * Date: 09.02.14
 * Time: 3:40
 */
require_once('vendor/class.MySQL.php');
require_once('config.php');
$my = new MySQL($config['mysql']['database'], $config['mysql']['user'], $config['mysql']['password'], $config['mysql']['host']);
if(!empty($my->lastError)){
    print 'MySQL error: "' . $my->lastError . '".
';
    exit;
}

$my->ExecuteSQL('SET NAMES utf8');
$my->ExecuteSQL('SET CHARSET utf8');


if($vk_events = $my->ExecuteSQL("SELECT gid, contacts FROM `new_events` e WHERE contacts LIKE '%@%'")){
    if(count($vk_events)){
        foreach($vk_events as $vk_event){
            $contacts = json_decode($vk_event['contacts'], true);
            foreach($contacts as $contact){
                if(isset($contact['user_id'])){
                    $keys = "`" . implode('`,`', array_keys($contact)) . "`";
                    $vals = "'" . implode("','", array_map('addslashes', $contact)) . "'";

                    $gid = $vk_event['gid'];

                    $q = "INSERT INTO new_orgs(`event_id`, $keys) VALUES($gid, $vals);";
                    print_r($q . PHP_EOL);
                }
            }
        }
    }
} else{
    print 'MySQL error: "' . $my->lastError . '".';
}
exit;