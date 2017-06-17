<?php

/////////////////////////////////////////////////////////////////////////////
// General information
///////////////////////////////////////////////////////////////////////////// 

$server_name = (empty($_SERVER['SERVER_NAME'])) ? '127.0.0.1' : $_SERVER['SERVER_NAME'];

$app['basename'] = 'plex';
$app['version'] = '2.0.6';
$app['release'] = '1';
$app['vendor'] = 'eGloo';
$app['packager'] = 'eGloo';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('plex_app_description');
$app['tooltip'] = sprintf(lang('plex_app_tooltip'), "<a href='http://" . $server_name . ":32400/web'>http://" . $server_name . ":32400/web</a>");

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('plex_app_name');
$app['category'] = lang('base_category_server');
$app['subcategory'] = lang('base_subcategory_file');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['plex']['title'] = $app['name'];

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['core_requires'] = array(
    'app-network-map-core',
    'plexmediaserver'
);

$app['core_file_manifest'] = array(
    'plexmediaserver.php' => array('target' => '/var/clearos/base/daemon/plexmediaserver.php'),
    'plex.conf' => array(
        'target' => '/etc/clearos/plex.conf',
        'mode' => '0644',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace'
    ),
    '10-plex' => array(
        'target' => '/etc/clearos/firewall.d/10-plex',
        'mode' => '0755',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace'
    ),
    'acl.conf' => array(
        'target' => '/var/clearos/plex/acl.conf',
        'mode' => '0644',
        'owner' => 'webconfig',
        'group' => 'webconfig',
        'config' => TRUE,
        'config_params' => 'noreplace'
    )
);

$app['core_directory_manifest'] = array(
    '/var/clearos/plex' => array(
        'mode' => '0755',
        'owner' => 'webconfig',
        'group' => 'webconfig'
    )
);

$app['delete_dependency'] = array(
    'app-plex-core',
    'plexmediaserver'
);
