<?php

namespace Tests\Unit;

use Tests\TestCase;
//use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\StatisticUsing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StatisticUsingTest extends TestCase
{
    protected $stat;
    
    public function setUp() : void
    {
        parent::setUp();
        $db = DB::connection();
        $validator = Validator::make([], []);
        $this->stat = new StatisticUsing($db, $validator);        
    }
    
    public function testExample()
    {
        $res = $this->stat->getData();
        $this->assertIsArray($res);
    }
    
    
}
