<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?></title>
	<link rel="apple-touch-icon" sizes="180x180" href="/GRAPHICS/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/GRAPHICS/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/GRAPHICS/favicons/favicon-16x16.png">
	<link rel="manifest" href="/GRAPHICS/favicons/site.webmanifest">
	<link rel="mask-icon" href="/GRAPHICS/favicons/safari-pinned-tab.svg" color="#5bbad5">
	<link rel="shortcut icon" href="/GRAPHICS/favicons/favicon.ico">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="msapplication-config" content="/GRAPHICS/favicons/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.1/css/bulma.min.css">
	
	<style>
	
	.custom {
		font-family: SpecialElite;
		font-weight: bold;
		border: medium solid silver;
		background-color: #D3A13B;
	}
	.custom span {
		color: #FFF9D4;
	}
		
	
	</style>
	<!--SYNTAX FROM https://www.shakzee.com/how-to-add-css-and-js-files-in-codeigniter-4/
	<link rel="stylesheet" href="<!php echo base_url('mybulma/css/mystyles.css'); ?>"> -->
		<?=link_tag('mybulma/css/mystyles.css');?>
		<script src="https://code.jquery.com/jquery-3.5.0.js"></script>

	
</head>
<body>

<nav class="navbar" role="navigation" aria-label="main navigation">
  <div class="custom navbar-brand">
  
	<img src="<?php echo base_url('GRAPHICS/momsLogoImage.jpg')?>">
	
    <a class="navbar-item" href="<?php site_url()?>">
		<span>Mom's Recipes</span>
    </a>
	

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasicExample" class="navbar-menu">
    <div class="navbar-start">
      <a class="navbar-item"  href="<?php echo base_url('home');?>">
        Home
      </a>

       <a class="navbar-item"  href="<?php echo base_url('about')?>">
        About
      </a>

      <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link">
          News
        </a>

        <div class="navbar-dropdown">
          <a class="navbar-item"  href="<?php echo base_url('news')?>">
        News Archive
      </a>
          
          <hr class="navbar-divider">
          <a class="navbar-item" href="<?php echo base_url('news/create')?>">
            Create a News Item
          </a>
        </div>
      </div>
    </div>

    <div class="navbar-end">
      <div class="navbar-item">
        <div class="buttons">
          <a class="button is-primary">
            <strong>Sign up</strong>
          </a>
          <a class="button is-light">
            Log in
          </a>
        </div>
      </div>
    </div>
  </div>
</nav>
