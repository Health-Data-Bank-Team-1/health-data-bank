# Health Data Bank Backend Setup Guide

## Tech Stack
- Laravel (PHP framework)
- MySQL (database)
- Docker
- Laravel Sail containers (Laravel’s Docker dev environment)
- PHPStorm (IDE)

## Step 1. Clone the repo
git clone https://github.com/Health-Data-Bank-Team-1/health-data-bank.git
cd health-data-bank

## Step 2. Start the Docker Containers
Run Docker Desktop
Open the project in PHPStorm
In the terminal, run “docker-compose -f compose.yaml up -d”
This will start the Laravel app container as well as the MySQL database container

## Step 3. Configure the Env File
Copy the example file:
cp .env.example .env
Generate the app key:
docker-compose -f compose.yaml exec laravel.test php artisan key:generate

Finish up by running
docker-compose -f compose.yaml exec laravel.test php artisan config:clear
docker-compose -f compose.yaml exec laravel.test php artisan cache:clear

## Step 4: Set up the database
Run Migrations:
docker-compose -f compose.yaml exec laravel.test php artisan migrate
This will create the required tables (at the moment we just have the placeholder “patients” table)

Should migrations fail, you can run
docker-compose -f compose.yaml exec laravel.test php artisan migrate:fresh

## Step 5: Install Node Dependencies
The package we are using for features such as user registration/authentication, 2FA, role management, security features etc. Jetstream, requires this for frontend assets

Run the following:
docker-compose -f compose.yaml exec laravel.test npm install
docker-compose -f compose.yaml exec laravel.test npm run build

## Step 6: Accessing the application
The backend API will be running at:
http://localhost/api

As an example:

GET http://localhost/api/patients

## Step 7: Familiarizing with the API
In order to familiarize yourself, create a test patient with the following powershell command in the terminal

irm http://localhost/api/patients -Method Post -Body '{"name":"Firstname Lastname","email":"firstname@test.com"}' -ContentType 'application/json'

This will create a patient using the model laid out already

Test that this was successful by running the following command to list patients in the database:

irm http://localhost/api/patients

# Backend Architecture

The backend uses a layered structure, being Route (Maps an HTTP request URL to a specific controller method.) -> Controller (Receives HTTP requests, validates basic input, calls the service layer) -> Service (Contains the business logic, deciding how the application will behave) -> Repository (Handles the database operations like queries, inserts, updates using Eloquent models) -> Model (Object representation of a database table which defines the stored fields and manages relationships between tables. Essentially the data structure of the system) -> Database (The MySQL database where the data is actually stored. Table structure and schema changes are defined through migrations).

I’ve created a “Patient” that follows this structure as an example. Every database entity has to follow this exact pattern. In order to add a new backend module, you should follow the process as follows:

**1. Create Model and Migration**

docker-compose -f compose.yaml exec laravel.test php artisan make:model ModelName -m

This will create a ModelName.php file in app/Models as well as a migration file in database/migrations

You can then edit the migration file with the items in the table, for example

Schema::create('ModelName', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('email');
$table->timestamps();
});

Then simply run the migration
docker-compose -f compose.yaml exec laravel.test php artisan migrate

**2. Update the model**

Find the newly created model in app/Models, and edit it to declare what fields can be stored. Matching the example above:

use Illuminate\Database\Eloquent\Model;

class ModelName extends Model
{
protected $fillable = ['name', 'email'];
}

**3. Create Repository for the Database Layer**

Create:

app/Repositories/ModelNameRepository.php

This will be responsible for all of the database operations, see the Patient repo for an example

**4. Create Service (Business layer logic)**

app/Services/ModelNameService.php

This will handle all of the business rules and calls the associated repo. Again, see the Patient example for more details on how this might look.

**5. Create a Controller as the API entry layer**

Run:

docker-compose -f compose.yaml exec laravel.test php artisan make:controller Api/ModelNameController

This will receive the http requests and call the service layer

**6. Add a Route to Connect the API to the Controller**

In routes/api.php:

Add:

use App\Http\Controllers\Api\ModelNameController;

Route::apiResource('examples', ModelNameController::class);

This will expose API endpoints such as:

GET /api/examples

POST /api/examples

DELETE /api/examples/{id}

**7. Test the completed module**

Run:

irm http://localhost/api/examples

# Environment Variables

Configuration is managed through .env

The key values here are DB_HOST being the MySQL container, DB_DATABASE being the database name, DB_USERNAME, and DB_PASSWORD which are both self explanatory.

Do NOT commit the .env file, use .env.example as a template. Remember to run Docker before starting with Laravel commands.

# Misc

When pulling new code:

git pull
docker-compose -f compose.yaml exec laravel.test php artisan migrate

If any dependencies are ever missing:

docker-compose -f compose.yaml exec laravel.test composer install

docker-compose -f compose.yaml exec laravel.test npm install
