
#!/bin/bash
yum groupinstall -y "Web Server" "MySQL Database" "PHP Support"
# Install Apache 2.4
sudo yum install httpd24

# Install PHP 7.0 
# automatically includes php70-cli php70-common php70-json php70-process php70-xml
sudo yum install php70

# Install additional commonly used php packages
sudo yum install php70-gd
sudo yum install php70-imap
sudo yum install php70-mbstring
sudo yum install php70-mysqlnd
sudo yum install php70-opcache
sudo yum install php70-pdo
sudo yum install php70-pecl-apcu