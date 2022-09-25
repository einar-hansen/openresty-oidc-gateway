# Getting Started


```bash
cp api
touch database/database.sqlite
cp .env.example .env
php artisan migrate
```

Fill inn the .env file:
```
AUTH0_DOMAIN=
AUTH0_CLIENT_ID=
AUTH0_CLIENT_SECRET=
AUTH0_AUDIENCE=
```

Prepare openresty

```
cd openresty
cp conf/auth.conf.example conf/auth.conf
```

Now edit the file and add the auth0 credentials

## Laravel
The app is for demo purpose only, so we are using a sqlite database. We have also slimmed down the user migrations file and added a `sub` column. 

You should add the credentials to the `.env` file.

In the `config/auth.php` file I've added a new guard. Check out [Laravels documentation for creating a custom guard](https://laravel.com/docs/9.x/authentication#closure-request-guards). 

It's probably not the best idea to use `firstOrCreate` in the guard, be we are doing it for now as we naivly trus the values from OpenResty. In production you should probably add some more checks before automatically creating an user based on the data from the headers.
```php
...
'guards' => [
    ...
    'auth0' => [
        'driver' => 'auth0-subject-token',
    ],
],

'audience' => [
    env('AUTH0_CLIENT_ID'),
    env('AUTH0_AUDIENCE'),
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
                        'email' => $request->header('x-auth-emai'),
                    ]);
                }

                return null;
            }
        );
}
```

# Starting


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
