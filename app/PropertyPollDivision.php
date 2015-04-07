<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PropertyPollDivision extends Model {

	public function properties(){
		return $this->hasMany('App\Property');
	}

	public function users(){
		return $this->hasManyThrough('App\User','App\Property');
	}

}
