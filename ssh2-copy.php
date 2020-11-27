<?php

function listar_dir($connection, $sftp, $remoteDir, $files, $localDir)
{
    $files    = scandir('ssh2.sftp://' . $sftp . $remoteDir);

    if (!empty($files)) {
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir('ssh2.sftp://' . $sftp . $remoteDir . "/" . $file)) {
                    mkdir($localDir . "/" . $file, 777, true);
                    listar_dir($connection, $sftp, $remoteDir . "/" . $file, scandir('ssh2.sftp://' . $sftp . $remoteDir . "/" . $file), $localDir . "/" . $file);
                } else {
                    ssh2_scp_recv($connection, $remoteDir . "/" . $file, $localDir . "/" . $file);
                }
            }
        }
    }
}


$username = "youruser"; //exemple ubuntu
$password = "yourpassword"; //exemple 123456
$url      = 'yourhost'; // 192.168.0.0
$port     = 22;         //port 

$localDir  = '/var/www/html/';
$remoteDir = '/home/test';

// Make our connection

$connection = ssh2_connect($url, 65002);

if (!$connection) {
    throw new Exception("Could not connect to server.");
}

if (!ssh2_auth_password($connection, $username, $password)) {
    throw new Exception("Authentication failed!");
}

// Create our SFTP resource
if (!$sftp = ssh2_sftp($connection)) throw new Exception('Unable to create SFTP connection.');

// download all the files

try {
    $files    = scandir('ssh2.sftp://' . $sftp . $remoteDir);
    listar_dir($connection, $sftp, $remoteDir, $files, $localDir);
    ssh2_exec($connection, 'exit');
    echo true;
} catch (Exception $ex) {
    ssh2_exec($connection, 'exit');
    echo false;
}
