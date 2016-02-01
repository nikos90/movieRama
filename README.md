movieRama
=========

A Symfony project created on January 30, 2016, 2:10 pm.

### Installation

Clone the repo && go to directory
```sh
cd movieRama
```

Download Composer
```sh
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```
Update Dependencies
```sh
composer update
```

Update database credentials on app/config/parameters.yml file to match your mysql credentials
```sh
    database_host: localhost
    database_port: 3306
    database_name: movierama_db
    database_user: root
    database_password: root
```
Run the schema update
```sh
php app/console doctrine:schema:update --force
```

Publish the assets
```sh
php app/console assets:install --symlink
```

Clear the cache

```sh
php app/console cache:clear --env=prod
php app/console cache:clear --env=dev
```


