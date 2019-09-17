<?php
include('app/bootstrap.php');
$path = BP;

print("<PRE>");
passthru("/bin/bash rename.sh");
//echo shell_exec('sh /chroot/home/magentot/magento2.qdos-technology.com/html/rename.sh');
print("<PRE>");


//$m_command = 'bin/magento module:status';
//$m_command = 'bin/magento module:disable Neo_Commands';

$m_command = 'bin/magento s:up';
$m_command = 'bin/magento setup:di:compile';
//$m_command = 'bin/magento setup:static-content:deploy en_US en_AU -f';
// $m_command = 'bin/magento deploy:mode:show';
// $m_command = 'bin/magento deploy:mode:set production';
// $m_command = 'bin/magento cache:status';
// $m_command = 'bin/magento c:c';
//$m_command = 'bin/magento setup:backup --code --media --db';
$m_command = 'bin/magento c:f';
//$m_command = 'bin/magento i:rei';
//$m_command = 'bin/magento catalog:images:resize';
//$m_command = 'bin/magento cron:run';
//$m_command = './app/code/Qdos/QdosSync/Cron/rename.sh';
$command = '/usr/local/lsws/lsphp70/bin/php '.$path.'/'.$m_command;
echo $command;
echo '<pre>';
$result = shell_exec($command);
print_r($result);
exit;

// $command = 'php70 bin/magento cache:clean && php70 bin/magento cache:flush';
// echo '<pre>' . shell_exec($command) . '</pre>';
// echo "Pradeep";
// exit;


// echo "Upgradation in process... \r\n";
// shell_exec("/usr/local/lsws/lsphp70/bin/php bin/magento setup:upgrade");

// echo "Compilation in process... \r\n";
// shell_exec("/usr/local/lsws/lsphp70/bin/php bin/magento setup:di:compile");

echo "Static content deployement in process... \r\n";
shell_exec("/usr/local/lsws/lsphp70/bin/php bin/magento -vvv setup:static-content:deploy en_US en_AU -f");

// echo "Cache clean in process... \r\n";
// shell_exec("/usr/local/lsws/lsphp70/bin/php bin/magento cache:clean");

// echo "Cache flush in process. \r\n";
// shell_exec("/usr/local/lsws/lsphp70/bin/php bin/magento cache:flush");

// echo "Folder permission set in process... \r\n";
// shell_exec("chmod -R 777 var/ generated/ pub/");

echo "Sucessfully Done! \r\n";