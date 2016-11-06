$(document).ready( function() {
    $('#filetree').fileTree({
        root: '/home5/isahasse/public_html/'
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