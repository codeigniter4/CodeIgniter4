<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Web Application | todo</title>
        <meta name="description"
              content="app, web app, responsive, admin dashboard, admin, flat, flat ui, ui kit, off screen nav"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/app.v2.css" type="text/css"/>
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/css/font.css" type="text/css"/>
        <!--[if lt IE 9]>
        <script src="/assets/admin/js/ie/respond.min.js" ></script>
        <script src="/assets/admin/js/ie/html5.js" ></script>
        <script src="/assets/admin/js/ie/fix.js" ></script>
        <![endif]-->

        <?php echo link_tag("assets/css/bootstrap.min.css"); ?>

        <script src="<?php echo base_url(); ?>assets/js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>assets/js/bootstrap.min.js" type="text/javascript"></script>

        <script src="<?php echo base_url(); ?>assets/js/jquery.slimscroll.min.js" type="text/javascript"></script>

        <?php echo link_tag("assets/js/datatables/media/datatables.bootstrap.min.css"); ?>
        <script src="<?php echo base_url(); ?>assets/js/datatables/datatables.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/datatables/media/datatables.bootstrap.js" type="text/javascript"></script>
        <script src="<?php echo base_url(); ?>assets/js/admin_functions.js" type="text/javascript"></script>

        <script type="text/javascript">
            var oDataTable;
            $(document).ready(function () {

                oDataTable = $('.datatables').dataTable({
                    //sDom: '<"top"fl<"clear">>rt<"bottom"ip<"clear">>',
                    //bJQueryUI: true,
                    //responsive:true,
                    scrollX: true,
                    sPaginationType: "full_numbers",
                    bStateSave: true,
                    fnStateSave: function (oSettings, oData) {
                        localStorage.setItem('DataTables_' + $(document).getUrlParam("main"), JSON.stringify(oData));
                    },
                    fnStateLoad: function (oSettings) {
                        return JSON.parse(localStorage.getItem('DataTables_' + $(document).getUrlParam("main")));
                    },
//                    oLanguage: {
//                        sUrl: "<?php echo base_url(); ?>assets/js/datatables/media/es.txt"
//                    },
                    fnInitComplete: function (oSettings, json) {
                        if (window.fnInitComplete) {
                            fnInitComplete(oSettings, json);
                        }
                    }
                });

//                $.mask.definitions['~'] = '[+-]';
//                $(".cuit").mask("99-99999999-9");
//                $(".fecha").mask("99/99/9999");
//
//                $("#editform").validate({
                // some general settings
                // errorContainer: "div.container",
                // wrapper : "li",
                // errorLabelContainer : "div.container ul",

                // errorClass : "invalid",
                // errorPlacement : function (error, element) {
                //      error.appendTo(element.parent("td").next("td"));    //Ponerlo en el siguiente td
                //      error.isertAfter(element);
                // },
//                    'onfocusout': function (element) {
//                        this.element(element);
//                    }
//                });

//                $('.fecha').datepicker($.datepicker.regional['es']);

                $(".foco_inicial").focus();


                //para que funcione en los móviles las dos nav-bar
                $("#abrir_principal").click(function () {
                    $(".nav-primary").toggleClass('show');
                });

//                $("#abrir_secundario").click(function () {
//                    $(".navbar-collapse").toggleClass('collapse');
//                });


//                $('.dropdown-toggle').click(function (e) {
//                    //alert($(e).find('.dropdown-menu'));
//                    $(e).toggleClass('dropdown');
//                });

            });

        </script>
    </head>

    <body>
        <section class="vbox">
            <header class="header bg-black navbar navbar-inverse pull-in">
                <div class="navbar-header nav-bar aside dk">
                    <a id="abrir_principal" class="btn btn-link visible-xs" data-toggle="class:show" data-target=".nav-primary"> 
                        <i class="fa fa-bars"></i> </a> 
                    <a href="#" class="nav-brand" data-toggle="fullscreen"><?php echo!empty($general->logo_text) ? $general->logo_text : 'Admin'; ?></a> 
                    <a class="btn btn-link visible-xs" data-toggle="collapse" data-target=".navbar-collapse"> 
                        <i class="fa fa-comment-o"></i> </a></div>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav">
                        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> 
                                <i class="fa fa-flask text-white"></i> <span class="text-white">UI kit</span> <b class="caret"></b>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="buttons.html">Buttons</a></li>
                                <li><a href="icons.html"> <b class="badge pull-right">302</b>Icons </a></li>
                                <li><a href="grid.html">Grid</a></li>
                                <li><a href="widgets.html"> <b class="badge bg-primary pull-right">8</b>Widgets </a></li>
                                <li><a href="components.html"> <b class="badge pull-right">18</b>Components </a></li>
                                <li><a href="list.html">List groups</a></li>
                                <li><a href="table.html">Table</a></li>
                                <li><a href="form.html">Form</a></li>
                                <li><a href="chart.html">Chart</a></li>
                                <li><a href="calendar.html">Fullcalendar</a></li>
                            </ul>
                        </li>
                        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i
                                    class="fa fa-file-text text-white"></i> <span class="text-white">Pages</span> <b
                                    class="caret"></b> </a>
                            <ul class="dropdown-menu">
                                <li><a href="dashboard.html">Dashboard</a></li>
                                <li><a href="dashboard-1.html">Dashboard one</a></li>
                                <li><a href="gallery.html">Gallery</a></li>
                                <li><a href="profile.html">Profile</a></li>
                                <li><a href="blog.html">Blog</a></li>
                                <li><a href="invoice.html">Invoice</a></li>
                                <li><a href="signin.html">Signin page</a></li>
                                <li><a href="signup.html">Signup page</a></li>
                                <li><a href="404.html">404 page</a></li>
                            </ul>
                        </li>
                    </ul>
                    <form class="navbar-form navbar-left m-t-sm" role="search">
                        <div class="form-group">
                            <div class="input-group input-s"><input type="text"
                                                                    class="form-control input-sm no-border dk text-white"
                                                                    placeholder="Search"> <span class="input-group-btn"> <button
                                        type="submit" class="btn btn-sm btn-primary btn-icon"><i class="fa fa-search"></i></button> </span>
                            </div>
                        </div>
                    </form>
                    <ul class="nav navbar-nav navbar-right">
                        <?php if (isset($this->session->userdata['admin_user_name'])) { ?>
                            <li class="hidden-xs"><a href="#" class="dropdown-toggle dk" data-toggle="dropdown"> <i
                                        class="fa fa-bell-o text-white"></i> <span class="badge up bg-danger m-l-n-sm">2</span> </a>
                                <section class="dropdown-menu animated fadeInUp input-s-lg">
                                    <section class="panel bg-white">
                                        <header class="panel-heading"><strong>You have <span class="count-n">2</span> notifications</strong>
                                        </header>
                                        <div class="list-group"><a href="#" class="media list-group-item"> <span
                                                    class="pull-left thumb-sm"> <img src="<?php echo base_url(); ?>assets/images/avatar.jpg" alt=""
                                                                                 class="img-circle"> </span> <span
                                                    class="media-body block m-b-none"> Use awesome animate.css<br> <small
                                                        class="text-muted">28 Aug 13
                                                    </small> </span> </a> <a href="#" class="media list-group-item"> <span
                                                    class="media-body block m-b-none"> 1.0 initial released<br> <small
                                                        class="text-muted">27 Aug 13
                                                    </small> </span> </a></div>
                                        <footer class="panel-footer text-sm"><a href="#" class="pull-right"><i
                                                    class="fa fa-cog"></i></a> <a href="#">See all the notifications</a></footer>
                                    </section>
                                </section>
                            </li>

                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle aside-sm dker" data-toggle="dropdown"> 
                                    <span class="thumb-sm avatar pull-left m-t-n-xs m-r-xs"> <img src="<?php echo base_url(); ?>assets/images/avatar.jpg"> </span>
                                    <?php echo $this->session->userdata['admin_user_name'] ?> 
                                    <b class="caret"></b> 
                                </a>
                                <ul class="dropdown-menu animated fadeInLeft">
                                    <li><a href="#">Settings</a></li>
                                    <li><a href="profile.html">Profile</a></li>
                                    <li><a href="#"> <span class="badge bg-danger pull-right">3</span> Notifications </a></li>
                                    <li><a href="docs.html">Help</a></li>
                                    <li><a href="admin/users/logout">Logout</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="<?php echo base_url(); ?>admin/general/lockme" data-toggle="ajaxModal" class="">
                                    <i class="fa fa-power-off"></i>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            </header>
            <section>
                <section class="hbox stretch">
                    <aside class="aside bg-dark dk" id="nav">
                        <section class="vbox">
                            <section class="scrollable">
                                <div class="slim-scroll" data-height="auto" data-disable-fade-out="true" data-distance="0"
                                     data-size="5px">
                                    <nav class="nav-primary hidden-xs" data-ride="collapse">
                                        <ul class="nav">
                                            <li><a href="index"> <i class="fa fa-eye"></i> <span>Discover</span> </a></li>
                                            <li><a href="#" class="dropdown-toggle" data-toggle="dropdown"> 
                                                    <span class="pull-right auto"> 
                                                        <i class="fa fa-angle-down text"></i> 
                                                        <i class="fa fa-angle-up text-active"></i> 
                                                    </span> 
                                                    <i class="fa fa-file-text"></i> <span>Pages</span> 
                                                </a>
                                                <ul class="nav none dker dropdown-menu">
                                                    <li><a href="dashboard.html">Dashboard</a></li>
                                                    <li><a href="dashboard-1.html">Dashboard one</a></li>
                                                    <li><a href="dashboard-2.html">Dashboard layout</a></li>
                                                    <li><a href="analysis.html">Analysis</a></li>
                                                    <li><a href="master.html">Master</a></li>
                                                    <li><a href="maps.html">Maps</a></li>
                                                    <li><a href="gallery.html">Gallery</a></li>
                                                    <li><a href="profile.html">Profile</a></li>
                                                </ul>
                                            </li>
                                            <li class="<?php echo in_array('dashboard', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/dashboard'); ?>"><span>Statistics</span></a></li>
                                            <li class="<?php echo in_array('general', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/general'); ?>"><span>General info</span></a></li>
                                            <li class="<?php echo in_array('categories', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/categories'); ?>"><span>Categories</span></a></li>
                                            <li class="<?php echo in_array('products', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/products'); ?>"><span>Products</span></a></li>
                                            <li class="<?php echo in_array('orders', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/orders'); ?>"><span>Orders</span></a></li>
                                            <li class="<?php echo in_array('pages', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/pages'); ?>"><span>Pages</span></a></li>
                                            <li class="<?php echo in_array('filters', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/filters'); ?>"><span>Filters</span></a></li>
                                            <li class="<?php echo in_array('comments', $segments) ? 'active' : ''; ?>"><a href="<?php echo site_url('admin/comments'); ?>"><span>Comments</span></a></li>
                                            <li class="confirm"><a href="<?php echo site_url('admin/users/logout'); ?>"><span>Logout</span></a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </section>
                        </section>
                    </aside>
                    <section>
                        <div class="wrapper" style="min-height: 100%; height: auto; height: 100%;">

                            <div class="bg-white row"><p></p></div>

                            <div class="row">
                                <div class="col-lg-12">
                                    <?php if (session('message')) { ?>
                                        <div class="alert alert-success">
                                            <button type="button" class="close" data-dismiss="alert">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <i class="fa fa-check-sign"></i> <?= session('message'); ?>
                                        </div>
                                    <?php } ?>

                                    <?php
                                    if (session('errors')) :
                                        foreach (session('errors') as $error) :
                                            ?>
                                            <div class="alert alert-danger">
                                                <button type="button" class="close" data-dismiss="alert">
                                                    <i class="fa fa-times"></i>
                                                </button>   
                                                <i class="fa fa-ban-circle"></i> 
                                                <?= $error; ?>
                                            </div>
                                            <?php
                                        endforeach;
                                    endif;
                                    ?>
                                </div>
                            </div>
<?php echo $content; ?>
                            <div class="push"></div>
                        </div>
                        <footer class="footer bg-dark" style=""><p>This is a footer</p></footer>
                    </section>
                </section>
            </section>
        </section>

    </body>
</html>