<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Input;
use Auth;
use DB;
use League\Csv\Reader;

use App\PropertyBlock;
use App\PropertyPollDivision;
use App\PropertyAssessment;
use App\PropertyCoordinate;
use App\PropertyPlan;
use App\Property;
use App\PropertyZoning;
use App\PropertyDescription;


class PropertyController extends ApiController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if(!Auth::user()->can('administrate-properties')){ //Logged in user will want to see if they voted on these things
			abort(401,'You do not have permission to see property assessments');
		}

		$limit = Request::get('limit') ?: 50;

		return $property = Property::simplePaginate($limit);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if(!Auth::user()->can('create-properties')){
			abort(401,'You do not have permission to create a property assessment');
		}

		return (new Property)->fields;	
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		if(!Auth::user()->can('create-properties')){
			abort(401,'You do not have permission to create a property assessment');
		}

		$property = (new Property)->secureFill(Request::all()); 
		if(!$property->save()){
		 	abort(403,$property->errors);
		}
     	return $property;
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show(Property $property)
	{
		return $property;
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit(Property $property)
	{
		if(!Auth::user()->can('create-properties')){
			abort(403,'You do not have permission to create/update propert yassessment');
		}

		if(!Auth::user()->can('administrate-properties')){ //Is not the user who made it, or the site admin
			abort(401,"This user can not administrate this property assessment ($id)");
		}

		return $property->fields;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Property $property)
	{
		if(!Auth::user()->can('create-properties')){
			abort(403,'You do not have permission to update a property assessment');
		}

		if(!Auth::user()->can('administrate-properties')){ //Is not the user who made it, or the site admin
			abort(401,"This user can not edit property assessment ($id)");
		}

		$property->secureFill(Request::all());

		if(!$property->save()){
		 	abort(403,$property->errors);
		}

		$property->save();
		
		return $property;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy(Property $property)
	{
		//
	}

	public function uploadCSV(){
		Request::file('csvfile')->move(base_path()."/storage/uploads",'properties.csv');

		$csv = Reader::createFromPath(base_path()."/storage/uploads/properties.csv");
		$allrows = $csv->setOffset(1)->fetchAll(); //because we don't want to insert the header
				
		/* csv format
		    [0] => Roll Number
		    [1] => Block
		    [2] => Plan
		    [3] => Unit
		    [4] => Civic Address
		    [5] => Street Name
		    [6] => Zoning
		    [7] => Description Code
		    [8] => Description
		    [9] => Assessment Improvement Value
		    [10] => Assessment Land Value
		    [11] => Assessment Other Value
		    [12] => Assessment Year
		    [13] => Assessment Total Value
		    [14] => Poll Division
		    [15] => Poll Division Name
		    [16] => Voting Station
		    [17] => Latitude
		    [18] => Longitude
		*/
		ini_set('max_execution_time', 300);
		foreach($allrows as $row){

			$property = Property::where('roll_number',trim($row[0]))->first();
			
			if(!$property){ //If the property hasn't been entered then we might not have all these, otherwise just check the assesment value hasn't changed
				$block = PropertyBlock::where('name',trim($row[1]))->first();
				if($block==null){
					$block = new PropertyBlock;
					$block->name = $row[1];
					$block->save();
				}

				$poll = PropertyPollDivision::where('name',trim($row[15]))->first();
				if($poll==null){
					$poll = new PropertyPollDivision;
					$poll->name = $row[15];
					$poll->voting_station = $row[16];
					$poll->save();
				}

				$zone = PropertyZoning::where('type',trim($row[6]))->first();
				if($zone==null){
					$zone = new PropertyZoning;
					$zone->type = $row[6];
					$zone->save();
				}

				$propertyDescription = PropertyDescription::where('description_code',trim($row[7]))->first();
				if($propertyDescription==null){
					$propertyDescription = new PropertyDescription;
					$propertyDescription->description_code = $row[7];
					$propertyDescription->description = $row[8];
					$propertyDescription->save();
				}

				$plan = PropertyPlan::where('name',trim($row[6]))->first();
				if($plan==null){
					$plan = new PropertyPlan;
					$plan->name = $row[2];
					$plan->save();
				}

				$coordinate = PropertyCoordinate::whereRaw("latitude = ".trim($row[17])." AND longitude = ".trim($row[18]))->first();
				if($coordinate==null){
					$coordinate = new PropertyCoordinate;
					$coordinate->latitude 	= 	$row[17];
					$coordinate->longitude 	= 	$row[18];
					$coordinate->block_id 		= 	$block->id;
					$coordinate->save();
				}

				$property = new Property;
				$property->roll_number 	= 	$row[0];
				$property->address 		= 	$row[4];
				$property->street 		= 	strtolower($row[5]);
				$property->unit 		= 	strtolower($row[3]);

				$property->property_block_id 			= 	$block->id;
				$property->property_coordinate_id 		= 	$coordinate->id;
				$property->property_poll_division_id 	= 	$poll->id;
				$property->property_zoning_id 			= 	$zone->id;
				$property->property_description_id		= 	$propertyDescription->id;
				$property->property_plan_id				= 	$plan->id;

				if(!$property->save()){
				 	abort(403,$property->errors);
				}
			}

			$row[9] = intval(str_replace(",","",$row[9]));
			$row[10] = intval(str_replace(",","",$row[10]));
			$row[11] = intval(str_replace(",","",$row[11]));
   			$propertyId = $property->id;



			$assessment = PropertyAssessment::whereRaw("improvement_value = $row[9] AND land_value = $row[10] AND other_value = $row[11] AND property_id = $propertyId")->first();
			if($assessment==null){
				$assessment = new PropertyAssessment;
  				$assessment->land_value			= $row[10];
  				$assessment->improvement_value	= $row[9];
  				$assessment->other_value		= $row[11];
  				$assessment->year				= $row[12];
  				$assessment->property_id			= $propertyId;
  				$assessment->save();
			}
		}
	}
}
