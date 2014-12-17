BACKUPDIR=/home/prohfesor/backup
MYSQLDIR=/var/lib/mysql
VHOSTSDIR=/var/www/vhosts

#check if backupdir exists
if [ ! -e $BACKUPDIR ];
then
	mkdir $BACKUPDIR
	echo "Backup dir was absent. Created again."
fi

#backup mysql
BACKUPFILE=$BACKUPDIR/mysql_$(date +%d_%m_%y).zip
if [ -e $BACKUPFILE ];
then
	rm $BACKUPFILE
fi
echo " Backing up $BACKUPFILE"
zip -rq $BACKUPFILE $MYSQLDIR/

#backup vhosts
for i in $(ls -d $VHOSTSDIR/*)
do
	BACKUPFILE=$BACKUPDIR/site_$(date +%d_%m_%y)_$(basename $i).zip
	if [ -e $BACKUPFILE ];
	then
		rm $BACKUPFILE
	fi
	echo " Backing up $BACKUPFILE"
	#zip -rq $BACKUPFILE $i/
	#if you have 32bit system, then php uploader will not be able to upload, use next line instead:
	zip -rq $BACKUPFILE $i/ -s=2000M
done
