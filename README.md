# Overview
This is an implementation of a gateway “service” using OpenResty. OpenResty is based on Nginx, and in this case will work as an Nginx reverse proxy. In addiditon to beeing a reverse-proxy, it will authenticate every request via Auth0 using OpenId Connect.

The project has a NextJS frontend, and a Laravel API service. It can be extended with as many services as you would like, as illustrated below.

![Concept illustration](https://github.com/einar-hansen/openresty-oidc-gateway/blob/main/image.jpg?raw=true)


# Getting Started
Clone repository
```bash
git clone https://github.com/einar-hansen/openresty-oidc-gateway.git 
cd openresty-oidc-gateway
```

## OpenResty
```bash
# From project root.
cd openresty
cp conf/auth.conf.example conf/auth.conf

# Add Auth0 credentials to conf/auth.conf, and then this module is done
# Update between lines 18 -> 33
vi conf/auth.conf
```

Now this part is complete!

## Laravel
```bash
# From project root.
cd api

# Let's create a environment file
cp .env.example .env
# Now update it with Auth0 Credentials
vi .env

# Let's install composer locally, since it doesn't come with PHPs Docker Image
docker run -it --rm -v "$(pwd)":/app -w /app php:8.1-fpm-alpine \
    php -r "copy('https://getcomposer.org/installer', 'bin/composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'bin/composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('bin/composer-setup.php'); } echo PHP_EOL;" \
    && php bin/composer-setup.php \
    && php -r "unlink('bin/composer-setup.php');" \
    && php -r "rename('composer.phar', 'bin/composer.phar');" 

docker run -it --rm -v "$(pwd)":/app -w /app php:8.1-fpm-alpine bin/composer.phar install
docker run -it --rm -v "$(pwd)":/app -w /app php:8.1-fpm-alpine php artisan key:generate
docker run -it --rm -v "$(pwd)":/app -w /app php:8.1-fpm-alpine php artisan migrate
```

## NextJS
```bash
# From project root.
cd app

docker run -it --rm -v "$(pwd)":/app -w /app node:alpine npm install && next build
```

# Running

You are now ready to run the app. Go ahead and start docker and try the URL's in your browser.

```bash
# From the project root dir
docker-compose -f docker-compose.yml up
# or as a daemon, but I like to see the log
docker-compose -f docker-compose.yml up -d
```

Visit one of the project URL's in the browser:

- NextJS [http://0.0.0.0](http://0.0.0.0)
- Laravel public route [http://0.0.0.0/web](http://0.0.0.0/web)
- Laravel private route [http://0.0.0.0/api/user](http://0.0.0.0/api/user)
- Log out route [http://0.0.0.0/logout](http://0.0.0.0/logout)

## About Laravel App

The app is for demo purpose only, so we are using a sqlite database. We have also slimmed down the user migrations file and added a `sub` column. 

You should add the credentials to the `.env` file.

In the `config/auth.php` file I've added a new guard. Check out [Laravels documentation for creating a custom guard](https://laravel.com/docs/9.x/authentication#closure-request-guards). 

It's probably not the best idea to use `firstOrCreate` in the guard, be we are doing it for now as we naivly trust the values from OpenResty. In production you should probably add some more checks before automatically creating an user based on the data from the headers.
```php
...
'guards' => [
    ...
    'auth0' => [
        'driver' => 'auth0-subject-token',
    ],
],

'audience' => [
    env('AUTH0_CLIENT_ID'), // To allow for traditional server APP - OIDC
    env('AUTH0_AUDIENCE'), // To check audience on API Tokens
],
```


```php
public function boot()
{
    $this->registerPolicies();
    $this->app->make('auth')
        ->viaRequest(
            'auth0-subject-token',
            function (Request $request) {
                if (in_array($request->header('x-auth-audience'), $this->app['config']['auth']['audience'])) {
                    return User::firstOrCreate([
                        'sub' => $request->header('x-auth-subject'),
                    ], [
                        'name' => $request->header('x-auth-name'),
                        'email' => $request->header('x-auth-email'),
                    ]);
                }

                return null;
            }
        );
}
```
