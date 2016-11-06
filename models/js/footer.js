//Sets up ajax

$.ajaxSetup({
    cache: false
});
var ajax_load = "<div class=\"solid-yellow\"><img src='models/images/loadingSmall.gif' alt='loading...' /></div>";
var loadUrl = "do.php";

function testFTP() {
    var loadUrl = "do.php?ftpconfig";

    var ftph = $("input[name=ftphost]").val();
    var ftpd = $("input[name=ftpdir]").val();
    var ftpu = $("input[name=ftpusername]").val();
    var ftpp = $("input[name=ftppassword]").val();

    $("#msgs_ftp")
        .html(ajax_load)
        .load(loadUrl, {ftphost: ftph, ftpdir: ftpd, ftpusername: ftpu, ftppass: ftpp});
}

function doRunBcS(id, name) {

    name = escape(name);

    var ajax_load = "<div class=\"solid-yellow\"><img src='models/images/loadingSmall.gif' alt='loading...' />Running backup set...this may take upto 5 minutes. If you close this window, the backup zip will still be created in the background. View the log file later on to check for errors.</div>";

    if (confirm("Do you want to run backup set '" + name + "'? This may take upto 5 minutes, depending on how large it is.")) {
        $("#" + id + "_runs").hide();

        $("#msgs")
        .html(ajax_load)
        .load(loadUrl, "runbcs=" + id);
    }


    getBcSRuns(id);
}

function doDelBcS(id, name) {

    if (confirm("Hey, are you sure you want to PERMENANTLY DELETE '" + name + "'? This does not remove the backup zips which have already been created.")) {

        $("#msgs")
        .html(ajax_load)
        .load(loadUrl, "delbcs=" + id);

        $("#" + id).remove();
        $("#" + id + "_runs").remove();
    }
}

function doDelRun(id, bset) {
    if (confirm("Are you sure you want to PERMENANTLY DELETE this backup file?")) {

        $("#" + bset + "_msgs")
        .html(ajax_load)
        .load(loadUrl, "delrun=" + id);

        $("#" + id).remove();
    }
}

function openEditBcS(id, name, schedule, notify, email_backup, ftp_upload, store_local) {
    $("#editBcS_form_notify").removeAttr("checked");
    $("#editBcS_form_email_backup").removeAttr("checked");
    $("#editBcS_form_ftp_upload").removeAttr("checked");
    $("#editBcS_form_store_local").removeAttr("checked");

    $("#editBcS:hidden").show("slow");

    $("#editBcS_form").attr('action', 'do.php?editbcs&id=' + id);
    $("#editBcS_form_id").attr('value', id);
    $("#editBcS_form_name").attr('value', name);
    $("#editBcS_form_schedule option[value=" + schedule + "]").attr('selected', 'selected');
    if (notify == 1) {
        $("#editBcS_form_notify").attr('checked', 'true');
    }
    if (email_backup == 1) {
        $("#editBcS_form_email_backup").attr('checked', 'true');
    }
    if (ftp_upload == 1) {
        $("#editBcS_form_ftp_upload").attr('checked', 'true');
    }
    if (store_local == 1) {
        $("#editBcS_form_store_local").attr('checked', 'true');
    }
}

function hideEditBcS() {
    $("#editBcS").hide("slow");
}

function getBcSRuns(id) {
    var ajax_load = "<img src='models/images/loadingSmall.gif' alt='loading...' />"
    var loadUrl = "do.php";

    $("#" + id + "_runs")
    .toggle()
    .html(ajax_load)
    .load(loadUrl, "bcsruns=" + id);

}

function testRemoteDb(dbfield) {
    var ajax_load = "<div class=\"solid-yellow\"><img src='models/images/loadingSmall.gif' alt='loading...' />Testing database connection</div>"
    var loadUrl = "do.php";
    var testString = escape(dbfield.prev().val());

    $("#msgs_db")
    .html(ajax_load)
    .load(loadUrl, "testdb=" + testString);
}

function addRemoteDbField(location) {
    location.before('<input name="db[]" class="dbinput" type="text" size="65" value="\'mysqlhost\':\'username\':\'password\':\'dbname\'" /><span class="btntext" onclick="testRemoteDb($(this));return false;">Test this DB</span>');
}