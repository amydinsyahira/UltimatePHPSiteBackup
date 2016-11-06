<html>
<head>
    <title>Ultimate PHP Site Backup</title>
<link rel="shortcut icon" href="favicon.ico">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="models/js/jqueryFileTree.js" type="text/javascript"></script>
<link href="models/styles/jqueryFileTree.css" rel="stylesheet" type="text/css" media="screen" />

<link href="models/styles/style.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="models/js/tablecloth.js"></script>
<script type="text/javascript" src="models/js/jquery.simpletip-1.3.1.min.js"></script>
<script type="text/javascript">
{literal}
$(document).ready( function() {
    $('#filetree').fileTree({
        root: '{/literal}{if $smarty.const.DEMO}/home5/isahasse/public_html/phpsitebackup/demofiles/{else}{$docroot}{/if}{literal}'
    }, function(file) {
        alert(file);
    });
    
    $(".bset_table_row").simpletip({
        content:'Double click row (or click name) to expand/retract',
        fixed: true,
        position: 'right',
        offset: [20, 0]
    });

    $('.rollover').hover(
        function(){ // Change the input image's source when we "roll on"
            var t = $(this);
            t.attr('src',t.attr('src').replace("_btn", "_btn_down"));
        },
        function(){
            var t= $(this);
            t.attr('src',t.attr('src').replace('_down',''));
        }
        );
    
});
 
function toggleAddBcS(){
    $("#addBcS").animate({
        "height": "toggle"
    }, {
        duration: 1000
    });
}

function toggleConfig(){
    $("#config").animate({
        "height": "toggle"
    }, {
        duration: 1000
    });
}
{/literal}
</script>

    </head>
    <body bgcolor="#ffffff">
<center>
<div id="outer-wrapper">
<center>
<img src="models/images/logo.png" width="471" height="161" alt="Ultimate PHP Site Backup"/>
</center>
