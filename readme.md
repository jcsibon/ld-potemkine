# Projet Potemkine

## Instructions d'installation

git clone https://github.com/lapeyre-digital/potemkine lapeyre-potemkine
cd lapeyre-potemkine
ln -s "/Applications/Sublime Text.app/Contents/SharedSupport/bin/subl" /usr/local/bin/sublime
installer node
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
composer install
npm install
sudo sublime /private/etc/hosts # 127.0.0.1 lapeyre.localhost
http://foundationphp.com/tutorials/vhosts_mamp.php