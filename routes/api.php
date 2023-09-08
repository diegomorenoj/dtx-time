<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ConstancyController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\TrainingRequestController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ObjectiveController;
use App\Http\Controllers\ObjectiveSpecialtyController;
use App\Http\Controllers\TrainingRequestsCommentsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// RUTAS SIN AUTENTICACIÓN
Route::post('users/register', 'App\Http\Controllers\UserController@register');
Route::post('users/login', 'App\Http\Controllers\UserController@authenticate');

// RUTAS CON AUTENTICACIÓN
Route::group(['middleware' => ['jwt.verify']], function() {

    Route::get('file/{archivo}', function ($archivo) {
        $url = public_path().'/files/'.$archivo;
        //verificamos si el archivo existe y lo retornamos
        if (file_exists($url))
        {
          return response()->download($url);
        }else{
            //si no se encuentra lanzamos un error 404.
            abort(404);
        }
    });

    Route::get('start', function () {
        return 'welcome';
    });

    Route::get('users/user','App\Http\Controllers\UserController@getAuthenticatedUser');
    Route::post('users/logout','App\Http\Controllers\UserController@logout');
    Route::get('users', 'App\Http\Controllers\UserController@index');
    Route::get('ldap/importusers', 'App\Http\Controllers\LdapController@importLdapUsers');
    Route::get('ldap/validateusers', 'App\Http\Controllers\LdapController@validateStatus');
    Route::get('parameters/type/{tp_parameter_id}', 'App\Http\Controllers\ParameterController@getByType');
    Route::get('parameters/status/{training_request_id}', 'App\Http\Controllers\ParameterController@getStatesByTrainingAndRole');
    Route::resource('users', UserController::class);
    Route::resource('rols', RolController::class);
    Route::resource('specialties', SpecialtyController::class);
    Route::post('specialties/filter', 'App\Http\Controllers\SpecialtyController@getByFilter');
    Route::put('specialties/excel/{id}', 'App\Http\Controllers\SpecialtyController@storeExcel');
    Route::resource('providers', ProviderController::class);
    Route::resource('trainings', TrainingRequestController::class);
    Route::put('trainings/changestatus/{id}', 'App\Http\Controllers\TrainingRequestController@changeStatus');
    Route::get('trainings/user/{user_id}','App\Http\Controllers\TrainingRequestController@getByUser');
    Route::post('trainings/filter', 'App\Http\Controllers\TrainingRequestController@getByFilter');
    Route::get('trainings/search/{val}','App\Http\Controllers\TrainingRequestController@filter');
    Route::resource('courses', CourseController::class);
    Route::post('courses/filter', 'App\Http\Controllers\CourseController@getByFilter');
    Route::post('courses/dashboard/filter', 'App\Http\Controllers\CourseController@getDashboardByFilter');
    Route::post('courses/instructors/filter', 'App\Http\Controllers\CourseController@getInstructorsByFilter');
    Route::post('courses/excel/course', 'App\Http\Controllers\CourseController@storeExcel');
    Route::post('courses/excel/user', 'App\Http\Controllers\CourseController@storeUsersExcel');
    Route::post('courses/excelimport', 'App\Http\Controllers\CourseController@excelImport');
    Route::resource('objetives', ObjectiveController::class);
    Route::post('objetives/filter', 'App\Http\Controllers\ObjectiveController@getByFilter');
    Route::post('objetives/filtergeneral', 'App\Http\Controllers\ObjectiveController@getByFilterGeneral');
    Route::resource('objetivesspecialties', ObjectiveSpecialtyController::class);
    Route::post('objetivesspecialties/filter', 'App\Http\Controllers\ObjectiveSpecialtyController@getByFilter');
    Route::resource('comments', TrainingRequestsCommentsController::class);
    Route::resource('files', FileController::class);
    Route::resource('constancies', ConstancyController::class);
    Route::resource('budgets', BudgetController::class);
    Route::get('cycles', 'App\Http\Controllers\CycleController@index');
    Route::post('budgets/store', 'App\Http\Controllers\BudgetController@storeBudgets');
    
    Route::get('parameters/areas/all','App\Http\Controllers\ParameterController@getAllAreas');
    Route::get('parameters/positions/area/{area}','App\Http\Controllers\ParameterController@getPositionsByArea');
    Route::get('parameters/levels/position/{position}','App\Http\Controllers\ParameterController@getLevelsByPosition');
    
    Route::get('parameters/filterusers/{val}','App\Http\Controllers\ParameterController@filterUsers');
    Route::get('parameters/filtercities/{val}','App\Http\Controllers\ParameterController@filterCities');
    Route::get('parameters/filterareas/{val}','App\Http\Controllers\ParameterController@filterAreas');
    Route::get('parameters/filterareasbycity/{city}/{val}','App\Http\Controllers\ParameterController@filterAreasByCity');
    Route::get('parameters/filterpositions/{val}','App\Http\Controllers\ParameterController@filterPositions');
    Route::get('parameters/filterlevels/{val}','App\Http\Controllers\ParameterController@filterLevels');
    
});
