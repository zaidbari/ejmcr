# Course: DBMS2
<hr />

## Final Project
This is a project made for Database management 2 project for Cyprus International University by Two team members, (Muhammad Zaid Bari & Nouman Usman). Project was created using MySQL (mariadb) for DDL and for queries and frontend a custom framework made by the team member Muhammad Zaid Bari using PHP and templating engine TWIG was used. We used phpstorm as our code editor and phpMYAdmin and Xampp for our MySQL server.

## Team Members
- Muhammad Zaid Bari (21901798 | hello@itszbari.com)
- Nouman Usman (21901797 | usmannouman56@gmail.com)

## Requirements
- Download and install composer (https://getcomposer.org/download/)
- Download and install NodeJS (https://nodejs.org/en/)
- Check NPM version ```npm -v```
- Check composer version ```composer -v```
- Check NodeJS version ```node -v```

## Setup and Installation
####1: Run the following command to install all the dependencies:
```bash
composer install
npm install
```

####2: Use ```script.sql``` to create the database and tables.

####3: Run the project using the following command:
```bash
php -S localhost:8000
```

## Usage
####1: Use the following commands to create a new user:
```bash
curl --location --request POST 'localhost:8000/user/create' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--header 'Cookie: PHPSESSID=p9k9nntt9odpmb1mkmsesue4bi' \
--data-urlencode 'email=Marian68@gmail.com' \
--data-urlencode 'password=Password!' \
--data-urlencode 'confirm_password=Password!' \
--data-urlencode 'name=Dr. Alvin Purdy' \
--data-urlencode 'address=some address' \
--data-urlencode 'phone=990-409-5747' \
--data-urlencode 'city=lefkosa' \
--data-urlencode 'role=admin'
```
The above command will create a new user with the provided details and an admin role.


####2: Or use the login and signup pages to login and signup:
