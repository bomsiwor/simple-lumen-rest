# Simple REST API Lumen

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

This project is implementing REST API to Laravel Lumen using simple database and authorization (JWT).

## Contributing

I say Thank! for you who contributing to improve this project!

## Installation Instruction

1. Run `git clone https://github.com/bomsiwor/simple-lumen-rest.git your-folder`
2. Start your database server for the project then import `db.sql` from /database
3. Configure your `.env` file.
4. Run the local server using

```
php -S localhost:8000 -t public
```

5. Test the API using endpoints.

## Endpoints

| method | uri         | params          | description                     |
| ------ | ----------- | --------------- | ------------------------------- |
| POST   | `/login`    | described later | Login and getting the JWT Token |
| POST   | `/register` | described later | Register using some params      |

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
