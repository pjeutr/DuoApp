<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../../assets/img/favicon.ico">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Maasland</title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <!-- CSS Files -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" />
    <link href="/assets/css/fontawesome.min.css" rel="stylesheet" />
    <!-- TODO hamburger, radio en checkbox werken niet bij de lokale fontawesome, daarom de online versie als backup -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />
    <link href="/assets/css/light-bootstrap-dashboard.css?v=2.0.3" rel="stylesheet" />
    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="/assets/css/app.css?2.0.3" rel="stylesheet" />
</head>

<body>
    <div class="wrapper wrapper-full-page">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-transparent navbar-absolute">
            <div class="container">
                <div class="navbar-wrapper">
                    <a class="navbar-brand" href="#">Maasland</a>
                    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" aria-controls="navigation-index" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                        <span class="navbar-toggler-bar burger-lines"></span>
                    </button>
                </div>
                <div class="collapse navbar-collapse justify-content-end" id="navbar">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="/" class="nav-link">
                                <i class="nc-icon nc-chart-pie-35"></i> Dashboard
                            </a>
                        </li>

                        <li class="nav-item  active ">
                            <a href="<?= url_for('login') ?>" class="nav-link">
                                <i class="nc-icon nc-mobile"></i> Login
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        

        <div class="full-page  section-image" data-color="black" data-image="../../assets/img/bg.jpg" ;>

<!-- header part -->

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12 col-sm-12">
                <div class="card">
                    <div class="card-header">
                    </div>
                    <div class="card-body">
                        <div class="card-body table-responsive">

                       
<form class="masterForm" action="<?= url_for('manage/network', 3) ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-3 flex-cell flex-cell" role="cell">Master IP<br>
            <sub>This overules automatic discovery of the Master controller for slaves<br>
            Only use in special Network situation, where multicast doesn't work.</sub>
        </div>
        <div class="flex-row-4 flex-cell" role="cell">
            <input type="text" class="form-control"
                name="master" value="<?= $network["master"] ?>"> 
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
            </button>
        </div>

    </div>
</form>
<form class="networkForm" action="<?= url_for('manage/network', 2) ?>" method="POST">
    <input type="hidden" name="_method" id="_method" value="PUT">

    <div class="flex-table row" role="rowgroup">
        <div class="flex-row-3 flex-cell flex-cell" role="cell">DHCP</div>
        <div class="flex-row-4 flex-cell" role="cell">
            <input class="toggle-dhcp" name="dhcp" type="checkbox" data-toggle="switch" 
            <?= $network["dhcp"] ? 'checked' : ""  ?>
            data-on-color="info" data-off-color="info" data-eye-open-class="fa-toggle-off"  data-eye-close-class="fa-toggle-on">
        </div>
        <div class="flex-row-2 flex-cell" role="cell">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-edit"></i> <?=  L("button_save"); ?>
            </button>
        </div>
    </div>

    <div id="static_config" 
        <?= $network["dhcp"] ? 'style="display: none;"' : ""  ?>
    >

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">IPv4 Address</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="ip" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["ip"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
        </div>

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">Subnet Mask</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="subnet" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["subnet"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
        </div>

        <div class="flex-table row" role="rowgroup">
            <div class="flex-row-3 flex-cell flex-cell" role="cell">Gateway</div>
            <div class="flex-row-4 flex-cell" role="cell">
                <input name="router" type="text" minlength="7" maxlength="15" size="15" 
                value = "<?= $network["router"] ?>"
                pattern="^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])$">
            </div>
        </div>  

    </div>      

</form>
            
                        </div>                                          
                    </div>
                </div>
            </div>               
        </div>
    </div>
</div>
        
<!-- footer part -->

       <footer class="footer">
            <div class="container-fluid">
                <nav>
                    <ul class="footer-menu">
                        <li>
                            <a href="https://www.maaslandgroep.nl/" target="_blank">
                                Company
                            </a>
                        </li>
                        <li>
                            <a href="https://www.maaslandgroep.nl/contact" target="_blank">
                                Contact
                            </a>
                        </li>
                        <li>
                            <a href="https://maaslandserver.com/" target="_blank">
                                Faq
                            </a>
                        </li>
                        <li>
                            <a href="https://www.maaslandgroep.nl/nieuws" target="_blank">
                                Blog
                            </a>
                        </li>
                    </ul>
                    <p class="copyright text-center">
                        ©
                        <script>
                            document.write(new Date().getFullYear())
                        </script>
                        <a href="https://maaslandgroup.com/">Maasland Group</a>, Your Access To Safety. All Rights Reserved.
                    </p>
                </nav>
            </div>
        </footer>
    </div>

</body>
<!--   Core JS Files   -->
<!--   Core JS Files   -->
<script src="/assets/js/core/jquery.3.2.1.min.js" type="text/javascript"></script>
<script src="/assets/js/core/popper.min.js" type="text/javascript"></script>
<script src="/assets/js/core/bootstrap.min.js" type="text/javascript"></script>
<!--  Plugin for Switches, full documentation here: http://www.jque.re/plugins/version3/bootstrap.switch/ -->
<script src="/assets/js/plugins/bootstrap-switch.js"></script>
<!--  Notifications Plugin    -->
<script src="/assets/js/plugins/bootstrap-notify.js"></script>
<!--  Plugin for Date Time Picker and Full Calendar Plugin-->
<script src="/assets/js/plugins/moment.min.js"></script>
<!--  DatetimePicker   -->
<script src="/assets/js/plugins/bootstrap-datetimepicker.js"></script>
<!--  Sweet Alert  -->
<script src="/assets/js/plugins/sweetalert2.min.js" type="text/javascript"></script>
<!--  Tags Input  -->
<script src="/assets/js/plugins/bootstrap-tagsinput.js" type="text/javascript"></script>
<!--  Sliders  -->
<script src="/assets/js/plugins/nouislider.js" type="text/javascript"></script>
<!--  Bootstrap Select  -->
<script src="/assets/js/plugins/bootstrap-selectpicker.js" type="text/javascript"></script>
<!--  jQueryValidate https://jqueryvalidation.org  -->
<script src="/assets/js/plugins/jquery.validate.min.js" type="text/javascript"></script>
<!--  Plugin for the Wizard, full documentation here: https://github.com/VinceG/twitter-bootstrap-wizard -->
<script src="/assets/js/plugins/jquery.bootstrap-wizard.js"></script>
<!--  Bootstrap Table Plugin -->
<script src="/assets/js/plugins/bootstrap-table.js"></script>
<!--  DataTable Plugin -->
<script src="/assets/js/plugins/jquery.dataTables.min.js"></script>
<!--  Full Calendar   -->
<script src="/assets/js/plugins/fullcalendar.min.js"></script>
<!--  JQuery Plugin: WeekDays https://www.jqueryscript.net/time-clock/inline-week-day-picker.html -->
<script src="/assets/js/plugins/jquery-weekdays.js"></script>
<!--  Hide Password  -->
<script src="/assets/js/plugins/bootstrap-show-password.min.js"></script>
<!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
<script src="/assets/js/light-bootstrap-dashboard.js?v=2.0.1" type="text/javascript"></script>
<script src="/assets/js/resource.<?= $_SESSION["lang"] ?>.js?1.5"></script>
<script src="/assets/js/app.js?1.8"></script>
<script type="text/javascript">
    // Content for SweetAlert
    <?= empty($swalMessage) ? '' : 'swal( '.$swalMessage.');' ?>
    <?= isset($flashMessage['swalMessage']) ? 'swal( '.swal_message_success($flashMessage['swalMessage']).');' : '' ?>

    $(document).ready(function() {
        $page = $('.full-page');
        image_src = $page.data('image');

        if (image_src !== undefined) {
            image_container = '<div class="full-page-background" style="background-image: url(' + image_src + ') "/>'
            $page.append(image_container);
        }

        setTimeout(function() {
            // after 1000 ms we add the class animated to the login/register card
            $('.card').removeClass('card-hidden');
        }, 700)
    });

    function networkFormValidation(id) {
        console.log("neworkFormValidation");
        //$("#networkForm").validate();

        var toggle = $("[name='dhcp']").bootstrapSwitch();

        toggle.on("switchChange.bootstrapSwitch", function(event, state) {
            console.log("change="+state);
            if(state){
                $('#static_config').hide();
            } else {
                $('#static_config').show();
            }
        });
        /*
        * Validation docs https://jqueryvalidation.org/documentation/
        */
        $("#networkForm").validate({
            rules: {
                maxLength: 4,
                hostname: {
                    required: true,
                    maxlength: 30
                }
            }
            // ,
            // messages: {
            //     hostname: "Please enter a hostname",
            // },
        });
        $("#masterForm").validate({
            rules: {
                maxLength: 4,
                hostname: {
                    required: true,
                    maxlength: 30
                }
            }
            // ,
            // messages: {
            //     hostname: "Please enter a hostname",
            // },
        });
    }
</script>

</html>
