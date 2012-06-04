#!/bin/bash
baseDir="$(dirname $0)/../"
idFile="${baseDir}var/lastuser.id"
launcherCmd="${baseDir}launcher.php start SyncDvUser"
[[ ! -f $idFile ]] && echo 0 > $idFile
lastId=$(cat $idFile)
while [ "$lastId" != "finish" ];do
    ${launcherCmd} --psi=${lastId}
    lastId=$(cat $idFile)
    sleep 1
done
