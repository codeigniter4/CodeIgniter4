<?php namespace CodeIgniter\Database\Live;

class AliasTest extends \CIDatabaseTestCase
{
    protected $refresh = true;

    protected $seed = 'CITestSeeder';

    public function testAlias()
    {
        $builder = $this->db->table('job j');

        $jobs = $builder
            ->where('j.name', 'Developer')
            ->get();

        $this->assertEquals(1, count($jobs->getResult()));
    }

    //--------------------------------------------------------------------

}
