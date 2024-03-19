# Expense Management System with Symfony [BE]

In this project we have created a RESTful API backend for expense management application, based on PHP 8 symfony framework. 

This application allows you to manage expenses with all API that you might need to build a front end application.
<br>

Now let's jump into the project testing :D

## Prerequisite
- Docker and docker-compose installed in minimal version 1.24. You can check version with docker-compose -v If you're missing this requirement please refer docker-installation.md file.


## Insallation

1. Clone this repository by running the following command in the desired directory:
```bash
git clone https://github.com/alihemadeh/expense.git
```

2. Change directory to access the root folder of the project:
```bash
cd expense
```

3. Run the docker container to start the project:
```bash
docker-compose up -d --build 
```

later you can only use ``docker-compose up -d`` inside your root directory

Once you can access the web application throw the port defined in `.env` file port `:8101`
and the database throw port `:33016` (also you can access phpmyadmin webpage for DB managing throw port: `8102`)

4. Access the container:
```bash
docker exec -it expense-server bash
```

5. Run the migrations inside the container, and then clear the cache:
```bash
php bin/console doctrine:migrations:execute --up 'DoctrineMigrations\Version20240314144059'
```
## Configuration

- Configuration is already done. But in case you want to change anything please feel free to update the `.env` file before running the containers.

```bash 
APP_NAME=expense
APP_PORT=8101
APP_DB_ADMIN_PORT=8102
DB_PORT=33016

MYSQL_ROOT_PASS=root
MYSQL_USER=root
MYSQL_PASS=root
MYSQL_DB=expense
```

## Testing
Now that you have set your project up, it's time for fun testing. <br>
Since this is a Back End application, so you will have to download Postman (https://www.postman.com/downloads/)

Now to help you check all the API endpoints, I have prepared a Postman Collection that you can download as well:
https://drive.google.com/drive/folders/1Vnksa2_blbvARvoXKTopnYExG6LvQo-3?usp=sharing

Happy Testing! :)

## Improvements
Of course this project can be enhanced a lot, where it can have: 
- User authentication
- List filtering
- List pagination
- etc .. 
