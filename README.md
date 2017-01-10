## Synopsis

This project allows you to import the output from google apps manager command "print cros full > crosdevices.csv" and read data about your Chrome devices.

This utility relies on you having your Chrome Devices organized in your google apps structure in sub-organizationss with the parent organization being named "Carts" and each OU under that being named after a school.
IMAGE HERE
You can edit my_constants.php under application/config if your root OU is not named "Carts"

Note: this utility has no security built in and should not be run on a public web-server.  It is intended for use on a local or directory protected machine.

## Installation

1. Install PHP, Apache and Mysql (or MariaDB).  If running locally on your Windows computer you may use XAMPP as an easy package.
2. Clone/download this git project to your webserver
  * If using GIT, change directory into your webserver directory and use command "git clone https://github.com/liaan/codeigniter_migration_base_generation.git"
  * If downloading,unzip this into your web directory
3. Copy file database.sample.php in application/config to database.php and update username, password and database lines to match your DB
4. Copy file my_constants.sample.php in application/config to my_constants.php, edit ROOT_SCHOOL_ORG to match your school org
5. Edit migration.php in application/config and set line "$config['migration_enabled'] = FALSE;" to TRUE
6. Edit config.php in application/config and set line "$config['base_url'] = 'http://localhost/projects/crosdevices';" to your webserver http path
7. Visit your webserver http path (i,e. 'http://localhost/crosdevices')
8. Click the "First run readme/install" link
9. Click on "Run database install" at the bottom of the page to setup your database
10. Edit migration.php in application/config and set line "$config['migration_enabled'] = TRUE;" to FALSE
11. Go back to the main page (i,e. 'http://localhost/crosdevices') and choose "Proceed to device activity after install"
12. This is the main page to access your reports

## Importing data
1. Using Google Apps Manager, export your Chrome OS data to a file called crosdevices.csv with this command "print cros full > crosdevices.csv"
2. Copy the crosdevices.csv file into the csv_files folder in your web root
3. From your main page (i,e. 'http://localhost/crosdevices') choose "Import devices from crosdevices.csv"
4. This will take a while to run depending on how many devices you have
5. After it is complete you can view your reports.  Re-run this process to update data.
  * IMPORTANT NOTE: This sets the enrollment and target_devices columns for each school when they are first imported to 0.  You can update these values in the SQL table schools to reflect your actual enrollment and targets in your report

## Viewing data
1. From your main page (i,e. 'http://localhost/crosdevices') choose "Device activity"
2. This page contains all of the details about your devices/usage/unused devices/etc