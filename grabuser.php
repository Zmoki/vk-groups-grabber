<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Зарема
 * Date: 21.10.13
 * Time: 12:58
 * To change this template use File | Settings | File Templates.
 */
if(!isset($argv[1])){
    print 'No args.';
    exit;
}

$loop = intval($argv[1]);
print 'Loop: ' . $loop . '. ';


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


$limit = 100;
$offset = $limit * $loop;

$user_ids = array();

if($vk_orgs = $my->ExecuteSQL("SELECT GROUP_CONCAT(user_id) AS user_ids FROM (SELECT user_id FROM orgs o LIMIT $limit OFFSET $offset) AS temp_orgs;")){
    $user_ids = $vk_orgs['user_ids'];
} else{
    print 'MySQL error: "' . $my->lastError . '".';
    exit;
}

$params = array(
    'v' => '5.8',
    'user_ids' => $user_ids,
    'fields' => 'sex,bdate,city,country,contacts,connections,site,last_seen,counters'
);

require_once('vendor/vkapi.php');
$VK = new Vkapi();

$results = $VK->api('users.get', $params);

if(!isset($results['response'])){
    print 'No response.';
    print 'Results: ' . json_encode($results) . '.
';
    exit;
}

$results = $results['response'];
if(empty($results)){
    print 'No results.' . '
';
    exit;
}

$update_counter = 0;

foreach($results as $row){
    $vars = array();

    if(!empty($row['first_name'])){
        $vars['first_name'] = $row['first_name'];
    }
    if(!empty($row['last_name'])){
        $vars['last_name'] = $row['last_name'];
    }
    if(!empty($row['sex'])){
        $vars['sex'] = $row['sex'];
    }
    if(!empty($row['bdate'])){
        $vars['bdate'] = $row['bdate'];
    }

    if(!empty($row['mobile_phone'])){
        $vars['mobile_phone'] = $row['mobile_phone'];
    }
    if(!empty($row['home_phone'])){
        $vars['home_phone'] = $row['home_phone'];
    }
    if(!empty($row['skype'])){
        $vars['skype'] = $row['skype'];
    }
    if(!empty($row['twitter'])){
        $vars['twitter'] = $row['twitter'];
    }
    if(!empty($row['site'])){
        $vars['site'] = $row['site'];
    }
    if(!empty($row['last_seen'])){
        $vars['last_seen'] = is_array($row['last_seen']) ? $row['last_seen']['time'] : json_decode($row['last_seen'], true)['time'];
    }

    if(!empty($row['counters'])){
        $counters = is_array($row['counters']) ? $row['counters'] : json_decode($row['counters'], true);

        $vars['friends'] = $counters['friends'];
        $vars['followers'] = $counters['followers'];
    }

    //    print json_encode($vars) . $row['id'] .PHP_EOL;
    if($my->Update('orgs', $vars, array('user_id' => $row['id']))){
        $update_counter++;
    } else{
        print 'MySQL error: "' . $my->lastError . '".' . PHP_EOL;
    }
}

print 'Rows: ' . $update_counter . '.
';
exit;