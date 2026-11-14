# WTU setup file

##Step 1. Clone or Pull

If this is a NEW installation, clone this repository with the following command:

```git clone https://github.com/asterisktec/WTU_Configuration```

If this is an existing WTU, then simply pull the latest updates with:

```cd WTU_Configuration```

```git pull```


##Step 2. Update or Install

If this is a NEW WTU ... In the shell run 

```cd WTU_Configuration```

```sudo ./install.sh```

That will link all of the appropriate files and scipts to the correct places.

If this is an EXISTING WTU ... In the shell run 

```sudo ./update.sh```

That will update all of the files with the most current version and will not change the email settings.


**Please make sure that you have the email password ready, as you will be asked during install**

(Hint: It's in 1Password)

That's it! Once you've done that, reboot and relax.
