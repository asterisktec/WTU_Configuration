#!/bin/bash
#
#
# This will automatically update the WTU menus and scripts without touching email.
#
# Must be run as root or in sudo
#
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
    echo "Not running with sufficient permissions, please use sudo."
    exit
fi
#
clear
#
echo "Beginning WTU update process..."
echo ""
echo "Moving main files to the /opt folder." ; sleep 1 
#
cp -r opt /
#
# Copy over the new crontab file
# but backup the old one just to be safe.
#
echo "Backing up system scheduler." ; sleep 1 
#
mv /etc/crontab /etc/crontab.bac
#
# Copy over the crontab file
#
echo "Updating the system scheduler." ; sleep 1 
#
cp -r etc/crontab /etc/crontab
#
# Linking required menu scripts
#
echo "Linking menu and other scripts." ; sleep 1 
#
# remove old links
# 
rm /usr/bin/wp-diagnostics
rm /usr/bin/wp-dataclean
rm /usr/bin/wp-serverchange
rm /usr/bin/run_speedtest
rm /usr/bin/change_sitename
#
ln -s /opt/wellpass/wp-diagnostics /usr/bin/
ln -s /opt/wellpass/wp-dataclean /usr/bin/
ln -s /opt/wellpass/wp-serverchange /usr/bin/
ln -s /opt/wellpass/run_speedtest /usr/bin/
ln -s /opt/wellpass/change_sitename /usr/bin/
#
# Setting as executable
#
echo "Setting executable permissions on scripts." ; sleep 1 
#
chmod +x /usr/bin/wp-diagnostics /usr/bin/wp-dataclean /usr/bin/wp-serverchange /usr/bin/run_speedtest
#
# Copy over the webpage
#
echo "Copying results viewer web page data." ; sleep 1 
#
# remove old links
#
rm /home/wpadmin/http
#
ln -s /opt/wellpass/http/ /home/wpadmin/
#
# Copy the results files
#
echo "Copying database files to wpadmin home directory." ; sleep 1 
#
cp /opt/wellpass/results_blank.csv /home/wpadmin/
cp /opt/wellpass/results.csv /home/wpadmin/
#
# Link the archive data folder
#
echo "Linking archive data folder directory" ; sleep 1 
#
# remove old links
#
rm /home/wpadmin/ARCHIVE_DATA
#
ln -s /opt/wellpass/ARCHIVE_DATA/ /home/wpadmin/
#
# And lastly, make sure all permissions are set correctly
#
echo "Setting file permissions." ; sleep 1 
#
chown wpadmin:wpadmin -R /home/wpadmin/
#
# echo ""
# echo "Update nearly complete..."
# echo ""
# echo "Please change the /etc/crontab file with the correct" 
# echo "SYSTEMHOST variable to $HOSTNAME to ensure correct mail delivery."
# echo "Opening file for editing in 2 seconds" ; sleep 2 
#
# nano /etc/crontab
#
systemctl restart cron.service
#
echo "Update Finished."
