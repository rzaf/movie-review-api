# movie-review-api

media rating rest api using laravel framework

## Build

- clone repository `git clone https://github.com/rzaf/movie-review-api.git`
- cd to directory `cd movie-review-api`
- create .env file and configure your database `cp .env.example .env`
- install dependencies `composer install`
- genereate new keys `php artisan key:generate`
- run migrations and seed database `php artisan migrate --seed`
- run server `php artisan serve`
- visit `/api/documentation` route for swagger docs

## API Endpoints

protected routes require authentication with api token.
admin routes require authenticated user to be admin .

#### Users

| type |                 url                |protected|admin |
|------|------------------------------------|-------|-------|
| GET  |`api/users`                         |&cross;|&cross;|
| POST |`api/users/`                        |&cross;|&cross;|
| POST |`api/users/login`                   |&cross;|&cross;|
| GET  |`api/users/{username}`              |&cross;|&cross;|
| PUT  |`api/users/{username}`              |&check;|&cross;|
|DELETE|`api/users/{username}`              |&check;|&cross;|

#### Categories

| type |                 url                |protected|admin|
|------|------------------------------------|-------|-------|
| GET  |`api/categories`                    |&cross;|&cross;|
| POST |`api/categories/`                   |&check;|&check;|
| GET  |`api/categories/{name}`             |&cross;|&cross;|
| PUT  |`api/categories/{name}`             |&check;|&check;|
|DELETE|`api/categories/{name}`             |&check;|&check;|

#### People

| type |                 url                |protected|admin|
|------|------------------------------------|-------|-------|
| GET  |`api/people`                        |&cross;|&cross;|
| GET  |`api/people/{url}`                  |&cross;|&cross;|
| GET  |`api/people/{url}/medias`           |&cross;|&cross;|
| POST |`api/people/`                       |&check;|&check;|
| PUT  |`api/people/{url}`                  |&check;|&check;|
|DELETE|`api/people/{url}`                  |&check;|&check;|
| GET  |`api/people/{url}/following`        |&check;|&cross;|
|DELETE|`api/people/{url}/following`        |&check;|&cross;|

#### Medias

| type |                 url                      |protected|admin|
|------|------------------------------------------|-------|-------|
| GET  |`api/medias`                              |&cross;|&cross;|
| GET  |`api/medias/{url}`                        |&cross;|&cross;|
| POST |`api/medias`                              |&check;|&check;|
| PUT  |`api/medias/{url}`                        |&check;|&check;|
|DELETE|`api/medias/{url}`                        |&check;|&check;|
| POST |`api/medias/{url}/genres/{name}`          |&check;|&check;|
|DELETE|`api/medias/{url}/genres/{name}`          |&check;|&check;|
| POST |`api/medias/{url}/kewords/{name}`         |&check;|&check;|
|DELETE|`api/medias/{url}/kewords/{name}`         |&check;|&check;|
| POST |`api/medias/{url}/languages/{name}`       |&check;|&check;|
|DELETE|`api/medias/{url}/languages/{name}`       |&check;|&check;|
| POST |`api/medias/{url}/countries/{name}`       |&check;|&check;|
|DELETE|`api/medias/{url}/countries/{name}`       |&check;|&check;|
| POST |`api/medias/{url}/companies/{name}`       |&check;|&check;|
|DELETE|`api/medias/{url}/companies/{name}`       |&check;|&check;|
| POST |`api/medias/{url}/people/{person_url}`    |&check;|&check;|
|DELETE|`api/medias/{url}/people/{person_url}`    |&check;|&check;|

#### Reviews

| type |                 url                      |protected|admin|
|------|------------------------------------------|-------|-------|
| GET  |`api/reviews/{review_id}`                 |&cross;|&cross;|
|DELETE|`api/reviews/{review_id}`                 |&check;|&cross;|
| PUT  |`api/reviews/{review_id}`                 |&check;|&cross;|
| POST |`api/medias/{url}/reviews`                |&check;|&cross;|
| GET  |`api/medias/{url}/reviews`                |&cross;|&cross;|

#### Replies

| type |                 url                      |protected|admin|
|------|------------------------------------------|-------|-------|
| GET  |`api/replies/{reply_id}`                  |&cross;|&cross;|
|DELETE|`api/replies/{reply_id}`                  |&check;|&cross;|
| PUT  |`api/replies/{reply_id}`                  |&check;|&cross;|
| POST |`api/replies`                             |&check;|&cross;|
| GET  |`api/reviews/{review_id}/replies`         |&cross;|&cross;|
| GET  |`api/replies/{reply_id}/replies`          |&cross;|&cross;|

#### Likes/Dislikes

| type |                 url                      |protected|admin|
|------|------------------------------------------|-------|-------|
| POST |`api/medias/{url}/like`                  |&check;|&cross;|
|DELETE|`api/medias/{url}/like`                  |&check;|&cross;|
| POST |`api/reviews/{review_id}/like`           |&check;|&cross;|
|DELETE|`api/reviews/{review_id}/like`           |&check;|&cross;|
| POST |`api/replies/{reply_id}/like`            |&check;|&cross;|
|DELETE|`api/replies/{reply_id}/like`            |&check;|&cross;|
