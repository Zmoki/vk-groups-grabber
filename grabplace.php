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

$step = 100;
$offset = $loop * $step;

if($groups = $my->ExecuteSQL("SELECT gid FROM moderate_events WHERE moderate_status='gray' AND FROM_UNIXTIME(start_date, '%Y') IN (2013, 2014) AND length(place)>0 LIMIT " . $step . " OFFSET " . $offset)){
    if(count($groups)){
        $group_ids = array();
        foreach($groups as $group){
            $group_ids[] = $group['gid'];
        }
        $group_ids = implode(',', $group_ids);


        $params = array(
            'v' => '5.2',
            'group_ids' => $group_ids,
            'fields' => 'place'
        );

        require_once('vendor/vkapi.php');
        $VK = new Vkapi();

        $results = $VK->api('groups.getById', $params);

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

        foreach($results as $row){
            if(isset($row['place'])){
                if(!empty($row['place']['address'])){
                    if($my->Update('moderate_events', array('place_address' => $row['place']['address']), array('gid' => $row['id']))){
                    } else{
                        print 'MySQL error: "' . $my->lastError . '".';
                    }
                }
            }
        }
    }
} else{
    print 'MySQL error: "' . $my->lastError . '".';
}
exit;