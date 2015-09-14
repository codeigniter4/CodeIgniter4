<?php

require_once 'system/DI/DI.php';
require_once dirname(__FILE__) .'/support/SimpleClass.php';
require_once dirname(__FILE__) .'/support/DependingClass.php';

use \CodeIgniter\DI\DI;

class DITest extends PHPUnit_Framework_TestCase {

    protected $config = [
        'services' => [
            'simple' => '\Tests\Support\SimpleClass',
            'depend' => '\Tests\Support\DependingClass',
        ]
    ];

    //--------------------------------------------------------------------

    public function setUp() {}
    public function tearDown() {
        DI::getInstance()->reset();
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Services
    //--------------------------------------------------------------------

    public function testCanCreateService()
    {
        $di = DI::getInstance( $this->config );

        $this->assertInstanceOf($this->config['services']['simple'], $di->make('simple'));
    }

    //--------------------------------------------------------------------

    public function testMakeCreatesDifferentInstances()
    {
        $di = DI::getInstance( $this->config );

        $first  = $di->make('simple');
        $second = $di->make('simple');

        $this->assertFalse($first === $second);
    }

    //--------------------------------------------------------------------

    public function testSingleReturnsSameInstance()
    {
        $di = DI::getInstance( $this->config );

        $first  = $di->single('simple');
        $second = $di->single('simple');

        $this->assertTrue($first === $second);
        $this->assertInstanceOf($this->config['services']['simple'], $first);
        $this->assertInstanceOf($this->config['services']['simple'], $second);
    }

    //--------------------------------------------------------------------

    public function testInjectInjectsCorrectClass()
    {
        $di =DI::getInstance( $this->config );

        $depends = $di->make('depend');

        $this->assertInstanceOf($this->config['services']['simple'], $depends->child);
    }

    //--------------------------------------------------------------------

    public function testInjectInjectsSingletons()
    {
        $di =DI::getInstance( $this->config );

        $simple  = $di->single('simple');
        $depends = $di->make('depend', true);

        $this->assertTrue($simple === $depends->child);
        $this->assertInstanceOf($this->config['services']['simple'], $simple);
        $this->assertInstanceOf($this->config['services']['simple'], $depends->child);
    }

    //--------------------------------------------------------------------

    public function testInjectInjectsNewInstances()
    {
        $di =DI::getInstance( $this->config );

        $simple  = $di->single('simple');
        $depends = $di->make('depend');

        $this->assertFalse($simple === $depends->child);
        $this->assertInstanceOf($this->config['services']['simple'], $simple);
        $this->assertInstanceOf($this->config['services']['simple'], $depends->child);
    }

    //--------------------------------------------------------------------

    public function testCanCreateWithClosures()
    {
        $di =DI::getInstance( $this->config );

        $di->register('another', function($di) { return $di->single('simple'); });

        $simple  = $di->single('simple');
        $another = $di->make('another');

        $this->assertTrue($simple === $another);
        $this->assertInstanceOf($this->config['services']['simple'], $simple);
        $this->assertInstanceOf($this->config['services']['simple'], $another);
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // the Instance
    //--------------------------------------------------------------------

    public function testGetInstanceMatches()
    {
        $first = DI::getInstance();
        $second = DI::getInstance(['some' => 'one']);

        $this->assertTrue($first === $second);
    }

    //--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Parameters
    //--------------------------------------------------------------------

    public function testWillSaveSimpleParameters()
    {
        $di = DI::getInstance();

        $expects = "Who Killed John Henry";

        $di->song = $expects;

        $this->assertEquals($expects, $di->song);
    }

    //--------------------------------------------------------------------

    public function testWillSaveClosureParameters()
    {
        $di = DI::getInstance();

        $expects = function () {
            return 'here';
        };

        $di->song = $expects;

        $this->assertEquals($expects, $di->song);
        $this->assertEquals('here', $di->song());
    }

    //--------------------------------------------------------------------

    public function testWillSaveClosureParametersWithArgs()
    {
        $di = DI::getInstance();

        $expects = function ($verb) {
            return $verb;
        };

        $di->song = $expects;

        $this->assertEquals($expects, $di->song);
        $this->assertEquals('lovely', $di->song('lovely'));
    }

    //--------------------------------------------------------------------
    
    
}