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
    
    public function testNoFilter()
    {
        $this->stat->fromMonth = 1;
        $this->stat->toMonth = 2;
        $this->stat->fromYear = 2018;
        $this->stat->toYear = 2018;
        $res = $this->stat->getData();
        $this->assertIsArray($res);
    }
    
    public function testValidFilter()
    {
        $this->stat->fromMonth = 1;
        $this->stat->toMonth = 12;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->assertTrue($this->stat->validate());    
    }
    
    public function testBadFilterFromMonthstring()
    {
        $this->stat->fromMonth = 'test';
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterFromMonthFloat()
    {
        $this->stat->fromMonth = '1.1';
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterFromMonthMax()
    {
        $this->stat->fromMonth = 14;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterFromMonthMin()
    {
        $this->stat->fromMonth = 0;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }    
    
    public function testBadFilterToMonthString()
    {
        $this->stat->toMonth = 'test';
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterToMonthFloat()
    {
        $this->stat->toMonth = '1.1';
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterToMonthMax()
    {
        $this->stat->toMonth = 14;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }
    
    public function testBadFilterToMonthMin()
    {
        $this->stat->toMonth = 0;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }    
    
    public function testBadFilterToMonthNoBiggerFromMonth()
    {
        $this->stat->toMonth = 2;
        $this->stat->fromMonth = 4;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();
    }    
    
    public function testBadFilterNoYearWhenSetMonth()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();       
    }
    
    public function testBadFilterNoFromYearWhenSetFromMonth()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }
    
    public function testBadFilterNoToYearWhenSetToMonth()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }
    
    public function testBadFilterToYearSmallerFromYear()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2017;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }    
    
    public function testBadFilterBadToYear()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = 2019;
        $this->stat->toYear = 2020;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }        
    
    public function testBadFilterBadFromYear()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = 1950;
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }        
    
    public function testBadFilterBadFromYearString()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = '2017';
        $this->stat->toYear = 2019;
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }            
    
    public function testBadFilterBadToYearString()
    {
        $this->stat->toMonth = 1;
        $this->stat->fromMonth = 12;
        $this->stat->fromYear = 2017;
        $this->stat->toYear = '2019';
        $this->expectException(\InvalidArgumentException::class);
        $this->stat->validate();        
    }                
}
