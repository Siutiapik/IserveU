<?php namespace App\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider {

	/**
	 * This namespace is applied to the controller routes in your routes file.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function boot(Router $router)
	{
		parent::boot($router);

		$router->model('user','App\User');
		$router->model('motion','App\Motion');
		$router->model('file','App\File');
		$router->model('vote','App\Vote');
		$router->model('comment','App\Comment');
		$router->model('comment_vote','App\CommentVote');
		$router->model('background_image','App\BackgroundImage');
		$router->model('department', 'App\Department');
		$router->model('property', 'App\Property');
		$router->model('propertyassessment', 'App\PropertyAssessment');
		$router->model('propertyblock', 'App\PropertyBlock');
		$router->model('propertycoordinate', 'App\PropertyCoordinate');
		$router->model('propertydescription', 'App\PropertyDescription');
		$router->model('propertyplan', 'App\PropertyPlan');
		$router->model('propertypolldivision', 'App\PropertyPollDivision');
		$router->model('propertyzone', 'App\PropertyZoning');

	}


	/**
	 * Define the routes for the application.
	 *
	 * @param  \Illuminate\Routing\Router  $router
	 * @return void
	 */
	public function map(Router $router)
	{
		$router->group(['namespace' => $this->namespace], function($router)
		{
			require app_path('Http/routes.php');
		});
	}

}
