<?php

namespace App\Controllers;


//use CodeIgniter\Controller;


//CHANGED FROM JUST 'extends Controller' in order to autoload helpers
		
class Momsrecipes extends BaseController
{
	
	//DOESN'T REALLY GO HERE NOW
    public function index()
    {
        
		//THIS HANDLES WHEN PEOPLE JUST GO TO momsrecipes WITHOUT A SPECIFIC PAGE
		//COULD BE A LANDING PAGE OR ANYTHING. nOW JUST GOING TO 'HOME'
		
		$page = 'Mom\'s Recipes';
		
		
		$data['title'] = ucfirst($page); // Capitalize the first letter
		echo view('momsrecipes/widgets/header', $data);
		echo view('momsrecipes/home', $data);
		echo view('momsrecipes/widgets/footer', $data);
    }

    //THIS WORKS FOR BUT HOME PAGE (INDEX) BEING SERVED DIFFERENTLY AT FIRST
	public function view($page = 'home')
	{
		//THIS MEANS IF THERE IS NO PAGE FOR WHAT IS CALLED THEN THROWS EXCEPTION
		if ( ! is_file(APPPATH.'/Views/momsrecipes/'.$page.'.php'))
		{
			// Whoops, we don't have a page for that!
			throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
		}
		
		

		$data['title'] = ucfirst($page); // Capitalize the first letter

		echo view('momsrecipes/widgets/header', $data);
		echo view('momsrecipes/'.$page, $data);
		echo view('momsrecipes/widgets/footer', $data);
	}
}