# GeoDirectory Unit Tests

## Installation ##
1. Install PHPUnit
    1. Note: According to [WP-CLI Wiki](https://github.com/wp-cli/wp-cli/wiki/Plugin-Unit-Tests) PHPUnit 5.x not supported. To install 4.8 follow these steps
        1. wget https://phar.phpunit.de/phpunit-old.phar
        2. chmod +x phpunit-old.phar
        3. mv phpunit-old.phar /usr/local/bin/phpunit
2. Install [WP-CLI](http://wp-cli.org/#install)
3. cd to this plugin directory. Ex: cd /Users/username/Sites/whoop/wp-content/plugins/geodirectory
4. Make a directory for wordpress testing under your web root. 
    1. I call the directory "wp_tests". 
    2. So my test site installation path looks like this.  /Users/giri/Sites/wp_tests
5. Enter the command in this format:  
    1. bash bin/install-wp-tests.sh [db-name] [db-user] [db-pass] [db-host] [wp-version] [installation-path]  
    2. Ex: bash bin/install-wp-tests.sh wordpress_test root 'root' localhost latest /Users/giri/Sites/wp_tests  
    3. Note: Database will be automatically created by script. So don't create one manually. 
    4. For installation-path use the path from step 4. 
    5. This will put all testing files under wp_tests
6. Open bash_profile with your favorite editor. 
    1. nano ~/.bash_profile 
    2. Add this line at the end of the file
        1. export WP_TESTS_DIR="/Users/username/Sites/wp_tests/wordpress-tests-lib" 
        2. Note: wordpress-tests-lib should be added to the path you got from step 4
7. Finally cd to geodirectory folder again and then run this command.
    1. phpunit 
    2. If everything setup correctly, you will see unit test results in terminal
    
    
## Steps to run selenium tests ##
1. Install Firefox browser
2. Download and Install [Selenium Standalone Server](http://www.seleniumhq.org/download/).
    1. Start selenium server
    2. On Mac OSX, I use this command to start the server
        1. java -jar /usr/local/bin/selenium-server-standalone-2.48.2.jar 
3. Create a dummy site for testing purpose. 
    1. Test site should point to http://localhost/wordpress/
    2. If you are planning to use different base url, change the GDTEST_BASE_URL constant value in selenium/base.php
    3. Use admin/admin for username/password 
    4. Warning: Don't use a real site since the test will be resetting the database often. 
4. Copy selenium/initialize.py to dummy site's wp-content/plugins folder
5. Run initialize.py with the following command
    1. python initialize.py [github username] [bitbucket username] 
    2. Ex: python initialize.py GeoDirectory GeoDirectory
6. Enter the password in terminal. All plugins will be installed.
    1. Don't activate any plugins. Plugins will be activated automatically by the script.
7. Change the directory to themes folder and then install GDF theme.
    1. Don't activate the theme. It will be activated automatically by the script.
8. cd to geodirectory root folder. In my case its /Users/giri/Sites/whoop/wp-content/plugins/geodirectory 
9. Execute this command vendor/bin/phpunit --debug 
10. selenium/completed.txt keeping the file number of last completed test. 
   So if completed.txt contains number 10, then previous 9 tests will be skipped. 
   Please reset this number to 0, if you want to start the test from the beginning.   
   
   
## Scrunitizer tests ##
build:
    environment:
        variables:
            GITHUB_REPO_USER: ''
            BITBUCKET_LOGIN_USER: ''
            BITBUCKET_REPO_USER: ''
            BITBUCKET_LOGIN_PASS: ''   