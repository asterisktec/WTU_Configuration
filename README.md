# Simple WTU setup file

Step 1. Clone this repository with the following command:

```git clone https://github.com/asterisktec/WTU_Configuration```

Step 2. Change to the new WTU_Menu folder:

```cd WTU_Configuration```

Step 3. Make the install.sh script executable:

```chmod +x install.sh```

Step 4. In the shell run 

```./install.sh```

That will link all of the appropriate files and scipts to the correct places.

**Please make sure that you have the email password ready as you will be asked during install**
(Hint: It's in 1Password)

The only manual code editing you have to do is modify the /etc/crontab file with
the correct hostname. That's it! Once you've done that, reboot and relax.
