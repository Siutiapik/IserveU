<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use App\Vote;
use App\Events\UserChangedVote;

class VoteController extends ApiController {


	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if(Auth::user()->can('view-vote')){ //Administrator able to see any vote
			return Vote::all();	
		}		
		return Vote::where('user_id',Auth::user()->id)->get(); //Get standard users comment votes	
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create(){

		if(!Auth::user()->can('create-votes')){
			abort(401,'You do not have permission to create a vote on a motion');			
		}

		return (new Vote)->fields;
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//Check if the user has permission to cast votes
		if(!Auth::user()->can('create-votes')){
			abort(401,'You do not have permission to create a vote');
		}

		//Validate the input
		$input = Request::all();
		$input['user_id'] = Auth::user()->id;
		$vote  = new Vote($input);
 		if(!$vote->save()){
			abort(403,$vote->errors);
		}
			
		return $vote;
	}

	/**
	 * Display the specified resource. User with decent permissions can see who posses other people votes, or you can see your own vote.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$vote = Vote::find($id);
		if(!$vote){
			abort(403,"There is no vote with the id of $id");
		}
	
		if(Auth::user()->can('show-votes')){ //Is a person who can review votes
			return $vote;
		}

		if($vote->user_id != Auth::user()->id){		//This is not the person who cast the vote
			abort(401,"You do not have permission to see this vote");
		}
		
		return $vote; //This person has no right to see this vote
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$vote = Vote::find($id);
		if(!$vote){
			abort(403,"This vote does not exist ($id)");
		}
		return $vote->fields;
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//Check if the user has permission to cast votes
		if(!Auth::user()->can('create-votes')){
			abort(401,'You do not have permission to update a vote');
		}

		$vote = Vote::find($id);
		if(!$vote){
			abort(403,"This vote does not exits ($id)");
		}

		if($vote->user_id != Auth::user()->id){
			abort(401,"This user does not have permission to edit this vote");
		}

		//Validate the input
		$input = Request::all();
		$vote->position = $input['position'];

 		if(!$vote->save()){
			abort(403,$vote->errors);
		}
		//event(new UserChangedVote($vote));
		return $vote;
	}

	/**
	 * You can't delete a vote, just switch to abstain
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		if(!Auth::user()->can('create-votes')){
			abort(401,"user can not create or destroy votes");
		}

		$vote = Vote::find($id);
		if(!$vote){
			abort(403,"Vote does not exist ($id)");
		}

		if($vote->user_id != Auth::user()->id){
			abort(401,"User does not have permission to destroy another users vote");
		}

		$vote->position = 0;
		$vote->save();
		return $vote;
		
	}

}
