<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignDetails extends Model
{
    protected $table        = 'campaigns_details';
    protected $fillable     = ['campaigns_id', 'contents_id'];
  
    public function campaign()
    {
        return $this->belongsTo('App\Models\Campaign', 'id');
    }
    
    public function content()
    {
        return $this->belongsTo('App\Models\Content', 'id');
    }
    
}
