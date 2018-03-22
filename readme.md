
## Servicio Login

Es un servicio API REST para encargarse de gestionar el perfil del los usuarios basado en Laravel, además de
la autenticación y autorización de estos. Para la autenticación se  utiliza JWT como token.
Se consume un api rest generada en el sitio http://www.mockapi.io ( http://5ab1f27462a6ae001408c1aa.mockapi.io/users).


## Instalación

- $ git clone https://github.com/FernandoGomezS/Login.git
- $ cd Login
- $ composer install
- $ cp .env.example .env
- $ php artisan key:generate
- $ php artisan jwt:secret


## Docker

- $ docker-compose up -d
- $ docker exec -it login_app_1 /bin/bash
- # php artisan migrate
- # exit

## Api

- POST: localhost:8080/api/login
- POST: localhost:8080/api/new
- GET/PUT/DELETE: localhost:8080/api/me
- POST: localhost:8080/api/logout

