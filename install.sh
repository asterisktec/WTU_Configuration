#!/bin/bash
#
# Version 1.5
#
# This will automatically install the WTU menus and email configuration.
#
# Must be run as root or in sudo
#
if [[ $(/usr/bin/id -u) -ne 0 ]]; then
    echo "Not running with sufficient permissions, please use sudo."
    exit
fi
#
echo "Moving main files to the /opt folder" ; sleep 2
#
cp -r opt /
#
# Copy over the new crontab file
# but backup the old one just to be safe.
#
echo "Backing up old crontab file" ; sleep 2
#
mv /etc/crontab /etc/crontab.bac
#
# Copy over the postfix data and crontab
#
echo "Copying /etc folder to root" ; sleep 2
#
cp -r etc /
#
# Now lets get the email password
#
echo "You will now need the email sending password..."
echo ""
read -p "Enter the email password: " email_password
#
email_password="${email_password//[$'\n']/}"
#
printf %s "smtp.mailgun.org raspberrypi_diagnostic@sandboxd9ab5c704d1e4ae9b9c66b3b7f3ac419.mailgun.org:$email_password" > /etc/postfix/sasl_passwd
#
# Now to reload Postfix with the new data
#
echo "Updating PostFix Email System" ; sleep 2
#
postmap /etc/postfix/sasl_passwd
#
# Linking required menu scripts
#
echo "Linking menu and other scripts" ; sleep 2
#
# remove old links
# 
rm /usr/bin/wp-diagnostics
rm /usr/bin/wp-dataclean
#
ln -s /opt/wellpass/wp-diagnostics /usr/bin/
ln -s /opt/wellpass/wp-dataclean /usr/bin/
#
# Setting as executable
#
echo "Setting executable permissions on scripts" ; sleep 2
#
chmod +x /usr/bin/wp-diagnostics /usr/bin/wp-dataclean
#
# Copy over the webpage
#
echo "Copying web data" ; sleep 2
#
# remove old links
#
rm /home/wpadmin/http
#
ln -s /opt/wellpass/http/ /home/wpadmin/
#
# Copy the results files
#
echo "Copying database files to wpadmin home" ; sleep 1
#
cp /opt/wellpass/results_blank.csv /home/wpadmin/
cp /opt/wellpass/results.csv /home/wpadmin/
#
# Link the archive data folder
#
echo "Linking archive data folder" ; sleep 1
#
# remove old links
#
rm /home/wpadmin/ARCHIVE_DATA/
#
ln -s /opt/wellpass/ARCHIVE_DATA/ /home/wpadmin/
#
# And lastly, make sure all permissions are set correctly
#
echo "Setting permissions" ; sleep 2
#
chown wpadmin:wpadmin -R /home/wpadmin/
#
echo "Installation and setup complete..."
echo "Please update the /etc/crontab file with the correct hostname of $HOSTNAME to ensure correct mail delivery."
