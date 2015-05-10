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
$min = $loop;
$max = $step + $loop - 1;

$params = array(
    'v' => '5.2',
    'group_ids' => implode(',', range($min, $max)),
    'fields' => 'country,city,place,description,members_count,start_date,end_date,status,contacts,links,fixed_post,site'
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

print 'Results: ' . count($results) . '.
';

$other_fields = array(
    'description',
    'start_date',
    'end_date',
    'status',
    'contacts',
    'links',
    'fixed_post',
    'site',
    'photo_50',
    'photo_100',
    'photo_200'
);
//$city = 2;
$type = 'event';
$members_count = 5;

$insert_counter = 0;

foreach($results as $row){
    if(!empty($row['name']) && $row['name'] != 'DELETED'){
        if($row['type'] == $type && $row['members_count'] > $members_count){
            $vars = array(
                'gid' => $row['id'],
                'name' => $row['name'],
                'screen_name' => $row['screen_name'],
                'is_closed' => $row['is_closed'],
                'members_count' => $row['members_count'],
                'country' => $row['country']['id'],
                'city' => $row['city']['id'],
            );

            foreach($other_fields as $field){
                if(isset($row[$field])){
                    $vars[$field] = is_array($row[$field]) ? json_encode($row[$field]) : $row[$field];
                }
            }

            if(isset($row['place'])){
                $vars['place'] = json_encode($row['place']);
                $vars['place_id'] = isset($row['place']['id']) ? $row['place']['id'] : null;
                $vars['place_longitude'] = isset($row['place']['longitude']) ? $row['place']['longitude'] : null;
                $vars['place_latitude'] = isset($row['place']['latitude']) ? $row['place']['latitude'] : null;
                $vars['place_address'] = isset($row['place']['address']) ? $row['place']['address'] : null;
                if(empty($vars['country']) && !empty($row['place']['country'])){
                    $vars['country'] = $row['place']['country'];
                }
                if(empty($vars['city']) && !empty($row['place']['city'])){
                    $vars['city'] = $row['place']['city'];
                }
            }

            if($my->Insert($vars, 'new_events')){
                $insert_counter++;
            } else{
                print 'MySQL error: "' . $my->lastError . '".';
            }
        }
    }
}

print 'Rows: ' . $insert_counter . '.
';
exit;