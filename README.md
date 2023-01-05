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

5. Test the API endpoints using Postman, etc.

## Features

| App Features                                                     |
| :--------------------------------------------------------------- |
| Built on [Lumen](https://laravel.com/) 8                         |
| Uses [MySQL](https://github.com/mysql) Database (can be changed) |
| Login, Register with JWTAuth                                     |
| CRUD                                                             |
| Input data by uploading CSV Files.                               |
| Improved Exception Handling                                      |

## Endpoints

| method | uri                         | params          | description                                                          |
| ------ | --------------------------- | --------------- | -------------------------------------------------------------------- |
| POST   | `/login`                    | described later | Login and getting the JWT Token                                      |
| POST   | `/register`                 | described later | Register using some params                                           |
| POST   | `/logout`                   | none            | Logout and reset the JWT                                             |
| GET    | `/user-profile`             | none            | Get logged-in user                                                   |
| POST   | `/refresh`                  | none            | Refresh the JWT Token                                                |
| GET    | `/api/nilai`                | none            | Return all "nilai" tables                                            |
| GET    | `/api/avg-nilai`            | none            | Return all "nilai" tables and average by mahasiswa                   |
| GET    | `/api/avg-nilai-jurusan`    | none            | Return all "nilai" tables and average by jurusan                     |
| POST   | `/api/nilai`                | none            | Add data nilai. Only dosen role allowed                              |
| POST   | `/api/upload-nilai`         | none            | Upload `.csv` and add the files to database. Only Dosen role allowed |
| PUT    | `/api/nilai/{nim}/{matkul}` | none            | Edit data nilai. Only Dosen Role Allowed                             |
| DELETE | `/api/nilai/{nim}/{matkul}` | none            | Delete data nilai. Only dosen role allowed                           |

## Note

Enjoy!

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
